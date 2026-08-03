<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * En-tête éditorial des pages institutionnelles (`/agence`, `/expertises`, …).
 *
 * Rôle strict : présenter la promesse éditoriale d'une page avec un H1
 * unique, une introduction et — quand la hiérarchie l'exige — un enfant
 * optionnel (contenu additionnel, illustration légère). Aucun composant
 * lourd (SVG animé, formulaire) ne doit être inséré ici : ce sont les
 * sections suivantes qui portent les interactions.
 *
 * Accessibilité :
 *   - le H1 reçoit un `id` stable (`editorial-hero-title`) pour être ciblé
 *     par `aria-labelledby` sur la `<section>` parente si nécessaire ;
 *   - l'eyebrow n'est pas un titre — c'est un `<p>` typé
 *     (`BaseEyebrow`) qui précède visuellement le H1 sans casser la
 *     hiérarchie des lecteurs d'écran ;
 *   - fond `cream` par défaut, alignement conforme au reste du design
 *     system ; aucune animation.
 */

interface Props {
  eyebrow: string
  title: string
  lead: string
}

defineProps<Props>()
</script>

<template>
  <section class="editorial-hero" aria-labelledby="editorial-hero-title">
    <BaseContainer class="editorial-hero__container">
      <div class="editorial-hero__content">
        <BaseEyebrow class="editorial-hero__eyebrow">
          {{ eyebrow }}
        </BaseEyebrow>
        <h1 id="editorial-hero-title" class="editorial-hero__title">
          {{ title }}
        </h1>
        <p class="editorial-hero__lead">{{ lead }}</p>
        <div v-if="$slots.actions" class="editorial-hero__actions">
          <slot name="actions" />
        </div>
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.editorial-hero {
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-16) var(--space-12);
}

.editorial-hero__container {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
}

.editorial-hero__content {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  max-width: 52rem;
}

.editorial-hero__eyebrow {
  margin-bottom: var(--space-2);
}

.editorial-hero__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2rem, 4.6vw, 3.25rem);
  line-height: 1.1;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 26ch;
}

.editorial-hero__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.5vw, 1.125rem);
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

.editorial-hero__actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-3);
  margin-top: var(--space-2);
}

@media (min-width: 768px) {
  .editorial-hero {
    padding-block: var(--space-20) var(--space-16);
  }
}
</style>
