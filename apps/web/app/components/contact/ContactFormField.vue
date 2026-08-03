<script setup lang="ts">
import { computed, useId } from "vue"

/**
 * Primitive de champ formulaire — label + input/textarea + hint + erreur.
 *
 * Responsabilités :
 *   - garantir un couplage `label[for]` ↔ `input[id]` unique par instance ;
 *   - exposer `aria-describedby` sur l'input pour lier l'aide (hint) et le
 *     message d'erreur éventuel ;
 *   - refléter l'état de validité (`aria-invalid`) et le caractère requis
 *     (`aria-required`) au niveau ARIA — les attributs HTML natifs (`required`,
 *     `maxlength`) restent utiles pour le navigateur mais ne sont pas la source
 *     de vérité (la validation finale est côté serveur).
 *
 * Le composant est volontairement minimal : il ne construit pas d'état, ne
 * connaît pas Turnstile, ne parle pas de submit — il rend un champ contrôlé
 * (`v-model`) et retourne toutes ces responsabilités à ContactForm.vue.
 */

type ControlType = "text" | "email" | "tel" | "textarea"

interface Props {
  modelValue: string
  label: string
  type?: ControlType
  name: string
  required?: boolean
  autocomplete?: string
  hint?: string
  error?: string
  maxlength?: number
  minlength?: number
  rows?: number
  pattern?: string
  placeholder?: string
  optionalLabel?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  type: "text",
  required: false,
  autocomplete: undefined,
  hint: undefined,
  error: undefined,
  maxlength: undefined,
  minlength: undefined,
  rows: 5,
  pattern: undefined,
  placeholder: undefined,
  optionalLabel: false,
})

const emit = defineEmits<{
  "update:modelValue": [value: string]
}>()

const inputId = useId()
const hintId = `${inputId}-hint`
const errorId = `${inputId}-error`

const describedBy = computed(() => {
  const ids: string[] = []
  if (props.hint) ids.push(hintId)
  if (props.error) ids.push(errorId)
  return ids.length > 0 ? ids.join(" ") : undefined
})

function onInput(event: Event): void {
  const target = event.target as HTMLInputElement | HTMLTextAreaElement | null
  if (!target) return
  emit("update:modelValue", target.value)
}
</script>

<template>
  <div class="contact-field" :data-invalid="Boolean(error) || undefined">
    <div class="contact-field__label-row">
      <label :for="inputId" class="contact-field__label">
        {{ label }}
        <span v-if="required" class="contact-field__required" aria-hidden="true">*</span>
        <span v-else-if="optionalLabel" class="contact-field__optional">(optionnel)</span>
      </label>
      <slot name="label-append" />
    </div>

    <textarea
      v-if="type === 'textarea'"
      :id="inputId"
      class="contact-field__control"
      :name="name"
      :value="modelValue"
      :required="required"
      :maxlength="maxlength"
      :minlength="minlength"
      :rows="rows"
      :autocomplete="autocomplete"
      :placeholder="placeholder"
      :aria-required="required || undefined"
      :aria-invalid="Boolean(error) || undefined"
      :aria-describedby="describedBy"
      @input="onInput"
    />
    <input
      v-else
      :id="inputId"
      class="contact-field__control"
      :type="type"
      :name="name"
      :value="modelValue"
      :required="required"
      :maxlength="maxlength"
      :minlength="minlength"
      :autocomplete="autocomplete"
      :pattern="pattern"
      :placeholder="placeholder"
      :aria-required="required || undefined"
      :aria-invalid="Boolean(error) || undefined"
      :aria-describedby="describedBy"
      @input="onInput"
    >

    <p v-if="hint" :id="hintId" class="contact-field__hint">{{ hint }}</p>
    <p v-if="error" :id="errorId" class="contact-field__error">{{ error }}</p>
  </div>
</template>

<style scoped>
.contact-field {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.contact-field__label-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: var(--space-3);
}

.contact-field__label {
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.contact-field__required {
  color: var(--color-status-error);
  margin-left: 0.15rem;
}

.contact-field__optional {
  margin-left: 0.35rem;
  font-weight: var(--font-weight-body);
  font-size: 0.8125rem;
  color: var(--text-muted);
}

.contact-field__control {
  font: inherit;
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.5;
  color: var(--text-primary);
  background-color: #fcfbf8;
  border: 1px solid var(--border-default);
  border-radius: var(--radius-md);
  padding: 0.8125rem var(--space-4);
  min-height: var(--touch-target-min);
  transition: border-color var(--duration-fast) var(--ease-out),
    box-shadow var(--duration-fast) var(--ease-out);
}

.contact-field__control::placeholder {
  color: #a29d93;
}

textarea.contact-field__control {
  min-height: 8.5rem;
  resize: vertical;
}

.contact-field__control:focus-visible {
  outline: none;
  border-color: var(--color-petrol);
  box-shadow: 0 0 0 3px rgba(46, 134, 217, 0.28);
}

.contact-field[data-invalid="true"] .contact-field__control {
  border-color: var(--color-status-error);
  box-shadow: 0 0 0 3px rgba(178, 58, 46, 0.2);
}

.contact-field__hint {
  margin: 0;
  font-size: 0.8125rem;
  color: var(--text-muted);
}

.contact-field__error {
  margin: 0;
  font-size: 0.8125rem;
  font-weight: var(--font-weight-body-strong);
  color: var(--color-cream);
  background-color: var(--color-status-error);
  padding: 0.375rem 0.5rem;
  border-radius: var(--radius-sm);
}

@media (prefers-reduced-motion: reduce) {
  .contact-field__control {
    transition: none;
  }
}
</style>
