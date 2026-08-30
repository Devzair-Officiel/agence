<script setup lang="ts">
import { nextTick, ref, watch, computed } from "vue"
import { useEstimator } from "~/composables/useEstimator"
import { useEstimatorApi } from "~/composables/useEstimatorApi"

const {
  currentStep,
  projectType,
  objectives,
  currentSituation,
  scale,
  features,
  additionalFeatureNote,
  contentNeeds,
  visibilityNeeds,
  careNeeds,
  careAnswered,
  maxVisitedStep,
  path,
  totalSteps,
  canGoNext,
  isComplete,
  goNext,
  goPrev,
  goToStep,
  setProjectType,
  setCurrentSituation,
  setScale,
  setFeatures,
  setAdditionalFeatureNote,
  setObjectives,
  setContentNeeds,
  setVisibilityNeeds,
  setCareNeeds,
  toEstimatePayload,
} = useEstimator()

const { estimateResult, estimateStatus, estimateError, submitEstimate, clearResult } =
  useEstimatorApi()

const stepRef = ref<HTMLElement | null>(null)

const isLoading = computed(() => estimateStatus.value === "loading")
const showResult = computed(
  () => estimateStatus.value === "success" && estimateResult.value !== null,
)
const showError = computed(() => estimateStatus.value === "error")

watch(currentStep, async () => {
  await nextTick()
  const target = stepRef.value?.querySelector<HTMLElement>(
    "legend[tabindex='-1'], h2[tabindex='-1']",
  )
  target?.focus()
})

async function handleNext(): Promise<void> {
  if (isComplete.value) {
    await submitEstimate(toEstimatePayload())
  } else {
    goNext()
  }
}

function handleModifyAnswers(): void {
  clearResult()
}

async function handleRetry(): Promise<void> {
  await submitEstimate(toEstimatePayload())
}

const errorMessage = computed<string>(() => {
  switch (estimateError.value) {
    case "validation_error":
      return "Certaines informations doivent être vérifiées avant de calculer votre estimation."
    case "forbidden":
    case "payload_too_large":
      return "Impossible de calculer l'estimation pour le moment."
    case "rate_limited":
      return "Vous avez effectué plusieurs estimations en peu de temps. Veuillez patienter un instant avant de réessayer."
    case "server_error":
    case "network_error":
    case "unknown_error":
    default:
      return "Nous n'avons pas pu calculer votre estimation pour le moment."
  }
})

const canRetry = computed(
  () =>
    estimateError.value === "server_error" ||
    estimateError.value === "network_error" ||
    estimateError.value === "unknown_error",
)
</script>

