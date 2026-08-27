<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * « Notre approche » sur la page Construire — pipeline de fabrication.
 *
 * Cinq étapes validées par le brief éditorial, dans cet ordre exact :
 *   1. Socle          — Architecture et environnement
 *   2. Interfaces     — Écrans et parcours
 *   3. Logique métier — Fonctionnalités et règles
 *   4. Connexions     — Données et outils existants
 *   5. Validation     — Tests, documentation, livraison
 *
 * Sémantique : le rendu est une vraie liste ordonnée (`<ol>`). Les
 * marqueurs numérotés sont rendus en HTML plutôt qu'en `::before`
 * cosmétique — lisible en copie clipboard et sans dépendance CSS.
 *
 * Responsive :
 *   - mobile (< 720px) : liste verticale, connecteur vertical entre
 *     chaque étape ;
 *   - tablette (720px–1023px) : grille 3+2, connecteurs supprimés
 *     (ils casseraient sur deux rangées) ;
 *   - desktop (≥ 1024px) : 5 colonnes en pipeline avec rail horizontal.
 *
 * Aucune promesse ni chiffre inventés : chaque libellé reprend un mot déjà
 * validé par le brief.
 */

interface BuildStep {
  readonly key: string
  readonly index: number
  readonly title: string
  readonly caption: string
}

const steps: readonly BuildStep[] = [
  {
    key: "socle",
    index: 1,
    title: "Socle",
    caption: "Architecture et environnement.",
  },
  {
    key: "interfaces",
    index: 2,
    title: "Interfaces",
    caption: "Écrans et parcours.",
  },
  {
    key: "logique",
    index: 3,
    title: "Logique métier",
    caption: "Fonctionnalités et règles.",
  },
  {
    key: "connexions",
    index: 4,
    title: "Connexions",
    caption: "Données et outils existants.",
  },
  {
    key: "validation",
    index: 5,
    title: "Validation",
    caption: "Tests, documentation, livraison.",
  },
]

interface Props {
  approachDescription: string
}

defineProps<Props>()

const generatedId = useId()
const titleId = computed(() => `construire-flow-title-${generatedId}`)
</script>

<template>
  <section
    class="construire-flow"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="construire-flow__container">
      <header class="construire-flow__header">
        <BaseEyebrow tone="inverse" class="construire-flow__eyebrow">
          Notre approche
        </BaseEyebrow>
        <h2 :id="titleId" class="construire-flow__title">
          Construire par couches. Valider à chaque étape.
        </h2>
        <p class="construire-flow__lead">
          {{ approachDescription }}
        </p>
      </header>

      <ol class="construire-flow__list" role="list">
        <li
          v-for="step in steps"
          :key="step.key"
          class="construire-flow__item"
        >
          <div class="construire-flow__marker" aria-hidden="true">
            <span class="construire-flow__marker-index">
              {{ String(step.index).padStart(2, "0") }}
            </span>
          </div>
          <div class="construire-flow__body">
            <p class="construire-flow__step-label" aria-hidden="true">
              Étape {{ step.index }} sur {{ steps.length }}
            </p>
            <h3 class="construire-flow__step-title">{{ step.title }}</h3>
            <p class="construire-flow__step-caption">{{ step.caption }}</p>
          </div>
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
.construire-flow {
  position: relative;
  background-color: var(--color-navy);
  color: var(--text-inverse);
  padding-block: var(--space-16) var(--space-16);
  overflow: hidden;
}

.construire-flow::before {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  background-image:
    linear-gradient(to right, rgba(46, 134, 217, 0.06) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(46, 134, 217, 0.06) 1px, transparent 1px);
  background-size: 40px 40px;
  mask-image: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.8), transparent 72%);
  -webkit-mask-image: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.8), transparent 72%);
}

.construire-flow__container {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.construire-flow__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 48rem;
}

.construire-flow__eyebrow {
  margin: 0;
}

.construire-flow__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 3.8vw, 2.75rem);
  line-height: 1.1;
  letter-spacing: -0.01em;
  color: var(--color-cream);
  margin: 0;
  max-width: 24ch;
}

.construire-flow__lead {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.65;
  color: var(--text-inverse-muted);
  margin: 0;
  max-width: 62ch;
}

.construire-flow__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-6);
  position: relative;
}

.construire-flow__item {
  position: relative;
  display: grid;
  grid-template-columns: auto 1fr;
  gap: var(--space-4);
  padding: var(--space-5);
  border: 1px solid rgba(46, 134, 217, 0.20);
  border-radius: var(--radius-lg);
  background-color: rgba(20, 30, 44, 0.72);
  backdrop-filter: blur(4px);
}

/* Connecteur vertical mobile : petit rail entre les étapes. */
.construire-flow__item + .construire-flow__item::before {
  content: "";
  position: absolute;
  top: calc(-1 * var(--space-6));
  left: calc(var(--space-5) + 22px);
  width: 2px;
  height: var(--space-6);
  background: linear-gradient(
    to bottom,
    transparent,
    rgba(46, 134, 217, 0.6)
  );
}

.construire-flow__marker {
  width: 44px;
  height: 44px;
  border-radius: var(--radius-sm, 4px);
  border: 1.5px solid var(--color-devzair-blue);
  background-color: rgba(46, 134, 217, 0.12);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.construire-flow__marker-index {
  font-family: var(--font-family-mono);
  font-size: 0.8125rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  color: var(--color-devzair-blue);
}

.construire-flow__body {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.construire-flow__step-label {
  font-family: var(--font-family-mono);
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: rgba(196, 203, 211, 0.72);
  margin: 0;
}

.construire-flow__step-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.25rem;
  line-height: 1.2;
  color: var(--color-cream);
  margin: 0;
}

.construire-flow__step-caption {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: rgba(244, 241, 234, 0.82);
  margin: var(--space-1) 0 0;
  max-width: 34ch;
}

/* Tablette : 3 + 2 (5 items, wrap naturel sur deux rangées). */
@media (min-width: 720px) {
  .construire-flow__list {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--space-4);
  }

  .construire-flow__item {
    grid-template-columns: 1fr;
    gap: var(--space-4);
    padding: var(--space-6);
  }

  .construire-flow__item + .construire-flow__item::before {
    content: none;
  }
}

/* Desktop : 5 colonnes en pipeline horizontal. */
@media (min-width: 1024px) {
  .construire-flow__list {
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: var(--space-4);
    padding-top: var(--space-8);
  }

  .construire-flow__list::before {
    content: "";
    position: absolute;
    top: calc(var(--space-8) + var(--space-6) + 22px);
    left: 10%;
    right: 10%;
    height: 2px;
    background: linear-gradient(
      to right,
      transparent,
      rgba(46, 134, 217, 0.5) 15%,
      rgba(46, 134, 217, 0.5) 85%,
      transparent
    );
    z-index: 0;
  }

  .construire-flow__item {
    z-index: 1;
    background-color: rgba(20, 30, 44, 0.92);
  }

  .construire-flow__step-caption {
    max-width: none;
  }
}

@media (min-width: 1440px) {
  .construire-flow {
    padding-block: var(--space-20) var(--space-20);
  }
}

@media (prefers-reduced-motion: reduce) {
  .construire-flow__item {
    backdrop-filter: none;
  }
}
</style>
