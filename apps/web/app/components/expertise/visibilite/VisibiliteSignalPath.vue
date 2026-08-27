<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Section « Notre approche » de la page Visibilité — direction parcours
 * cartographique en cinq étapes : le « chemin du signal ».
 *
 * Cinq étapes rendues dans une vraie `<ol>` :
 *   01 · Intentions   — Ce que vos publics cherchent réellement
 *   02 · Fondations   — Blocages techniques du site
 *   03 · Pages        — Rédaction et optimisation
 *   04 · Présence     — Fiche locale et citations
 *   05 · Mesure       — Positions et trafic organique
 *
 * Direction visuelle :
 *   - fond navy (unique bande sombre de la page — mêmes principes que
 *     Valoriser Editorial Flow) ;
 *   - trajectoire en fil continu qui traverse les cinq étapes ;
 *   - repères mono petrol/cream, largeurs asymétriques desktop pour
 *     éviter la timeline industrielle.
 *
 * Aucune promesse chiffrée, aucune durée. Les libellés reformulent la
 * `approachDescription` (intentions de recherche, blocages techniques,
 * contenus, mesures).
 */

interface Props {
  approachDescription: string
}

defineProps<Props>()

interface Step {
  readonly key: string
  readonly label: string
  readonly note: string
}

const steps: readonly Step[] = [
  {
    key: "intentions",
    label: "Intentions",
    note: "Ce que vos publics cherchent réellement.",
  },
  {
    key: "fondations",
    label: "Fondations",
    note: "Blocages techniques et structure du site.",
  },
  {
    key: "pages",
    label: "Pages",
    note: "Rédaction et optimisation stratégique.",
  },
  {
    key: "presence",
    label: "Présence",
    note: "Fiche locale, citations, cohérence.",
  },
  {
    key: "mesure",
    label: "Mesure",
    note: "Positions et trafic organique dans le temps.",
  },
]

const generatedId = useId()
const titleId = computed(() => `visibilite-path-title-${generatedId}`)
</script>

<template>
  <section
    class="visibilite-path"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="visibilite-path__container">
      <header class="visibilite-path__header">
        <BaseEyebrow tone="inverse" class="visibilite-path__eyebrow">
          Notre approche
        </BaseEyebrow>
        <h2 :id="titleId" class="visibilite-path__title">
          Le parcours d'un signal, de l'intention à la mesure.
        </h2>
        <p class="visibilite-path__lead">
          {{ approachDescription }}
        </p>
      </header>

      <ol class="visibilite-path__list" role="list">
        <li
          v-for="(step, index) in steps"
          :key="step.key"
          class="visibilite-path__item"
          :data-position="index + 1"
        >
          <p class="visibilite-path__item-index" aria-hidden="true">
            {{ String(index + 1).padStart(2, "0") }}
          </p>
          <h3 class="visibilite-path__item-label">{{ step.label }}</h3>
          <p class="visibilite-path__item-note">{{ step.note }}</p>
          <span
            v-if="index < steps.length - 1"
            class="visibilite-path__connector"
            aria-hidden="true"
          />
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
/*
 * Seule bande sombre de la page — sépare visuellement l'approche du bloc
 * cream qui précède (audience territoires) et du bloc cream qui suit
 * (layers). Rythme éditorial cream → navy → cream.
 */
.visibilite-path {
  background-color: var(--color-navy-elevated);
  color: var(--text-inverse);
  padding-block: var(--space-14) var(--space-14);
  position: relative;
  overflow: hidden;
}

/*
 * Discrète grille topographique en fond, courbes horizontales.
 * Contrairement à Valoriser (grille éditoriale verticale), Visibilité
 * utilise des lignes horizontales évoquant des courbes de niveau.
 */
.visibilite-path::before {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  background-image:
    linear-gradient(to bottom, rgba(196, 203, 211, 0.05) 1px, transparent 1px);
  background-size: 100% 42px;
  opacity: 0.6;
}

.visibilite-path__container {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.visibilite-path__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 52rem;
}

.visibilite-path__eyebrow {
  margin: 0;
}

