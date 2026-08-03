<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import { trustPromises } from "~/config/trust-promises"

/**
 * Section « Pourquoi Devzair » — cinq promesses observables, verbatim du
 * brief Phase 5C. Source unique `~/config/trust-promises.ts`.
 *
 * Rendu :
 *   - <640px  : liste verticale, une carte par item.
 *   - 640–1023px : grille 2 colonnes, la 5ᵉ carte s'étend sur toute la
 *     largeur pour éviter l'orpheline visuelle.
 *   - ≥1024px : grille asymétrique 3+2 — rangée 1 (3 promesses), rangée 2
 *     (2 promesses centrées via `grid-column`).
 *
 * Choix des balises :
 *   - `<ul>` sémantique (l'ordre n'est pas significatif entre les cinq
 *     promesses) ;
 *   - un H3 par promesse ;
 *   - aucune interaction : les cartes ne sont ni des liens ni des boutons.
 */

const promises = [...trustPromises].sort((a, b) => a.order - b.order)
</script>

<template>
  <section
    class="home-trust"
    aria-labelledby="home-trust-title"
  >
    <BaseContainer width="wide" class="home-trust__container">
      <header class="home-trust__intro">
        <BaseEyebrow class="home-trust__eyebrow">Pourquoi Devzair</BaseEyebrow>
        <h2 id="home-trust-title" class="home-trust__title">
          Ce qui fait la différence, concrètement.
        </h2>
        <p class="home-trust__lead">
          Nous ne cherchons pas à être la plus grosse agence, mais celle qui
          tient ses engagements. Cinq pratiques que nos clients retrouvent
          projet après projet.
        </p>
      </header>

      <ul class="home-trust__list" aria-label="Cinq engagements Devzair">
        <li
          v-for="promise in promises"
          :key="promise.id"
          class="home-trust__item"
          :data-order="promise.order"
        >
          <span class="home-trust__item-index" aria-hidden="true">
            {{ String(promise.order).padStart(2, "0") }}
          </span>
          <h3 class="home-trust__item-title">{{ promise.label }}</h3>
          <p class="home-trust__item-description">{{ promise.description }}</p>
        </li>
      </ul>
    </BaseContainer>
  </section>
</template>

<style scoped>
.home-trust {
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-16);
}

.home-trust__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.home-trust__intro {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 44rem;
}

.home-trust__eyebrow {
  margin-bottom: var(--space-2);
}

.home-trust__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 4vw, 2.5rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 22ch;
}

.home-trust__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 56ch;
}

.home-trust__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
}

.home-trust__item {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding: var(--space-6);
  background-color: var(--surface-primary);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-md);
}

.home-trust__item-index {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.08em;
  color: var(--color-petrol);
}

.home-trust__item-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1.125rem;
  line-height: 1.3;
  color: var(--text-primary);
  margin: 0;
}

.home-trust__item-description {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
  max-width: 44ch;
}

/* ----- Tablette : 2 colonnes, 5ᵉ carte pleine largeur ----- */
@media (min-width: 640px) {
  .home-trust__list {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-5);
  }

  .home-trust__item[data-order="5"] {
    grid-column: 1 / -1;
  }
}

@media (min-width: 768px) {
  .home-trust {
    padding-block: var(--space-20);
  }
}

/* ----- Desktop : grille asymétrique 3 + 2 centré ----- */
@media (min-width: 1024px) {
  .home-trust {
    padding-block: var(--space-24);
  }

  .home-trust__list {
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: var(--space-6);
  }

  .home-trust__item {
    grid-column: span 2;
  }

  /* Rangée 2 (items 4 et 5) : chaque carte span 3, décalage pour centrer. */
  .home-trust__item[data-order="4"] {
    grid-column: 2 / span 2;
  }

  .home-trust__item[data-order="5"] {
    grid-column: 4 / span 2;
  }
}
</style>
