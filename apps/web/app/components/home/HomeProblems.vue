<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Section « Le constat » — cinq difficultés fréquentes que Devzair rencontre
 * chez les entreprises qui prennent contact.
 *
 * Choix éditorial :
 *   - constat général, jamais nommer un client ni citer un chiffre ;
 *   - ton descriptif, pas anxiogène ;
 *   - la liste est une vraie `<ol>` sémantique (les difficultés sont ordonnées
 *     de la plus visible à la plus structurelle), la numérotation Space Mono
 *     est purement décorative (aria-hidden) et duplique l'ordre logique.
 *
 * Responsive :
 *   - <768px : colonne unique, titre puis liste ;
 *   - ≥768px : deux colonnes (bloc éditorial gauche ~2/5, liste droite ~3/5) ;
 *   - les séparateurs `border-block-start` isolent chaque item sans boîte lourde.
 *
 * Accessibilité :
 *   - un H2 unique pour la section ;
 *   - chaque item porte un H3 (titre court), suivi d'un paragraphe descriptif ;
 *   - aucun élément interactif : rien n'entre dans le parcours clavier.
 *
 * La liste des difficultés vit localement — elle n'est consommée par aucun
 * autre composant. Le jour où un autre écran devrait la partager, on
 * l'extraira dans `app/config/`.
 */

interface HomeProblem {
  readonly id: string
  readonly title: string
  readonly description: string
}

const problems: readonly HomeProblem[] = [
  {
    id: "visibilite",
    title: "Une entreprise invisible sur les recherches qui comptent",
    description:
      "Les prospects cherchent, trouvent des concurrents et passent à côté d'une offre pourtant pertinente. Le référencement n'est ni pensé, ni suivi.",
  },
  {
    id: "image",
    title: "Une présence en ligne qui ne reflète pas le niveau réel",
    description:
      "Le site actuel donne une impression d'improvisation, d'ancienneté ou d'amateurisme, alors que l'entreprise, elle, tient ses engagements.",
  },
  {
    id: "performance",
    title: "Un site lent, peu fluide, peu convaincant",
    description:
      "Chargements longs, navigation confuse, formulaire qui rebute : les visiteurs partent avant de comprendre ce que vous proposez.",
  },
  {
    id: "outils",
    title: "Des outils internes désorganisés ou vieillissants",
    description:
      "Fichiers dispersés, tableurs saturés, applications qui ne parlent pas entre elles : l'équipe perd du temps sur des tâches à faible valeur.",
  },
  {
    id: "suivi",
    title: "Aucune stratégie durable, ni suivi de ce qui marche",
    description:
      "Le site a été livré, puis abandonné. Personne ne mesure les visites, les demandes, la position dans les résultats. Rien n'évolue.",
  },
]
</script>

<template>
  <section
    class="home-problems"
    aria-labelledby="home-problems-title"
  >
    <BaseContainer class="home-problems__container">
      <header class="home-problems__intro">
        <BaseEyebrow class="home-problems__eyebrow">Le constat</BaseEyebrow>
        <h2 id="home-problems-title" class="home-problems__title">
          Beaucoup d’entreprises méritent mieux que leur présence en ligne
          actuelle.
        </h2>
        <p class="home-problems__lead">
          Cinq situations que nous rencontrons régulièrement quand une
          entreprise nous contacte. Chacune se corrige, à condition de
          l’identifier honnêtement et d’y répondre avec un travail structuré.
        </p>
      </header>

      <ol class="home-problems__list" aria-label="Cinq difficultés courantes">
        <li
          v-for="(problem, index) in problems"
          :key="problem.id"
          class="home-problems__item"
        >
          <span class="home-problems__number" aria-hidden="true">
            {{ String(index + 1).padStart(2, "0") }}
          </span>
          <div class="home-problems__body">
            <h3 class="home-problems__item-title">{{ problem.title }}</h3>
            <p class="home-problems__item-description">
              {{ problem.description }}
            </p>
          </div>
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
.home-problems {
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-16);
}

.home-problems__container {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-12);
}

.home-problems__intro {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 36rem;
}

.home-problems__eyebrow {
  margin-bottom: var(--space-2);
}

.home-problems__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 4vw, 2.5rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 22ch;
}

.home-problems__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 44ch;
}

.home-problems__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
}

.home-problems__item {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: var(--space-4);
  align-items: baseline;
  padding-block: var(--space-6);
  border-block-start: 1px solid var(--border-default);
}

.home-problems__item:last-child {
  border-block-end: 1px solid var(--border-default);
}

.home-problems__number {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.875rem;
  color: var(--color-petrol);
  letter-spacing: 0.06em;
}

.home-problems__body {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.home-problems__item-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: clamp(1.0625rem, 1.6vw, 1.25rem);
  line-height: 1.35;
  color: var(--text-primary);
  margin: 0;
}

.home-problems__item-description {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

@media (min-width: 768px) {
  .home-problems {
    padding-block: var(--space-20);
  }

  .home-problems__container {
    grid-template-columns: minmax(0, 2fr) minmax(0, 3fr);
    gap: var(--space-16);
    align-items: start;
  }

  .home-problems__intro {
    position: sticky;
    top: var(--space-12);
  }

  .home-problems__item {
    grid-template-columns: 3.5rem 1fr;
    gap: var(--space-5);
    padding-block: var(--space-6);
  }

  .home-problems__number {
    font-size: 1rem;
  }
}

@media (min-width: 1024px) {
  .home-problems {
    padding-block: var(--space-24);
  }

  .home-problems__container {
    gap: var(--space-20);
  }
}
</style>
