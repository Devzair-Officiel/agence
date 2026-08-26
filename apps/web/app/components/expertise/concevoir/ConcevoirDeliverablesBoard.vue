<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Livrables du pôle Concevoir présentés sous forme de « board » — grille
 * de fiches type cartouche, plutôt que la liste plate générique.
 *
 * Les libellés sont ceux de `expertise-pages.ts` (deliverables) — pas de
 * nouveau contenu commercial. Chaque fiche affiche :
 *   - un numéro de repère (mono, ton petrol) ;
 *   - le libellé du livrable, verbatim.
 *
 * On préserve l'eyebrow « Prestations » attendu par l'e2e existant, tout
 * en donnant à la section un ton propre à Concevoir.
 */

interface Props {
  deliverables: readonly string[]
}

defineProps<Props>()

const generatedId = useId()
const titleId = computed(() => `concevoir-deliverables-title-${generatedId}`)
</script>

<template>
  <section
    class="concevoir-deliverables"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="concevoir-deliverables__container">
      <header class="concevoir-deliverables__header">
        <BaseEyebrow class="concevoir-deliverables__eyebrow">
          Prestations
        </BaseEyebrow>
        <h2 :id="titleId" class="concevoir-deliverables__title">
          Les livrables associés à ce pôle.
        </h2>
      </header>

      <ol class="concevoir-deliverables__board" role="list">
        <li
          v-for="(deliverable, index) in deliverables"
          :key="deliverable"
          class="concevoir-deliverables__card"
        >
          <span class="concevoir-deliverables__index" aria-hidden="true">
            {{ String(index + 1).padStart(2, "0") }}
          </span>
          <span class="concevoir-deliverables__marker" aria-hidden="true" />
          <p class="concevoir-deliverables__label">{{ deliverable }}</p>
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
.concevoir-deliverables {
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-14);
}

.concevoir-deliverables__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.concevoir-deliverables__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 40rem;
}

.concevoir-deliverables__eyebrow {
  margin: 0;
}

.concevoir-deliverables__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.625rem, 3.4vw, 2.25rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
}

.concevoir-deliverables__board {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
  counter-reset: deliverable;
}

.concevoir-deliverables__card {
  position: relative;
  display: grid;
  grid-template-columns: auto 1fr;
  grid-template-rows: auto auto;
  column-gap: var(--space-4);
  row-gap: var(--space-1);
  padding: var(--space-6);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  background-color: var(--color-cream-elevated);
  transition: border-color var(--duration-fast) var(--ease-out);
}

.concevoir-deliverables__card:hover {
  border-color: var(--color-petrol);
}

.concevoir-deliverables__index {
  grid-row: 1 / span 2;
  align-self: start;
  font-family: var(--font-family-mono);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  color: var(--color-petrol);
  padding-top: 0.15rem;
}

.concevoir-deliverables__marker {
  grid-column: 2;
  width: 32px;
  height: 2px;
  background-color: var(--color-devzair-blue);
  margin-bottom: var(--space-2);
}

.concevoir-deliverables__label {
  grid-column: 2;
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1.0625rem;
  line-height: 1.35;
  color: var(--text-primary);
  margin: 0;
}

@media (min-width: 720px) {
  .concevoir-deliverables__board {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1024px) {
  .concevoir-deliverables {
    padding-block: var(--space-16) var(--space-16);
  }

  .concevoir-deliverables__board {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (prefers-reduced-motion: reduce) {
  .concevoir-deliverables__card {
    transition: none;
  }
}
</style>
