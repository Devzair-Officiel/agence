<script setup lang="ts">
import { computed } from "vue"
import { getFeaturesForType } from "~/config/estimator-features"
import type { ProjectFeatureCode, ProjectTypeCode } from "~/types/estimator"

const props = defineProps<{
  modelValue: ProjectFeatureCode[]
  projectType: ProjectTypeCode
}>()

const emit = defineEmits<{
  "update:modelValue": [values: ProjectFeatureCode[]]
}>()

const availableFeatures = computed(() => getFeaturesForType(props.projectType))

function toggle(code: ProjectFeatureCode): void {
  const current = props.modelValue
  const idx = current.indexOf(code)
  const updated =
    idx === -1 ? [...current, code] : current.filter((f) => f !== code)
  emit("update:modelValue", updated)
}
</script>

<template>
  <div class="features-step">
    <fieldset class="features-step__fieldset">
      <legend class="features-step__legend" tabindex="-1">
        Quelles fonctionnalités prévoyez-vous ?
        <span class="features-step__hint">Sélection optionnelle.</span>
      </legend>
      <div class="features-step__grid">
        <EstimatorCheckboxCard
          v-for="feature in availableFeatures"
          :key="feature.value"
          :value="feature.value"
          :label="feature.label"
          :description="feature.description"
          :checked="modelValue.includes(feature.value)"
          name="features"
          @toggle="toggle(feature.value)"
        />
      </div>
    </fieldset>
  </div>
</template>

<style scoped>
.features-step__fieldset {
  border: none;
  margin: 0;
  padding: 0;
}

.features-step__legend {
  font-family: var(--font-family-heading);
  font-size: clamp(1.125rem, 2vw, 1.375rem);
  font-weight: 600;
  color: var(--color-ink);
  margin-bottom: var(--space-6);
  display: block;
  width: 100%;
}

.features-step__legend:focus {
  outline: none;
}

.features-step__hint {
  display: block;
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  font-weight: 400;
  color: var(--color-ink);
  opacity: 0.6;
  margin-top: var(--space-1);
}

.features-step__grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-3);
}

@media (min-width: 600px) {
  .features-step__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
