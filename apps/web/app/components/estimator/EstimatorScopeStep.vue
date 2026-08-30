<script setup lang="ts">
import { computed } from "vue"
import { getScaleConfig } from "~/config/estimator-scale-options"
import type { ProjectScaleCode, ProjectTypeCode } from "~/types/estimator"

const props = defineProps<{
  modelValue: ProjectScaleCode | null
  projectType: ProjectTypeCode
}>()

const emit = defineEmits<{
  "update:modelValue": [value: ProjectScaleCode]
}>()

const config = computed(() => getScaleConfig(props.projectType))
</script>

<template>
  <div class="scope-step">
    <fieldset class="scope-step__fieldset">
      <legend class="scope-step__legend" tabindex="-1">
        {{ config.legend }}
      </legend>
      <div class="scope-step__grid">
        <label
          v-for="option in config.options"
          :key="option.value"
          class="scope-card"
          :class="{ 'scope-card--selected': modelValue === option.value }"
        >
          <input
            class="scope-card__input"
            type="radio"
            name="project-scale"
            :value="option.value"
            :checked="modelValue === option.value"
            @change="emit('update:modelValue', option.value)"
          >
          <span class="scope-card__label">{{ option.label }}</span>
          <span class="scope-card__description">{{ option.description }}</span>
        </label>
      </div>
    </fieldset>
  </div>
</template>

<style scoped>
.scope-step__fieldset {
  border: none;
  margin: 0;
  padding: 0;
}

.scope-step__legend {
  font-family: var(--font-family-heading);
  font-size: clamp(1.125rem, 2vw, 1.375rem);
  font-weight: 600;
  color: var(--color-ink);
  margin-bottom: var(--space-6);
  display: block;
  width: 100%;
}

.scope-step__legend:focus {
  outline: none;
}

.scope-step__grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-3);
}

@media (min-width: 600px) {
  .scope-step__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.scope-card {
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

.scope-card:hover {
  border-color: color-mix(in srgb, var(--color-petrol) 35%, transparent);
  background: var(--color-sand);
}

.scope-card--selected {
  border-color: var(--color-petrol);
  background: color-mix(in srgb, var(--color-petrol) 6%, var(--color-cream-elevated));
}

.scope-card:focus-within {
  outline: 2px solid var(--focus-ring);
  outline-offset: 2px;
}

.scope-card--selected:focus-within {
  outline: none;
}

@media (prefers-reduced-motion: reduce) {
  .scope-card {
    transition: none;
  }
}

.scope-card__input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
  pointer-events: none;
}

.scope-card__label {
  font-family: var(--font-family-heading);
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--color-ink);
  line-height: 1.3;
}

.scope-card--selected .scope-card__label {
  color: var(--color-petrol);
}

.scope-card__description {
  font-size: 0.8125rem;
  color: var(--color-ink);
  opacity: 0.65;
  line-height: 1.45;
}

.scope-card--selected .scope-card__description {
  opacity: 0.8;
}

@media (max-width: 599px) {
  .scope-step__grid {
    gap: 0;
  }

  .scope-card {
    border-radius: 0;
  }

  .scope-card:first-child {
    border-top-left-radius: var(--radius-lg);
    border-top-right-radius: var(--radius-lg);
  }

  .scope-card:last-child {
    border-bottom-left-radius: var(--radius-lg);
    border-bottom-right-radius: var(--radius-lg);
  }

  .scope-card + .scope-card {
    border-top-width: 1px;
    border-top-color: var(--border-default);
  }

  .scope-card--selected:not(:first-child) {
    border-top-width: 2px;
    border-top-color: var(--color-petrol);
  }
}
</style>
