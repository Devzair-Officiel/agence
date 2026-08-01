<script setup lang="ts">
/**
 * Petit libellé de section (« eyebrow »).
 * Space Mono 700, majuscules, couleur pétrole par défaut, inverse sur fond sombre.
 */

interface Props {
  tone?: "default" | "inverse"
  as?: "p" | "span" | "div"
}

withDefaults(defineProps<Props>(), {
  tone: "default",
  as: "p",
})
</script>

<template>
  <component :is="as" class="base-eyebrow" :data-tone="tone">
    <slot />
  </component>
</template>

<style scoped>
.base-eyebrow {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem; /* 11px */
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text-accent);
  margin: 0;
}

.base-eyebrow[data-tone="inverse"] {
  color: var(--color-devzair-blue);
}
</style>
