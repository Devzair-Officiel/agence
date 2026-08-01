<script setup lang="ts">
/**
 * Wrapper de largeur maximale + gouttières responsive.
 * Responsabilité unique : cadrer horizontalement le contenu.
 */

interface Props {
  as?: keyof HTMLElementTagNameMap
}

withDefaults(defineProps<Props>(), {
  as: "div",
})
</script>

<template>
  <component :is="as" class="base-container">
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
</style>
