<script setup lang="ts">
import { ref } from "vue"
import {
  CONTENT_TEXT_OPTIONS,
  IDENTITY_OPTIONS,
  type ContentStatus,
  type IdentityStatus,
} from "~/config/estimator-content-options"
import type { ContentNeedCode } from "~/types/estimator"

const props = defineProps<{
  modelValue: ContentNeedCode[]
}>()

const emit = defineEmits<{
  "update:modelValue": [values: ContentNeedCode[]]
}>()

function identityFromCodes(codes: ContentNeedCode[]): IdentityStatus {
  if (codes.includes("visual_identity_needed")) return "needed"
  if (codes.includes("visual_identity_partial")) return "partial"
  return "ready"
}

function contentFromCodes(codes: ContentNeedCode[]): ContentStatus {
  if (codes.includes("content_to_write")) return "to_write"
  if (codes.includes("content_with_help")) return "with_help"
  return "ready"
}

const identityStatus = ref<IdentityStatus>(identityFromCodes(props.modelValue))
const contentStatus = ref<ContentStatus>(contentFromCodes(props.modelValue))
const photographyNeeded = ref<boolean>(
  props.modelValue.includes("photography_needed"),
)

function buildAndEmit(): void {
  const codes: ContentNeedCode[] = []
  if (identityStatus.value === "needed") codes.push("visual_identity_needed")
  if (identityStatus.value === "partial") codes.push("visual_identity_partial")
  if (contentStatus.value === "to_write") codes.push("content_to_write")
  if (contentStatus.value === "with_help") codes.push("content_with_help")
  if (photographyNeeded.value) codes.push("photography_needed")
  emit("update:modelValue", codes)
}

function setIdentity(value: IdentityStatus): void {
  identityStatus.value = value
  buildAndEmit()
}

function setContent(value: ContentStatus): void {
  contentStatus.value = value
  buildAndEmit()
}

function togglePhotography(): void {
  photographyNeeded.value = !photographyNeeded.value
  buildAndEmit()
}
</script>

<template>
  <div class="content-step">
    <h2 class="content-step__heading" tabindex="-1">
      Contenu et ressources visuelles
      <span class="content-step__hint">Sélection optionnelle.</span>
    </h2>

    <div class="content-step__section">
      <fieldset class="content-step__fieldset">
        <legend class="content-step__legend">Identité visuelle</legend>
        <div class="content-step__options">
          <label
            v-for="option in IDENTITY_OPTIONS"
            :key="option.value"
            class="content-option-card"
            :class="{ 'content-option-card--selected': identityStatus === option.value }"
          >
            <input
              class="content-option-card__input"
              type="radio"
              name="identity-status"
              :value="option.value"
              :checked="identityStatus === option.value"
              @change="setIdentity(option.value)"
            >
            <span class="content-option-card__label">{{ option.label }}</span>
            <span class="content-option-card__description">{{ option.description }}</span>
          </label>
        </div>
      </fieldset>
    </div>

    <div class="content-step__section">
      <fieldset class="content-step__fieldset">
        <legend class="content-step__legend">Contenu textuel</legend>
        <div class="content-step__options">
          <label
            v-for="option in CONTENT_TEXT_OPTIONS"
            :key="option.value"
            class="content-option-card"
            :class="{ 'content-option-card--selected': contentStatus === option.value }"
          >
            <input
              class="content-option-card__input"
              type="radio"
              name="content-status"
              :value="option.value"
              :checked="contentStatus === option.value"
              @change="setContent(option.value)"
            >
            <span class="content-option-card__label">{{ option.label }}</span>
            <span class="content-option-card__description">{{ option.description }}</span>
          </label>
        </div>
      </fieldset>
    </div>

    <div class="content-step__section">
      <p class="content-step__legend" aria-hidden="true">Photographie</p>
      <label
        class="content-option-card"
        :class="{ 'content-option-card--selected': photographyNeeded }"
      >
        <input
          class="content-option-card__input"
          type="checkbox"
          name="photography"
          :checked="photographyNeeded"
          @change="togglePhotography"
        >
        <span class="content-option-card__label">Photos ou visuels à créer</span>
        <span class="content-option-card__description">
          Shooting photo, illustrations ou infographies à produire.
        </span>
      </label>
    </div>
  </div>
</template>

<style scoped>
.content-step__heading {
  font-family: var(--font-family-heading);
  font-size: clamp(1.125rem, 2vw, 1.375rem);
  font-weight: 600;
  color: var(--color-ink);
  margin: 0 0 var(--space-2);
}

.content-step__heading:focus {
  outline: none;
}

.content-step__hint {
  display: block;
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  font-weight: 400;
  color: var(--color-ink);
  opacity: 0.6;
  margin-top: var(--space-1);
}

.content-step__section {
  margin-top: var(--space-6);
}

.content-step__fieldset {
  border: none;
  margin: 0;
  padding: 0;
}

.content-step__legend {
  font-family: var(--font-family-heading);
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--color-ink);
  margin: 0 0 var(--space-3);
  display: block;
}

.content-step__options {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-2);
}

@media (min-width: 600px) {
  .content-step__options {
    grid-template-columns: repeat(3, 1fr);
  }
}

.content-option-card {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding: var(--space-3) var(--space-4);
  background: var(--color-cream-elevated);
  border: 2px solid transparent;
  border-radius: var(--radius-md);
  cursor: pointer;
  transition:
    border-color var(--duration-base) var(--ease-out),
    background var(--duration-base) var(--ease-out);
}

.content-option-card:hover {
  border-color: color-mix(in srgb, var(--color-petrol) 35%, transparent);
  background: var(--color-sand);
}

.content-option-card--selected {
  border-color: var(--color-petrol);
  background: color-mix(in srgb, var(--color-petrol) 6%, var(--color-cream-elevated));
}

.content-option-card:focus-within {
  outline: 2px solid var(--focus-ring);
  outline-offset: 2px;
}

.content-option-card--selected:focus-within {
  outline: none;
}

@media (prefers-reduced-motion: reduce) {
  .content-option-card {
    transition: none;
  }
}

.content-option-card__input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
  pointer-events: none;
}

.content-option-card__label {
  font-family: var(--font-family-heading);
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-ink);
  line-height: 1.3;
}

.content-option-card--selected .content-option-card__label {
  color: var(--color-petrol);
}

.content-option-card__description {
  font-size: 0.75rem;
  color: var(--color-ink);
  opacity: 0.65;
  line-height: 1.45;
}

.content-option-card--selected .content-option-card__description {
  opacity: 0.8;
}
</style>
