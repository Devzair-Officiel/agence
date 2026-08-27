<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Section « Notre approche » de la page Faire évoluer — direction boucle
 * continue en cinq étapes.
 *
 * Cinq étapes rendues dans une vraie `<ol>` (l'ordre importe) :
 *   01 · OBSERVER   — Usages, performances, erreurs
 *   02 · MAINTENIR  — Mises à jour et sécurité
 *   03 · PRIORISER  — Besoins et évolutions utiles
 *   04 · LIVRER     — Correctifs et améliorations
 *   05 · RÉÉVALUER  — Mesurer l'effet et poursuivre
 *
 * Direction visuelle spécifique à ce pôle :
 *   - fond navy-elevated (unique bande sombre de la page) ;
 *   - décor SVG qui reconnecte visuellement l'étape 05 à l'étape 01 —
 *     une flèche de retour qui matérialise la boucle sans la porter
 *     par la seule position ;
 *   - l'ordre HTML reste linéaire pour la sémantique et l'accessibilité.
 *
 * Aucune promesse chiffrée. Aucune valeur métrique.
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
    key: "observer",
    label: "Observer",
    note: "Usages, performances, erreurs.",
  },
  {
    key: "maintenir",
    label: "Maintenir",
    note: "Mises à jour et sécurité.",
  },
  {
    key: "prioriser",
    label: "Prioriser",
    note: "Besoins et évolutions utiles.",
  },
  {
    key: "livrer",
    label: "Livrer",
    note: "Correctifs et améliorations.",
  },
  {
    key: "reevaluer",
    label: "Réévaluer",
    note: "Mesurer l'effet et poursuivre.",
  },
]

const generatedId = useId()
const titleId = computed(() => `faire-evoluer-loop-title-${generatedId}`)
</script>

<template>
  <section
    class="faire-evoluer-loop"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="faire-evoluer-loop__container">
      <header class="faire-evoluer-loop__header">
        <BaseEyebrow tone="inverse" class="faire-evoluer-loop__eyebrow">
          Notre approche
        </BaseEyebrow>
        <h2 :id="titleId" class="faire-evoluer-loop__title">
          Observer. Prioriser. Améliorer. Recommencer.
        </h2>
        <p class="faire-evoluer-loop__lead">
          {{ approachDescription }}
        </p>
      </header>

      <div class="faire-evoluer-loop__wrap">
        <!-- Décor de boucle : orbite partielle + flèche de retour de 05 → 01.
             Rendu purement décoratif. La sémantique de « cycle » n'est pas
             portée par ce SVG seul : le H2 et le texte des cinq étapes la
             posent explicitement (Observer → Réévaluer → recommencer). -->
        <svg
          class="faire-evoluer-loop__decor"
          viewBox="0 0 960 480"
          role="presentation"
          focusable="false"
          aria-hidden="true"
          preserveAspectRatio="none"
        >
          <!-- Fil principal reliant les cinq nœuds -->
          <path
            d="M 96 240 Q 216 96 336 240 T 576 240 T 864 240"
            fill="none"
            stroke="#5aa0e6"
            stroke-width="1.6"
            stroke-linecap="round"
            opacity="0.55"
          />
          <!-- Boucle de retour large : de 864 (05) vers 96 (01), en bas -->
          <path
            d="M 864 240 C 864 420, 96 420, 96 240"
            fill="none"
            stroke="#0c5b57"
            stroke-width="1.4"
            stroke-linecap="round"
            stroke-dasharray="6 6"
            opacity="0.6"
          />
          <!-- Flèche à l'atterrissage sur le nœud 01 -->
          <g fill="none" stroke="#0c5b57" stroke-width="1.6" stroke-linecap="round">
            <path d="M 106 254 L 96 240 L 108 226" />
          </g>
        </svg>

        <ol class="faire-evoluer-loop__list" role="list">
          <li
            v-for="(step, index) in steps"
            :key="step.key"
            class="faire-evoluer-loop__step"
            :data-position="index + 1"
          >
            <p class="faire-evoluer-loop__step-index" aria-hidden="true">
              {{ String(index + 1).padStart(2, "0") }}
            </p>
            <h3 class="faire-evoluer-loop__step-label">{{ step.label }}</h3>
            <p class="faire-evoluer-loop__step-note">{{ step.note }}</p>
          </li>
        </ol>
      </div>

      <p class="faire-evoluer-loop__return" aria-hidden="true">
        Réévaluer → recommencer
      </p>
    </BaseContainer>
  </section>
</template>

