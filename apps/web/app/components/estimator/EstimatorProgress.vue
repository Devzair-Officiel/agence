<script setup lang="ts">
import { computed } from "vue"

const props = defineProps<{
  currentStep: number
  totalSteps: number
}>()

const percent = computed(() => Math.round((props.currentStep / props.totalSteps) * 100))
</script>

<template>
  <div class="estimator-progress">
    <div
      class="estimator-progress__bar"
      role="progressbar"
      :aria-valuenow="currentStep"
      aria-valuemin="1"
      :aria-valuemax="totalSteps"
      :aria-label="`Étape ${currentStep} sur ${totalSteps}`"
    >
      <div
        class="estimator-progress__fill"
        :style="{ width: `${percent}%` }"
      />
    </div>
    <p class="estimator-progress__label" aria-hidden="true">
      Étape <strong>{{ currentStep }}</strong> sur {{ totalSteps }}
    </p>
  </div>
</template>

<style scoped>
.estimator-progress {
  display: flex;
  align-items: center;
  gap: var(--space-4);
}

.estimator-progress__bar {
  flex: 1;
  height: 4px;
  background: color-mix(in srgb, var(--color-petrol) 15%, transparent);
  border-radius: var(--radius-pill);
  overflow: hidden;
}

.estimator-progress__fill {
  height: 100%;
  background: var(--color-petrol);
  border-radius: var(--radius-pill);
  transition: width var(--duration-base) var(--ease-out);
}

@media (prefers-reduced-motion: reduce) {
  .estimator-progress__fill {
    transition: none;
  }
}

.estimator-progress__label {
  font-size: 0.8125rem;
  color: var(--color-ink);
  opacity: 0.6;
  white-space: nowrap;
  margin: 0;
}
</style>
