<script setup lang="ts">
import { ESTIMATOR_VISIBILITY_OPTIONS } from "~/config/estimator-visibility-options"
import type { VisibilityNeedCode } from "~/types/estimator"

const props = defineProps<{
  modelValue: VisibilityNeedCode[]
}>()

const emit = defineEmits<{
  "update:modelValue": [values: VisibilityNeedCode[]]
}>()

function toggle(code: VisibilityNeedCode): void {
  const current = props.modelValue
  const idx = current.indexOf(code)
  const updated =
    idx === -1 ? [...current, code] : current.filter((v) => v !== code)
  emit("update:modelValue", updated)
}
</script>

<template>
  <div class="visibility-step">
    <fieldset class="visibility-step__fieldset">
      <legend class="visibility-step__legend" tabindex="-1">
        Avez-vous des besoins en visibilité en ligne ?
        <span class="visibility-step__hint">Sélection optionnelle.</span>
      </legend>
      <div class="visibility-step__grid">
        <EstimatorCheckboxCard
          v-for="option in ESTIMATOR_VISIBILITY_OPTIONS"
          :key="option.value"
          :value="option.value"
          :label="option.label"
          :description="option.description"
          :checked="modelValue.includes(option.value)"
          name="visibility-needs"
          @toggle="toggle(option.value)"
        />
      </div>
    </fieldset>
  </div>
</template>

<style scoped>
.visibility-step__fieldset {
  border: none;
  margin: 0;
  padding: 0;
}

.visibility-step__legend {
  font-family: var(--font-family-heading);
  font-size: clamp(1.125rem, 2vw, 1.375rem);
  font-weight: 600;
  color: var(--color-ink);
  margin-bottom: var(--space-6);
  display: block;
  width: 100%;
}

.visibility-step__legend:focus {
  outline: none;
}

.visibility-step__hint {
  display: block;
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  font-weight: 400;
  color: var(--color-ink);
  opacity: 0.6;
  margin-top: var(--space-1);
}

.visibility-step__grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-3);
}

@media (min-width: 600px) {
  .visibility-step__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