<style scoped>
/*
 * Seule bande sombre de la page — sépare visuellement l'approche du bloc
 * cream qui précède (audience) et du bloc cream qui suit (cadence).
 * Rythme éditorial cream → navy → cream, comme Visibilité et Valoriser.
 */
.faire-evoluer-loop {
  background-color: var(--color-navy-elevated);
  color: var(--text-inverse);
  padding-block: var(--space-14) var(--space-14);
  position: relative;
  overflow: hidden;
}

.faire-evoluer-loop::before {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  background-image:
    radial-gradient(
      circle at 50% 60%,
      rgba(90, 160, 230, 0.08) 0%,
      rgba(90, 160, 230, 0.03) 40%,
      transparent 70%
    );
}

.faire-evoluer-loop__container {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.faire-evoluer-loop__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 52rem;
}

.faire-evoluer-loop__eyebrow {
  margin: 0;
}

/*
 * BaseEyebrow tone=inverse = devzair-blue #2e86d9 → 4.41:1 sur
 * navy-elevated. On remonte à #5aa0e6 (même famille, plus clair) pour
 * assurer ≥ 5:1 sur ce fond — même pattern que Visibilité.
 */
:deep(.base-eyebrow.faire-evoluer-loop__eyebrow[data-tone="inverse"]) {
  color: #5aa0e6;
}

.faire-evoluer-loop__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 3.8vw, 2.75rem);
  line-height: 1.1;
  letter-spacing: -0.01em;
  color: var(--color-cream);
  margin: 0;
  max-width: 26ch;
}

.faire-evoluer-loop__lead {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.65;
  color: var(--text-inverse-muted);
  margin: 0;
  max-width: 62ch;
}

.faire-evoluer-loop__wrap {
  position: relative;
}

/* Décor de boucle : caché sur mobile pour privilégier la pile verticale. */
.faire-evoluer-loop__decor {
  display: none;
}

/* Étapes : mobile en pile verticale, chaque étape sur bandeau. */
.faire-evoluer-loop__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.faire-evoluer-loop__step {
  position: relative;
  display: grid;
  grid-template-columns: auto minmax(0, 1fr);
  column-gap: var(--space-5);
  row-gap: var(--space-1);
  padding-left: var(--space-2);
  padding-bottom: var(--space-6);
}

.faire-evoluer-loop__step-index {
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

.faire-evoluer-loop__step-label {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.375rem;
  line-height: 1.15;
  color: var(--color-cream);
  margin: 0;
}

.faire-evoluer-loop__step-note {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-inverse-muted);
  margin: 0;
  max-width: 44ch;
}

.faire-evoluer-loop__return {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #5aa0e6;
  margin: 0;
  align-self: flex-end;
}

@media (min-width: 768px) {
  .faire-evoluer-loop {
    padding-block: var(--space-16) var(--space-16);
  }
}

/*
 * Desktop : composition horizontale évoquant une trajectoire ondulante.
 * Les cinq étapes prennent 12 col, décalées verticalement pour esquisser
 * une courbe. Le décor SVG passe DERRIÈRE — 05 se raccroche à 01 par
 * une boucle basse en pointillés + flèche.
 */
@media (min-width: 1024px) {
  .faire-evoluer-loop__wrap {
    padding-block: var(--space-6);
  }

  .faire-evoluer-loop__decor {
    display: block;
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    pointer-events: none;
  }

  .faire-evoluer-loop__list {
    position: relative;
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: var(--space-4);
    z-index: 1;
  }

  .faire-evoluer-loop__step {
    padding: 0;
    grid-template-columns: 1fr;
    grid-template-rows: auto auto auto;
    row-gap: var(--space-2);
    padding-top: var(--space-4);
    border-top: 1px solid rgba(196, 203, 211, 0.2);
  }

  .faire-evoluer-loop__step[data-position="1"] {
    margin-top: 0;
  }

  .faire-evoluer-loop__step[data-position="2"] {
    margin-top: var(--space-8);
  }

  .faire-evoluer-loop__step[data-position="3"] {
    margin-top: 0;
  }

  .faire-evoluer-loop__step[data-position="4"] {
    margin-top: var(--space-8);
  }

  .faire-evoluer-loop__step[data-position="5"] {
    margin-top: 0;
  }

  .faire-evoluer-loop__step-index {
    grid-row: auto;
    align-self: auto;
    padding-top: 0;
  }
}

@media (min-width: 1440px) {
  .faire-evoluer-loop {
    padding-block: var(--space-20) var(--space-20);
  }
}

@media (prefers-reduced-motion: reduce) {
  .faire-evoluer-loop__decor {
    opacity: 0.9;
  }
}
</style>
