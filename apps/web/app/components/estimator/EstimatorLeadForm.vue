<script setup lang="ts">
import { ref, reactive, computed, nextTick } from "vue"
import { useEstimatorLeadApi } from "~/composables/useEstimatorLeadApi"
import { toLeadPayload } from "~/utils/estimator-lead-payload"
import type { EstimatePayload } from "~/types/estimator"

const props = defineProps<{
  questionnairePayload: EstimatePayload
  additionalFeatureNote: string
}>()

const emit = defineEmits<{
  submitted: [contact: { name: string; email: string; phone?: string; company?: string }]
}>()

const { leadStatus, leadError, submitLead } = useEstimatorLeadApi()

// ── Form state ──────────────────────────────────────────────────────────────

const fields = reactive({
  name: "",
  email: "",
  phone: "",
  company: "",
  website: "", // honeypot — always empty for humans
})

const errors = reactive<Record<string, string>>({})

const successRef = ref<HTMLElement | null>(null)
const nameId    = "lead-name"
const emailId   = "lead-email"
const phoneId   = "lead-phone"
const companyId = "lead-company"
const privacyId = "lead-privacy-notice"

// ── Validation client légère ────────────────────────────────────────────────

function validate(): boolean {
  errors.name  = ""
  errors.email = ""

  if (fields.name.trim().length < 2) {
    errors.name = "Votre prénom ou nom est requis (2 caractères minimum)."
  }

  if (!fields.email.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(fields.email.trim())) {
    errors.email = "Une adresse e-mail valide est requise."
  }

  return !errors.name && !errors.email
}

// ── Submit ──────────────────────────────────────────────────────────────────

async function handleSubmit(): Promise<void> {
  if (leadStatus.value === "loading") return
  if (!validate()) return

  const payload = toLeadPayload(props.questionnairePayload, {
    name: fields.name,
    email: fields.email,
    phone: fields.phone,
    company: fields.company,
    additionalFeatureNote: props.additionalFeatureNote,
  })

  payload.website = fields.website

  await submitLead(payload)

  if (leadStatus.value === "success") {
    await nextTick()
    successRef.value?.focus()
    emit("submitted", {
      name:    fields.name.trim(),
      email:   fields.email.trim(),
      phone:   fields.phone.trim() || undefined,
      company: fields.company.trim() || undefined,
    })
  }
}

// ── Error messages ──────────────────────────────────────────────────────────

const apiErrorMessage = computed<string>(() => {
  switch (leadError.value) {
    case "rate_limited":
      return "Vous avez effectué plusieurs demandes en peu de temps. Veuillez patienter un instant avant de réessayer."
    case "validation_error":
      return "Certaines informations doivent être vérifiées. Veuillez contrôler vos saisies."
    default:
      return "Une erreur est survenue. Vous pouvez réessayer ou nous contacter directement."
  }
})
</script>

<template>
  <!-- ─── État succès ───────────────────────────────────────────────────── -->
  <div
    v-if="leadStatus === 'success'"
    ref="successRef"
    class="lead-form__success"
    role="status"
    tabindex="-1"
  >
    <p class="lead-form__success-title">Message envoyé !</p>
    <p class="lead-form__success-body">
      Merci, nous reviendrons vers vous rapidement pour échanger sur votre projet.
    </p>
  </div>

  <!-- ─── Formulaire ────────────────────────────────────────────────────── -->
  <form
    v-else
    class="lead-form"
    novalidate
    @submit.prevent="handleSubmit"
  >
    <h3 class="lead-form__heading">Parler de mon projet</h3>

    <p class="lead-form__intro">
      Partagez vos coordonnées pour qu'on puisse revenir vers vous.
      Aucun engagement, premier échange gratuit.
    </p>

    <!-- Erreur API -->
    <div
      v-if="leadStatus === 'error'"
      class="lead-form__api-error"
      role="alert"
    >
      {{ apiErrorMessage }}
    </div>

    <!-- Champ Nom -->
    <div class="lead-form__field">
      <label :for="nameId" class="lead-form__label">
        Prénom et nom
        <span class="lead-form__required" aria-hidden="true">*</span>
      </label>
      <input
        :id="nameId"
        v-model="fields.name"
        type="text"
        name="name"
        class="lead-form__input"
        :class="{ 'lead-form__input--error': errors.name }"
        autocomplete="name"
        required
        aria-required="true"
        :aria-describedby="errors.name ? `${nameId}-error` : privacyId"
        :aria-invalid="errors.name ? 'true' : undefined"
        maxlength="120"
      >
      <p
        v-if="errors.name"
        :id="`${nameId}-error`"
        class="lead-form__field-error"
        role="alert"
      >
        {{ errors.name }}
      </p>
    </div>

    <!-- Champ Email -->
    <div class="lead-form__field">
      <label :for="emailId" class="lead-form__label">
        Adresse e-mail
        <span class="lead-form__required" aria-hidden="true">*</span>
      </label>
      <input
        :id="emailId"
        v-model="fields.email"
        type="email"
        name="email"
        class="lead-form__input"
        :class="{ 'lead-form__input--error': errors.email }"
        autocomplete="email"
        required
        aria-required="true"
        :aria-describedby="errors.email ? `${emailId}-error` : undefined"
        :aria-invalid="errors.email ? 'true' : undefined"
        maxlength="254"
      >
      <p
        v-if="errors.email"
        :id="`${emailId}-error`"
        class="lead-form__field-error"
        role="alert"
      >
        {{ errors.email }}
      </p>
    </div>

    <!-- Champ Téléphone (optionnel) -->
    <div class="lead-form__field">
      <label :for="phoneId" class="lead-form__label">
        Téléphone
        <span class="lead-form__optional">(optionnel)</span>
      </label>
      <input
        :id="phoneId"
        v-model="fields.phone"
        type="tel"
        name="phone"
        class="lead-form__input"
        autocomplete="tel"
        maxlength="40"
      >
    </div>

    <!-- Champ Société (optionnel) -->
    <div class="lead-form__field">
      <label :for="companyId" class="lead-form__label">
        Société
        <span class="lead-form__optional">(optionnel)</span>
      </label>
      <input
        :id="companyId"
        v-model="fields.company"
        type="text"
        name="company"
        class="lead-form__input"
        autocomplete="organization"
        maxlength="160"
      >
    </div>

    <!-- Honeypot — invisible pour les humains, rempli par les bots -->
    <div aria-hidden="true" class="lead-form__honeypot">
      <label for="lead-website">Site web</label>
      <input
        id="lead-website"
        v-model="fields.website"
        type="text"
        name="website"
        tabindex="-1"
        autocomplete="off"
      >
    </div>

    <!-- Vie privée -->
    <p :id="privacyId" class="lead-form__privacy">
      Vos données sont traitées par Devzair pour vous recontacter concernant
      votre projet. Base légale&nbsp;: intérêt légitime (RGPD, art.&nbsp;6-1-f).
      Conservation&nbsp;: deux ans. Vous pouvez demander l'accès, la rectification
      ou la suppression en nous écrivant.
    </p>

    <!-- Actions -->
    <div class="lead-form__actions">
      <button
        type="submit"
        class="lead-form__submit"
        :disabled="leadStatus === 'loading'"
        :aria-busy="leadStatus === 'loading' ? 'true' : undefined"
      >
        {{ leadStatus === "loading" ? "Envoi en cours…" : "Envoyer ma demande" }}
      </button>
    </div>
  </form>
