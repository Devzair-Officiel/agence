<script setup lang="ts">
import { computed } from "vue"

/**
 * Bouton polymorphe (button | NuxtLink interne | ancre externe).
 *
 * Règles :
 *   - variante primary : action pétrole
 *   - variante secondary : contour discret
 *   - `to` interne → NuxtLink (préserve le SSR + le préchargement)
 *   - `href` externe → <a> avec rel="noopener noreferrer"
 *   - sinon → <button>
 *   - disabled et loading n'existent que pour <button>
 *   - hauteur minimale WCAG 2.2 : 44×44px
 */

interface Props {
  to?: string
  href?: string
  type?: "button" | "submit" | "reset"
  variant?: "primary" | "secondary"
  disabled?: boolean
  loading?: boolean
  ariaLabel?: string
  /**
   * Forcer un rendu <a href> même pour un `to` interne : utile tant que la route
   * cible n'existe pas encore (évite un warning vue-router).
   */
  external?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  to: undefined,
  href: undefined,
  type: "button",
  variant: "primary",
  disabled: false,
  loading: false,
  ariaLabel: undefined,
  external: false,
})

const isExternal = computed(() => Boolean(props.href))
const isInternal = computed(() => Boolean(props.to))
const isButton = computed(() => !isExternal.value && !isInternal.value)

const isDisabled = computed(() => props.disabled || props.loading)
</script>

<template>
  <NuxtLink
    v-if="isInternal && !isDisabled"
    :to="to"
    :external="external || undefined"
    class="base-button"
    :data-variant="variant"
    :aria-label="ariaLabel"
  >
    <span v-if="loading" class="base-button__spinner" aria-hidden="true" />
    <span class="base-button__label"><slot /></span>
    <span v-if="$slots.icon" class="base-button__icon" aria-hidden="true">
      <slot name="icon" />
    </span>
  </NuxtLink>

  <a
    v-else-if="isExternal && !isDisabled"
    :href="href"
    class="base-button"
    :data-variant="variant"
    rel="noopener noreferrer"
    target="_blank"
    :aria-label="ariaLabel"
  >
    <span v-if="loading" class="base-button__spinner" aria-hidden="true" />
    <span class="base-button__label"><slot /></span>
    <span v-if="$slots.icon" class="base-button__icon" aria-hidden="true">
      <slot name="icon" />
    </span>
  </a>

  <button
    v-else
    class="base-button"
    :type="type"
    :data-variant="variant"
    :disabled="isDisabled"
    :aria-busy="loading || undefined"
    :aria-label="ariaLabel"
  >
    <span v-if="loading" class="base-button__spinner" aria-hidden="true" />
    <span class="base-button__label"><slot /></span>
    <span v-if="$slots.icon" class="base-button__icon" aria-hidden="true">
      <slot name="icon" />
    </span>
  </button>
</template>

<style scoped>
.base-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-2);
  min-height: var(--touch-target-min);
  padding: var(--space-3) var(--space-5);
  border-radius: var(--radius-pill);
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 1rem;
  line-height: 1;
  text-decoration: none;
  cursor: pointer;
  transition:
    background-color var(--duration-fast) var(--ease-out),
    color var(--duration-fast) var(--ease-out),
    box-shadow var(--duration-fast) var(--ease-out),
    transform var(--duration-instant) var(--ease-out);
}

.base-button[data-variant="primary"] {
  background-color: var(--action-primary);
  color: var(--action-on-primary);
  box-shadow: var(--shadow-md);
}

.base-button[data-variant="primary"]:hover:not([disabled]) {
  background-color: var(--action-primary-hover);
  transform: translateY(-1px);
}

.base-button[data-variant="primary"]:active:not([disabled]) {
  background-color: var(--action-primary-active);
  transform: translateY(1px);
}

.base-button[data-variant="secondary"] {
  background-color: transparent;
  color: var(--text-primary);
  border: 1px solid var(--border-strong);
}

.base-button[data-variant="secondary"]:hover:not([disabled]) {
  background-color: var(--color-ink);
  color: var(--color-cream);
  border-color: var(--color-ink);
}

.base-button[disabled],
.base-button[aria-disabled="true"] {
  background-color: var(--action-primary-disabled);
  color: var(--color-cream);
  cursor: not-allowed;
  box-shadow: none;
  transform: none;
  opacity: 0.7;
}

.base-button[data-variant="secondary"][disabled] {
  background-color: transparent;
  color: var(--text-muted);
  border-color: var(--border-default);
}

.base-button__icon {
  display: inline-flex;
  align-items: center;
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
}

.base-button__spinner {
  width: 0.875rem;
  height: 0.875rem;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: currentColor;
  border-radius: 50%;
  animation: base-button-spin var(--duration-slow) linear infinite;
}

@keyframes base-button-spin {
  to {
    transform: rotate(360deg);
  }
}

@media (prefers-reduced-motion: reduce) {
  .base-button {
    transition: none;
  }
  .base-button__spinner {
    animation-duration: 1.5s;
  }
}
</style>
