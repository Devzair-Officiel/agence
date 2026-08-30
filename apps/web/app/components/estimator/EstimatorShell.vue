<script setup lang="ts">
import { useEstimator } from "~/composables/useEstimator"

const TOTAL_STEPS = 7

const { currentStep, projectType, canGoNext, goNext, goPrev, setProjectType } =
  useEstimator({ maxStep: 1 })
</script>

<template>
  <section class="estimator-shell" aria-label="Configurateur de projet">
    <BaseContainer width="wide">
      <div class="estimator-shell__card">
        <EstimatorProgress
          :current-step="currentStep"
          :total-steps="TOTAL_STEPS"
        />

        <div class="estimator-shell__step">
          <EstimatorProjectTypeStep
            v-if="currentStep === 1"
            :model-value="projectType"
            @update:model-value="setProjectType"
          />
        </div>

        <EstimatorNavigation
          :current-step="currentStep"
          :can-go-next="canGoNext"
          @prev="goPrev"
          @next="goNext"
        />
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

.estimator-shell__step {
  min-height: 320px;
}
</style>
