<script setup lang="ts">
import { computed, nextTick, onMounted, ref, useId } from "vue"

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
 *   - un fieldset radiogroup pour le type de projet (radio-cards visuelles
 *     mais `<input type="radio">` réels : la case native est masquée
 *     visuellement, un dot dessiné en CSS reflète l'état `:checked`) ;
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
  /**
   * Aligné avec `TURNSTILE_ENABLED` côté API. Quand `false`, le widget
   * n'appelle jamais challenges.cloudflare.com et émet un token factice.
   * Défaut : `false` (aucun script tiers tant que la prod n'active pas
   * explicitement).
   */
  turnstileEnabled?: boolean
  privacyNoteId?: string
}

const props = withDefaults(defineProps<Props>(), {
  turnstileEnabled: false,
  privacyNoteId: undefined,
})

const form = useContactForm({ endpoint: props.endpoint })
const titleId = useId()
const privacyId = props.privacyNoteId ?? `${useId()}-privacy`

// Signal d'hydratation exposé via `data-hydrated` sur le <form>. Sert de
// synchronisation fiable pour l'E2E : le hook `onMounted` du composant
// parent est le SEUL point où l'on peut garantir que Vue a attaché ses
// listeners (`@submit.prevent`, `v-model`) — les enfants (TurnstileWidget)
// montent AVANT le parent, donc leur propre signal (dev-notice) ne prouve
// pas que le form parent est hydraté. Voir DEV-045.
const isHydrated = ref(false)
onMounted(() => {
  isHydrated.value = true
})

const statusRef = ref<InstanceType<typeof ContactFormStatus> | null>(null)
const nameFieldRef = ref<InstanceType<typeof ContactFormField> | null>(null)
const emailFieldRef = ref<InstanceType<typeof ContactFormField> | null>(null)
const messageFieldRef = ref<InstanceType<typeof ContactFormField> | null>(null)
const consentInputRef = ref<HTMLInputElement | null>(null)
const turnstileRef = ref<InstanceType<typeof TurnstileWidget> | null>(null)

const projectOptions: readonly {
  value: (typeof PROJECT_TYPES)[number]
  label: string
  full?: boolean
}[] = [
  { value: "refonte", label: "Refonte d'un site existant" },
  { value: "creation", label: "Création d'un nouveau site" },
  { value: "seo", label: "SEO / visibilité" },
  { value: "audit", label: "Audit ou conseil" },
  { value: "autre", label: "Autre / je ne sais pas encore", full: true },
]

const MESSAGE_MIN = 20

