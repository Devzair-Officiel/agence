<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import { expertisePages } from "~/config/expertise-pages"

/**
 * « Aller plus loin » — placement de Visibilité dans le flux
 * VALORISER → VISIBILITÉ → FAIRE ÉVOLUER.
 *
 * Direction éditoriale distincte de Valoriser (fond cream avec nœud
 * central petrol) : Visibilité utilise un centre navy-elevated cerclé
 * de petrol (« pôle actif »), et les deux pôles latéraux gardent la
 * bande d'accent petrol côté gauche.
 *
 * Contrat : `pillarIds` reste la source de vérité fournie par la page.
 * L'ordre narratif du flux Visibilité étant FIXE (amont Valoriser → aval
 * Faire évoluer), le composant réordonne selon `NARRATIVE_ORDER` — la
 * config `relatedPillarIds` peut être déclarée dans n'importe quel ordre
 * sans casser l'orientation Amont / Aval.
 */

interface Props {
  pillarIds: readonly string[]
}

const props = defineProps<Props>()

const NARRATIVE_ORDER = ["valoriser", "faire-evoluer"] as const

const relatedPages = computed(() => {
  const found = props.pillarIds
    .map((id) => expertisePages.find((p) => p.pillarId === id))
    .filter((p): p is (typeof expertisePages)[number] => Boolean(p))
    .filter((p) => p.status === "published")
  return [...found].sort((a, b) => {
    const ai = NARRATIVE_ORDER.indexOf(a.pillarId as (typeof NARRATIVE_ORDER)[number])
    const bi = NARRATIVE_ORDER.indexOf(b.pillarId as (typeof NARRATIVE_ORDER)[number])
    const aRank = ai === -1 ? Number.MAX_SAFE_INTEGER : ai
    const bRank = bi === -1 ? Number.MAX_SAFE_INTEGER : bi
    return aRank - bRank
  })
})

const generatedId = useId()
const titleId = computed(() => `visibilite-related-title-${generatedId}`)
</script>

<template>
  <section
    class="visibilite-related"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="visibilite-related__container">
      <header class="visibilite-related__header">
        <BaseEyebrow class="visibilite-related__eyebrow">
          Aller plus loin
        </BaseEyebrow>
        <h2 :id="titleId" class="visibilite-related__title">
          Visibilité s'inscrit dans un mouvement plus large.
        </h2>
        <p class="visibilite-related__lead">
          On produit d'abord la matière visuelle et éditoriale (Valoriser),
          on la rend visible dans la durée (Visibilité), puis on suit,
          mesure et fait évoluer (Faire évoluer). Trois pôles enchaînés.
        </p>
      </header>

      <nav
        v-if="relatedPages.length > 0"
        class="visibilite-related__flow"
        :aria-label="'Pôles connexes à Visibilité'"
      >
        <NuxtLink
          v-if="relatedPages[0]"
          :to="relatedPages[0].route"
          class="visibilite-related__node visibilite-related__node--side"
        >
          <p class="visibilite-related__node-eyebrow">Amont</p>
          <h3 class="visibilite-related__node-title">
            {{ relatedPages[0].shortTitle }}
          </h3>
          <p class="visibilite-related__node-summary">
            {{ relatedPages[0].summary }}
          </p>
          <p class="visibilite-related__node-cta" aria-hidden="true">
            Découvrir ce pôle →
          </p>
        </NuxtLink>

        <div
          class="visibilite-related__node visibilite-related__node--center"
          aria-hidden="true"
        >
          <p class="visibilite-related__node-eyebrow">Vous êtes ici</p>
          <h3 class="visibilite-related__node-title">Visibilité</h3>
          <p class="visibilite-related__node-summary">
            Référencement naturel, présence locale, stratégie éditoriale.
          </p>
        </div>

        <NuxtLink
          v-if="relatedPages[1]"
          :to="relatedPages[1].route"
          class="visibilite-related__node visibilite-related__node--side"
        >
          <p class="visibilite-related__node-eyebrow">Aval</p>
          <h3 class="visibilite-related__node-title">
            {{ relatedPages[1].shortTitle }}
          </h3>
          <p class="visibilite-related__node-summary">
            {{ relatedPages[1].summary }}
          </p>
          <p class="visibilite-related__node-cta" aria-hidden="true">
            Découvrir ce pôle →
          </p>
        </NuxtLink>
      </nav>
    </BaseContainer>
  </section>
</template>

<style scoped>
.visibilite-related {
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-14);
}

.visibilite-related__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.visibilite-related__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 52rem;
}

.visibilite-related__eyebrow {
  margin: 0;
}

.visibilite-related__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.625rem, 3.4vw, 2.25rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 28ch;
}

.visibilite-related__lead {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

.visibilite-related__flow {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
}

.visibilite-related__node {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding: var(--space-6);
  background-color: var(--color-cream-elevated);
  border: 1px solid var(--border-default);
  color: var(--text-primary);
  text-decoration: none;
  overflow: hidden;
  transition:
    border-color var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out);
}

.visibilite-related__node--side {
  border-left: 4px solid var(--color-petrol);
  padding-left: calc(var(--space-6) + 4px);
}

.visibilite-related__node--side:hover,
.visibilite-related__node--side:focus-visible {
  border-color: var(--color-petrol);
  transform: translateY(-2px);
}

/*
 * Centre navy-elevated cerclé de petrol — distinct du centre petrol de
 * Valoriser. Signale « vous êtes ici » comme un point d'ancrage sombre
 * dans un plan clair.
 */
.visibilite-related__node--center {
  background-color: var(--color-navy-elevated);
  color: var(--color-cream);
  border-color: var(--color-petrol);
  border-width: 2px;
  cursor: default;
}

.visibilite-related__node-eyebrow {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
}

.visibilite-related__node--center .visibilite-related__node-eyebrow {
  color: #5aa0e6;
}

.visibilite-related__node-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.25rem;
  line-height: 1.2;
  color: var(--text-primary);
  margin: 0;
}

.visibilite-related__node--center .visibilite-related__node-title {
  color: var(--color-cream);
}

.visibilite-related__node-summary {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
}

.visibilite-related__node--center .visibilite-related__node-summary {
  color: var(--text-inverse-muted);
}

.visibilite-related__node-cta {
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  font-weight: var(--font-weight-body-strong);
  color: var(--color-petrol);
  margin: var(--space-2) 0 0;
}

@media (min-width: 900px) {
  .visibilite-related__flow {
    grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr) minmax(0, 1fr);
    align-items: center;
    gap: var(--space-5);
    position: relative;
  }

  .visibilite-related__flow::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 6%;
    right: 6%;
    height: 1px;
    background: linear-gradient(
      to right,
      rgba(12, 91, 87, 0.4) 0%,
      rgba(12, 91, 87, 0.7) 50%,
      rgba(12, 91, 87, 0.4) 100%
    );
    z-index: 0;
    pointer-events: none;
  }

  .visibilite-related__node {
    z-index: 1;
  }
}

@media (min-width: 1024px) {
  .visibilite-related {
    padding-block: var(--space-16) var(--space-16);
  }
}

@media (prefers-reduced-motion: reduce) {
  .visibilite-related__node {
    transition: none;
  }
  .visibilite-related__node--side:hover,
  .visibilite-related__node--side:focus-visible {
    transform: none;
  }
}
</style>
