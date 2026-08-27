<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import type { ExpertiseBenefit } from "~/config/expertise-pages"

/**
 * « Bénéfices concrets » de la page Visibilité — traités comme trois
 * principes cartographiques (DURÉE / PERTINENCE / PROXIMITÉ) plutôt que
 * trois cartes SaaS. Chaque principe reprend le `title` et la
 * `description` verbatim depuis `expertise-pages.ts`.
 *
 * Correspondance narrative :
 *   - benefit 01 « Une visibilité durable »        → DURÉE
 *   - benefit 02 « Un trafic qualifié »            → PERTINENCE
 *   - benefit 03 « Une présence locale crédible »  → PROXIMITÉ
 *
 * Direction visuelle :
 *   - grand mot-clef en Space Mono (DURÉE / PERTINENCE / PROXIMITÉ) —
 *     narratif, pas une métrique ;
 *   - séparateurs mono petrol ;
 *   - grille 3 colonnes desktop, pile mobile.
 */

interface Props {
  benefits: readonly ExpertiseBenefit[]
}

defineProps<Props>()

const labels = ["Durée", "Pertinence", "Proximité"] as const

const generatedId = useId()
const titleId = computed(() => `visibilite-principles-title-${generatedId}`)
</script>

<template>
  <section
    class="visibilite-principles"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="visibilite-principles__container">
      <header class="visibilite-principles__header">
        <BaseEyebrow class="visibilite-principles__eyebrow">
          Bénéfices concrets
        </BaseEyebrow>
        <h2 :id="titleId" class="visibilite-principles__title">
          Une visibilité qui tient, cible et rayonne localement.
        </h2>
      </header>

      <ol class="visibilite-principles__list" role="list">
        <li
          v-for="(benefit, index) in benefits"
          :key="benefit.title"
          class="visibilite-principles__item"
          :data-position="index + 1"
        >
          <p class="visibilite-principles__item-index" aria-hidden="true">
            0{{ index + 1 }}
          </p>
          <p class="visibilite-principles__item-label" aria-hidden="true">
            {{ labels[index] ?? "" }}
          </p>
          <h3 class="visibilite-principles__item-title">
            {{ benefit.title }}
          </h3>
          <p class="visibilite-principles__item-description">
            {{ benefit.description }}
          </p>
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
.visibilite-principles {
  background-color: var(--background-secondary);
  color: var(--text-primary);
  padding-block: var(--space-16) var(--space-16);
}

.visibilite-principles__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.visibilite-principles__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 46rem;
}

.visibilite-principles__eyebrow {
  margin: 0;
}

.visibilite-principles__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.625rem, 3.4vw, 2.25rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 28ch;
}

.visibilite-principles__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
}

.visibilite-principles__item {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding-top: var(--space-6);
  border-top: 1px solid var(--border-default);
}

.visibilite-principles__item-index {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.10em;
  color: var(--color-petrol);
  margin: 0;
}

.visibilite-principles__item-label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: clamp(1.375rem, 3vw, 1.875rem);
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
  line-height: 1;
}

.visibilite-principles__item-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.125rem, 1.8vw, 1.375rem);
  line-height: 1.2;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 24ch;
}

.visibilite-principles__item-description {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 44ch;
}

@media (min-width: 1024px) {
  .visibilite-principles__list {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--space-8);
  }

  .visibilite-principles__item {
    padding-top: var(--space-8);
  }
}
</style>
