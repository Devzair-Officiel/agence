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
    <label :for="inputId" class="contact-field__label">
      {{ label }}
      <span v-if="required" class="contact-field__required" aria-hidden="true">*</span>
    </label>

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

.contact-field__label {
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 0.9375rem;
  color: var(--text-inverse);
}

.contact-field__required {
  color: var(--color-cream-muted);
  margin-left: 0.15rem;
}

.contact-field__control {
  font: inherit;
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.5;
  color: var(--text-primary);
  background-color: var(--color-cream);
  border: 1px solid var(--border-strong);
  border-radius: var(--radius-md);
  padding: var(--space-3) var(--space-3);
  min-height: var(--touch-target-min);
  transition: border-color var(--duration-fast) var(--ease-out),
    box-shadow var(--duration-fast) var(--ease-out);
}

textarea.contact-field__control {
  min-height: 8rem;
  resize: vertical;
}

.contact-field__control:focus-visible {
  outline: 2px solid var(--color-devzair-blue);
  outline-offset: 2px;
  border-color: var(--color-devzair-blue);
}

.contact-field[data-invalid="true"] .contact-field__control {
  border-color: var(--color-status-error);
  box-shadow: inset 0 0 0 1px var(--color-status-error);
}

.contact-field__hint {
  margin: 0;
  font-size: 0.8125rem;
  color: var(--color-cream-muted);
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
