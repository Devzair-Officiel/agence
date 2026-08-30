<script setup lang="ts">
import { ref, reactive, computed, nextTick } from "vue"
import { useEstimatorPartnershipApi } from "~/composables/useEstimatorPartnershipApi"
import { toPartnershipPayload } from "~/utils/estimator-partnership-payload"
import type { EstimatePayload } from "~/types/estimator"
import type {
  GeneratesRevenueCode,
  PartnershipInitialContact,
  PartnershipTypeCode,
  ProjectStageCode,
} from "~/types/estimator-partnership"

const props = defineProps<{
  questionnairePayload: EstimatePayload
  initialContact?: PartnershipInitialContact
}>()

const emit = defineEmits<{
  submitted: []
  close: []
}>()

const { partnershipStatus, partnershipError, submitPartnership } = useEstimatorPartnershipApi()

// ── IDs accessibilité ────────────────────────────────────────────────────────
const nameId            = "partnership-name"
const emailId           = "partnership-email"
const phoneId           = "partnership-phone"
const companyId         = "partnership-company"
const partnershipTypeId = "partnership-type"
const projectStageId    = "partnership-stage"
const proposalTextId    = "partnership-proposal"
const relevanceTextId   = "partnership-relevance"
const privacyId         = "partnership-privacy"

const successRef = ref<HTMLElement | null>(null)

// ── Form state ──────────────────────────────────────────────────────────────

const fields = reactive({
  name:            props.initialContact?.name    ?? "",
  email:           props.initialContact?.email   ?? "",
  phone:           props.initialContact?.phone   ?? "",
  company:         props.initialContact?.company ?? "",
  partnershipType: "" as PartnershipTypeCode | "",
  projectStage:    "" as ProjectStageCode | "",
  generatesRevenue: "" as GeneratesRevenueCode | "",
  proposalText:    "",
  relevanceText:   "",
  website:         "", // honeypot
})

const errors = reactive<Record<string, string>>({})

// ── Options ─────────────────────────────────────────────────────────────────

const partnershipTypeOptions: { value: PartnershipTypeCode; label: string }[] = [
  { value: "revenue_share",             label: "Partage de revenus" },
  { value: "equity_stake",              label: "Participation au projet / entreprise" },
  { value: "commercial_collaboration",  label: "Collaboration commerciale" },
  { value: "service_exchange",          label: "Échange de prestations" },
  { value: "other",                     label: "Autre proposition" },
]

const projectStageOptions: { value: ProjectStageCode; label: string }[] = [
  { value: "idea",         label: "Idée / préparation" },
  { value: "in_creation",  label: "Projet en cours de création" },
  { value: "launched",     label: "Activité déjà lancée" },
  { value: "with_clients", label: "Activité avec premiers clients" },
  { value: "established",  label: "Activité établie" },
]

const generatesRevenueOptions: { value: GeneratesRevenueCode; label: string }[] = [
  { value: "yes",               label: "Oui" },
  { value: "not_yet",           label: "Pas encore" },
  { value: "prefer_to_discuss", label: "Je préfère en discuter avec vous" },
]

// ── Computed ─────────────────────────────────────────────────────────────────

const proposalTextLeft = computed(() => 500 - fields.proposalText.length)
const relevanceTextLeft = computed(() => 500 - fields.relevanceText.length)

// ── Validation cliente légère ─────────────────────────────────────────────────

function validate(): boolean {
  errors.name            = ""
  errors.email           = ""
  errors.partnershipType = ""
  errors.projectStage    = ""
  errors.proposalText    = ""

  let valid = true

  if (fields.name.trim().length < 2) {
    errors.name = "Votre prénom ou nom est requis (2 caractères minimum)."
    valid = false
  }

  if (!fields.email.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(fields.email.trim())) {
    errors.email = "Une adresse e-mail valide est requise."
    valid = false
  }

  if (!fields.partnershipType) {
    errors.partnershipType = "Veuillez sélectionner une nature de proposition."
    valid = false
  }

  if (!fields.projectStage) {
    errors.projectStage = "Veuillez indiquer l'avancement de votre projet."
    valid = false
  }

  if (fields.proposalText.trim().length < 10) {
    errors.proposalText = "Veuillez décrire brièvement votre proposition (10 caractères minimum)."
    valid = false
  }

  if (fields.proposalText.length > 500) {
    errors.proposalText = "La proposition ne peut pas dépasser 500 caractères."
    valid = false
  }

  if (fields.relevanceText.length > 500) {
    errors.relevanceText = "Ce champ ne peut pas dépasser 500 caractères."
    valid = false
  }

  return valid
}

