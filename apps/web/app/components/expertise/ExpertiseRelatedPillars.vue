<script setup lang="ts">
import { computed } from "vue"
import { expertisePages } from "~/config/expertise-pages"

/**
 * Section « Aller plus loin » — deux liens vers les pôles connexes.
 *
 * Contrat :
 *   - `pillarIds` liste les ids des pôles à mettre en avant (typiquement
 *     deux, garanti par un test sur `expertise-pages.ts`) ;
 *   - la source de vérité pour le libellé, le résumé et l'URL est
 *     `expertise-pages.ts`. On filtre les entrées `status === "published"`
 *     et on ignore silencieusement les autres — le test unitaire garantit
 *     qu'à ce stade toutes les entrées ciblées sont publiées.
 *
 * Rendu :
 *   - `<ul role="list">` avec deux cartes `<NuxtLink>` (une par pôle) ;
 *   - chaque carte affiche le libellé court et le résumé du pôle.
 *
 * Accessibilité : les cartes sont des liens à part entière (pas de div
 * cliquable simulée). Le titre H3 sert de libellé accessible du lien via
 * le contenu textuel ; aucun `aria-label` supplémentaire n'est nécessaire.
 */

interface Props {
  pillarIds: readonly string[]
}

const props = defineProps<Props>()

const relatedPages = computed(() =>
  props.pillarIds
    .map((id) => expertisePages.find((p) => p.pillarId === id))
    .filter((p): p is (typeof expertisePages)[number] => Boolean(p))
    .filter((p) => p.status === "published"),
)
</script>

<template>
  <ul v-if="relatedPages.length > 0" class="expertise-related" role="list">
    <li
      v-for="page in relatedPages"
      :key="page.id"
      class="expertise-related__item"
    >
      <NuxtLink :to="page.route" class="expertise-related__link">
        <p class="expertise-related__eyebrow">{{ page.eyebrow }}</p>
        <h3 class="expertise-related__title">{{ page.shortTitle }}</h3>
        <p class="expertise-related__summary">{{ page.summary }}</p>
        <p class="expertise-related__cta" aria-hidden="true">
          Découvrir ce pôle →
        </p>
      </NuxtLink>
    </li>
  </ul>
</template>

<style scoped>
.expertise-related {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
}

.expertise-related__item {
  display: flex;
}

.expertise-related__link {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding: var(--space-6);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg, 0.75rem);
  background-color: var(--background-primary);
  color: var(--text-primary);
  text-decoration: none;
  width: 100%;
  transition: border-color 120ms ease, transform 120ms ease;
}

.expertise-related__link:hover,
.expertise-related__link:focus-visible {
  border-color: var(--color-devzair-blue);
  transform: translateY(-2px);
}

.expertise-related__eyebrow {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.06em;
  color: var(--text-accent);
  margin: 0;
  text-transform: uppercase;
}

.expertise-related__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.25rem;
  line-height: 1.2;
  color: var(--text-primary);
  margin: 0;
}

.expertise-related__summary {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
}

.expertise-related__cta {
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  font-weight: 700;
  /* Petrol pour un contraste WCAG AA sur cream/sand ; devzair-blue échoue. */
  color: var(--color-petrol);
  margin: var(--space-2) 0 0;
}

@media (prefers-reduced-motion: reduce) {
  .expertise-related__link {
    transition: none;
  }

  .expertise-related__link:hover,
  .expertise-related__link:focus-visible {
    transform: none;
  }
}

@media (min-width: 720px) {
  .expertise-related {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>
