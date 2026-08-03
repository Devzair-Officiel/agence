<script setup lang="ts">
import type { ExpertiseBenefit } from "~/config/expertise-pages"

/**
 * Grille des bénéfices d'un pôle d'expertise.
 *
 * Rendu :
 *   - `<ul role="list">` contenant N cartes ;
 *   - chaque carte porte un H3 (le H2 est porté par la `EditorialSection`
 *     parente qui englobe le composant) et une description courte.
 *
 * Contrat : chaque bénéfice a un `title` et une `description` non vides —
 * validé au niveau de la source de vérité (`expertise-pages.ts` + spec).
 */

interface Props {
  benefits: readonly ExpertiseBenefit[]
}

defineProps<Props>()
</script>

<template>
  <ul class="expertise-benefits" role="list">
    <li
      v-for="benefit in benefits"
      :key="benefit.title"
      class="expertise-benefits__item"
    >
      <h3 class="expertise-benefits__title">{{ benefit.title }}</h3>
      <p class="expertise-benefits__description">{{ benefit.description }}</p>
    </li>
  </ul>
</template>

<style scoped>
.expertise-benefits {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
}

.expertise-benefits__item {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding: var(--space-5) var(--space-6);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg, 0.75rem);
  background-color: var(--background-primary);
  color: var(--text-primary);
}

.expertise-benefits__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.125rem;
  line-height: 1.25;
  color: var(--text-primary);
  margin: 0;
}

.expertise-benefits__description {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
}

@media (min-width: 720px) {
  .expertise-benefits {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1080px) {
  .expertise-benefits {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
</style>
