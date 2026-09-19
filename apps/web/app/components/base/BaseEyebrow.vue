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