// ── Submit ────────────────────────────────────────────────────────────────────

async function handleSubmit(): Promise<void> {
  if (partnershipStatus.value === "loading") return
  if (!validate()) return

  const payload = toPartnershipPayload(
    props.questionnairePayload,
    {
      name:          fields.name,
      email:         fields.email,
      phone:         fields.phone,
      company:       fields.company,
      leadRequestId: props.initialContact?.leadRequestId,
    },
    {
      partnershipType: fields.partnershipType as PartnershipTypeCode,
      projectStage:    fields.projectStage as ProjectStageCode,
      generatesRevenue: fields.generatesRevenue ? (fields.generatesRevenue as GeneratesRevenueCode) : undefined,
      proposalText:    fields.proposalText,
      relevanceText:   fields.relevanceText || undefined,
    },
  )

  payload.website = fields.website

  await submitPartnership(payload)

  if (partnershipStatus.value === "success") {
    await nextTick()
    successRef.value?.focus()
    emit("submitted")
  }
}

// ── Error messages ────────────────────────────────────────────────────────────

const apiErrorMessage = computed<string>(() => {
  switch (partnershipError.value) {
    case "rate_limited":
      return "Plusieurs propositions ont été envoyées récemment. Veuillez patienter avant de réessayer."
    case "validation_error":
      return "Certaines informations doivent être vérifiées. Veuillez contrôler vos saisies."
    default:
      return "Nous n'avons pas pu transmettre votre proposition pour le moment. Vous pouvez réessayer."
  }
})
</script>

