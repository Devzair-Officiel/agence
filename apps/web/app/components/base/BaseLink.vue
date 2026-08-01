<script setup lang="ts">
import { computed } from "vue"

/**
 * Lien éditorial souligné avec flèche.
 * Utiliser BaseButton lorsqu'il s'agit d'une action ; utiliser ce composant
 * pour les liens de lecture (« Lire l'étude de cas → »).
 */

interface Props {
  to?: string
  href?: string
  tone?: "default" | "inverse"
  /** Cf. BaseButton : rendre <a href> tant que la route n'existe pas encore. */
  external?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  to: undefined,
  href: undefined,
  tone: "default",
  external: false,
})

const isExternal = computed(() => Boolean(props.href))
</script>

<template>
  <NuxtLink
    v-if="to"
    :to="to"
    :external="external || undefined"
    class="base-link"
    :data-tone="tone"
  >
    <span class="base-link__label"><slot /></span>
    <span class="base-link__arrow" aria-hidden="true">→</span>
  </NuxtLink>

  <a
    v-else-if="isExternal"
    :href="href"
    class="base-link"
    :data-tone="tone"
    rel="noopener noreferrer"
    target="_blank"
  >
    <span class="base-link__label"><slot /></span>
    <span class="base-link__arrow" aria-hidden="true">→</span>
  </a>
</template>

<style scoped>
.base-link {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 0.9375rem;
  color: var(--text-accent);
  text-decoration: none;
  padding-bottom: 3px;
  border-bottom: 1px solid currentColor;
  transition: color var(--duration-fast) var(--ease-out);
}

.base-link[data-tone="inverse"] {
  color: var(--color-devzair-blue);
}

.base-link:hover {
  color: var(--text-primary);
}

.base-link[data-tone="inverse"]:hover {
  color: var(--text-inverse);
}

.base-link__arrow {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  transition: transform var(--duration-fast) var(--ease-out);
}

.base-link:hover .base-link__arrow {
  transform: translateX(2px);
}

@media (prefers-reduced-motion: reduce) {
  .base-link,
  .base-link__arrow {
    transition: none;
  }
}
</style>
