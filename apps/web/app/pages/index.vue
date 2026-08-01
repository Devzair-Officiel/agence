<script setup lang="ts">
/**
 * Page d'accueil temporaire (Phase 3).
 *
 * La vraie page d'accueil (hero + écosystème 5 pôles) sera implémentée dans le
 * prochain lot. En attendant, on donne un point d'entrée honnête vers la
 * preview du design system, sans contenu marketing inventé.
 */
import BaseButton from "~/components/base/BaseButton.vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

useSeoMeta({
  title: "Devzair — Chantier en cours",
  robots: "noindex, nofollow",
})
</script>

<template>
  <BaseContainer as="section" class="index">
    <BaseEyebrow>Chantier en cours</BaseEyebrow>
    <h1 class="index__title">Le site Devzair est en construction.</h1>
    <p class="index__lead">
      Les fondations du design system sont posées. La page d'accueil publique
      sera livrée à l'étape suivante.
    </p>
    <BaseButton to="/design-preview" variant="primary">
      Voir la preview du design system
      <template #icon>→</template>
    </BaseButton>
  </BaseContainer>
</template>

<style scoped>
.index {
  padding-block: var(--space-16);
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  align-items: flex-start;
}

.index__title {
  max-width: 22ch;
}

.index__lead {
  max-width: 55ch;
  color: var(--text-secondary);
}
</style>
