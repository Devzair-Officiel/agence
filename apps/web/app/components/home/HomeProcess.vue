<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import { projectProcess } from "~/config/project-process"

/**
 * Section « Notre méthode » — parcours en six étapes verbatim
 * `docs/01-CONTENT.md §7.3`, rendu depuis la source unique
 * `~/config/project-process.ts`.
 *
 * Rendu :
 *   - <640px  : timeline verticale, chaque étape reçoit un point à gauche et
 *     un trait vertical décoratif (via `::before` sur `<li>`).
 *   - 640–1023px : grille 2 colonnes, ordre naturel (3 rangées).
 *   - ≥1024px : grille 3×2 (six colonnes seraient trop serrées pour le
 *     paragraphe explicatif de chaque étape).
 *
 * Accessibilité :
 *   - un H2 unique, un H3 par étape ;
 *   - `<ol>` sémantique (l'ordre importe), numérotation textuelle 01..06
 *     lisible au lecteur d'écran ;
 *   - aucun élément interactif : pas d'accordion, pas de tab, pas de bouton.
 *
 * Réduction du mouvement : rien à neutraliser (aucune animation introduite).
 */

const steps = [...projectProcess].sort((a, b) => a.order - b.order)
</script>

<template>
  <section
    class="home-process"
    aria-labelledby="home-process-title"
  >
    <BaseContainer width="wide" class="home-process__container">
      <header class="home-process__intro">
        <BaseEyebrow class="home-process__eyebrow">Notre méthode</BaseEyebrow>
        <h2 id="home-process-title" class="home-process__title">
          Un cadre clair, du premier échange au suivi.
        </h2>
        <p class="home-process__lead">
          Un projet digital tient rarement à une intuition : il tient à une
          méthode. Six étapes structurent chaque intervention, du premier
          échange jusqu'à l'accompagnement dans le temps.
        </p>
      </header>

      <ol class="home-process__list" aria-label="Les six étapes de la méthode Devzair">
        <li
          v-for="step in steps"
          :key="step.id"
          class="home-process__step"
          :data-order="step.order"
        >
          <span class="home-process__step-index" aria-hidden="true">
            {{ String(step.order).padStart(2, "0") }}
          </span>
          <h3 class="home-process__step-title">{{ step.label }}</h3>
          <p class="home-process__step-description">{{ step.description }}</p>
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
.home-process {
  background-color: var(--background-secondary);
  color: var(--text-primary);
  padding-block: var(--space-16);
}

.home-process__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.home-process__intro {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 44rem;
}

.home-process__eyebrow {
  margin-bottom: var(--space-2);
}

.home-process__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 4vw, 2.5rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 22ch;
}

.home-process__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 56ch;
}

/* ----- Mobile : timeline verticale ----- */
.home-process__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0;
  /* Espace à gauche pour le trait et le point de la timeline. */
  padding-inline-start: var(--space-6);
}

.home-process__step {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding-block: var(--space-5);
  padding-inline-start: var(--space-2);
}

/* Trait vertical continu à gauche (pseudo-élément décoratif). */
.home-process__step::before {
  content: "";
  position: absolute;
  left: calc(-1 * var(--space-6));
  top: 0;
  bottom: 0;
  width: 1px;
  background-color: var(--border-default);
}

/* Le premier item n'affiche pas de trait au-dessus du point. */
.home-process__step:first-child::before {
  top: var(--space-5);
}

/* Le dernier item n'affiche pas de trait sous le point. */
.home-process__step:last-child::before {
  bottom: calc(100% - var(--space-5) - 12px);
}

/* Point sur la timeline (deuxième pseudo-élément). */
.home-process__step::after {
  content: "";
  position: absolute;
  left: calc(-1 * var(--space-6) - 5px);
  top: var(--space-5);
  width: 11px;
  height: 11px;
  border-radius: 50%;
  background-color: var(--color-petrol);
  border: 2px solid var(--background-secondary);
}

.home-process__step-index {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.08em;
  color: var(--color-petrol);
}

.home-process__step-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1.125rem;
  line-height: 1.3;
  color: var(--text-primary);
  margin: 0;
}

.home-process__step-description {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
  max-width: 44ch;
}

/* ----- Tablette : grille 2 colonnes, on retire la timeline ----- */
@media (min-width: 640px) {
  .home-process__list {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-6) var(--space-8);
    padding-inline-start: 0;
  }

  .home-process__step {
    padding-block: var(--space-4);
    padding-inline-start: 0;
    padding-block-start: var(--space-4);
    border-top: 1px solid var(--border-default);
  }

  .home-process__step::before,
  .home-process__step::after {
    content: none;
  }
}

@media (min-width: 768px) {
  .home-process {
    padding-block: var(--space-20);
  }
}

/* ----- Desktop : grille 3×2 ----- */
@media (min-width: 1024px) {
  .home-process {
    padding-block: var(--space-24);
  }

  .home-process__list {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--space-8) var(--space-10);
  }
}
</style>
