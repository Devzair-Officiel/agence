<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Section « Notre approche » de la page Valoriser — direction séquence
 * éditoriale « de la matière au message ».
 *
 * Cinq étapes rendues dans une vraie `<ol>` :
 *   01 · Observer     — Activité, offre, publics
 *   02 · Préparer     — Angles, prises de vue, messages
 *   03 · Produire     — Images et textes
 *   04 · Structurer   — Pages, offres, hiérarchie
 *   05 · Décliner     — Site et supports
 *
 * Direction visuelle :
 *   - fond navy (contraste avec les sections cream) — SEULE bande sombre
 *     de la page ; les autres sections restent lumineuses ;
 *   - desktop : frise horizontale asymétrique, chaque étape occupe une
 *     largeur légèrement différente pour ne pas ressembler à une timeline
 *     industrielle ; connecteurs mono petrol ;
 *   - mobile : pile verticale, chaque étape sur une bande dédiée.
 *
 * Aucune promesse chiffrée, aucune durée. Les libellés sont des mots-clefs
 * qui reformulent la matière décrite par `approachDescription`
 * (photographies, textes, structuration).
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
  { key: "observer", label: "Observer", note: "Activité, offre, publics." },
  { key: "preparer", label: "Préparer", note: "Angles, prises de vue, messages." },
  { key: "produire", label: "Produire", note: "Images et textes." },
  { key: "structurer", label: "Structurer", note: "Pages, offres, hiérarchie." },
  { key: "decliner", label: "Décliner", note: "Site et supports." },
]

const generatedId = useId()
const titleId = computed(() => `valoriser-flow-title-${generatedId}`)
</script>

<template>
  <section
    class="valoriser-flow"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="valoriser-flow__container">
      <header class="valoriser-flow__header">
        <BaseEyebrow tone="inverse" class="valoriser-flow__eyebrow">
          Notre approche
        </BaseEyebrow>
        <h2 :id="titleId" class="valoriser-flow__title">
          De la matière au message.
        </h2>
        <p class="valoriser-flow__lead">
          {{ approachDescription }}
        </p>
      </header>

      <ol class="valoriser-flow__list" role="list">
        <li
          v-for="(step, index) in steps"
          :key="step.key"
          class="valoriser-flow__item"
          :data-position="index + 1"
        >
          <p class="valoriser-flow__item-index" aria-hidden="true">
            {{ String(index + 1).padStart(2, "0") }}
          </p>
          <h3 class="valoriser-flow__item-label">{{ step.label }}</h3>
          <p class="valoriser-flow__item-note">{{ step.note }}</p>
          <span
            v-if="index < steps.length - 1"
            class="valoriser-flow__connector"
            aria-hidden="true"
          />
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
/*
 * Seule bande sombre de la page — sépare visuellement l'approche de ce
 * qui la précède (audience éditoriale cream) et de ce qui la suit
 * (content kit cream). La transition cream → navy → cream cadence la
 * lecture comme des chapitres.
 */
.valoriser-flow {
  background-color: var(--color-navy-elevated);
  color: var(--text-inverse);
  padding-block: var(--space-14) var(--space-14);
  position: relative;
  overflow: hidden;
}

/*
 * Discrète grille éditoriale en fond, cadrant la frise. Petrol atténué
 * pour évoquer un tableau d'atelier plutôt qu'un dashboard.
 */
.valoriser-flow::before {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  background-image:
    linear-gradient(to right, rgba(196, 203, 211, 0.06) 1px, transparent 1px);
  background-size: 96px 100%;
  opacity: 0.6;
}

.valoriser-flow__container {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.valoriser-flow__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 52rem;
}

.valoriser-flow__eyebrow {
  margin: 0;
}

/*
 * `BaseEyebrow tone="inverse"` par défaut = devzair-blue #2e86d9 → 4.41:1
 * sur navy-elevated (juste sous AA). On remonte à une teinte plus claire
 * même famille pour assurer ≥ 5:1 sur ce fond, sans changer la
 * personnalité. `:deep()` percerait le scope de BaseEyebrow.
 */
:deep(.base-eyebrow.valoriser-flow__eyebrow[data-tone="inverse"]) {
  color: #5aa0e6;
}

.valoriser-flow__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 3.8vw, 2.75rem);
  line-height: 1.1;
  letter-spacing: -0.01em;
  color: var(--color-cream);
  margin: 0;
  max-width: 24ch;
}

.valoriser-flow__lead {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.65;
  color: var(--text-inverse-muted);
  margin: 0;
  max-width: 62ch;
}

/*
 * Frise. Mobile : pile verticale, chaque étape sur une bande à gauche
 * (index + label + note). Connecteur vertical entre étapes.
 */
.valoriser-flow__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.valoriser-flow__item {
  position: relative;
  display: grid;
  grid-template-columns: auto minmax(0, 1fr);
  column-gap: var(--space-5);
  row-gap: var(--space-1);
  padding-left: var(--space-2);
  padding-bottom: var(--space-6);
}

.valoriser-flow__item-index {
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

.valoriser-flow__item-label {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.375rem;
  line-height: 1.15;
  color: var(--color-cream);
  margin: 0;
}

.valoriser-flow__item-note {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-inverse-muted);
  margin: 0;
  max-width: 44ch;
}

.valoriser-flow__connector {
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
  .valoriser-flow {
    padding-block: var(--space-16) var(--space-16);
  }
}

/*
 * Desktop : frise horizontale asymétrique. La grille alloue 12 colonnes
 * et chaque étape prend un span légèrement différent (3 - 2 - 3 - 2 - 2)
 * pour donner un rythme éditorial (première et troisième étapes plus
 * larges, dernières plus resserrées). Les connecteurs deviennent
 * horizontaux et forment un fil continu.
 */
@media (min-width: 1024px) {
  .valoriser-flow__list {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    column-gap: var(--space-4);
    row-gap: var(--space-6);
  }

  .valoriser-flow__item {
    padding-left: 0;
    padding-bottom: 0;
    grid-template-columns: 1fr;
    grid-template-rows: auto auto auto;
    row-gap: var(--space-2);
    border-top: 1px solid rgba(196, 203, 211, 0.2);
    padding-top: var(--space-4);
  }

  .valoriser-flow__item[data-position="1"] { grid-column: 1 / span 3; }
  .valoriser-flow__item[data-position="2"] { grid-column: 4 / span 2; }
  .valoriser-flow__item[data-position="3"] { grid-column: 6 / span 3; }
  .valoriser-flow__item[data-position="4"] { grid-column: 9 / span 2; }
  .valoriser-flow__item[data-position="5"] { grid-column: 11 / span 2; }

  .valoriser-flow__item-index {
    grid-row: auto;
    align-self: auto;
    padding-top: 0;
  }

  .valoriser-flow__connector {
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
  .valoriser-flow {
    padding-block: var(--space-20) var(--space-20);
  }
}

@media (prefers-reduced-motion: reduce) {
  .valoriser-flow__connector {
    background: rgba(90, 160, 230, 0.35);
  }
}
</style>
