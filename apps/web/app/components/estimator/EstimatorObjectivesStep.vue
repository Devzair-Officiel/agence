<script setup lang="ts">
import { ESTIMATOR_OBJECTIVES } from "~/config/estimator-objectives"
import type { ProjectObjectiveCode } from "~/types/estimator"

const props = defineProps<{
  modelValue: ProjectObjectiveCode[]
}>()

const emit = defineEmits<{
  "update:modelValue": [values: ProjectObjectiveCode[]]
}>()

function toggle(code: ProjectObjectiveCode): void {
  const current = props.modelValue
  const idx = current.indexOf(code)
  const updated =
    idx === -1 ? [...current, code] : current.filter((c) => c !== code)
  emit("update:modelValue", updated)
}
</script>

<template>
  <div class="objectives-step">
    <fieldset class="objectives-step__fieldset">
      <legend class="objectives-step__legend" tabindex="-1">
        Quel est l'objectif principal de votre projet ?
        <span class="objectives-step__hint">Plusieurs choix possibles.</span>
      </legend>
      <div class="objectives-step__grid">
        <EstimatorCheckboxCard
          v-for="objective in ESTIMATOR_OBJECTIVES"
          :key="objective.value"
          :value="objective.value"
          :label="objective.label"
          :description="objective.description"
          :checked="modelValue.includes(objective.value)"
          name="objectives"
          @toggle="toggle(objective.value)"
        />
      </div>
    </fieldset>
  </div>
</template>

<style scoped>
.objectives-step__fieldset {
  border: none;
  margin: 0;
  padding: 0;
}

.objectives-step__legend {
  font-family: var(--font-family-heading);
  font-size: clamp(1.125rem, 2vw, 1.375rem);
  font-weight: 600;
  color: var(--color-ink);
  margin-bottom: var(--space-6);
  display: block;
  width: 100%;
}

.objectives-step__legend:focus {
  outline: none;
}

.objectives-step__hint {
  display: block;
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  font-weight: 400;
  color: var(--color-ink);
  opacity: 0.6;
  margin-top: var(--space-1);
}

.objectives-step__grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-3);
}

@media (min-width: 600px) {
  .objectives-step__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
