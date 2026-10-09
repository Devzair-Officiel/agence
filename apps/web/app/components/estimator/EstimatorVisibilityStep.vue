<script setup lang="ts">
import { ESTIMATOR_VISIBILITY_OPTIONS } from "~/config/estimator-visibility-options"
import type { VisibilityNeedCode } from "~/types/estimator"

const props = defineProps<{
  modelValue: VisibilityNeedCode[]
}>()

const includedOptions = ESTIMATOR_VISIBILITY_OPTIONS.filter((option) => option.included)
const selectableOptions = ESTIMATOR_VISIBILITY_OPTIONS.filter((option) => !option.included)

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
      <div
        v-for="option in includedOptions"
        :key="option.value"
        class="visibility-step__included"
      >
        <span class="visibility-step__included-icon" aria-hidden="true">✓</span>
        <p class="visibility-step__included-text">
          <span class="visibility-step__included-label">
            {{ option.label }}
            <span class="visibility-step__included-badge">Inclus</span>
          </span>
          <span class="visibility-step__included-description">
            {{ option.description }} Intégré à tous nos projets, sans surcoût.
          </span>
        </p>
      </div>
      <div class="visibility-step__grid">
        <EstimatorCheckboxCard
          v-for="option in selectableOptions"
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

.visibility-step__included {
  display: flex;
  align-items: flex-start;
  gap: var(--space-3);
  margin-bottom: var(--space-4);
  padding: var(--space-4);
  border: 1px solid color-mix(in srgb, var(--color-petrol) 35%, transparent);
  border-radius: var(--radius-lg);
  background: color-mix(in srgb, var(--color-petrol) 6%, var(--color-cream-elevated));
}

.visibility-step__included-icon {
  flex: none;
  display: inline-grid;
  place-items: center;
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 50%;
  background: var(--color-petrol);
  color: var(--color-cream);
  font-size: 0.8125rem;
  line-height: 1;
}

.visibility-step__included-text {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
  margin: 0;
}

.visibility-step__included-label {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: var(--space-2);
  font-family: var(--font-family-heading);
  font-size: 0.9375rem;
  font-weight: 600;
  line-height: 1.3;
  color: var(--color-petrol);
}

.visibility-step__included-badge {
  padding: 0.125rem var(--space-2);
  border-radius: 999px;
  background: var(--color-petrol);
  color: var(--color-cream);
  font-family: var(--font-family-mono);
  font-size: 0.6875rem;
  font-weight: var(--font-weight-mono);
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.visibility-step__included-description {
  font-size: 0.8125rem;
  line-height: 1.45;
  color: var(--color-ink);
  opacity: 0.8;
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
