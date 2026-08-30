<script setup lang="ts">
import { computed, ref } from "vue"
import { ESTIMATOR_PROJECT_TYPES } from "~/config/estimator-project-types"
import { ESTIMATOR_OBJECTIVES } from "~/config/estimator-objectives"
import { ESTIMATOR_SITUATION_OPTIONS } from "~/config/estimator-situation-options"
import { getScaleConfig } from "~/config/estimator-scale-options"
import { ALL_FEATURES } from "~/config/estimator-features"
import { ESTIMATOR_VISIBILITY_OPTIONS } from "~/config/estimator-visibility-options"
import { ESTIMATOR_CARE_OPTIONS } from "~/config/estimator-care-options"
import type { EstimatorPath } from "~/composables/useEstimator"
import type {
  CareNeedCode,
  ContentNeedCode,
  CurrentSituationCode,
  ProjectFeatureCode,
  ProjectObjectiveCode,
  ProjectScaleCode,
  ProjectTypeCode,
  VisibilityNeedCode,
} from "~/types/estimator"

const props = defineProps<{
  currentStep: number
  maxVisitedStep: number
  path: EstimatorPath
  projectType: ProjectTypeCode | null
  objectives: ProjectObjectiveCode[]
  currentSituation: CurrentSituationCode | null
  scale: ProjectScaleCode | null
  features: ProjectFeatureCode[]
  contentNeeds: ContentNeedCode[]
  visibilityNeeds: VisibilityNeedCode[]
  careNeeds: CareNeedCode[]
  careAnswered: boolean
  additionalFeatureNote: string
}>()

const emit = defineEmits<{
  "go-to-step": [step: number]
}>()

const expanded = ref(false)

const CONTENT_NEED_LABELS: Record<ContentNeedCode, string> = {
  visual_identity_needed: "Identité visuelle à créer",
  visual_identity_partial: "Identité partiellement définie",
  content_to_write: "Rédaction à déléguer",
  content_with_help: "Rédaction avec accompagnement",
  photography_needed: "Photographie à prévoir",
}

function getProjectTypeLabel(): string {
  return ESTIMATOR_PROJECT_TYPES.find((t) => t.value === props.projectType)?.label ?? ""
}

function getObjectiveLabels(): string[] {
  return props.objectives.map(
    (code) => ESTIMATOR_OBJECTIVES.find((o) => o.value === code)?.label ?? code,
  )
}

function getSituationLabel(): string {
  return ESTIMATOR_SITUATION_OPTIONS.find((o) => o.value === props.currentSituation)?.label ?? ""
}

function getScaleLabel(): string {
  if (!props.projectType || !props.scale) return ""
  return getScaleConfig(props.projectType).options.find((o) => o.value === props.scale)?.label ?? ""
}

function getFeatureLabels(): string[] {
  return props.features.map((code) => ALL_FEATURES.find((f) => f.value === code)?.label ?? code)
}

function getContentLabels(): string[] {
  return props.contentNeeds.map((code) => CONTENT_NEED_LABELS[code] ?? code)
}

function getVisibilityLabels(): string[] {
  return props.visibilityNeeds.map(
    (code) => ESTIMATOR_VISIBILITY_OPTIONS.find((o) => o.value === code)?.label ?? code,
  )
}

function getCareLabels(): string[] {
  if (!props.careAnswered) return []
  if (props.careNeeds.length === 0) return ["Autonomie (sans accompagnement)"]
  return props.careNeeds.map(
    (code) => ESTIMATOR_CARE_OPTIONS.find((o) => o.value === code)?.label ?? code,
  )
}

const truncatedNote = computed(() => {
  const note = props.additionalFeatureNote
  return note.length <= 72 ? note : note.slice(0, 72) + "…"
})

interface SummarySection {
  step: number
  title: string
  answers: string[]
  optional: boolean
}

const sections = computed<SummarySection[]>(() => {
  const max = props.maxVisitedStep
  const all: SummarySection[] =
    props.path === "standard"
      ? [
          {
            step: 1,
            title: "Type de projet",
            answers: props.projectType ? [getProjectTypeLabel()] : [],
            optional: false,
          },
          {
            step: 2,
            title: "Situation actuelle",
            answers: props.currentSituation ? [getSituationLabel()] : [],
            optional: false,
          },
          {
            step: 3,
            title: "Périmètre",
            answers: props.scale ? [getScaleLabel()] : [],
            optional: false,
          },
          {
            step: 4,
            title: "Fonctionnalités",
            answers: getFeatureLabels(),
            optional: true,
          },
          {
            step: 5,
            title: "Contenu",
            answers: getContentLabels(),
            optional: true,
          },
          {
            step: 6,
            title: "Visibilité",
            answers: getVisibilityLabels(),
            optional: true,
          },
          {
            step: 7,
            title: "Accompagnement",
            answers: getCareLabels(),
            optional: true,
          },
        ]
      : [
          {
            step: 1,
            title: "Type de projet",
            answers: props.projectType ? [getProjectTypeLabel()] : [],
            optional: false,
          },
          {
            step: 2,
            title: "Objectifs",
            answers: getObjectiveLabels(),
            optional: false,
          },
          {
            step: 3,
            title: "Situation actuelle",
            answers: props.currentSituation ? [getSituationLabel()] : [],
            optional: false,
          },
          {
            step: 4,
            title: "Contenu",
            answers: getContentLabels(),
            optional: true,
          },
          {
            step: 5,
            title: "Visibilité",
            answers: getVisibilityLabels(),
            optional: true,
          },
          {
            step: 6,
            title: "Accompagnement",
            answers: getCareLabels(),
            optional: true,
          },
        ]
  return all.filter((s) => s.step <= max)
})
</script>

