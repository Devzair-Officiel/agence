<script setup lang="ts">
import {
  ESTIMATOR_PROJECT_TYPES,
  type ProjectTypeCode,
} from "~/config/estimator-project-types"

defineProps<{
  modelValue: ProjectTypeCode | null
}>()

const emit = defineEmits<{
  "update:modelValue": [value: ProjectTypeCode]
}>()

function select(value: ProjectTypeCode): void {
  emit("update:modelValue", value)
}
</script>

<template>
  <div class="project-type-step">
    <fieldset class="project-type-step__fieldset">
      <legend class="project-type-step__legend">
        Quel type de projet souhaitez-vous réaliser ?
      </legend>
      <div class="project-type-step__grid">
        <label
          v-for="type in ESTIMATOR_PROJECT_TYPES"
          :key="type.value"
          class="project-type-card"
          :class="{ 'project-type-card--selected': modelValue === type.value }"
        >
          <input
            class="project-type-card__input"
            type="radio"
            name="project-type"
            :value="type.value"
            :checked="modelValue === type.value"
            @change="select(type.value)"
          >
          <span class="project-type-card__label">{{ type.label }}</span>
          <span class="project-type-card__description">{{
            type.description
          }}</span>
        </label>
      </div>
    </fieldset>
  </div>
</template>

<style scoped>
.project-type-step__fieldset {
  border: none;
  margin: 0;
  padding: 0;
}

.project-type-step__legend {
  font-family: var(--font-family-heading);
  font-size: clamp(1.125rem, 2vw, 1.375rem);
  font-weight: 600;
  color: var(--color-ink);
  margin-bottom: var(--space-6);
  display: block;
  width: 100%;
}

.project-type-step__grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-3, 0.75rem);
}

@media (min-width: 600px) {
  .project-type-step__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 960px) {
  .project-type-step__grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.project-type-card {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
  padding: var(--space-4) var(--space-4);
  background: var(--color-cream-elevated);
  border: 2px solid transparent;
  border-radius: var(--radius-lg);
  cursor: pointer;
  min-height: var(--touch-target-min);
  transition:
    border-color var(--duration-base) var(--ease-out),
    background var(--duration-base) var(--ease-out),
    box-shadow var(--duration-base) var(--ease-out);
}

.project-type-card:hover {
  border-color: color-mix(in srgb, var(--color-petrol) 35%, transparent);
  background: var(--color-sand);
}

.project-type-card--selected {
  border-color: var(--color-petrol);
  background: color-mix(in srgb, var(--color-petrol) 6%, var(--color-cream-elevated));
}

.project-type-card:focus-within {
  outline: 2px solid var(--focus-ring);
  outline-offset: 2px;
}

.project-type-card--selected:focus-within {
  outline: none;
}

@media (prefers-reduced-motion: reduce) {
  .project-type-card {
    transition: none;
  }
}

/* Mobile (colonne unique) : liste avec filets séparateurs */
@media (max-width: 599px) {
  .project-type-step__grid {
    gap: 0;
  }

  .project-type-card {
    border-radius: 0;
  }

  .project-type-card:first-child {
    border-top-left-radius: var(--radius-lg);
    border-top-right-radius: var(--radius-lg);
  }

  .project-type-card:last-child {
    border-bottom-left-radius: var(--radius-lg);
    border-bottom-right-radius: var(--radius-lg);
  }

  /* Filet entre chaque option — purement visuel, sans nœud DOM */
  .project-type-card + .project-type-card {
    border-top-width: 1px;
    border-top-color: var(--border-default);
  }

  /* Option sélectionnée non-première : rétablir le contour petrol complet */
  .project-type-card--selected:not(:first-child) {
    border-top-width: 2px;
    border-top-color: var(--color-petrol);
  }
}

.project-type-card__input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
  pointer-events: none;
}

.project-type-card__label {
  font-family: var(--font-family-heading);
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--color-ink);
  line-height: 1.3;
}

.project-type-card__description {
  font-size: 0.8125rem;
  color: var(--color-ink);
  opacity: 0.65;
  line-height: 1.45;
}

.project-type-card--selected .project-type-card__label {
  color: var(--color-petrol);
}

.project-type-card--selected .project-type-card__description {
  opacity: 0.8;
}
</style>
