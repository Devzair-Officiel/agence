<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import type { ExpertiseBenefit } from "~/config/expertise-pages"

/**
 * « Bénéfices concrets » de la page Faire évoluer — trois principes
 * courts (SANTÉ / CLARTÉ / RYTHME) qui reprennent le `title` et la
 * `description` verbatim depuis `expertise-pages.ts`.
 *
 * Correspondance narrative :
 *   - benefit 01 « Un site à jour »              → SANTÉ
 *   - benefit 02 « Des décisions informées »     → CLARTÉ
 *   - benefit 03 « Une réactivité maîtrisée »    → RYTHME
 *
 * Direction visuelle :
 *   - mot-clef Space Mono petrol en grand ;
 *   - séparateurs mono petrol ;
 *   - grille 3 colonnes desktop, pile mobile ;
 *   - fond cream (cohérent avec le reste de la page qui alterne
 *     cream / navy / cream).
 */

interface Props {
  benefits: readonly ExpertiseBenefit[]
}

defineProps<Props>()

const labels = ["Santé", "Clarté", "Rythme"] as const

const generatedId = useId()
const titleId = computed(() => `faire-evoluer-principles-title-${generatedId}`)
</script>

<template>
  <section
    class="faire-evoluer-principles"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="faire-evoluer-principles__container">
      <header class="faire-evoluer-principles__header">
        <BaseEyebrow class="faire-evoluer-principles__eyebrow">
          Bénéfices concrets
        </BaseEyebrow>
        <h2 :id="titleId" class="faire-evoluer-principles__title">
          Stable sans rester immobile.
        </h2>
      </header>

      <ol class="faire-evoluer-principles__list" role="list">
        <li
          v-for="(benefit, index) in benefits"
          :key="benefit.title"
          class="faire-evoluer-principles__item"
          :data-position="index + 1"
        >
          <p class="faire-evoluer-principles__item-index" aria-hidden="true">
            0{{ index + 1 }}
          </p>
          <p class="faire-evoluer-principles__item-label" aria-hidden="true">
            {{ labels[index] ?? "" }}
          </p>
          <h3 class="faire-evoluer-principles__item-title">
            {{ benefit.title }}
          </h3>
          <p class="faire-evoluer-principles__item-description">
            {{ benefit.description }}
          </p>
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
.faire-evoluer-principles {
  background-color: var(--background-secondary);
  color: var(--text-primary);
  padding-block: var(--space-16) var(--space-16);
}

.faire-evoluer-principles__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.faire-evoluer-principles__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 46rem;
}

.faire-evoluer-principles__eyebrow {
  margin: 0;
}

.faire-evoluer-principles__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.625rem, 3.4vw, 2.25rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 26ch;
}

.faire-evoluer-principles__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
}

.faire-evoluer-principles__item {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding-top: var(--space-6);
  border-top: 1px solid var(--border-default);
}

.faire-evoluer-principles__item-index {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.10em;
  color: var(--color-petrol);
  margin: 0;
}

.faire-evoluer-principles__item-label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: clamp(1.375rem, 3vw, 1.875rem);
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
  line-height: 1;
}

.faire-evoluer-principles__item-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.125rem, 1.8vw, 1.375rem);
  line-height: 1.2;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 24ch;
}

.faire-evoluer-principles__item-description {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 44ch;
}

@media (min-width: 1024px) {
  .faire-evoluer-principles__list {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--space-8);
  }

  .faire-evoluer-principles__item {
    padding-top: var(--space-8);
  }
}
</style>
