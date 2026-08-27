<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import { expertisePages } from "~/config/expertise-pages"

/**
 * « Aller plus loin » — placement de Valoriser au cœur d'un flux
 * CONCEVOIR → VALORISER → VISIBILITÉ.
 *
 * Direction éditoriale distincte de `ConstruireRelatedPillars` :
 *   - trois blocs typographiques enchaînés en ligne éditoriale (mono
 *     séparateurs), pas trois cartes égales avec liseré ;
 *   - le pôle central (Valoriser) reçoit une bande d'accent petrol qui
 *     signale « vous êtes ici » ;
 *   - les deux pôles latéraux sont des `<NuxtLink>` cliquables ;
 *   - version desktop : composition en ligne avec flèches éditoriales.
 *
 * Contrat : `pillarIds` reste la source de vérité fournie par la page.
 * L'ordre narratif du flux Valoriser étant FIXE (amont Concevoir → aval
 * Visibilité), le composant réordonne selon la position dans la séquence
 * `NARRATIVE_ORDER` — la config `relatedPillarIds` peut être déclarée dans
 * n'importe quel ordre sans casser l'orientation Amont / Aval.
 */

interface Props {
  pillarIds: readonly string[]
}

const props = defineProps<Props>()

// Séquence éditoriale imposée : Concevoir précède Valoriser, Visibilité
// vient ensuite. Utilisée pour rendre les pôles reçus dans l'ordre correct
// même si la config déclare l'inverse.
const NARRATIVE_ORDER = ["concevoir", "visibilite"] as const

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
const titleId = computed(() => `valoriser-related-title-${generatedId}`)
</script>

<template>
  <section
    class="valoriser-related"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="valoriser-related__container">
      <header class="valoriser-related__header">
        <BaseEyebrow class="valoriser-related__eyebrow">
          Aller plus loin
        </BaseEyebrow>
        <h2 :id="titleId" class="valoriser-related__title">
          Valoriser prend sens dans un mouvement plus large.
        </h2>
        <p class="valoriser-related__lead">
          On définit d'abord ce que l'on veut dire (Concevoir), on produit
          la matière visuelle et éditoriale (Valoriser), puis on la rend
          visible (Visibilité). Trois pôles enchaînés.
        </p>
      </header>

      <nav
        v-if="relatedPages.length > 0"
        class="valoriser-related__flow"
        :aria-label="'Pôles connexes à Valoriser'"
      >
        <NuxtLink
          v-if="relatedPages[0]"
          :to="relatedPages[0].route"
          class="valoriser-related__node valoriser-related__node--side"
        >
          <p class="valoriser-related__node-eyebrow">Amont</p>
          <h3 class="valoriser-related__node-title">
            {{ relatedPages[0].shortTitle }}
          </h3>
          <p class="valoriser-related__node-summary">
            {{ relatedPages[0].summary }}
          </p>
          <p class="valoriser-related__node-cta" aria-hidden="true">
            Découvrir ce pôle →
          </p>
        </NuxtLink>

        <div
          class="valoriser-related__node valoriser-related__node--center"
          aria-hidden="true"
        >
          <p class="valoriser-related__node-eyebrow">Vous êtes ici</p>
          <h3 class="valoriser-related__node-title">Valoriser</h3>
          <p class="valoriser-related__node-summary">
            Photographie, contenus rédactionnels, structuration.
          </p>
        </div>

        <NuxtLink
          v-if="relatedPages[1]"
          :to="relatedPages[1].route"
          class="valoriser-related__node valoriser-related__node--side"
        >
          <p class="valoriser-related__node-eyebrow">Aval</p>
          <h3 class="valoriser-related__node-title">
            {{ relatedPages[1].shortTitle }}
          </h3>
          <p class="valoriser-related__node-summary">
            {{ relatedPages[1].summary }}
          </p>
          <p class="valoriser-related__node-cta" aria-hidden="true">
            Découvrir ce pôle →
          </p>
        </NuxtLink>
      </nav>
    </BaseContainer>
  </section>
</template>

<style scoped>
.valoriser-related {
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-14);
}

.valoriser-related__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.valoriser-related__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 52rem;
}

.valoriser-related__eyebrow {
  margin: 0;
}

.valoriser-related__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.625rem, 3.4vw, 2.25rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 26ch;
}

.valoriser-related__lead {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

/*
 * Mobile / tablette : pile verticale. Le centre est indenté à droite
 * pour évoquer un décalage éditorial (pas un simple empilement centré).
 */
.valoriser-related__flow {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
}

.valoriser-related__node {
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

.valoriser-related__node--side {
  border-left: 4px solid var(--color-petrol);
  padding-left: calc(var(--space-6) + 4px);
}

.valoriser-related__node--side:hover,
.valoriser-related__node--side:focus-visible {
  border-color: var(--color-petrol);
  transform: translateY(-2px);
}

/*
 * Nœud central « Valoriser (vous êtes ici) » — bande petrol dominante et
 * intérieur cream très clair. Différent du centre navy de Construire.
 */
.valoriser-related__node--center {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border-color: var(--color-petrol);
  cursor: default;
}

.valoriser-related__node-eyebrow {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.10em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
}

.valoriser-related__node--center .valoriser-related__node-eyebrow {
  color: var(--color-cream);
  opacity: 0.85;
}

.valoriser-related__node-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.25rem;
  line-height: 1.2;
  color: var(--text-primary);
  margin: 0;
}

.valoriser-related__node--center .valoriser-related__node-title {
  color: var(--color-cream);
}

.valoriser-related__node-summary {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
}

.valoriser-related__node--center .valoriser-related__node-summary {
  color: var(--color-cream);
  opacity: 0.9;
}

.valoriser-related__node-cta {
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  font-weight: var(--font-weight-body-strong);
  color: var(--color-petrol);
  margin: var(--space-2) 0 0;
}

/*
 * Desktop : composition trois blocs en ligne, avec un fil éditorial qui
 * traverse les trois nœuds. Le centre est agrandi et se distingue par le
 * fond petrol.
 */
@media (min-width: 900px) {
  .valoriser-related__flow {
    grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr) minmax(0, 1fr);
    align-items: center;
    gap: var(--space-5);
    position: relative;
  }

  /* Fil relie les trois nœuds. */
  .valoriser-related__flow::before {
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

  .valoriser-related__node {
    z-index: 1;
  }
}

@media (min-width: 1024px) {
  .valoriser-related {
    padding-block: var(--space-16) var(--space-16);
  }
}

@media (prefers-reduced-motion: reduce) {
  .valoriser-related__node {
    transition: none;
  }
  .valoriser-related__node--side:hover,
  .valoriser-related__node--side:focus-visible {
    transform: none;
  }
}
</style>
