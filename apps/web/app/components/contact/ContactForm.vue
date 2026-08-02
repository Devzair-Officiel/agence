<script setup lang="ts">
import { computed, nextTick, ref, useId } from "vue"

import BaseButton from "~/components/base/BaseButton.vue"
import ContactFormField from "~/components/contact/ContactFormField.vue"
import ContactFormStatus from "~/components/contact/ContactFormStatus.vue"
import TurnstileWidget from "~/components/contact/TurnstileWidget.vue"
import { useContactForm } from "~/composables/useContactForm"
import type { PROJECT_TYPES } from "~/types/contact"

/**
 * Formulaire de contact accessible — orchestrateur.
 *
 * Structure sémantique :
 *   - <form aria-labelledby> avec un titre invisible pour lecteur d'écran ;
 *   - un fieldset radiogroup pour le type de projet (labels visibles) ;
 *   - un honeypot `website` (name obligatoire, positionné hors flux, aria-hidden) ;
 *   - un widget Turnstile rendu client-only avec fallback dev automatique ;
 *   - une seule zone d'état (`ContactFormStatus`) qui bascule succès/erreur.
 *
 * Focus management :
 *   - après succès : focus sur le bandeau succès (`role="status"`) ;
 *   - après erreur globale : focus sur le bandeau alerte (`role="alert"`) ;
 *   - après erreurs de validation : focus sur le premier champ invalide.
 *
 * Le composable `useContactForm` héberge toute la logique : validation client
 * minimale, appel `fetch`, mapping des réponses HTTP et gestion du token.
 */

interface Props {
  endpoint: string
  turnstileSiteKey: string
  privacyNoteId?: string
}

const props = withDefaults(defineProps<Props>(), {
  privacyNoteId: undefined,
})

const form = useContactForm({ endpoint: props.endpoint })
const titleId = useId()
const privacyId = props.privacyNoteId ?? `${useId()}-privacy`

const statusRef = ref<InstanceType<typeof ContactFormStatus> | null>(null)
const nameFieldRef = ref<InstanceType<typeof ContactFormField> | null>(null)
const emailFieldRef = ref<InstanceType<typeof ContactFormField> | null>(null)
const messageFieldRef = ref<InstanceType<typeof ContactFormField> | null>(null)
const consentInputRef = ref<HTMLInputElement | null>(null)
const turnstileRef = ref<InstanceType<typeof TurnstileWidget> | null>(null)

const projectOptions: readonly { value: (typeof PROJECT_TYPES)[number]; label: string }[] = [
  { value: "refonte", label: "Refonte d'un site existant" },
  { value: "creation", label: "Création d'un nouveau site" },
  { value: "seo", label: "SEO / visibilité" },
  { value: "audit", label: "Audit ou conseil" },
  { value: "autre", label: "Autre / je ne sais pas encore" },
]

const globalErrorTitle = computed(() => {
  const code = form.globalError.value?.code
  switch (code) {
    case "rate_limited":
      return "Trop de tentatives"
    case "turnstile_rejected":
      return "Vérification anti-bot refusée"
    case "payload_too_large":
      return "Message trop long"
    case "origin_not_allowed":
      return "Requête refusée"
    case "network_error":
      return "Connexion impossible"
    default:
      return "Envoi impossible"
  }
})

const globalErrorMessage = computed(() => {
  const err = form.globalError.value
  if (!err) return ""
  switch (err.code) {
    case "rate_limited": {
      const seconds = err.retryAfter ?? 60
      return `Merci de patienter environ ${seconds} secondes avant de renvoyer votre message.`
    }
    case "turnstile_rejected":
      return "La vérification anti-bot a échoué. Rechargez la protection puis renvoyez votre message."
    case "payload_too_large":
      return "Votre message dépasse la taille autorisée. Merci d'abréger le contenu."
    case "origin_not_allowed":
      return "Votre navigateur n'a pas été reconnu comme un visiteur légitime. Merci de recharger la page."
    case "network_error":
      return "Aucune réponse du serveur. Vérifiez votre connexion et réessayez."
    case "validation_failed":
      return "Certaines informations sont incorrectes ou manquantes. Corrigez les champs signalés ci-dessous."
    default:
      return "Une erreur inattendue est survenue. Merci de réessayer dans un instant."
  }
})

function onTurnstileSuccess(token: string): void {
  form.setTurnstileToken(token)
}

function onTurnstileFailure(): void {
  form.setTurnstileToken(null)
}

async function focusFirstInvalidField(): Promise<void> {
  await nextTick()
  const errors = form.fieldErrors.value
  if (errors.name && nameFieldRef.value?.$el instanceof HTMLElement) {
    nameFieldRef.value.$el.querySelector<HTMLInputElement>("input, textarea")?.focus()
    return
  }
  if (errors.email && emailFieldRef.value?.$el instanceof HTMLElement) {
    emailFieldRef.value.$el.querySelector<HTMLInputElement>("input, textarea")?.focus()
    return
  }
  if (errors.message && messageFieldRef.value?.$el instanceof HTMLElement) {
    messageFieldRef.value.$el.querySelector<HTMLTextAreaElement>("textarea")?.focus()
    return
  }
  if (errors.consent && consentInputRef.value) {
    consentInputRef.value.focus()
  }
}

