<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import type { ExpertiseBenefit } from "~/config/expertise-pages"

/**
 * Bénéfices concrets de la page Construire, présentés sous forme de
 * « contrôles qualité » plutôt que d'une simple grille.
 *
 * Chaque contrôle mappe 1:1 avec un `benefit` reçu en prop :
 *   - « Architecture — validée » → « Un socle technique moderne »
 *   - « Tests — validés »        → « Un produit testé »
 *   - « Maintenabilité — validée » → « Une maintenance sereine »
 *
 * Le titre et la description restent verbatim ceux définis dans
 * `expertise-pages.ts` (aucune promesse ajoutée). Le pictogramme « ✓ »
 * en marker est purement décoratif (aria-hidden).
 *
 * Note : le composant préserve les libellés attendus par le test e2e
 * générique — eyebrow « Bénéfices concrets » et les titres verbatim des
 * trois benefits.
 */

interface Props {
  benefits: readonly ExpertiseBenefit[]
}

const props = defineProps<Props>()

interface ControlLabel {
  readonly label: string
  readonly status: string
}

const CONTROLS: readonly ControlLabel[] = [
  { label: "Architecture", status: "Validée" },
  { label: "Tests", status: "Validés" },
  { label: "Maintenabilité", status: "Validée" },
]

const items = computed(() =>
  props.benefits.map((benefit, index) => ({
    key: benefit.title,
    benefit,
    control: CONTROLS[index] ?? { label: "Contrôle", status: "Validé" },
  })),
)

const generatedId = useId()
const titleId = computed(() => `construire-quality-title-${generatedId}`)
</script>

<template>
  <section
    class="construire-quality"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="construire-quality__container">
      <header class="construire-quality__header">
        <BaseEyebrow class="construire-quality__eyebrow">
          Bénéfices concrets
        </BaseEyebrow>
        <h2 :id="titleId" class="construire-quality__title">
          Construit pour fonctionner aujourd'hui. Et demain.
        </h2>
      </header>

      <ul class="construire-quality__grid" role="list">
        <li
          v-for="item in items"
          :key="item.key"
          class="construire-quality__item"
        >
          <div class="construire-quality__control" aria-hidden="true">
            <span class="construire-quality__control-icon">✓</span>
            <span class="construire-quality__control-label">
              {{ item.control.label }}
            </span>
            <span class="construire-quality__control-status">
              {{ item.control.status }}
            </span>
          </div>
          <h3 class="construire-quality__benefit-title">
            {{ item.benefit.title }}
          </h3>
          <p class="construire-quality__benefit-description">
            {{ item.benefit.description }}
          </p>
        </li>
      </ul>
    </BaseContainer>
  </section>
</template>

<style scoped>
.construire-quality {
  background-color: var(--background-secondary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-14);
}

.construire-quality__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.construire-quality__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 46rem;
}

.construire-quality__eyebrow {
  margin: 0;
}

.construire-quality__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.625rem, 3.4vw, 2.25rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 28ch;
}

.construire-quality__grid {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-5);
}

.construire-quality__item {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-6);
  background-color: var(--background-primary);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.construire-quality__control {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-2) var(--space-3);
  border: 1px solid rgba(46, 134, 217, 0.3);
  border-radius: var(--radius-sm, 4px);
  background-color: rgba(46, 134, 217, 0.06);
  font-family: var(--font-family-mono);
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  color: var(--color-petrol);
  width: fit-content;
}

.construire-quality__control-icon {
  color: var(--color-petrol);
  font-size: 0.8125rem;
}

.construire-quality__control-label {
  color: var(--color-petrol);
}

.construire-quality__control-status {
  color: var(--color-petrol);
}

.construire-quality__benefit-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.125rem;
  line-height: 1.25;
  color: var(--text-primary);
  margin: var(--space-1) 0 0;
}

.construire-quality__benefit-description {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
}

@media (min-width: 720px) {
  .construire-quality__grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (min-width: 1024px) {
  .construire-quality {
    padding-block: var(--space-16) var(--space-16);
  }
}
</style>
