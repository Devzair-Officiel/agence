<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * « Du flou au système » — carte de processus de la page Concevoir.
 *
 * Cinq étapes validées par le brief éditorial, dans cet ordre exact :
 *   1. Objectifs
 *   2. Structure
 *   3. Parcours
 *   4. Interface
 *   5. Système
 *
 * Sémantique : le rendu est une vraie liste ordonnée (`<ol>`). Les
 * marqueurs numérotés ne sont pas des `::before` cosmétiques mais rendus
 * en HTML pour rester lisibles quand la feuille de styles ne charge pas
 * (défensif SSR + copie clipboard).
 *
 * Responsive :
 *   - mobile (< 720px) : liste verticale, un connecteur vertical entre
 *     chaque étape ;
 *   - tablette (720px–1023px) : grille 3 étapes / 2 étapes, sans lignes
 *     de connexion (elles casseraient sur deux rangées) ;
 *   - desktop (≥ 1024px) : 5 colonnes en ligne avec un rail horizontal.
 *
 * Chaque étape reprend un mot-clé validé + une brève phrase de contexte
 * dérivée de vocabulaire déjà présent dans `expertise-pages.ts`
 * (deliverables/approach) — aucune promesse ni chiffre nouveau.
 */

interface ProcessStep {
  readonly key: string
  readonly index: number
  readonly title: string
  readonly caption: string
}

const steps: readonly ProcessStep[] = [
  {
    key: "objectifs",
    index: 1,
    title: "Objectifs",
    caption: "Cadrer vos objectifs et vos utilisateurs.",
  },
  {
    key: "structure",
    index: 2,
    title: "Structure",
    caption: "Hiérarchiser les contenus et l'arborescence.",
  },
  {
    key: "parcours",
    index: 3,
    title: "Parcours",
    caption: "Cartographier les parcours principaux.",
  },
  {
    key: "interface",
    index: 4,
    title: "Interface",
    caption: "Produire les maquettes responsive.",
  },
  {
    key: "systeme",
    index: 5,
    title: "Système",
    caption: "Poser typographies, couleurs et composants.",
  },
]

const generatedId = useId()
const titleId = computed(() => `concevoir-process-title-${generatedId}`)
</script>

<template>
  <section
    class="concevoir-process"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="concevoir-process__container">
      <header class="concevoir-process__header">
        <BaseEyebrow tone="inverse" class="concevoir-process__eyebrow">
          Méthode de conception
        </BaseEyebrow>
        <h2 :id="titleId" class="concevoir-process__title">
          Du flou au système.
        </h2>
      </header>

      <ol class="concevoir-process__list" role="list">
        <li
          v-for="step in steps"
          :key="step.key"
          class="concevoir-process__item"
        >
          <div class="concevoir-process__marker" aria-hidden="true">
            <span class="concevoir-process__marker-index">
              {{ String(step.index).padStart(2, "0") }}
            </span>
          </div>
          <div class="concevoir-process__body">
            <p class="concevoir-process__step-label" aria-hidden="true">
              Étape {{ step.index }} sur {{ steps.length }}
            </p>
            <h3 class="concevoir-process__step-title">{{ step.title }}</h3>
            <p class="concevoir-process__step-caption">{{ step.caption }}</p>
          </div>
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
.concevoir-process {
  position: relative;
  background-color: var(--color-navy);
  color: var(--text-inverse);
  padding-block: var(--space-16) var(--space-16);
  overflow: hidden;
}

/* Fond grille discret, façon calque de plan sur fond sombre. */
.concevoir-process::before {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  background-image:
    linear-gradient(to right, rgba(244, 241, 234, 0.05) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(244, 241, 234, 0.05) 1px, transparent 1px);
  background-size: 40px 40px;
  mask-image: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.8), transparent 70%);
  -webkit-mask-image: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.8), transparent 70%);
}

.concevoir-process__container {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.concevoir-process__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 40rem;
}

.concevoir-process__eyebrow {
  margin: 0;
}

.concevoir-process__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 3.8vw, 2.75rem);
  line-height: 1.1;
  letter-spacing: -0.01em;
  color: var(--color-cream);
  margin: 0;
}

.concevoir-process__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-6);
  position: relative;
}

.concevoir-process__item {
  position: relative;
  display: grid;
  grid-template-columns: auto 1fr;
  gap: var(--space-4);
  padding: var(--space-5);
  border: 1px solid rgba(244, 241, 234, 0.12);
  border-radius: var(--radius-lg);
  background-color: rgba(20, 30, 44, 0.6);
  backdrop-filter: blur(4px);
}

/* Connecteur vertical mobile : petit rail entre les étapes. */
.concevoir-process__item + .concevoir-process__item::before {
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

.concevoir-process__marker {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: 1.5px solid var(--color-devzair-blue);
  background-color: rgba(46, 134, 217, 0.12);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.concevoir-process__marker-index {
  font-family: var(--font-family-mono);
  font-size: 0.8125rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  color: var(--color-devzair-blue);
}

.concevoir-process__body {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.concevoir-process__step-label {
  font-family: var(--font-family-mono);
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: rgba(196, 203, 211, 0.72);
  margin: 0;
}

.concevoir-process__step-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.25rem;
  line-height: 1.2;
  color: var(--color-cream);
  margin: 0;
}

.concevoir-process__step-caption {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.5;
  color: rgba(244, 241, 234, 0.82);
  margin: var(--space-1) 0 0;
  max-width: 34ch;
}

/* Tablette : 3 + 2 (5 items, wrap naturel sur deux rangées). */
@media (min-width: 720px) {
  .concevoir-process__list {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--space-4);
  }

  .concevoir-process__item {
    grid-template-columns: 1fr;
    gap: var(--space-4);
    padding: var(--space-6);
  }

  /* On désactive le rail vertical mobile — les items sont sur deux rangées. */
  .concevoir-process__item + .concevoir-process__item::before {
    content: none;
  }
}

/* Desktop : 5 colonnes en ligne, avec un rail horizontal continu. */
@media (min-width: 1024px) {
  .concevoir-process__list {
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: var(--space-4);
    padding-top: var(--space-8);
  }

  /* Rail horizontal derrière les markers. */
  .concevoir-process__list::before {
    content: "";
    position: absolute;
    top: calc(var(--space-8) + var(--space-6) + 22px);
    left: 10%;
    right: 10%;
    height: 2px;
    background: linear-gradient(
      to right,
      transparent,
      rgba(46, 134, 217, 0.4) 15%,
      rgba(46, 134, 217, 0.4) 85%,
      transparent
    );
    z-index: 0;
  }

  .concevoir-process__item {
    z-index: 1;
    background-color: rgba(20, 30, 44, 0.9);
  }

  .concevoir-process__step-caption {
    max-width: none;
  }
}

@media (min-width: 1440px) {
  .concevoir-process {
    padding-block: var(--space-20) var(--space-20);
  }
}

@media (prefers-reduced-motion: reduce) {
  .concevoir-process__item {
    backdrop-filter: none;
  }
}
</style>
