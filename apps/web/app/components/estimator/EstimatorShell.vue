<script setup lang="ts">
import { nextTick, ref, watch } from "vue"
import { useEstimator } from "~/composables/useEstimator"

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
} = useEstimator()

const stepRef = ref<HTMLElement | null>(null)

watch(currentStep, async () => {
  await nextTick()
  const target = stepRef.value?.querySelector<HTMLElement>(
    "legend[tabindex='-1'], h2[tabindex='-1']",
  )
  target?.focus()
})
</script>

<template>
  <section class="estimator-shell" aria-label="Configurateur de projet">
    <BaseContainer width="wide">
      <div class="estimator-shell__card">
        <EstimatorProgress
          :current-step="currentStep"
          :total-steps="totalSteps"
        />

        <div class="estimator-shell__body">
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

          <div class="estimator-shell__main">
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
              @prev="goPrev"
              @next="goNext"
            />
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
</style>