<template>
  <!-- ─── État succès ────────────────────────────────────────────────────── -->
  <div
    v-if="partnershipStatus === 'success'"
    ref="successRef"
    class="partnership-form__success"
    role="status"
    tabindex="-1"
  >
    <p class="partnership-form__success-title">Votre proposition nous a bien été transmise</p>
    <p class="partnership-form__success-body">
      Nous étudierons votre proposition ainsi que les éléments de votre projet
      avant de vous répondre. Chaque proposition est analysée individuellement.
    </p>
  </div>

  <!-- ─── Formulaire ─────────────────────────────────────────────────────── -->
  <form
    v-else
    class="partnership-form"
    novalidate
    @submit.prevent="handleSubmit"
  >
    <h3 class="partnership-form__heading">Proposer un partenariat</h3>

    <p class="partnership-form__intro">
      Dans certains cas particuliers, nous pouvons étudier des formes de
      collaboration différentes d'une prestation classique. Chaque proposition
      est analysée individuellement et ne constitue pas une alternative
      automatiquement disponible au règlement du projet.
    </p>

    <!-- Erreur API -->
    <div
      v-if="partnershipStatus === 'error'"
      class="partnership-form__api-error"
      role="alert"
    >
      {{ apiErrorMessage }}
    </div>

    <!-- ── Section Contact ──────────────────────────────────────────────── -->
    <fieldset class="partnership-form__fieldset">
      <legend class="partnership-form__legend">Vos coordonnées</legend>

      <!-- Nom -->
      <div class="partnership-form__field">
        <label :for="nameId" class="partnership-form__label">
          Prénom et nom
          <span class="partnership-form__required" aria-hidden="true">*</span>
        </label>
        <input
          :id="nameId"
          v-model="fields.name"
          type="text"
          name="name"
          class="partnership-form__input"
          :class="{ 'partnership-form__input--error': errors.name }"
          autocomplete="name"
          required
          aria-required="true"
          :aria-describedby="errors.name ? `${nameId}-error` : undefined"
          :aria-invalid="errors.name ? 'true' : undefined"
          maxlength="120"
        >
        <p
          v-if="errors.name"
          :id="`${nameId}-error`"
          class="partnership-form__field-error"
          role="alert"
        >
          {{ errors.name }}
        </p>
      </div>

      <!-- Email -->
      <div class="partnership-form__field">
        <label :for="emailId" class="partnership-form__label">
          Adresse e-mail
          <span class="partnership-form__required" aria-hidden="true">*</span>
        </label>
        <input
          :id="emailId"
          v-model="fields.email"
          type="email"
          name="email"
          class="partnership-form__input"
          :class="{ 'partnership-form__input--error': errors.email }"
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
          class="partnership-form__field-error"
          role="alert"
        >
          {{ errors.email }}
        </p>
      </div>

      <!-- Téléphone (optionnel) -->
      <div class="partnership-form__field">
        <label :for="phoneId" class="partnership-form__label">
          Téléphone
          <span class="partnership-form__optional">(optionnel)</span>
        </label>
        <input
          :id="phoneId"
          v-model="fields.phone"
          type="tel"
          name="phone"
          class="partnership-form__input"
          autocomplete="tel"
          maxlength="40"
        >
      </div>

      <!-- Société (optionnel) -->
      <div class="partnership-form__field">
        <label :for="companyId" class="partnership-form__label">
          Société / activité
          <span class="partnership-form__optional">(optionnel)</span>
        </label>
        <input
          :id="companyId"
          v-model="fields.company"
          type="text"
          name="company"
          class="partnership-form__input"
          autocomplete="organization"
          maxlength="160"
        >
      </div>
    </fieldset>

    <!-- ── Section Qualification ────────────────────────────────────────── -->
    <fieldset class="partnership-form__fieldset">
      <legend class="partnership-form__legend">Votre proposition</legend>

      <!-- Nature de la proposition -->
      <div class="partnership-form__field" role="group" :aria-labelledby="`${partnershipTypeId}-label`">
        <p :id="`${partnershipTypeId}-label`" class="partnership-form__label">
          Nature de la proposition
          <span class="partnership-form__required" aria-hidden="true">*</span>
        </p>
        <div
          class="partnership-form__radio-group"
          :aria-describedby="errors.partnershipType ? `${partnershipTypeId}-error` : undefined"
        >
          <label
            v-for="opt in partnershipTypeOptions"
            :key="opt.value"
            class="partnership-form__radio-label"
          >
            <input
              v-model="fields.partnershipType"
              type="radio"
              :name="partnershipTypeId"
              :value="opt.value"
              class="partnership-form__radio"
              :aria-invalid="errors.partnershipType ? 'true' : undefined"
            >
            {{ opt.label }}
          </label>
        </div>
        <p
          v-if="errors.partnershipType"
          :id="`${partnershipTypeId}-error`"
          class="partnership-form__field-error"
          role="alert"
        >
          {{ errors.partnershipType }}
        </p>
      </div>

      <!-- Avancement du projet -->
      <div class="partnership-form__field" role="group" :aria-labelledby="`${projectStageId}-label`">
        <p :id="`${projectStageId}-label`" class="partnership-form__label">
          Où en est votre projet ?
          <span class="partnership-form__required" aria-hidden="true">*</span>
        </p>
        <div
          class="partnership-form__radio-group"
          :aria-describedby="errors.projectStage ? `${projectStageId}-error` : undefined"
        >
          <label
            v-for="opt in projectStageOptions"
            :key="opt.value"
            class="partnership-form__radio-label"
          >
            <input
              v-model="fields.projectStage"
              type="radio"
              :name="projectStageId"
              :value="opt.value"
              class="partnership-form__radio"
              :aria-invalid="errors.projectStage ? 'true' : undefined"
            >
            {{ opt.label }}
          </label>
        </div>
        <p
          v-if="errors.projectStage"
          :id="`${projectStageId}-error`"
          class="partnership-form__field-error"
          role="alert"
        >
          {{ errors.projectStage }}
        </p>
      </div>

      <!-- Génère des revenus (optionnel) -->
      <div class="partnership-form__field" role="group" :aria-labelledby="`generates-revenue-label`">
        <p id="generates-revenue-label" class="partnership-form__label">
          Votre projet génère-t-il déjà des revenus ?
          <span class="partnership-form__optional">(optionnel)</span>
        </p>
        <div class="partnership-form__radio-group">
          <label
            v-for="opt in generatesRevenueOptions"
            :key="opt.value"
            class="partnership-form__radio-label"
          >
            <input
              v-model="fields.generatesRevenue"
              type="radio"
              name="generates-revenue"
              :value="opt.value"
              class="partnership-form__radio"
            >
            {{ opt.label }}
          </label>
        </div>
      </div>

      <!-- Proposition concrète (obligatoire) -->
      <div class="partnership-form__field">
        <label :for="proposalTextId" class="partnership-form__label">
          Que souhaitez-vous nous proposer ?
          <span class="partnership-form__required" aria-hidden="true">*</span>
        </label>
        <textarea
          :id="proposalTextId"
          v-model="fields.proposalText"
          name="proposal_text"
          class="partnership-form__textarea"
          :class="{ 'partnership-form__textarea--error': errors.proposalText }"
          rows="4"
          maxlength="500"
          required
          aria-required="true"
          :aria-describedby="errors.proposalText ? `${proposalTextId}-error` : `${proposalTextId}-count`"
          :aria-invalid="errors.proposalText ? 'true' : undefined"
        />
        <p :id="`${proposalTextId}-count`" class="partnership-form__char-count" aria-live="polite">
          {{ proposalTextLeft }} caractère{{ proposalTextLeft !== 1 ? 's' : '' }} restant{{ proposalTextLeft !== 1 ? 's' : '' }}
        </p>
        <p
          v-if="errors.proposalText"
          :id="`${proposalTextId}-error`"
          class="partnership-form__field-error"
          role="alert"
        >
          {{ errors.proposalText }}
        </p>
      </div>

      <!-- Pertinence (optionnel) -->
      <div class="partnership-form__field">
        <label :for="relevanceTextId" class="partnership-form__label">
          Pourquoi pensez-vous qu'un partenariat serait pertinent ?
          <span class="partnership-form__optional">(optionnel)</span>
        </label>
        <textarea
          :id="relevanceTextId"
          v-model="fields.relevanceText"
          name="relevance_text"
          class="partnership-form__textarea"
          :class="{ 'partnership-form__textarea--error': errors.relevanceText }"
          rows="3"
          maxlength="500"
          :aria-describedby="errors.relevanceText ? `${relevanceTextId}-error` : `${relevanceTextId}-count`"
          :aria-invalid="errors.relevanceText ? 'true' : undefined"
        />
        <p :id="`${relevanceTextId}-count`" class="partnership-form__char-count" aria-live="polite">
          {{ relevanceTextLeft }} caractère{{ relevanceTextLeft !== 1 ? 's' : '' }} restant{{ relevanceTextLeft !== 1 ? 's' : '' }}
        </p>
        <p
          v-if="errors.relevanceText"
          :id="`${relevanceTextId}-error`"
          class="partnership-form__field-error"
          role="alert"
        >
          {{ errors.relevanceText }}
        </p>
      </div>
    </fieldset>

    <!-- Honeypot — invisible pour les humains -->
    <div aria-hidden="true" class="partnership-form__honeypot">
      <label for="partnership-website">Site web</label>
      <input
        id="partnership-website"
        v-model="fields.website"
        type="text"
        name="website"
        tabindex="-1"
        autocomplete="off"
      >
    </div>

    <!-- Vie privée -->
    <p :id="privacyId" class="partnership-form__privacy">
      Les informations saisies sont traitées par Devzair pour l'étude de votre
      proposition. Base légale&nbsp;: intérêt légitime (RGPD, art.&nbsp;6-1-f).
      Conservation&nbsp;: deux ans. Vous pouvez demander l'accès, la rectification
      ou la suppression en nous écrivant.
    </p>

    <!-- Actions -->
    <div class="partnership-form__actions">
      <button
        type="submit"
        class="partnership-form__submit"
        :disabled="partnershipStatus === 'loading'"
        :aria-busy="partnershipStatus === 'loading' ? 'true' : undefined"
      >
        {{ partnershipStatus === "loading" ? "Envoi en cours…" : "Transmettre ma proposition" }}
      </button>
      <button
        type="button"
        class="partnership-form__cancel"
        @click="emit('close')"
      >
        Annuler
      </button>
    </div>
  </form>
