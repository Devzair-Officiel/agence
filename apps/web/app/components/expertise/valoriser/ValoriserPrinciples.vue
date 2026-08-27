<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import type { ExpertiseBenefit } from "~/config/expertise-pages"

/**
 * « Bénéfices concrets » de la page Valoriser — traités comme trois
 * principes éditoriaux (VOIX / IMAGE / CLARTÉ) plutôt que trois cartes
 * SaaS. Chaque principe reprend le `title` et la `description` verbatim
 * depuis `expertise-pages.ts`.
 *
 * Direction visuelle :
 *   - beaucoup d'espace vertical ;
 *   - grand mot-clef en Space Mono (VOIX / IMAGE / CLARTÉ) — un label
 *     narratif, pas une métrique ;
 *   - séparateur mono entre principes ;
 *   - trois « colonnes » desktop volontairement non rigides (grille
 *     asymétrique 12 col.), pile mobile.
 *
 * `labels` contient les mots-clefs narratifs affichés en tête de chaque
 * principe. Le mapping se fait par ordre — le contenu source reste la
 * `benefits` config. Si le nombre de benefits diffère, on tombe sur un
 * label par défaut plutôt que d'inventer un principe.
 */

interface Props {
  benefits: readonly ExpertiseBenefit[]
}

defineProps<Props>()

const labels = ["Voix", "Image", "Clarté"] as const

const generatedId = useId()
const titleId = computed(() => `valoriser-principles-title-${generatedId}`)
</script>

<template>
  <section
    class="valoriser-principles"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="valoriser-principles__container">
      <header class="valoriser-principles__header">
        <BaseEyebrow class="valoriser-principles__eyebrow">
          Bénéfices concrets
        </BaseEyebrow>
        <h2 :id="titleId" class="valoriser-principles__title">
          Une image, une voix, une offre cohérentes.
        </h2>
      </header>

      <ol class="valoriser-principles__list" role="list">
        <li
          v-for="(benefit, index) in benefits"
          :key="benefit.title"
          class="valoriser-principles__item"
          :data-position="index + 1"
        >
          <p class="valoriser-principles__item-index" aria-hidden="true">
            0{{ index + 1 }}
          </p>
          <p class="valoriser-principles__item-label" aria-hidden="true">
            {{ labels[index] ?? "" }}
          </p>
          <h3 class="valoriser-principles__item-title">
            {{ benefit.title }}
          </h3>
          <p class="valoriser-principles__item-description">
            {{ benefit.description }}
          </p>
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
.valoriser-principles {
  background-color: var(--background-secondary);
  color: var(--text-primary);
  padding-block: var(--space-16) var(--space-16);
}

.valoriser-principles__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.valoriser-principles__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 46rem;
}

.valoriser-principles__eyebrow {
  margin: 0;
}

.valoriser-principles__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.625rem, 3.4vw, 2.25rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 28ch;
}

.valoriser-principles__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
}

.valoriser-principles__item {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding-top: var(--space-6);
  border-top: 1px solid var(--border-default);
}

.valoriser-principles__item-index {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.10em;
  color: var(--color-petrol);
  margin: 0;
}

.valoriser-principles__item-label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: clamp(1.375rem, 3vw, 1.875rem);
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
  line-height: 1;
}

.valoriser-principles__item-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.125rem, 1.8vw, 1.375rem);
  line-height: 1.2;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 24ch;
}

.valoriser-principles__item-description {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 44ch;
}

@media (min-width: 1024px) {
  .valoriser-principles__list {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--space-8);
  }

  .valoriser-principles__item {
    padding-top: var(--space-8);
  }
}
</style>
