<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import { expertisePages } from "~/config/expertise-pages"

/**
 * « Aller plus loin » — placement de Faire évoluer comme confluent de
 * deux flux amont : CONSTRUIRE (le socle technique livré) et VISIBILITÉ
 * (la présence dans la durée). Faire évoluer prend le relais des deux.
 *
 * Contrairement à Valoriser / Visibilité (flux amont → ici → aval), la
 * page Faire évoluer est un point terminal : elle n'a pas de pôle aval.
 * On rend donc deux pôles latéraux qui pointent tous deux vers le centre.
 *
 * Le centre est un nœud navy-elevated avec cercle petrol — même
 * traitement visuel que Visibilité — pour signaler « vous êtes ici ».
 *
 * Contrat : `pillarIds` est la source de vérité. L'ordre narratif est
 * fixé par `NARRATIVE_ORDER` (Construire puis Visibilité), le composant
 * réordonne indépendamment de l'ordre dans la config.
 */

interface Props {
  pillarIds: readonly string[]
}

const props = defineProps<Props>()

const NARRATIVE_ORDER = ["construire", "visibilite"] as const

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
const titleId = computed(() => `faire-evoluer-related-title-${generatedId}`)
</script>

<template>
  <section
    class="faire-evoluer-related"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="faire-evoluer-related__container">
      <header class="faire-evoluer-related__header">
        <BaseEyebrow class="faire-evoluer-related__eyebrow">
          Aller plus loin
        </BaseEyebrow>
        <h2 :id="titleId" class="faire-evoluer-related__title">
          Faire évoluer prend le relais de Construire et de Visibilité.
        </h2>
        <p class="faire-evoluer-related__lead">
          Un site est livré (Construire), il est rendu visible dans la durée
          (Visibilité). Faire évoluer entretient ce socle, mesure son usage
          réel et l'ajuste au fil de l'activité.
        </p>
      </header>

      <nav
        v-if="relatedPages.length > 0"
        class="faire-evoluer-related__flow"
        :aria-label="'Pôles connexes à Faire évoluer'"
      >
        <NuxtLink
          v-if="relatedPages[0]"
          :to="relatedPages[0].route"
          class="faire-evoluer-related__node faire-evoluer-related__node--side"
        >
          <p class="faire-evoluer-related__node-eyebrow">Amont</p>
          <h3 class="faire-evoluer-related__node-title">
            {{ relatedPages[0].shortTitle }}
          </h3>
          <p class="faire-evoluer-related__node-summary">
            {{ relatedPages[0].summary }}
          </p>
          <p class="faire-evoluer-related__node-cta" aria-hidden="true">
            Découvrir ce pôle →
          </p>
        </NuxtLink>

        <div
          class="faire-evoluer-related__node faire-evoluer-related__node--center"
          aria-hidden="true"
        >
          <p class="faire-evoluer-related__node-eyebrow">Vous êtes ici</p>
          <h3 class="faire-evoluer-related__node-title">Faire évoluer</h3>
          <p class="faire-evoluer-related__node-summary">
            Maintenance, mesure, correctifs et évolutions dans la durée.
          </p>
        </div>

        <NuxtLink
          v-if="relatedPages[1]"
          :to="relatedPages[1].route"
          class="faire-evoluer-related__node faire-evoluer-related__node--side"
        >
          <p class="faire-evoluer-related__node-eyebrow">Amont</p>
          <h3 class="faire-evoluer-related__node-title">
            {{ relatedPages[1].shortTitle }}
          </h3>
          <p class="faire-evoluer-related__node-summary">
            {{ relatedPages[1].summary }}
          </p>
          <p class="faire-evoluer-related__node-cta" aria-hidden="true">
            Découvrir ce pôle →
          </p>
        </NuxtLink>
      </nav>
    </BaseContainer>
  </section>
</template>

<style scoped>
.faire-evoluer-related {
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-14);
}

.faire-evoluer-related__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.faire-evoluer-related__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 52rem;
}

.faire-evoluer-related__eyebrow {
  margin: 0;
}

.faire-evoluer-related__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.625rem, 3.4vw, 2.25rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 30ch;
}

.faire-evoluer-related__lead {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

.faire-evoluer-related__flow {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
}

.faire-evoluer-related__node {
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

.faire-evoluer-related__node--side {
  border-left: 4px solid var(--color-petrol);
  padding-left: calc(var(--space-6) + 4px);
}

.faire-evoluer-related__node--side:hover,
.faire-evoluer-related__node--side:focus-visible {
  border-color: var(--color-petrol);
  transform: translateY(-2px);
}

/*
 * Centre navy-elevated cerclé de petrol — même approche que Visibilité :
 * un ancrage sombre dans un plan clair. La différence structurelle vit
 * dans le flux (deux amonts au lieu d'un amont + un aval).
 */
.faire-evoluer-related__node--center {
  background-color: var(--color-navy-elevated);
  color: var(--color-cream);
  border-color: var(--color-petrol);
  border-width: 2px;
  cursor: default;
}

.faire-evoluer-related__node-eyebrow {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
}

/*
 * BaseEyebrow tone=inverse #2e86d9 échouerait le contraste sur
 * navy-elevated ; on reprend #5aa0e6 utilisé partout pour ce fond.
 */
.faire-evoluer-related__node--center .faire-evoluer-related__node-eyebrow {
  color: #5aa0e6;
}

.faire-evoluer-related__node-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.25rem;
  line-height: 1.2;
  color: var(--text-primary);
  margin: 0;
}

.faire-evoluer-related__node--center .faire-evoluer-related__node-title {
  color: var(--color-cream);
}

.faire-evoluer-related__node-summary {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
}

.faire-evoluer-related__node--center .faire-evoluer-related__node-summary {
  color: var(--text-inverse-muted);
}

.faire-evoluer-related__node-cta {
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  font-weight: var(--font-weight-body-strong);
  color: var(--color-petrol);
  margin: var(--space-2) 0 0;
}

@media (min-width: 900px) {
  .faire-evoluer-related__flow {
    grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr) minmax(0, 1fr);
    align-items: center;
    gap: var(--space-5);
    position: relative;
  }

  /*
   * Deux flèches convergentes : le fil part des deux pôles latéraux vers
   * le centre. On matérialise ça avec deux gradients qui s'atténuent au
   * bord opposé, plutôt qu'une ligne unique.
   */
  .faire-evoluer-related__flow::before,
  .faire-evoluer-related__flow::after {
    content: "";
    position: absolute;
    top: 50%;
    height: 1px;
    z-index: 0;
    pointer-events: none;
  }

  .faire-evoluer-related__flow::before {
    left: 6%;
    right: 55%;
    background: linear-gradient(
      to right,
      rgba(12, 91, 87, 0.35) 0%,
      rgba(12, 91, 87, 0.75) 100%
    );
  }

  .faire-evoluer-related__flow::after {
    left: 55%;
    right: 6%;
    background: linear-gradient(
      to left,
      rgba(12, 91, 87, 0.35) 0%,
      rgba(12, 91, 87, 0.75) 100%
    );
  }

  .faire-evoluer-related__node {
    z-index: 1;
  }
}

@media (min-width: 1024px) {
  .faire-evoluer-related {
    padding-block: var(--space-16) var(--space-16);
  }
}

@media (prefers-reduced-motion: reduce) {
  .faire-evoluer-related__node {
    transition: none;
  }
  .faire-evoluer-related__node--side:hover,
  .faire-evoluer-related__node--side:focus-visible {
    transform: none;
  }
}
</style>
