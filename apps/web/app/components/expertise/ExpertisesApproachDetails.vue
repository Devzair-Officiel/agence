<script setup lang="ts">
/**
 * Deux sous-blocs éditoriaux rendus dans le slot par défaut de la section
 * « Notre approche » de la page `/expertises` :
 *
 *   1. « Trois angles morts fréquents » — trois exemples génériques (pas
 *      d'anecdote client, pas de chiffre) illustrant concrètement
 *      l'affirmation portée par l'intro de la section (« traiter un pôle
 *      sans les autres génère des angles morts »). Non ordonnés.
 *   2. « Trois façons de mobiliser les pôles » — enumeration des scénarios
 *      typiques, du plus léger au plus large, pour répondre à la question
 *      naturelle « combien de pôles me faut-il ? ». Ordonnés (01 → 03).
 *
 * Accessibilité :
 *   - la section parente porte le H2 (« Des pôles pensés comme un
 *     ensemble… ») ; ici on descend d'un niveau avec deux H3 ;
 *   - les cartes sont des `<article>` avec un titre H4 propre — pas de
 *     lien cliquable, ce sont des blocs de contenu narratif ;
 *   - les listes sont sémantiques (`<ul>` pour angles morts, `<ol>` pour
 *     les modes ordonnés) ; les marqueurs `01/02/03` sont des `<span>`
 *     décoratifs `aria-hidden` (la sémantique de l'ordre passe par `<ol>`).
 */

interface Entry {
  readonly title: string
  readonly description: string
}

const blindSpots: readonly Entry[] = [
  {
    title: "Refondre sans repenser le contenu",
    description:
      "Une structure moderne posée sur un discours inchangé fait dérailler le message dès la mise en ligne.",
  },
  {
    title: "Construire sans préparer la visibilité",
    description:
      "Un site techniquement propre reste invisible si les intentions de recherche n'ont pas été anticipées en amont.",
  },
  {
    title: "Livrer sans prévoir la suite",
    description:
      "Sans mesure ni maintenance, un site vivant se dégrade en quelques mois — les gains obtenus s'effritent.",
  },
]

const mobilizationModes: readonly Entry[] = [
  {
    title: "Un seul pôle",
    description:
      "Une intervention ciblée : refonte SEO d'un site existant, série photographique, mise à jour d'identité visuelle.",
  },
  {
    title: "Deux ou trois pôles combinés",
    description:
      "Le cas le plus fréquent : concevoir puis construire, ou construire puis rendre visible, selon la maturité du projet.",
  },
  {
    title: "Les cinq pôles ensemble",
    description:
      "Création complète ou refonte profonde, quand l'ensemble de la présence digitale doit être repensé.",
  },
]
</script>

<template>
  <div class="approach-details">
    <div class="approach-details__block">
      <h3 class="approach-details__subtitle">Trois angles morts fréquents</h3>
      <ul class="approach-details__grid" role="list">
        <li
          v-for="item in blindSpots"
          :key="item.title"
          class="approach-details__item"
        >
          <article class="approach-details__card approach-details__card--warning">
            <h4 class="approach-details__card-title">{{ item.title }}</h4>
            <p class="approach-details__card-text">{{ item.description }}</p>
          </article>
        </li>
      </ul>
    </div>

    <div class="approach-details__block">
      <h3 class="approach-details__subtitle">
        Trois façons de mobiliser les pôles
      </h3>
      <ol class="approach-details__grid" role="list">
        <li
          v-for="(mode, index) in mobilizationModes"
          :key="mode.title"
          class="approach-details__item"
        >
          <article class="approach-details__card approach-details__card--mode">
            <p class="approach-details__card-order" aria-hidden="true">
              {{ String(index + 1).padStart(2, "0") }}
            </p>
            <h4 class="approach-details__card-title">{{ mode.title }}</h4>
            <p class="approach-details__card-text">{{ mode.description }}</p>
          </article>
        </li>
      </ol>
    </div>
  </div>
</template>

<style scoped>
.approach-details {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.approach-details__block {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.approach-details__subtitle {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: clamp(1.125rem, 1.6vw, 1.25rem);
  line-height: 1.25;
  letter-spacing: -0.005em;
  color: var(--text-primary);
  margin: 0;
}

.approach-details__grid {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
}

.approach-details__item {
  display: flex;
}

.approach-details__card {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-5);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  background-color: var(--color-cream-elevated);
  height: 100%;
}

/*
 * Angles morts : barre d'accent petrol à gauche pour signaler visuellement
 * la nature « avertissement » du bloc, sans texte ou pictogramme criards.
 */
.approach-details__card--warning {
  border-left: 3px solid var(--color-petrol);
}

.approach-details__card--mode {
  border-top: 1px solid var(--border-default);
}

.approach-details__card-order {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.08em;
  color: var(--text-accent);
  margin: 0;
}

.approach-details__card-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.0625rem;
  line-height: 1.3;
  letter-spacing: -0.005em;
  color: var(--text-primary);
  margin: 0;
}

.approach-details__card-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
}

@media (min-width: 720px) {
  .approach-details__grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--space-5);
  }
}
</style>
