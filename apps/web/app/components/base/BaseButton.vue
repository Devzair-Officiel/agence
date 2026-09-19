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
