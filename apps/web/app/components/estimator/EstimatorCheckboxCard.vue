<script setup lang="ts">
defineProps<{
  value: string
  label: string
  description: string
  checked: boolean
  name: string
}>()

const emit = defineEmits<{
  toggle: [value: string]
}>()
</script>

<template>
  <label
    class="estimator-checkbox-card"
    :class="{ 'estimator-checkbox-card--checked': checked }"
  >
    <input
      class="estimator-checkbox-card__input"
      type="checkbox"
      :name="name"
      :value="value"
      :checked="checked"
      @change="emit('toggle', value)"
    >
    <span class="estimator-checkbox-card__label">{{ label }}</span>
    <span class="estimator-checkbox-card__description">{{ description }}</span>
  </label>
</template>

<style scoped>
.estimator-checkbox-card {
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

.estimator-checkbox-card:hover {
  border-color: color-mix(in srgb, var(--color-petrol) 35%, transparent);
  background: var(--color-sand);
}

.estimator-checkbox-card--checked {
  border-color: var(--color-petrol);
  background: color-mix(in srgb, var(--color-petrol) 6%, var(--color-cream-elevated));
}

.estimator-checkbox-card:focus-within {
  outline: 2px solid var(--focus-ring);
  outline-offset: 2px;
}

.estimator-checkbox-card--checked:focus-within {
  outline: none;
}

@media (prefers-reduced-motion: reduce) {
  .estimator-checkbox-card {
    transition: none;
  }
}

.estimator-checkbox-card__input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
  pointer-events: none;
}

.estimator-checkbox-card__label {
  font-family: var(--font-family-heading);
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--color-ink);
  line-height: 1.3;
}

.estimator-checkbox-card--checked .estimator-checkbox-card__label {
  color: var(--color-petrol);
}

.estimator-checkbox-card__description {
  font-size: 0.8125rem;
  color: var(--color-ink);
  opacity: 0.65;
  line-height: 1.45;
}

.estimator-checkbox-card--checked .estimator-checkbox-card__description {
  opacity: 0.8;
}
</style>