<template>
  <section class="estimator-shell" aria-label="Configurateur de projet">
    <BaseContainer width="wide">
      <div class="estimator-shell__card">
        <!-- Progression : masquée pendant le résultat -->
        <EstimatorProgress
          v-if="!showResult"
          :current-step="currentStep"
          :total-steps="totalSteps"
        />

        <!-- Annonce accessibilité pour chargement / erreur -->
        <div aria-live="polite" aria-atomic="true" class="sr-only">
          <span v-if="isLoading">Nous préparons votre estimation…</span>
          <span v-else-if="showError">{{ errorMessage }}</span>
        </div>

        <div class="estimator-shell__body">
          <!-- Résumé — toujours présent (sticky desktop) -->
          <div class="estimator-shell__summary">
            <EstimatorProjectSummary
              :current-step="currentStep"
              :max-visited-step="maxVisitedStep"
              :path="path"
              :project-type="projectType"
              :objectives="objectives"
              :current-situation="currentSituation"
              :scale="scale"
              :features="features"
              :content-needs="contentNeeds"
              :visibility-needs="visibilityNeeds"
              :care-needs="careNeeds"
              :care-answered="careAnswered"
              :additional-feature-note="additionalFeatureNote"
              @go-to-step="goToStep"
            />
          </div>

          <!-- Zone principale -->
          <div class="estimator-shell__main">
            <!-- ─── RÉSULTAT ─────────────────────────────────────────────── -->
            <EstimatorResult
              v-if="showResult && estimateResult"
              :result="estimateResult"
              :additional-feature-note="additionalFeatureNote"
              :care-answered="careAnswered"
              :care-needs="careNeeds"
              @modify-answers="handleModifyAnswers"
            />

            <!-- ─── QUESTIONNAIRE ─────────────────────────────────────────── -->
            <template v-else>
              <!-- Chargement -->
              <div v-if="isLoading" class="estimator-shell__loading" aria-hidden="true">
                <p class="estimator-shell__loading-text">
                  Nous préparons votre estimation…
                </p>
              </div>

              <!-- Erreur API -->
              <div v-else-if="showError" class="estimator-shell__error" role="alert">
                <p class="estimator-shell__error-text">{{ errorMessage }}</p>
                <div class="estimator-shell__error-actions">
                  <BaseButton
                    v-if="canRetry"
                    variant="primary"
                    type="button"
                    @click="handleRetry"
                  >
                    Réessayer
                  </BaseButton>
                  <button
                    type="button"
                    class="estimator-shell__error-modify"
                    @click="handleModifyAnswers"
                  >
                    Modifier mes réponses
                  </button>
                </div>
              </div>

              <!-- Étapes -->
              <template v-else>
                <div ref="stepRef" class="estimator-shell__step">
                  <!-- Étape 1 — Type de projet (commun aux deux chemins) -->
                  <EstimatorProjectTypeStep
                    v-if="currentStep === 1"
                    :model-value="projectType"
                    @update:model-value="setProjectType"
                  />

                  <!-- Chemin standard (vitrinesite, ecommerce, businessapp, refonte, other) -->
                  <template v-else-if="path === 'standard'">
                    <EstimatorSituationStep
                      v-if="currentStep === 2"
                      :model-value="currentSituation"
                      @update:model-value="setCurrentSituation"
                    />
                    <EstimatorScopeStep
                      v-else-if="currentStep === 3"
                      :model-value="scale"
                      :project-type="projectType!"
                      @update:model-value="setScale"
                    />
                    <EstimatorFeaturesStep
                      v-else-if="currentStep === 4"
                      :model-value="features"
                      :additional-feature-note="additionalFeatureNote"
                      :project-type="projectType!"
                      @update:model-value="setFeatures"
                      @update:additional-feature-note="setAdditionalFeatureNote"
                    />
                    <EstimatorContentStep
                      v-else-if="currentStep === 5"
                      :model-value="contentNeeds"
                      @update:model-value="setContentNeeds"
                    />
                    <EstimatorVisibilityStep
                      v-else-if="currentStep === 6"
                      :model-value="visibilityNeeds"
                      @update:model-value="setVisibilityNeeds"
                    />
                    <EstimatorCareStep
                      v-else-if="currentStep === 7"
                      :model-value="careNeeds"
                      @update:model-value="setCareNeeds"
                    />
                  </template>

                  <!-- Chemin unknown (objectif-first, 6 étapes) -->
                  <template v-else>
                    <EstimatorObjectivesStep
                      v-if="currentStep === 2"
                      :model-value="objectives"
                      @update:model-value="setObjectives"
                    />
                    <EstimatorSituationStep
                      v-else-if="currentStep === 3"
                      :model-value="currentSituation"
                      @update:model-value="setCurrentSituation"
                    />
                    <EstimatorContentStep
                      v-else-if="currentStep === 4"
                      :model-value="contentNeeds"
                      @update:model-value="setContentNeeds"
                    />
                    <EstimatorVisibilityStep
                      v-else-if="currentStep === 5"
                      :model-value="visibilityNeeds"
                      @update:model-value="setVisibilityNeeds"
                    />
                    <EstimatorCareStep
                      v-else-if="currentStep === 6"
                      :model-value="careNeeds"
                      @update:model-value="setCareNeeds"
                    />
                  </template>
                </div>

                <EstimatorNavigation
                  :current-step="currentStep"
                  :can-go-next="canGoNext"
                  :is-last-step="isComplete"
                  :is-submitting="isLoading"
                  @prev="goPrev"
                  @next="handleNext"
                />
              </template>
            </template>
          </div>
        </div>
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.estimator-shell {
  background: var(--color-sand);
  padding-block: var(--space-10) var(--space-12);
  min-height: 60vh;
}

.estimator-shell__card {
  background: var(--color-cream-elevated);
  border-radius: var(--radius-xl);
  padding: var(--space-8);
  box-shadow:
    0 1px 3px color-mix(in srgb, var(--color-ink) 8%, transparent),
    0 8px 24px color-mix(in srgb, var(--color-ink) 5%, transparent);
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

@media (max-width: 480px) {
  .estimator-shell__card {
    padding: var(--space-6) var(--space-4);
  }
}

/* Body: stacked on mobile, two-column on desktop */
.estimator-shell__body {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

@media (min-width: 1024px) {
  .estimator-shell__body {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: var(--space-8);
    align-items: start;
  }

  .estimator-shell__summary {
    position: sticky;
    top: var(--space-6);
  }
}

.estimator-shell__main {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.estimator-shell__step {
  min-height: 320px;
}

/* Loading */
.estimator-shell__loading {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 200px;
}

.estimator-shell__loading-text {
  font-size: 1rem;
  color: color-mix(in srgb, var(--color-ink) 70%, transparent);
  font-style: italic;
  margin: 0;
}

/* Error */
.estimator-shell__error {
  padding: var(--space-6);
  background: color-mix(in srgb, var(--color-ink) 4%, var(--color-cream-elevated));
  border: 1px solid color-mix(in srgb, var(--color-ink) 12%, transparent);
  border-radius: var(--radius-lg);
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.estimator-shell__error-text {
  font-size: 1rem;
  color: var(--color-ink);
  margin: 0;
  line-height: 1.6;
}

.estimator-shell__error-actions {
  display: flex;
  gap: var(--space-3);
  flex-wrap: wrap;
  align-items: center;
}

.estimator-shell__error-modify {
  background: none;
  border: none;
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  font-weight: 500;
  color: color-mix(in srgb, var(--color-ink) 70%, transparent);
  cursor: pointer;
  text-decoration: underline;
  text-underline-offset: 2px;
  padding: 0;
}

.estimator-shell__error-modify:hover {
  color: var(--color-ink);
}

.estimator-shell__error-modify:focus-visible {
  outline: 2px solid var(--focus-ring);
  outline-offset: 2px;
  border-radius: 2px;
}

/* Classe utilitaire screen-reader only */
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}
</style>
