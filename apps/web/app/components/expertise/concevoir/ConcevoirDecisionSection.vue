<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Section « Avant de construire, il faut décider » — pivot narratif de la
 * page Concevoir.
 *
 * Compose deux blocs éditoriaux tirés de `expertise-pages.ts` :
 *   - `needTitle` / `needDescription` (à qui cela s'adresse) ;
 *   - `approachTitle` / `approachDescription` (comment nous procédons).
 *
 * Le composant n'invente aucun paragraphe. Le titre H2 (« Avant de
 * construire, il faut décider. ») et les deux libellés courts (« À qui
 * cela s'adresse » / « Notre approche ») sont explicitement validés par
 * le brief éditorial et permettent aussi de préserver les tests e2e qui
 * vérifient la présence de ces deux chaînes.
 */

interface Props {
  needTitle: string
  needDescription: string
  approachTitle: string
  approachDescription: string
}

defineProps<Props>()

const generatedId = useId()
const titleId = computed(() => `concevoir-decision-title-${generatedId}`)
</script>

<template>
  <section
    class="concevoir-decision"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="concevoir-decision__container">
      <header class="concevoir-decision__header">
        <BaseEyebrow class="concevoir-decision__eyebrow">
          Direction du pôle
        </BaseEyebrow>
        <h2 :id="titleId" class="concevoir-decision__title">
          Avant de construire, il faut décider.
        </h2>
      </header>

      <div class="concevoir-decision__grid">
        <article class="concevoir-decision__card">
          <p class="concevoir-decision__card-eyebrow">À qui cela s'adresse</p>
          <h3 class="concevoir-decision__card-title">{{ needTitle }}</h3>
          <p class="concevoir-decision__card-body">{{ needDescription }}</p>
        </article>

        <article class="concevoir-decision__card concevoir-decision__card--accent">
          <p class="concevoir-decision__card-eyebrow">Notre approche</p>
          <h3 class="concevoir-decision__card-title">{{ approachTitle }}</h3>
          <p class="concevoir-decision__card-body">{{ approachDescription }}</p>
        </article>
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.concevoir-decision {
  position: relative;
  background-color: var(--background-secondary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-14);
}

/* Trait technique de séparation en tête, façon marge de plan. */
.concevoir-decision::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(
    to right,
    transparent,
    rgba(12, 91, 87, 0.4) 20%,
    rgba(46, 134, 217, 0.4) 80%,
    transparent
  );
}

.concevoir-decision__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.concevoir-decision__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 40rem;
}

.concevoir-decision__eyebrow {
  margin: 0;
}

.concevoir-decision__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 3.6vw, 2.5rem);
  line-height: 1.12;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
}

.concevoir-decision__grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-5);
}

.concevoir-decision__card {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-6) var(--space-6);
  background-color: var(--background-primary);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.concevoir-decision__card::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background-color: var(--color-petrol);
}

.concevoir-decision__card--accent::before {
  background-color: var(--color-devzair-blue);
}

.concevoir-decision__card-eyebrow {
  font-family: var(--font-family-mono);
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
}

.concevoir-decision__card--accent .concevoir-decision__card-eyebrow {
  color: var(--color-petrol);
}

.concevoir-decision__card-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.25rem;
  line-height: 1.25;
  color: var(--text-primary);
  margin: 0;
  max-width: 28ch;
}

.concevoir-decision__card-body {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

@media (min-width: 768px) {
  .concevoir-decision {
    padding-block: var(--space-16) var(--space-16);
  }

  .concevoir-decision__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-6);
  }

  .concevoir-decision__card {
    padding: var(--space-8) var(--space-8);
  }
}
</style>
