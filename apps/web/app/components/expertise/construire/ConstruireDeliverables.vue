<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Livrables du pôle Construire — composition asymétrique en modules.
 *
 * Les libellés viennent verbatim de `expertise-pages.ts` (deliverables).
 * Aucun contenu commercial nouveau. On préserve l'eyebrow « Prestations »
 * attendu par le test e2e existant qui boucle sur les cinq pôles.
 *
 * Composition :
 *   - mobile : liste verticale (1 colonne) ;
 *   - tablette : 2 colonnes symétriques ;
 *   - desktop : composition 2 modules larges (haut) + 3 modules (bas)
 *     avec un rythme asymétrique — pas une grille uniforme.
 *
 * Sémantique : `<ol>` (l'ordre reflète la séquence naturelle de livraison
 * telle que définie dans le brief éditorial : site → e-commerce → applis →
 * intégrations → doc).
 */

interface Props {
  deliverables: readonly string[]
}

defineProps<Props>()

const generatedId = useId()
const titleId = computed(() => `construire-deliverables-title-${generatedId}`)
</script>

<template>
  <section
    class="construire-deliverables"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="construire-deliverables__container">
      <header class="construire-deliverables__header">
        <BaseEyebrow class="construire-deliverables__eyebrow">
          Prestations
        </BaseEyebrow>
        <h2 :id="titleId" class="construire-deliverables__title">
          Des briques qui composent un produit.
        </h2>
      </header>

      <ol class="construire-deliverables__board" role="list">
        <li
          v-for="(deliverable, index) in deliverables"
          :key="deliverable"
          class="construire-deliverables__card"
          :data-position="index + 1"
        >
          <div class="construire-deliverables__meta">
            <span class="construire-deliverables__index" aria-hidden="true">
              {{ String(index + 1).padStart(2, "0") }}
            </span>
            <span class="construire-deliverables__tag" aria-hidden="true">
              Module {{ String(index + 1).padStart(2, "0") }}
            </span>
          </div>
          <p class="construire-deliverables__label">{{ deliverable }}</p>
          <span class="construire-deliverables__connector" aria-hidden="true" />
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
.construire-deliverables {
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-14);
}

.construire-deliverables__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.construire-deliverables__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 46rem;
}

.construire-deliverables__eyebrow {
  margin: 0;
}

.construire-deliverables__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.625rem, 3.4vw, 2.25rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 26ch;
}

.construire-deliverables__board {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
}

.construire-deliverables__card {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-6);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  background-color: var(--color-cream-elevated);
  transition: border-color var(--duration-fast) var(--ease-out);
}

.construire-deliverables__card:hover {
  border-color: var(--color-devzair-blue);
}

.construire-deliverables__meta {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: var(--space-3);
}

.construire-deliverables__index {
  font-family: var(--font-family-mono);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.10em;
  color: var(--color-petrol);
}

.construire-deliverables__tag {
  font-family: var(--font-family-mono);
  font-size: 0.625rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-petrol);
}

.construire-deliverables__label {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1.0625rem;
  line-height: 1.35;
  color: var(--text-primary);
  margin: 0;
}

.construire-deliverables__connector {
  display: block;
  width: 40px;
  height: 2px;
  background: linear-gradient(
    to right,
    var(--color-devzair-blue),
    rgba(12, 91, 87, 0.6)
  );
  margin-top: var(--space-2);
}

/* Tablette : 2 colonnes symétriques. */
@media (min-width: 720px) {
  .construire-deliverables__board {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-5);
  }
}

/* Desktop : composition asymétrique 2 modules larges + 3 modules. */
@media (min-width: 1024px) {
  .construire-deliverables {
    padding-block: var(--space-16) var(--space-16);
  }

  .construire-deliverables__board {
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: var(--space-5);
  }

  /* Rangée 1 : deux modules larges (3 colonnes chacun). */
  .construire-deliverables__card[data-position="1"] { grid-column: span 3; }
  .construire-deliverables__card[data-position="2"] { grid-column: span 3; }

  /* Rangée 2 : trois modules (2 colonnes chacun). */
  .construire-deliverables__card[data-position="3"] { grid-column: span 2; }
  .construire-deliverables__card[data-position="4"] { grid-column: span 2; }
  .construire-deliverables__card[data-position="5"] { grid-column: span 2; }

  .construire-deliverables__card[data-position="1"] .construire-deliverables__label,
  .construire-deliverables__card[data-position="2"] .construire-deliverables__label {
    font-size: 1.1875rem;
    max-width: 28ch;
  }
}

@media (prefers-reduced-motion: reduce) {
  .construire-deliverables__card {
    transition: none;
  }
}
</style>