async function focusStatusBanner(): Promise<void> {
  await nextTick()
  const el = (statusRef.value?.$el ?? null) as HTMLElement | null
  el?.focus()
}

async function onSubmit(): Promise<void> {
  const result = await form.submit()
  if (result.status === "accepted") {
    await focusStatusBanner()
    // Après succès, on demande un nouveau token pour un envoi ultérieur.
    turnstileRef.value?.reset()
    return
  }
  if (result.status === "error" && result.code === "validation_failed") {
    await focusFirstInvalidField()
    return
  }
  // Erreurs globales (network, 403, 413, 429) : focus sur le bandeau d'alerte.
  await focusStatusBanner()
  // Un token peut être invalidé (Turnstile refused) : on redemande.
  if (result.status === "error" && result.code === "turnstile_rejected") {
    turnstileRef.value?.reset()
  }
}
</script>

<template>
  <form
    class="contact-form"
    novalidate
    :aria-labelledby="titleId"
    :aria-describedby="privacyId"
    @submit.prevent="onSubmit"
  >
    <h3 :id="titleId" class="contact-form__sr-title">
      Formulaire de contact
    </h3>

    <div
      v-if="form.status.value === 'success' && form.successRequestId.value"
      class="contact-form__status"
    >
      <ContactFormStatus
        ref="statusRef"
        variant="success"
        title="Message envoyé"
        message="Nous revenons vers vous rapidement. Merci pour votre demande."
        :request-id="form.successRequestId.value"
      />
    </div>

    <div
      v-else-if="form.status.value === 'error' && form.globalError.value"
      class="contact-form__status"
    >
      <ContactFormStatus
        ref="statusRef"
        variant="error"
        :title="globalErrorTitle"
        :message="globalErrorMessage"
        :request-id="form.globalError.value.requestId"
      />
    </div>

    <fieldset class="contact-form__grid" :disabled="form.isSubmitting.value">
      <legend class="contact-form__sr-title">Vos informations</legend>

      <ContactFormField
        ref="nameFieldRef"
        v-model="form.values.name"
        label="Votre nom"
        name="name"
        type="text"
        autocomplete="name"
        required
        :maxlength="120"
        :minlength="2"
        :error="form.fieldErrors.value.name"
      />

      <ContactFormField
        ref="emailFieldRef"
        v-model="form.values.email"
        label="Votre adresse email"
        name="email"
        type="email"
        autocomplete="email"
        required
        :maxlength="254"
        hint="Nous répondons à cette adresse — jamais partagée."
        :error="form.fieldErrors.value.email"
      />

      <ContactFormField
        v-model="form.values.company"
        label="Société (optionnel)"
        name="company"
        type="text"
        autocomplete="organization"
        :maxlength="160"
        :error="form.fieldErrors.value.company"
      />

      <ContactFormField
        v-model="form.values.telephone"
        label="Téléphone (optionnel)"
        name="telephone"
        type="tel"
        autocomplete="tel"
        :maxlength="40"
        pattern="[0-9 +().\-]{4,40}"
        hint="Chiffres, espaces et + ( ) . - uniquement."
        :error="form.fieldErrors.value.telephone"
      />

      <fieldset
        class="contact-form__project"
        :aria-describedby="form.fieldErrors.value.projectType ? 'contact-project-error' : undefined"
      >
        <legend class="contact-form__project-legend">Type de projet</legend>
        <div class="contact-form__project-options">
          <label
            v-for="option in projectOptions"
            :key="option.value"
            class="contact-form__project-option"
          >
            <input
              v-model="form.values.projectType"
              type="radio"
              name="projectType"
              :value="option.value"
            >
            <span>{{ option.label }}</span>
          </label>
        </div>
        <p
          v-if="form.fieldErrors.value.projectType"
          id="contact-project-error"
          class="contact-form__project-error"
        >
          {{ form.fieldErrors.value.projectType }}
        </p>
      </fieldset>

      <ContactFormField
        ref="messageFieldRef"
        v-model="form.values.message"
        label="Votre message"
        name="message"
        type="textarea"
        required
        :maxlength="4000"
        :minlength="20"
        :rows="6"
        hint="Décrivez votre besoin, contexte ou objectif — 20 caractères minimum."
        :error="form.fieldErrors.value.message"
      />

      <div class="contact-form__consent" :data-invalid="Boolean(form.fieldErrors.value.consent) || undefined">
        <label class="contact-form__consent-label">
          <input
            ref="consentInputRef"
            v-model="form.values.consent"
            type="checkbox"
            name="consent"
            required
            :aria-invalid="Boolean(form.fieldErrors.value.consent) || undefined"
          >
          <span>
            J'accepte que Devzair utilise ces informations pour répondre à ma
            demande. Aucune donnée n'est transmise à un tiers.
          </span>
        </label>
        <p v-if="form.fieldErrors.value.consent" class="contact-form__consent-error">
          {{ form.fieldErrors.value.consent }}
        </p>
      </div>

      <div class="contact-form__honeypot" aria-hidden="true">
        <label>
          Ne remplissez pas ce champ.
          <input
            v-model="form.values.website"
            type="text"
            name="website"
            tabindex="-1"
            autocomplete="off"
          >
        </label>
      </div>
    </fieldset>

    <ClientOnly>
      <TurnstileWidget
        ref="turnstileRef"
        :site-key="turnstileSiteKey"
        theme="auto"
        @success="onTurnstileSuccess"
        @error="onTurnstileFailure"
        @expired="onTurnstileFailure"
        @timeout="onTurnstileFailure"
      />
    </ClientOnly>

    <div class="contact-form__actions">
      <BaseButton
        type="submit"
        variant="primary"
        :disabled="!form.canSubmit.value"
        :loading="form.isSubmitting.value"
      >
        {{ form.isSubmitting.value ? "Envoi en cours…" : "Envoyer le message" }}
      </BaseButton>
    </div>

    <p :id="privacyId" class="contact-form__privacy">
      Vos données sont traitées par Devzair pour répondre à votre demande.
      Base légale : votre consentement (RGPD, art. 6-1-a). Conservation :
      trente-six mois après le dernier contact, sauf demande de suppression.
      Vous pouvez exercer vos droits d'accès, rectification et suppression à
      tout moment en nous écrivant.
    </p>
  </form>
