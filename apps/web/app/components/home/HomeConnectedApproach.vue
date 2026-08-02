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
 *     entre deux items (via un pseudo-élément décoratif) ;
 *   - ≥768px : parcours horizontal en cinq colonnes égales, flèches → entre
 *     items via pseudo-élément ;
 *   - ≥1024px : espacements amples, typographie légèrement montée.
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
    <BaseContainer class="home-approach__container">
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
          <span class="home-approach__step-index" aria-hidden="true">
            {{ String(step.order).padStart(2, "0") }}
          </span>
          <h3 class="home-approach__step-title">{{ step.label }}</h3>
          <p class="home-approach__step-tag">{{ step.description }}</p>
          <span class="sr-only">{{ step.longDescription }}</span>
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
.home-approach {
  background-color: var(--background-inverse);
  color: var(--text-inverse);
  padding-block: var(--space-16) var(--space-20);
}

.home-approach__container {
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
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.home-approach__step {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding: var(--space-5) var(--space-6);
  background-color: var(--surface-inverse);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-inverse);
}

/* Flèche décorative verticale entre deux étapes (mobile). */
.home-approach__step:not(:last-child)::after {
  content: "↓";
  position: absolute;
  left: 50%;
  bottom: calc(-1 * var(--space-6));
  transform: translateX(-50%);
  font-family: var(--font-family-mono);
  font-size: 1.125rem;
  line-height: 1;
  color: var(--color-devzair-blue);
  opacity: 0.7;
  pointer-events: none;
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

  .home-approach__journey {
    flex-direction: row;
    align-items: stretch;
    gap: var(--space-4);
  }

  .home-approach__step {
    flex: 1 1 0;
    min-width: 0;
  }

  /* Flèche horizontale entre deux étapes (tablette/desktop). */
  .home-approach__step:not(:last-child)::after {
    content: "→";
    left: auto;
    right: calc(-1 * var(--space-4));
    bottom: auto;
    top: 50%;
    transform: translate(50%, -50%);
    font-size: 1rem;
  }
}

@media (min-width: 1024px) {
  .home-approach__journey {
    gap: var(--space-5);
  }

  .home-approach__step {
    padding: var(--space-6) var(--space-6) var(--space-8);
  }

  .home-approach__step-title {
    font-size: 1.125rem;
  }
}
</style>
