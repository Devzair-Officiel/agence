<script setup lang="ts">
import { computed } from "vue"
import { getFeaturesForType } from "~/config/estimator-features"
import type { ProjectFeatureCode, ProjectTypeCode } from "~/types/estimator"

const props = defineProps<{
  modelValue: ProjectFeatureCode[]
  additionalFeatureNote: string
  projectType: ProjectTypeCode
}>()

const emit = defineEmits<{
  "update:modelValue": [values: ProjectFeatureCode[]]
  "update:additionalFeatureNote": [value: string]
}>()

const availableFeatures = computed(() => getFeaturesForType(props.projectType))

const noteLength = computed(() => props.additionalFeatureNote.length)

function toggle(code: ProjectFeatureCode): void {
  const current = props.modelValue
  const idx = current.indexOf(code)
  const updated =
    idx === -1 ? [...current, code] : current.filter((f) => f !== code)
  emit("update:modelValue", updated)
}

function onNoteInput(event: Event): void {
  const value = (event.target as HTMLTextAreaElement).value
  emit("update:additionalFeatureNote", value.slice(0, 400))
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

    <div class="features-step__extra">
      <label class="features-step__extra-label" for="additional-feature-note">
        Une autre fonctionnalité à prévoir ?
      </label>
      <span id="additional-feature-hint" class="features-step__extra-hint">
        Si votre besoin n'apparaît pas dans la liste, vous pouvez nous le préciser.
      </span>
      <div class="features-step__textarea-wrapper">
        <textarea
          id="additional-feature-note"
          class="features-step__textarea"
          :value="additionalFeatureNote"
          maxlength="400"
          rows="3"
          aria-describedby="additional-feature-hint additional-feature-disclaimer"
          @input="onNoteInput"
        />
        <span
          class="features-step__counter"
          aria-live="polite"
          aria-atomic="true"
        >{{ noteLength }} / 400</span>
      </div>
      <p id="additional-feature-disclaimer" class="features-step__disclaimer">
        Ce besoin sera étudié avec vous et n'est pas inclus automatiquement dans l'estimation.
      </p>
    </div>
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

.features-step__extra {
  margin-top: var(--space-8);
  padding-top: var(--space-6);
  border-top: 1px solid var(--border-default);
}

.features-step__extra-label {
  display: block;
  font-family: var(--font-family-heading);
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--color-ink);
  margin-bottom: var(--space-2);
}

.features-step__extra-hint {
  display: block;
  font-size: 0.8125rem;
  color: var(--color-ink);
  opacity: 0.65;
  margin-bottom: var(--space-3);
  line-height: 1.45;
}

.features-step__textarea-wrapper {
  position: relative;
}

.features-step__textarea {
  width: 100%;
  resize: vertical;
  min-height: 5rem;
  padding: var(--space-3) var(--space-4);
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  color: var(--color-ink);
  background: var(--color-cream-elevated);
  border: 1.5px solid var(--border-default);
  border-radius: var(--radius-md);
  outline: none;
  transition: border-color var(--duration-base) var(--ease-out);
  box-sizing: border-box;
}

.features-step__textarea:focus {
  border-color: var(--color-petrol);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-petrol) 20%, transparent);
}

@media (prefers-reduced-motion: reduce) {
  .features-step__textarea {
    transition: none;
  }
}

.features-step__counter {
  display: block;
  text-align: right;
  font-size: 0.75rem;
  color: var(--color-ink);
  opacity: 0.5;
  margin-top: var(--space-1);
}

.features-step__disclaimer {
  margin-top: var(--space-3);
  font-size: 0.8125rem;
  color: var(--color-ink);
  opacity: 0.55;
  line-height: 1.5;
  font-style: italic;
}
</style>
