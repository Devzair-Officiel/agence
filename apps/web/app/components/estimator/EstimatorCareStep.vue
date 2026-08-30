<script setup lang="ts">
import { computed } from "vue"
import { ESTIMATOR_CARE_OPTIONS } from "~/config/estimator-care-options"
import type { CareNeedCode } from "~/types/estimator"

const props = defineProps<{
  modelValue: CareNeedCode[]
}>()

const emit = defineEmits<{
  "update:modelValue": [values: CareNeedCode[]]
}>()

const autonomyMode = computed(() => props.modelValue.length === 0)

function toggle(code: CareNeedCode): void {
  const current = props.modelValue
  const idx = current.indexOf(code)
  const updated =
    idx === -1 ? [...current, code] : current.filter((c) => c !== code)
  emit("update:modelValue", updated)
}

function selectAutonomy(): void {
  emit("update:modelValue", [])
}
</script>

<template>
  <div class="care-step">
    <fieldset class="care-step__fieldset">
      <legend class="care-step__legend" tabindex="-1">
        Souhaitez-vous un accompagnement après la livraison ?
        <span class="care-step__hint">Sélection optionnelle.</span>
      </legend>
      <div class="care-step__grid">
        <EstimatorCheckboxCard
          v-for="option in ESTIMATOR_CARE_OPTIONS"
          :key="option.value"
          :value="option.value"
          :label="option.label"
          :description="option.description"
          :checked="modelValue.includes(option.value)"
          name="care-needs"
          @toggle="toggle(option.value)"
        />
      </div>

      <div class="care-step__autonomy-separator" aria-hidden="true" />

      <label
        class="care-step__autonomy-card"
        :class="{ 'care-step__autonomy-card--selected': autonomyMode }"
      >
        <input
          class="care-step__autonomy-input"
          type="checkbox"
          name="care-autonomy"
          :checked="autonomyMode"
          @change="selectAutonomy"
        >
        <span class="care-step__autonomy-label">Je préfère gérer en autonomie</span>
        <span class="care-step__autonomy-description">
          Pas d'accompagnement après livraison — je m'occupe de la suite.
        </span>
      </label>
    </fieldset>
  </div>
</template>

<style scoped>
.care-step__fieldset {
  border: none;
  margin: 0;
  padding: 0;
}

.care-step__legend {
  font-family: var(--font-family-heading);
  font-size: clamp(1.125rem, 2vw, 1.375rem);
  font-weight: 600;
  color: var(--color-ink);
  margin-bottom: var(--space-6);
  display: block;
  width: 100%;
}

.care-step__legend:focus {
  outline: none;
}

.care-step__hint {
  display: block;
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  font-weight: 400;
  color: var(--color-ink);
  opacity: 0.6;
  margin-top: var(--space-1);
}

.care-step__grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-3);
}

@media (min-width: 600px) {
  .care-step__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.care-step__autonomy-separator {
  height: 1px;
  background: color-mix(in srgb, var(--color-ink) 10%, transparent);
  margin-block: var(--space-4);
}

.care-step__autonomy-card {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
  padding: var(--space-4);
  background: var(--color-cream-elevated);
  border: 2px solid transparent;
  border-radius: var(--radius-lg);
  cursor: pointer;
  transition:
    border-color var(--duration-base) var(--ease-out),
    background var(--duration-base) var(--ease-out);
}

.care-step__autonomy-card:hover {
  border-color: color-mix(in srgb, var(--color-petrol) 35%, transparent);
  background: var(--color-sand);
}

.care-step__autonomy-card--selected {
  border-color: var(--color-petrol);
  background: color-mix(in srgb, var(--color-petrol) 6%, var(--color-cream-elevated));
}

.care-step__autonomy-card:focus-within {
  outline: 2px solid var(--focus-ring);
  outline-offset: 2px;
}

.care-step__autonomy-card--selected:focus-within {
  outline: none;
}

@media (prefers-reduced-motion: reduce) {
  .care-step__autonomy-card {
    transition: none;
  }
}

.care-step__autonomy-input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
  pointer-events: none;
}

.care-step__autonomy-label {
  font-family: var(--font-family-heading);
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--color-ink);
  line-height: 1.3;
}

.care-step__autonomy-card--selected .care-step__autonomy-label {
  color: var(--color-petrol);
}

.care-step__autonomy-description {
  font-size: 0.8125rem;
  color: var(--color-ink);
  opacity: 0.65;
  line-height: 1.45;
}

.care-step__autonomy-card--selected .care-step__autonomy-description {
  opacity: 0.8;
}
</style>
