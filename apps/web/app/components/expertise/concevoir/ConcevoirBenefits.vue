<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import type { ExpertiseBenefit } from "~/config/expertise-pages"

/**
 * Bénéfices concrets — variante propre à Concevoir, tons cartouche.
 *
 * Les données `benefits` (title + description) sont celles de
 * `expertise-pages.ts`. Aucune reformulation, aucune promesse ajoutée.
 */

interface Props {
  benefits: readonly ExpertiseBenefit[]
}

defineProps<Props>()

const generatedId = useId()
const titleId = computed(() => `concevoir-benefits-title-${generatedId}`)
</script>

<template>
  <section
    class="concevoir-benefits"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="concevoir-benefits__container">
      <header class="concevoir-benefits__header">
        <BaseEyebrow class="concevoir-benefits__eyebrow">
          Bénéfices concrets
        </BaseEyebrow>
        <h2 :id="titleId" class="concevoir-benefits__title">
          Ce que vous obtenez à l'issue de la conception.
        </h2>
      </header>

      <ul class="concevoir-benefits__grid" role="list">
        <li
          v-for="benefit in benefits"
          :key="benefit.title"
          class="concevoir-benefits__item"
        >
          <span
            class="concevoir-benefits__marker"
            aria-hidden="true"
          />
          <h3 class="concevoir-benefits__item-title">{{ benefit.title }}</h3>
          <p class="concevoir-benefits__item-description">
            {{ benefit.description }}
          </p>
        </li>
      </ul>
    </BaseContainer>
  </section>
</template>

<style scoped>
.concevoir-benefits {
  background-color: var(--background-secondary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-14);
}

.concevoir-benefits__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.concevoir-benefits__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 44rem;
}

.concevoir-benefits__eyebrow {
  margin: 0;
}

.concevoir-benefits__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.625rem, 3.4vw, 2.25rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 30ch;
}

.concevoir-benefits__grid {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-5);
}

.concevoir-benefits__item {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-6);
  padding-top: calc(var(--space-6) + 8px);
  background-color: var(--background-primary);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.concevoir-benefits__marker {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(
    to right,
    var(--color-petrol),
    var(--color-devzair-blue)
  );
}

.concevoir-benefits__item-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.125rem;
  line-height: 1.25;
  color: var(--text-primary);
  margin: 0;
}

.concevoir-benefits__item-description {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
}

@media (min-width: 720px) {
  .concevoir-benefits__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1024px) {
  .concevoir-benefits {
    padding-block: var(--space-16) var(--space-16);
  }

  .concevoir-benefits__grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
</style>
