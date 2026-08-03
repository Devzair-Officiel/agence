<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import { expertisePillars } from "~/config/expertise-pillars"

/**
 * Section « La réponse Devzair » — un parcours en cinq étapes qui reprend
 * les cinq pôles de `expertise-pillars.ts` (source unique). Aucune donnée
 * métier n'est dupliquée ici : label, shortLabel et ordre viennent tous
 * du contrat.
 *
 * Le parcours est explicitement un `<ol>` sémantique (ordre = passage de
 * relais). Les flèches entre étapes sont purement décoratives
 * (`aria-hidden="true"`).
 *
 * Responsive :
 *   - <768px : parcours vertical, chaque étape empilée, flèche verticale
 *     dans une ligne de connexion dédiée ;
 *   - 768–1099px : même lecture verticale, plus robuste que cinq cartes
 *     excessivement comprimées ;
 *   - ≥1100px : parcours horizontal alternant cinq colonnes de cartes et
 *     quatre colonnes de connexion dédiées.
 *
 * Fond : navy (contraste inversé), eyebrow en Devzair blue, corps cream.
 *
 * Accessibilité :
 *   - un H2 unique par section ;
 *   - libellé de la liste explicite (`aria-label`) ;
 *   - chaque étape a une numérotation textuelle (utile au lecteur d'écran)
 *     et un titre court ; la description longue est masquée visuellement
 *     mais lue par le lecteur d'écran, pour ne pas surcharger un parcours
 *     déjà dense visuellement.
 */

const steps = [...expertisePillars].sort((a, b) => a.order - b.order)
</script>

<template>
  <section
    class="home-approach"
    aria-labelledby="home-approach-title"
  >
    <!--
      Décor de fond « circuit imprimé » — traces à angles droits, pads de
      soudure et vias, en écho au logo Devzair. Purement décoratif :
      `aria-hidden`, `pointer-events: none`, `preserveAspectRatio` calé pour
      que le motif s'étende sur toute la largeur de la section sans se
      déformer verticalement.
    -->
    <svg
      class="home-approach__pcb"
      viewBox="0 0 800 400"
      preserveAspectRatio="xMidYMid slice"
      aria-hidden="true"
      focusable="false"
      xmlns="http://www.w3.org/2000/svg"
    >
      <!-- Traces principales : horizontales avec des ruptures à 90°. -->
      <g
        fill="none"
        stroke="var(--color-devzair-blue)"
        stroke-width="1.25"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <path d="M0 60 H200 L260 120 H420 L480 60 H620 L680 120 H800" />
        <path d="M0 240 H120 L180 300 H360 L420 240 H560 L620 300 H800" />
        <path d="M300 180 H500" />
        <path d="M120 240 V180 H240" />
        <path d="M620 300 V340 H720" />
      </g>
      <!-- Pads de soudure (composants raccordés). -->
      <g fill="var(--color-devzair-blue)">
        <circle cx="200" cy="60" r="4" />
        <circle cx="420" cy="60" r="4" />
        <circle cx="620" cy="60" r="4" />
        <circle cx="120" cy="240" r="4" />
        <circle cx="360" cy="240" r="4" />
        <circle cx="560" cy="240" r="4" />
        <circle cx="300" cy="180" r="3" />
        <circle cx="500" cy="180" r="3" />
        <circle cx="240" cy="180" r="3" />
        <circle cx="720" cy="340" r="3" />
      </g>
      <!-- Vias (traversées de plan) : anneaux creux. -->
      <g fill="none" stroke="var(--color-devzair-blue)" stroke-width="1">
        <circle cx="260" cy="120" r="5" />
        <circle cx="480" cy="60" r="5" />
        <circle cx="680" cy="120" r="5" />
        <circle cx="180" cy="300" r="5" />
        <circle cx="420" cy="240" r="5" />
        <circle cx="620" cy="300" r="5" />
      </g>
    </svg>
    <BaseContainer width="wide" class="home-approach__container">
      <header class="home-approach__intro">
        <BaseEyebrow tone="inverse" class="home-approach__eyebrow">
          La réponse Devzair
        </BaseEyebrow>
        <h2 id="home-approach-title" class="home-approach__title">
          Une seule démarche, où chaque pôle passe le relais au suivant.
        </h2>
        <p class="home-approach__lead">
          Plutôt qu'empiler des prestataires indépendants, nous réunissons les
          cinq expertises dans une même équipe et un même calendrier. Chaque
          étape prépare la suivante sans rupture d'information ni de
          responsabilité.
        </p>
      </header>

      <ol class="home-approach__journey" aria-label="Parcours en cinq étapes">
        <li
          v-for="step in steps"
          :key="step.id"
          class="home-approach__step"
          :data-order="step.order"
        >
          <div class="home-approach__step-card">
            <span class="home-approach__step-index" aria-hidden="true">
              {{ String(step.order).padStart(2, "0") }}
            </span>
            <h3 class="home-approach__step-title">{{ step.label }}</h3>
            <p class="home-approach__step-tag">{{ step.description }}</p>
            <span class="sr-only">{{ step.longDescription }}</span>
          </div>
          <span
            v-if="step.order < steps.length"
            class="home-approach__connector"
            aria-hidden="true"
          >
            <span class="home-approach__connector-glyph home-approach__connector-glyph--vertical">↓</span>
            <span class="home-approach__connector-glyph home-approach__connector-glyph--horizontal">→</span>
          </span>
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
.home-approach {
  position: relative;
  isolation: isolate;
  overflow: hidden;
  background-color: var(--background-inverse);
  color: var(--text-inverse);
  padding-block: var(--space-16) var(--space-20);
}