</template>

<style scoped>
.lead-form {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  border-top: 2px solid var(--color-petrol);
  padding-top: var(--space-6);
  margin-top: var(--space-2);
}

.lead-form__heading {
  font-family: var(--font-family-heading);
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--color-ink);
  margin: 0;
}

.lead-form__intro {
  font-size: 0.9375rem;
  color: color-mix(in srgb, var(--color-ink) 75%, transparent);
  line-height: 1.6;
  margin: 0;
}

.lead-form__api-error {
  background: color-mix(in srgb, #c0392b 8%, transparent);
  border: 1px solid color-mix(in srgb, #c0392b 30%, transparent);
  border-radius: var(--radius-md);
  padding: var(--space-3) var(--space-4);
  font-size: 0.9375rem;
  color: #8b2020;
  line-height: 1.5;
}

.lead-form__field {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.lead-form__label {
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--color-ink);
}

.lead-form__required {
  color: #c0392b;
  margin-left: 2px;
}

.lead-form__optional {
  font-weight: 400;
  color: color-mix(in srgb, var(--color-ink) 55%, transparent);
  font-size: 0.875rem;
  margin-left: var(--space-1);
}

.lead-form__input {
  font-family: var(--font-family-body);
  font-size: 1rem;
  color: var(--color-ink);
  background: var(--color-cream-elevated);
  border: 1px solid color-mix(in srgb, var(--color-ink) 25%, transparent);
  border-radius: var(--radius-md);
  padding: var(--space-2) var(--space-3);
  width: 100%;
  box-sizing: border-box;
  transition: border-color var(--duration-base) var(--ease-out);
}

.lead-form__input:focus {
  outline: 2px solid var(--focus-ring);
  outline-offset: 1px;
  border-color: var(--color-petrol);
}

.lead-form__input--error {
  border-color: #c0392b;
}

.lead-form__field-error {
  font-size: 0.875rem;
  color: #8b2020;
  margin: 0;
}

.lead-form__honeypot {
  position: absolute;
  left: -9999px;
  top: auto;
  width: 1px;
  height: 1px;
  overflow: hidden;
}

.lead-form__privacy {
  font-size: 0.8125rem;
  color: color-mix(in srgb, var(--color-ink) 55%, transparent);
  line-height: 1.5;
  margin: 0;
  font-style: italic;
}

.lead-form__actions {
  display: flex;
  gap: var(--space-3);
  flex-wrap: wrap;
}

.lead-form__submit {
  background: var(--color-petrol);
  color: #fff;
  border: none;
  border-radius: var(--radius-md);
  padding: var(--space-3) var(--space-6);
  font-family: var(--font-family-body);
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background var(--duration-base) var(--ease-out),
    opacity var(--duration-base) var(--ease-out);
}

.lead-form__submit:hover:not(:disabled) {
  background: color-mix(in srgb, var(--color-petrol) 85%, #000);
}

.lead-form__submit:focus-visible {
  outline: 2px solid var(--focus-ring);
  outline-offset: 2px;
}

.lead-form__submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.lead-form__success {
  padding: var(--space-5) var(--space-6);
  background: color-mix(in srgb, var(--color-petrol) 8%, var(--color-cream-elevated));
  border: 1px solid color-mix(in srgb, var(--color-petrol) 25%, transparent);
  border-radius: var(--radius-lg);
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  outline: none;
}

.lead-form__success-title {
  font-family: var(--font-family-heading);
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--color-petrol);
  margin: 0;
}

.lead-form__success-body {
  font-size: 0.9375rem;
  color: color-mix(in srgb, var(--color-ink) 80%, transparent);
  margin: 0;
  line-height: 1.6;
}

@media (prefers-reduced-motion: reduce) {
  .lead-form__input,
  .lead-form__submit {
    transition: none;
  }
}
</style>