/*
 * BaseEyebrow tone=inverse par défaut = devzair-blue #2e86d9 → 4.41:1
 * sur navy-elevated. On remonte à une teinte plus claire même famille
 * pour assurer ≥ 5:1 sur ce fond. `:deep()` percerait le scope.
 */
:deep(.base-eyebrow.visibilite-path__eyebrow[data-tone="inverse"]) {
  color: #5aa0e6;
}

.visibilite-path__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 3.8vw, 2.75rem);
  line-height: 1.1;
  letter-spacing: -0.01em;
  color: var(--color-cream);
  margin: 0;
  max-width: 26ch;
}

.visibilite-path__lead {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.65;
  color: var(--text-inverse-muted);
  margin: 0;
  max-width: 62ch;
}

/*
 * Frise. Mobile : pile verticale, chaque étape sur une bande à gauche.
 * Trajectoire verticale continue reliant les nœuds.
 */
.visibilite-path__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.visibilite-path__item {
  position: relative;
  display: grid;
  grid-template-columns: auto minmax(0, 1fr);
  column-gap: var(--space-5);
  row-gap: var(--space-1);
  padding-left: var(--space-2);
  padding-bottom: var(--space-6);
}

.visibilite-path__item-index {
  grid-row: 1 / span 2;
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.875rem;
  letter-spacing: 0.10em;
  color: #5aa0e6;
  margin: 0;
  align-self: start;
  padding-top: 0.15rem;
}

.visibilite-path__item-label {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.375rem;
  line-height: 1.15;
  color: var(--color-cream);
  margin: 0;
}

.visibilite-path__item-note {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-inverse-muted);
  margin: 0;
  max-width: 44ch;
}

.visibilite-path__connector {
  display: block;
  position: absolute;
  left: calc(var(--space-2) + 0.85rem);
  top: calc(1.5rem + 0.15rem);
  bottom: -0.5rem;
  width: 1px;
  background: linear-gradient(
    to bottom,
    rgba(90, 160, 230, 0.5) 0%,
    rgba(12, 91, 87, 0.5) 100%
  );
}

@media (min-width: 768px) {
  .visibilite-path {
    padding-block: var(--space-16) var(--space-16);
  }
}

/*
 * Desktop : frise horizontale asymétrique. Grille 12 col, chaque étape
 * prend un span légèrement différent (3-2-3-2-2). Connecteurs
 * horizontaux forment un fil continu de trajectoire signal.
 */
@media (min-width: 1024px) {
  .visibilite-path__list {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    column-gap: var(--space-4);
    row-gap: var(--space-6);
  }

  .visibilite-path__item {
    padding-left: 0;
    padding-bottom: 0;
    grid-template-columns: 1fr;
    grid-template-rows: auto auto auto;
    row-gap: var(--space-2);
    border-top: 1px solid rgba(196, 203, 211, 0.2);
    padding-top: var(--space-4);
  }

  .visibilite-path__item[data-position="1"] { grid-column: 1 / span 3; }
  .visibilite-path__item[data-position="2"] { grid-column: 4 / span 2; }
  .visibilite-path__item[data-position="3"] { grid-column: 6 / span 3; }
  .visibilite-path__item[data-position="4"] { grid-column: 9 / span 2; }
  .visibilite-path__item[data-position="5"] { grid-column: 11 / span 2; }

  .visibilite-path__item-index {
    grid-row: auto;
    align-self: auto;
    padding-top: 0;
  }

  .visibilite-path__connector {
    left: 0;
    right: calc(-1 * var(--space-4));
    top: -1px;
    bottom: auto;
    width: auto;
    height: 1px;
    background: linear-gradient(
      to right,
      rgba(90, 160, 230, 0.6) 0%,
      rgba(12, 91, 87, 0.4) 100%
    );
  }
}

@media (min-width: 1440px) {
  .visibilite-path {
    padding-block: var(--space-20) var(--space-20);
  }
}

@media (prefers-reduced-motion: reduce) {
  .visibilite-path__connector {
    background: rgba(90, 160, 230, 0.35);
  }
}
</style>