</template>

<style scoped>
.contact-form {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
  width: 100%;
}

.contact-form__sr-title {
  position: absolute;
  clip: rect(0 0 0 0);
  clip-path: inset(50%);
  width: 1px;
  height: 1px;
  overflow: hidden;
  white-space: nowrap;
  border: 0;
  padding: 0;
  margin: -1px;
}

.contact-form__status {
  margin-bottom: var(--space-2);
}

.contact-form__grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-5);
  padding: 0;
  border: 0;
  margin: 0;
}

@media (min-width: 720px) {
  .contact-form__grid {
    grid-template-columns: 1fr 1fr;
  }
  .contact-form__grid > :nth-child(5),
  .contact-form__grid > :nth-child(6),
  .contact-form__grid > :nth-child(7) {
    grid-column: 1 / -1;
  }
}

.contact-form__project {
  border: 1px solid var(--border-inverse);
  border-radius: var(--radius-md);
  padding: var(--space-4);
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  margin: 0;
}

.contact-form__project-legend {
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 0.9375rem;
  color: var(--text-inverse);
  padding: 0 var(--space-2);
}

.contact-form__project-options {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-2);
}

@media (min-width: 640px) {
  .contact-form__project-options {
    grid-template-columns: 1fr 1fr;
  }
}

.contact-form__project-option {
  display: flex;
  align-items: flex-start;
  gap: var(--space-2);
  font-size: 0.9375rem;
  color: var(--text-inverse);
  padding: var(--space-2);
  border-radius: var(--radius-sm);
  cursor: pointer;
  min-height: var(--touch-target-min);
}

.contact-form__project-option:hover {
  background-color: var(--border-inverse);
}

.contact-form__project-option input[type="radio"] {
  margin-top: 0.2rem;
  accent-color: var(--color-devzair-blue);
}

.contact-form__project-option input[type="radio"]:focus-visible {
  outline: 2px solid var(--color-devzair-blue);
  outline-offset: 3px;
}

.contact-form__project-error {
  margin: 0;
  font-size: 0.8125rem;
  font-weight: var(--font-weight-body-strong);
  color: var(--color-cream);
  background-color: var(--color-status-error);
  padding: 0.375rem 0.5rem;
  border-radius: var(--radius-sm);
}

.contact-form__consent {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.contact-form__consent-label {
  display: flex;
  gap: var(--space-3);
  align-items: flex-start;
  font-size: 0.9375rem;
  line-height: 1.5;
  color: var(--text-inverse);
  cursor: pointer;
}

.contact-form__consent-label input[type="checkbox"] {
  margin-top: 0.25rem;
  accent-color: var(--color-devzair-blue);
  min-width: 1.125rem;
  min-height: 1.125rem;
}

.contact-form__consent-label input[type="checkbox"]:focus-visible {
  outline: 2px solid var(--color-devzair-blue);
  outline-offset: 3px;
}

.contact-form__consent-error {
  margin: 0;
  font-size: 0.8125rem;
  font-weight: var(--font-weight-body-strong);
  color: var(--color-cream);
  background-color: var(--color-status-error);
  padding: 0.375rem 0.5rem;
  border-radius: var(--radius-sm);
}

/*
 * Honeypot : hors flux visible, hors du tabindex, `aria-hidden` sur le
 * conteneur. On évite `display:none` (les bots plus malins sautent ces
 * champs) au profit d'un `position: absolute` très déporté.
 */
.contact-form__honeypot {
  position: absolute;
  left: -10000px;
  width: 1px;
  height: 1px;
  overflow: hidden;
}

.contact-form__actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-3);
}

.contact-form__privacy {
  margin: 0;
  font-size: 0.8125rem;
  line-height: 1.5;
  color: var(--text-inverse-muted);
  max-width: 60ch;
}
</style>