/*
 * Décor PCB en arrière-plan : couvre toute la section, très basse opacité
 * pour rester une note graphique et non un motif dominant. `preserveAspect
 * -Ratio` slice (défini côté SVG) recadre le motif sans le déformer.
 */
.home-approach__pcb {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
  opacity: 0.09;
  pointer-events: none;
}

/*
 * Voile radial pour concentrer la lisibilité du contenu au centre et
 * laisser le décor PCB respirer sur les bords sans venir mordre le texte.
 */
.home-approach::before {
  content: "";
  position: absolute;
  inset: 0;
  z-index: 0;
  background: radial-gradient(
    ellipse at center,
    var(--background-inverse) 0%,
    transparent 65%
  );
  pointer-events: none;
}

.home-approach__container {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  gap: var(--space-12);
}

.home-approach__intro {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 44rem;
}

.home-approach__eyebrow {
  margin-bottom: var(--space-2);
}

.home-approach__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 4vw, 2.5rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-inverse);
  margin: 0;
  max-width: 22ch;
}

.home-approach__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  line-height: 1.6;
  color: var(--text-inverse-muted);
  margin: 0;
  max-width: 56ch;
}

.home-approach__journey {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: minmax(0, 1fr);
}

.home-approach__step {
  display: grid;
  grid-template-rows: minmax(0, auto) var(--space-8);
  min-width: 0;
}

.home-approach__step:last-child {
  grid-template-rows: minmax(0, auto);
}

.home-approach__step-card {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding: var(--space-5) var(--space-6);
  background-color: var(--surface-inverse);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-inverse);
}

.home-approach__connector {
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-family-mono);
  font-size: 1.125rem;
  line-height: 1;
  color: var(--color-devzair-blue);
  opacity: 0.7;
  pointer-events: none;
}

.home-approach__connector-glyph--horizontal {
  display: none;
}

/*
 * L'index numérique est du petit texte gras (Space Mono 12px bold).
 * Devzair blue (#2e86d9) sur surface-inverse (#141e2c) donne 4.41:1 —
 * juste en dessous du seuil WCAG AA 4.5:1. On repasse sur cream (~13:1).
 * Le libellé « eyebrow » plus haut reste, lui, en Devzair blue sur navy
 * de base — contraste largement suffisant.
 */
.home-approach__step-index {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.08em;
  color: var(--color-cream);
}

.home-approach__step-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1.0625rem;
  line-height: 1.3;
  color: var(--text-inverse);
  margin: 0;
}

.home-approach__step-tag {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--text-inverse-muted);
  margin: 0;
}

/* Réservé au lecteur d'écran — même règle que le skip link. */
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

@media (min-width: 768px) {
  .home-approach {
    padding-block: var(--space-20) var(--space-24);
  }
}

@media (min-width: 1100px) {
  .home-approach__journey {
    grid-template-columns:
      repeat(4, minmax(0, 1fr) var(--space-8))
      minmax(0, 1fr);
    align-items: stretch;
  }

  .home-approach__step {
    grid-column: span 2;
    grid-template-columns: minmax(0, 1fr) var(--space-8);
    grid-template-rows: minmax(0, 1fr);
  }

  .home-approach__step:last-child {
    grid-column: span 1;
    grid-template-columns: minmax(0, 1fr);
  }

  .home-approach__connector {
    font-size: 1rem;
  }

  .home-approach__connector-glyph--vertical {
    display: none;
  }

  .home-approach__connector-glyph--horizontal {
    display: inline;
  }

  .home-approach__step-card {
    padding: var(--space-6) var(--space-6) var(--space-8);
  }

  .home-approach__step-title {
    font-size: 1.125rem;
  }
}
</style>
