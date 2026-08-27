<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import { expertisePages } from "~/config/expertise-pages"

/**
 * « Aller plus loin » — représentation en cycle de production sur la page
 * Construire.
 *
 * Le pôle Construire est central dans le cycle : Concevoir (en amont) →
 * Construire (courant) → Faire évoluer (en aval). Le composant matérialise
 * cette continuité par une visualisation trois-nœuds avec un centre actif,
 * plutôt qu'une paire de cartes cousines.
 *
 * Contrat : `pillarIds` reste la source de vérité (fourni par la page). Les
 * deux pôles reçus sont rendus comme liens cliquables ; le pôle courant
 * (Construire) est affiché en centre non-cliquable.
 *
 * Sémantique :
 *   - `<nav aria-labelledby>` pour signaler un bloc de navigation ;
 *   - le nœud central est un simple `<div>` (pas d'ancre, pas d'auto-lien) ;
 *   - les deux nœuds latéraux sont des `<NuxtLink>` avec `aria-label`
 *     explicite pour les lecteurs d'écran.
 */

interface Props {
  pillarIds: readonly string[]
}

const props = defineProps<Props>()

const relatedPages = computed(() =>
  props.pillarIds
    .map((id) => expertisePages.find((p) => p.pillarId === id))
    .filter((p): p is (typeof expertisePages)[number] => Boolean(p))
    .filter((p) => p.status === "published"),
)

const generatedId = useId()
const titleId = computed(() => `construire-related-title-${generatedId}`)
</script>

<template>
  <section
    class="construire-related"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="construire-related__container">
      <header class="construire-related__header">
        <BaseEyebrow class="construire-related__eyebrow">
          Aller plus loin
        </BaseEyebrow>
        <h2 :id="titleId" class="construire-related__title">
          Le pôle Construire au cœur du cycle.
        </h2>
        <p class="construire-related__lead">
          Construire prend appui sur la conception en amont et se prolonge
          par le suivi dans le temps. Deux pôles à parcourir en complément.
        </p>
      </header>

      <nav
        v-if="relatedPages.length > 0"
        class="construire-related__cycle"
        :aria-label="'Pôles connexes à Construire'"
      >
        <NuxtLink
          v-if="relatedPages[0]"
          :to="relatedPages[0].route"
          class="construire-related__node construire-related__node--side"
        >
          <p class="construire-related__node-eyebrow">Amont</p>
          <h3 class="construire-related__node-title">
            {{ relatedPages[0].shortTitle }}
          </h3>
          <p class="construire-related__node-summary">
            {{ relatedPages[0].summary }}
          </p>
          <p class="construire-related__node-cta" aria-hidden="true">
            Découvrir ce pôle →
          </p>
        </NuxtLink>

        <div
          class="construire-related__node construire-related__node--center"
          aria-hidden="true"
        >
          <p class="construire-related__node-eyebrow">Vous êtes ici</p>
          <h3 class="construire-related__node-title">Construire</h3>
          <p class="construire-related__node-summary">
            Sites, e-commerce, applications métier.
          </p>
        </div>

        <NuxtLink
          v-if="relatedPages[1]"
          :to="relatedPages[1].route"
          class="construire-related__node construire-related__node--side"
        >
          <p class="construire-related__node-eyebrow">Aval</p>
          <h3 class="construire-related__node-title">
            {{ relatedPages[1].shortTitle }}
          </h3>
          <p class="construire-related__node-summary">
            {{ relatedPages[1].summary }}
          </p>
          <p class="construire-related__node-cta" aria-hidden="true">
            Découvrir ce pôle →
          </p>
        </NuxtLink>
      </nav>
    </BaseContainer>
  </section>
</template>

<style scoped>
.construire-related {
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-14);
}

.construire-related__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.construire-related__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 46rem;
}

.construire-related__eyebrow {
  margin: 0;
}

.construire-related__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.625rem, 3.4vw, 2.25rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
}

.construire-related__lead {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

.construire-related__cycle {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
  align-items: stretch;
}

.construire-related__node {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding: var(--space-6);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  background-color: var(--color-cream-elevated);
  color: var(--text-primary);
  text-decoration: none;
  overflow: hidden;
  transition:
    border-color var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out);
}

.construire-related__node--side::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 6px;
  height: 100%;
  background-color: var(--color-devzair-blue);
}

.construire-related__node--side {
  padding-left: calc(var(--space-6) + 6px);
}

.construire-related__node--side:hover,
.construire-related__node--side:focus-visible {
  border-color: var(--color-devzair-blue);
  transform: translateY(-2px);
}

.construire-related__node--center {
  background-color: var(--color-navy);
  color: var(--text-inverse);
  border-color: var(--color-navy);
  cursor: default;
}

.construire-related__node-eyebrow {
  font-family: var(--font-family-mono);
  font-weight: 700;
  font-size: 0.6875rem;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
}

.construire-related__node--center .construire-related__node-eyebrow {
  color: var(--color-devzair-blue);
}

.construire-related__node-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.25rem;
  line-height: 1.2;
  color: var(--text-primary);
  margin: 0;
}

.construire-related__node--center .construire-related__node-title {
  color: var(--color-cream);
}

.construire-related__node-summary {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
}

.construire-related__node--center .construire-related__node-summary {
  color: var(--text-inverse-muted);
}

.construire-related__node-cta {
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--color-petrol);
  margin: var(--space-2) 0 0;
}

@media (min-width: 900px) {
  .construire-related__cycle {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    align-items: center;
    gap: var(--space-5);
    position: relative;
  }

  /* Fil qui relie les trois nœuds (rail horizontal derrière). */
  .construire-related__cycle::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 8%;
    right: 8%;
    height: 2px;
    background: linear-gradient(
      to right,
      rgba(46, 134, 217, 0.6) 0%,
      rgba(46, 134, 217, 0.6) 50%,
      rgba(12, 91, 87, 0.6) 100%
    );
    z-index: 0;
    pointer-events: none;
  }

  .construire-related__node {
    z-index: 1;
  }

  .construire-related__node--center {
    transform: scale(1.04);
  }
}

@media (min-width: 1024px) {
  .construire-related {
    padding-block: var(--space-16) var(--space-16);
  }
}

@media (prefers-reduced-motion: reduce) {
  .construire-related__node {
    transition: none;
  }

  .construire-related__node--side:hover,
  .construire-related__node--side:focus-visible {
    transform: none;
  }

  .construire-related__node--center {
    transform: none;
  }
}
</style>
