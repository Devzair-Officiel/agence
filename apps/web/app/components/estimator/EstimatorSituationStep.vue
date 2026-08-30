<script setup lang="ts">
import { ESTIMATOR_SITUATION_OPTIONS } from "~/config/estimator-situation-options"
import type { CurrentSituationCode } from "~/types/estimator"

defineProps<{
  modelValue: CurrentSituationCode | null
}>()

const emit = defineEmits<{
  "update:modelValue": [value: CurrentSituationCode]
}>()
</script>

<template>
  <div class="situation-step">
    <fieldset class="situation-step__fieldset">
      <legend class="situation-step__legend" tabindex="-1">
        Avez-vous déjà une présence numérique pour ce projet ?
      </legend>
      <div class="situation-step__grid">
        <label
          v-for="option in ESTIMATOR_SITUATION_OPTIONS"
          :key="option.value"
          class="situation-card"
          :class="{ 'situation-card--selected': modelValue === option.value }"
        >
          <input
            class="situation-card__input"
            type="radio"
            name="current-situation"
            :value="option.value"
            :checked="modelValue === option.value"
            @change="emit('update:modelValue', option.value)"
          >
          <span class="situation-card__label">{{ option.label }}</span>
          <span class="situation-card__description">{{ option.description }}</span>
        </label>
      </div>
    </fieldset>
  </div>
</template>

<style scoped>
.situation-step__fieldset {
  border: none;
  margin: 0;
  padding: 0;
}

.situation-step__legend {
  font-family: var(--font-family-heading);
  font-size: clamp(1.125rem, 2vw, 1.375rem);
  font-weight: 600;
  color: var(--color-ink);
  margin-bottom: var(--space-6);
  display: block;
  width: 100%;
}

.situation-step__legend:focus {
  outline: none;
}

.situation-step__grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-3);
}

@media (min-width: 600px) {
  .situation-step__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.situation-card {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
  padding: var(--space-4);
  background: var(--color-cream-elevated);
  border: 2px solid transparent;
  border-radius: var(--radius-lg);
  cursor: pointer;
  min-height: var(--touch-target-min);
  transition:
    border-color var(--duration-base) var(--ease-out),
    background var(--duration-base) var(--ease-out);
}

.situation-card:hover {
  border-color: color-mix(in srgb, var(--color-petrol) 35%, transparent);
  background: var(--color-sand);
}

.situation-card--selected {
  border-color: var(--color-petrol);
  background: color-mix(in srgb, var(--color-petrol) 6%, var(--color-cream-elevated));
}

.situation-card:focus-within {
  outline: 2px solid var(--focus-ring);
  outline-offset: 2px;
}

.situation-card--selected:focus-within {
  outline: none;
}

@media (prefers-reduced-motion: reduce) {
  .situation-card {
    transition: none;
  }
}

.situation-card__input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
  pointer-events: none;
}

.situation-card__label {
  font-family: var(--font-family-heading);
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--color-ink);
  line-height: 1.3;
}

.situation-card--selected .situation-card__label {
  color: var(--color-petrol);
}

.situation-card__description {
  font-size: 0.8125rem;
  color: var(--color-ink);
  opacity: 0.65;
  line-height: 1.45;
}

.situation-card--selected .situation-card__description {
  opacity: 0.8;
}

@media (max-width: 599px) {
  .situation-step__grid {
    gap: 0;
  }

  .situation-card {
    border-radius: 0;
  }

  .situation-card:first-child {
    border-top-left-radius: var(--radius-lg);
    border-top-right-radius: var(--radius-lg);
  }

  .situation-card:last-child {
    border-bottom-left-radius: var(--radius-lg);
    border-bottom-right-radius: var(--radius-lg);
  }

  .situation-card + .situation-card {
    border-top-width: 1px;
    border-top-color: var(--border-default);
  }

  .situation-card--selected:not(:first-child) {
    border-top-width: 2px;
    border-top-color: var(--color-petrol);
  }
}
</style>
