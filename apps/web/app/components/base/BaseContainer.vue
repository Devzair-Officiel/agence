<script setup lang="ts">
/**
 * Wrapper de largeur maximale + gouttières responsive.
 * Responsabilité unique : cadrer horizontalement le contenu.
 *
 * Trois largeurs disponibles :
 *   - default : colonne de lecture (--container-max, ~1280px) ;
 *   - wide    : sections orchestrées (--container-max-wide, ~1440px) ;
 *   - full    : quasi full-bleed (--container-max-full, ~1600px).
 *
 * Les blocs de texte long doivent conserver un `max-width` interne
 * (~72ch) même dans un conteneur wide.
 */

interface Props {
  as?: keyof HTMLElementTagNameMap
  width?: "default" | "wide" | "full"
}

withDefaults(defineProps<Props>(), {
  as: "div",
  width: "default",
})
</script>

<template>
  <component :is="as" class="base-container" :data-width="width">
    <slot />
  </component>
</template>

<style scoped>
.base-container {
  width: 100%;
  max-width: var(--container-max);
  margin-inline: auto;
  padding-inline: var(--container-gutter-mobile);
}

.base-container[data-width="wide"] {
  max-width: var(--container-max-wide);
}

.base-container[data-width="full"] {
  max-width: var(--container-max-full);
}

@media (min-width: 480px) {
  .base-container {
    padding-inline: var(--container-gutter-tablet);
  }
}

@media (min-width: 1024px) {
  .base-container {
    padding-inline: var(--container-gutter-desktop);
  }
}

@media (min-width: 1440px) {
  .base-container[data-width="wide"],
  .base-container[data-width="full"] {
    padding-inline: var(--container-gutter-wide);
  }
}
</style>