</template>

<style scoped>
.partnership-form {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  border-top: 2px solid color-mix(in srgb, var(--color-petrol) 40%, transparent);
  padding-top: var(--space-6);
  margin-top: var(--space-2);
}

.partnership-form__heading {
  font-family: var(--font-family-heading);
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--color-ink);
  margin: 0;
}

.partnership-form__intro {
  font-size: 0.875rem;
  color: color-mix(in srgb, var(--color-ink) 70%, transparent);
  line-height: 1.6;
  margin: 0;
  font-style: italic;
}

.partnership-form__api-error {
  background: color-mix(in srgb, #c0392b 8%, transparent);
  border: 1px solid color-mix(in srgb, #c0392b 30%, transparent);
  border-radius: var(--radius-md);
  padding: var(--space-3) var(--space-4);
  font-size: 0.9375rem;
  color: #8b2020;
  line-height: 1.5;
}

.partnership-form__fieldset {
  border: 1px solid color-mix(in srgb, var(--color-ink) 12%, transparent);
  border-radius: var(--radius-md);
  padding: var(--space-4) var(--space-5);
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.partnership-form__legend {
  font-size: 0.8125rem;
  font-weight: 700;
  color: color-mix(in srgb, var(--color-ink) 65%, transparent);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  padding: 0 var(--space-2);
}

.partnership-form__field {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.partnership-form__label {
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--color-ink);
}

.partnership-form__required {
  color: #c0392b;
  margin-left: 2px;
}

.partnership-form__optional {
  font-weight: 400;
  color: color-mix(in srgb, var(--color-ink) 55%, transparent);
  font-size: 0.875rem;
  margin-left: var(--space-1);
}

.partnership-form__input {
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

.partnership-form__input:focus {
  outline: 2px solid var(--focus-ring);
  outline-offset: 1px;
  border-color: var(--color-petrol);
}

.partnership-form__input--error {
  border-color: #c0392b;
}

.partnership-form__textarea {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  color: var(--color-ink);
  background: var(--color-cream-elevated);
  border: 1px solid color-mix(in srgb, var(--color-ink) 25%, transparent);
  border-radius: var(--radius-md);
  padding: var(--space-2) var(--space-3);
  width: 100%;
  box-sizing: border-box;
  resize: vertical;
  line-height: 1.55;
  transition: border-color var(--duration-base) var(--ease-out);
}

.partnership-form__textarea:focus {
  outline: 2px solid var(--focus-ring);
  outline-offset: 1px;
  border-color: var(--color-petrol);
}

.partnership-form__textarea--error {
  border-color: #c0392b;
}

.partnership-form__char-count {
  font-size: 0.8125rem;
  color: color-mix(in srgb, var(--color-ink) 50%, transparent);
  margin: 0;
  text-align: right;
}

.partnership-form__radio-group {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.partnership-form__radio-label {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-size: 0.9375rem;
  color: var(--color-ink);
  cursor: pointer;
  line-height: 1.4;
}

.partnership-form__radio {
  accent-color: var(--color-petrol);
  width: 1rem;
  height: 1rem;
  flex-shrink: 0;
}

.partnership-form__field-error {
  font-size: 0.875rem;
  color: #8b2020;
  margin: 0;
}

.partnership-form__honeypot {
  position: absolute;
  left: -9999px;
  top: auto;
  width: 1px;
  height: 1px;
  overflow: hidden;
}

.partnership-form__privacy {
  font-size: 0.8125rem;
  color: color-mix(in srgb, var(--color-ink) 55%, transparent);
  line-height: 1.5;
  margin: 0;
  font-style: italic;
}

.partnership-form__actions {
  display: flex;
  gap: var(--space-3);
  flex-wrap: wrap;
  align-items: center;
}

.partnership-form__submit {
  background: color-mix(in srgb, var(--color-petrol) 75%, #000);
  color: #fff;
  border: none;
  border-radius: var(--radius-md);
  padding: var(--space-3) var(--space-5);
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  font-weight: 600;
  cursor: pointer;
  transition: background var(--duration-base) var(--ease-out),
    opacity var(--duration-base) var(--ease-out);
}

.partnership-form__submit:hover:not(:disabled) {
  background: var(--color-petrol);
}

.partnership-form__submit:focus-visible {
  outline: 2px solid var(--focus-ring);
  outline-offset: 2px;
}

.partnership-form__submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.partnership-form__cancel {
  background: none;
  border: none;
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  font-weight: 500;
  color: color-mix(in srgb, var(--color-ink) 65%, transparent);
  cursor: pointer;
  text-decoration: underline;
  text-underline-offset: 2px;
  padding: 0;
}

.partnership-form__cancel:hover {
  color: var(--color-ink);
}

.partnership-form__cancel:focus-visible {
  outline: 2px solid var(--focus-ring);
  outline-offset: 2px;
  border-radius: 2px;
}

.partnership-form__success {
  padding: var(--space-5) var(--space-6);
  background: color-mix(in srgb, var(--color-petrol) 6%, var(--color-cream-elevated));
  border: 1px solid color-mix(in srgb, var(--color-petrol) 20%, transparent);
  border-radius: var(--radius-lg);
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  outline: none;
}

.partnership-form__success-title {
  font-family: var(--font-family-heading);
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-petrol);
  margin: 0;
}

.partnership-form__success-body {
  font-size: 0.9375rem;
  color: color-mix(in srgb, var(--color-ink) 75%, transparent);
  margin: 0;
  line-height: 1.6;
}

@media (prefers-reduced-motion: reduce) {
  .partnership-form__input,
  .partnership-form__textarea,
  .partnership-form__submit {
    transition: none;
  }
}
</style>