<template>
  <aside class="summary" aria-label="Récapitulatif de votre projet">
    <button
      class="summary__toggle"
      type="button"
      :aria-expanded="expanded"
      aria-controls="summary-body"
      @click="expanded = !expanded"
    >
      <span class="summary__toggle-label">Récapitulatif</span>
      <svg
        class="summary__toggle-icon"
        aria-hidden="true"
        width="12"
        height="12"
        viewBox="0 0 12 12"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          :d="expanded ? 'M10 8L6 4L2 8' : 'M2 4L6 8L10 4'"
          stroke="currentColor"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
    </button>

    <div
      id="summary-body"
      class="summary__body"
      :class="{ 'summary__body--expanded': expanded }"
    >
      <p class="summary__heading" aria-hidden="true">Récapitulatif</p>

      <div
        v-for="section in sections"
        :key="section.step"
        class="summary__section"
        :class="{ 'summary__section--current': currentStep === section.step }"
      >
        <div class="summary__section-header">
          <span class="summary__section-title">{{ section.title }}</span>
          <button
            v-if="currentStep !== section.step"
            class="summary__modify"
            type="button"
            :aria-label="`Modifier : ${section.title}`"
            @click="emit('go-to-step', section.step)"
          >
            Modifier
          </button>
          <span v-else class="summary__current-badge">En cours</span>
        </div>

        <div class="summary__section-answers">
          <template v-if="section.answers.length > 0">
            <ul v-if="section.answers.length > 1" class="summary__answer-list">
              <li
                v-for="answer in section.answers"
                :key="answer"
                class="summary__answer-item"
              >
                {{ answer }}
              </li>
            </ul>
            <p v-else class="summary__answer">{{ section.answers[0] }}</p>
          </template>
          <p v-else class="summary__answer summary__answer--empty">
            {{ section.optional ? "Aucune sélection" : "À définir" }}
          </p>
        </div>

        <p
          v-if="path === 'standard' && section.step === 4 && truncatedNote"
          class="summary__extra-note"
        >
          + {{ truncatedNote }}
        </p>
      </div>
    </div>
  </aside>
</template>

<style scoped>
.summary {
  background: color-mix(in srgb, var(--color-petrol) 4%, var(--color-cream-elevated));
  border: 1px solid color-mix(in srgb, var(--color-petrol) 18%, transparent);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

/* Toggle — mobile only */
.summary__toggle {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  width: 100%;
  padding: var(--space-4);
  background: none;
  border: none;
  cursor: pointer;
  font-family: var(--font-family-heading);
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--color-petrol);
  text-align: left;
}

.summary__toggle:focus-visible {
  outline: 2px solid var(--focus-ring);
  outline-offset: -2px;
}

.summary__toggle-label {
  flex: 1;
}

.summary__toggle-icon {
  flex-shrink: 0;
  color: var(--color-petrol);
  opacity: 0.7;
  transition: transform var(--duration-base) var(--ease-out);
}

/* Desktop: hide toggle, always show body */
@media (min-width: 1024px) {
  .summary__toggle {
    display: none;
  }

  .summary__body {
    display: flex !important;
  }
}

/* Mobile: body hidden unless expanded */
@media (max-width: 1023px) {
  .summary__body:not(.summary__body--expanded) {
    display: none;
  }
}

@media (prefers-reduced-motion: reduce) {
  .summary__toggle-icon {
    transition: none;
  }
}

.summary__body {
  padding: var(--space-1) var(--space-3) var(--space-4);
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.summary__heading {
  font-family: var(--font-family-heading);
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-petrol);
  margin: 0 0 var(--space-3) 0;
  padding: var(--space-3) var(--space-1) 0;
}

@media (max-width: 1023px) {
  .summary__heading {
    display: none;
  }
}

.summary__section {
  padding: var(--space-2) var(--space-2);
  border-radius: var(--radius-sm);
}

.summary__section--current {
  background: color-mix(in srgb, var(--color-petrol) 10%, transparent);
}

.summary__section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-2);
  margin-bottom: 0.25rem;
}

.summary__section-title {
  font-size: 0.6875rem;
  font-weight: 600;
  color: color-mix(in srgb, var(--color-ink) 68%, var(--color-cream-elevated));
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.summary__current-badge {
  font-size: 0.6875rem;
  font-weight: 500;
  color: var(--color-petrol);
  opacity: 0.8;
}

.summary__modify {
  flex-shrink: 0;
  background: none;
  border: none;
  cursor: pointer;
  font-family: var(--font-family-body);
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--color-petrol);
  padding: 0;
  text-decoration: underline;
  text-underline-offset: 2px;
  line-height: 1;
}

.summary__modify:focus-visible {
  outline: 2px solid var(--focus-ring);
  outline-offset: 3px;
  border-radius: 2px;
}

.summary__answer {
  font-size: 0.875rem;
  color: var(--color-ink);
  margin: 0;
  line-height: 1.4;
}

.summary__answer--empty {
  color: color-mix(in srgb, var(--color-ink) 68%, var(--color-cream-elevated));
  font-style: italic;
}

.summary__answer-list {
  margin: 0;
  padding: 0;
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.summary__answer-item {
  font-size: 0.875rem;
  color: var(--color-ink);
  line-height: 1.4;
  padding-left: var(--space-3);
  position: relative;
}

.summary__answer-item::before {
  content: "–";
  position: absolute;
  left: 0;
  color: var(--color-petrol);
  opacity: 0.55;
}

.summary__extra-note {
  margin-top: 0.25rem;
  padding-left: var(--space-3);
  font-size: 0.8125rem;
  color: var(--color-ink);
  opacity: 0.55;
  font-style: italic;
  line-height: 1.4;
}
</style>