// Compteur de caractères — reproduit le pattern du mock : « N / 20 min. »
// tant que le seuil n'est pas atteint, puis « N caractères » en vert quand
// le message devient soumettable. Purement affichage : la validation reste
// dans le composable.
const messageLength = computed(() => form.values.message.length)
const messageReached = computed(() => messageLength.value >= MESSAGE_MIN)
const messageCountLabel = computed(() =>
  messageReached.value
    ? `${messageLength.value} caractères`
    : `${messageLength.value} / ${MESSAGE_MIN} min.`,
)

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
    case "temporary_error":
      return "Service momentanément indisponible"
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
    case "temporary_error":
      return "Le service est momentanément indisponible. Votre message n'a pas été envoyé. Merci de réessayer plus tard."
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
    :data-hydrated="isHydrated || undefined"
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
        class="contact-form__field contact-form__field--name"
        label="Votre nom"
        name="name"
        type="text"
        autocomplete="name"
        required
        placeholder="Jean Dupont"
        :maxlength="120"
        :minlength="2"
        :error="form.fieldErrors.value.name"
      />

      <ContactFormField
        ref="emailFieldRef"
        v-model="form.values.email"
        class="contact-form__field contact-form__field--email"
        label="Votre adresse email"
        name="email"
        type="email"
        autocomplete="email"
        required
        placeholder="vous@entreprise.fr"
        :maxlength="254"
        hint="Nous répondons à cette adresse — jamais partagée."
        :error="form.fieldErrors.value.email"
      />

      <ContactFormField
        v-model="form.values.company"
        class="contact-form__field contact-form__field--company"
        label="Société"
        name="company"
        type="text"
        autocomplete="organization"
        placeholder="Nom de votre entreprise"
        optional-label
        :maxlength="160"
        :error="form.fieldErrors.value.company"
      />

      <ContactFormField
        v-model="form.values.telephone"
        class="contact-form__field contact-form__field--telephone"
        label="Téléphone"
        name="telephone"
        type="tel"
        autocomplete="tel"
        placeholder="06 12 34 56 78"
        optional-label
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
            :class="{ 'contact-form__project-option--full': option.full }"
            :data-checked="form.values.projectType === option.value || undefined"
          >
            <input
              v-model="form.values.projectType"
              type="radio"
              name="projectType"
              class="contact-form__project-input"
              :value="option.value"
            >
            <span class="contact-form__project-dot" aria-hidden="true" />
            <span class="contact-form__project-label">{{ option.label }}</span>
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
        class="contact-form__field contact-form__field--message"
        label="Votre message"
        name="message"
        type="textarea"
        required
        :maxlength="4000"
        :minlength="MESSAGE_MIN"
        :rows="6"
        placeholder="Décrivez votre besoin, votre contexte ou votre objectif…"
        hint="20 caractères minimum."
        :error="form.fieldErrors.value.message"
      >
        <template #label-append>
          <span
            class="contact-form__counter"
            :data-reached="messageReached || undefined"
            aria-hidden="true"
          >{{ messageCountLabel }}</span>
        </template>
      </ContactFormField>

      <div class="contact-form__consent" :data-invalid="Boolean(form.fieldErrors.value.consent) || undefined">
        <label class="contact-form__consent-label">
          <input
            ref="consentInputRef"
            v-model="form.values.consent"
            type="checkbox"
            name="consent"
            class="contact-form__consent-input"
            required
            :aria-invalid="Boolean(form.fieldErrors.value.consent) || undefined"
          >
          <span class="contact-form__consent-box" aria-hidden="true" />
          <span class="contact-form__consent-text">
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
        :enabled="turnstileEnabled"
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
        :disabled="form.isSubmitting.value"
        :loading="form.isSubmitting.value"
      >
        {{ form.isSubmitting.value ? "Envoi en cours…" : "Envoyer le message" }}
        <template #icon>→</template>
      </BaseButton>
      <span class="contact-form__actions-hint">
        Un premier échange sans engagement.
      </span>
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
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: var(--space-5);
  }

  .contact-form__project,
  .contact-form__field--message,
  .contact-form__consent,
  .contact-form__honeypot {
    grid-column: 1 / -1;
  }
}

/*
 * Fieldset « type de projet » — cadre allégé, radio-cards visuelles ;
 * la case radio native est masquée pour l'affichage mais reste dans
 * l'accessibility tree (checkable au clavier, focusable, exposée à AT).
 */
.contact-form__project {
  border: 0;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.contact-form__project-legend {
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 0.875rem;
  color: var(--text-secondary);
  padding: 0;
  margin-bottom: var(--space-2);
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

  .contact-form__project-option--full {
    grid-column: 1 / -1;
  }
}

.contact-form__project-option {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.6875rem;
  padding: 0.875rem var(--space-4);
  background: #fcfbf8;
  border: 1px solid var(--border-default);
  border-radius: var(--radius-md);
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  color: var(--text-primary);
  cursor: pointer;
  min-height: var(--touch-target-min);
  transition:
    border-color var(--duration-fast) var(--ease-out),
    background-color var(--duration-fast) var(--ease-out),
    box-shadow var(--duration-fast) var(--ease-out);
}

.contact-form__project-option:hover {
  border-color: var(--color-petrol);
}

.contact-form__project-option[data-checked] {
  border-color: var(--color-petrol);
  background: #f1f5f3;
  box-shadow: 0 0 0 3px rgba(12, 91, 87, 0.12);
}

/*
 * Radio native masquée visuellement mais présente : elle porte encore le
 * focus, la sélection clavier et la sémantique ARIA du groupe.
 */
.contact-form__project-input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
  width: 1px;
  height: 1px;
}

.contact-form__project-input:focus-visible + .contact-form__project-dot {
  box-shadow: 0 0 0 3px rgba(46, 134, 217, 0.35);
}

.contact-form__project-dot {
  flex: none;
  width: 1.125rem;
  height: 1.125rem;
  border-radius: 50%;
  border: 2px solid rgba(22, 25, 28, 0.28);
  transition:
    border var(--duration-fast) var(--ease-out),
    background-color var(--duration-fast) var(--ease-out);
  /*
   * Overlay purement décoratif : on laisse le clic traverser vers le
   * <label> parent, qui active alors la radio native (position: absolute,
   * pointer-events: none). Sans ceci, un clic tombant pile sur la pastille
   * est capté par ce span et ne déclenche pas la sélection.
   */
  pointer-events: none;
}

.contact-form__project-option[data-checked] .contact-form__project-dot {
  border: 5px solid var(--color-petrol);
}

.contact-form__project-label {
  line-height: 1.4;
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

/*
 * Compteur de caractères — affiché aligné à droite du label du message
 * via le slot `#label-append` de ContactFormField. `aria-hidden` car le
 * hint textuel du champ (« 20 caractères minimum. ») porte déjà la même
 * information pour l'accessibilité.
 */
.contact-form__counter {
  font-family: var(--font-family-mono, ui-monospace, "Space Mono", monospace);
  font-size: 0.75rem;
  font-weight: var(--font-weight-body-strong);
  color: var(--text-muted);
  letter-spacing: 0.02em;
  transition: color var(--duration-fast) var(--ease-out);
}

.contact-form__counter[data-reached] {
  color: var(--color-petrol);
}

/*
 * Consentement — checkbox stylisée façon carte, sans jamais casser
 * l'input natif (garde `input[name=consent][type=checkbox]` pour les
 * tests unitaires et les lecteurs d'écran).
 */
.contact-form__consent {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.contact-form__consent-label {
  display: flex;
  gap: var(--space-3);
  align-items: flex-start;
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  line-height: 1.5;
  color: var(--text-secondary);
  cursor: pointer;
  min-height: var(--touch-target-min);
  padding-block: 0.125rem;
}

.contact-form__consent-input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
  width: 1px;
  height: 1px;
}

.contact-form__consent-box {
  flex: none;
  width: 1.375rem;
  height: 1.375rem;
  margin-top: 0.0625rem;
  border-radius: var(--radius-sm);
  border: 1px solid rgba(22, 25, 28, 0.3);
  background: #fcfbf8;
  color: var(--color-cream);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8125rem;
  transition:
    background-color var(--duration-fast) var(--ease-out),
    border-color var(--duration-fast) var(--ease-out);
  /*
   * Voir la note sur .contact-form__project-dot : la case décorative
   * doit laisser passer le clic au <label> parent pour cocher la
   * checkbox native masquée.
   */
  pointer-events: none;
}

.contact-form__consent-input:checked + .contact-form__consent-box {
  background: var(--color-petrol);
  border-color: var(--color-petrol);
}

.contact-form__consent-input:checked + .contact-form__consent-box::after {
  content: "✓";
}

.contact-form__consent-input:focus-visible + .contact-form__consent-box {
  box-shadow: 0 0 0 3px rgba(46, 134, 217, 0.35);
  outline: none;
}

.contact-form__consent[data-invalid] .contact-form__consent-box {
  border-color: var(--color-status-error);
  box-shadow: 0 0 0 3px rgba(178, 58, 46, 0.2);
}

.contact-form__consent-text {
  flex: 1;
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
  align-items: center;
  gap: var(--space-4);
}

.contact-form__actions-hint {
  font-family: var(--font-family-body);
  font-size: 0.8125rem;
  color: var(--text-muted);
}

@media (max-width: 559px) {
  .contact-form__actions {
    gap: var(--space-3);
  }
  .contact-form__actions :deep(.base-button) {
    width: 100%;
    justify-content: center;
  }
  .contact-form__actions-hint {
    width: 100%;
  }
}

.contact-form__privacy {
  margin: 0;
  padding-top: var(--space-4);
  border-top: 1px solid rgba(22, 25, 28, 0.1);
  font-family: var(--font-family-body);
  font-size: 0.75rem;
  line-height: 1.6;
  color: var(--text-muted);
  max-width: 68ch;
}
</style>
