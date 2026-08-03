<script setup lang="ts">
import type { ExpertisePillar } from "~/config/expertise-pillars"
import type { ExpertisePageDefinition } from "~/config/expertise-pages"

/**
 * Carte d'aperçu pour la page `/expertises` — présentation « fiche » d'un
 * pôle. **Distincte** de `HomeExpertisePillars` par la sémantique et la
 * mise en page :
 *   - `HomeExpertisePillars` construit une **grille asymétrique** dense sur
 *     l'accueil, orientée densité d'information ;
 *   - `ExpertiseOverviewCard` construit une **fiche uniforme** en 5 cartes
 *     alignées, orientée lisibilité et cadrage éditorial.
 *
 * Comportement de lien (Phase 7B) :
 *   - si une définition `page` est fournie et que `page.status === "published"`,
 *     la carte est rendue comme un `<NuxtLink>` cliquable pointant vers
 *     `page.route` — l'article devient interactif dans son intégralité ;
 *   - sinon, la carte reste un `<article>` narratif sans zone interactive
 *     (comportement Phase 7A, préservé pour tout pôle qui n'aurait pas
 *     encore de page dédiée publiée).
 *
 * Accessibilité :
 *   - le titre est rendu en H3 (le page `/expertises` porte le H2 unique
 *     de la section « Cinq pôles complémentaires… ») ;
 *   - le badge d'ordre (« 01 »…« 05 ») est décoratif (`aria-hidden`) ;
 *   - quand la carte est un lien, le contenu textuel du H3 sert de nom
 *     accessible — pas d'`aria-label` redondant.
 */

interface Props {
  pillar: ExpertisePillar
  /** Définition de page correspondante ; rend la carte cliquable si publiée. */
  page?: ExpertisePageDefinition
}

const props = defineProps<Props>()

const isPublished = (): boolean =>
  Boolean(props.page && props.page.status === "published")
</script>

<template>
  <NuxtLink
    v-if="isPublished()"
    :to="page!.route"
    class="expertise-overview-card expertise-overview-card--interactive"
    :data-variant="pillar.variant"
  >
    <p class="expertise-overview-card__order" aria-hidden="true">
      {{ String(pillar.order).padStart(2, "0") }}
    </p>
    <h3 class="expertise-overview-card__title">{{ pillar.label }}</h3>
    <p class="expertise-overview-card__description">
      {{ pillar.longDescription }}
    </p>
    <p class="expertise-overview-card__services-label">Prestations associées</p>
    <ul class="expertise-overview-card__services" role="list">
      <li
        v-for="service in pillar.services"
        :key="service"
        class="expertise-overview-card__service"
      >
        {{ service }}
      </li>
    </ul>
    <p class="expertise-overview-card__cta" aria-hidden="true">
      Découvrir ce pôle →
    </p>
  </NuxtLink>
  <article
    v-else
    class="expertise-overview-card"
    :data-variant="pillar.variant"
  >
    <p class="expertise-overview-card__order" aria-hidden="true">
      {{ String(pillar.order).padStart(2, "0") }}
    </p>
    <h3 class="expertise-overview-card__title">{{ pillar.label }}</h3>
    <p class="expertise-overview-card__description">
      {{ pillar.longDescription }}
    </p>
    <p class="expertise-overview-card__services-label">Prestations associées</p>
    <ul class="expertise-overview-card__services" role="list">
      <li
        v-for="service in pillar.services"
        :key="service"
        class="expertise-overview-card__service"
      >
        {{ service }}
      </li>
    </ul>
  </article>
</template>

<style scoped>
.expertise-overview-card {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-6);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  background-color: var(--background-primary);
  color: var(--text-primary);
  height: 100%;
  text-decoration: none;
}

.expertise-overview-card--interactive {
  transition: border-color 120ms ease, transform 120ms ease;
}

.expertise-overview-card--interactive:hover,
.expertise-overview-card--interactive:focus-visible {
  border-color: var(--color-devzair-blue);
  transform: translateY(-2px);
}

.expertise-overview-card[data-variant="primary"] {
  border-color: var(--color-devzair-blue);
}

.expertise-overview-card[data-variant="accent"] {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border-color: var(--color-petrol);
}

.expertise-overview-card__order {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.06em;
  color: var(--text-accent);
  margin: 0;
}

.expertise-overview-card[data-variant="accent"] .expertise-overview-card__order {
  color: var(--color-cream);
}

.expertise-overview-card__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.375rem;
  line-height: 1.2;
  letter-spacing: -0.01em;
  color: inherit;
  margin: 0;
}

.expertise-overview-card__description {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
}

.expertise-overview-card[data-variant="accent"] .expertise-overview-card__description {
  color: var(--color-cream-muted);
}

.expertise-overview-card__services-label {
  margin: var(--space-2) 0 0;
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text-muted);
}

.expertise-overview-card[data-variant="accent"] .expertise-overview-card__services-label {
  color: var(--color-cream-muted);
}

.expertise-overview-card__services {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.expertise-overview-card__service {
  position: relative;
  padding-left: var(--space-4);
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.5;
  color: inherit;
}

.expertise-overview-card__service::before {
  content: "";
  position: absolute;
  left: 0;
  top: 0.55em;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: var(--color-devzair-blue);
}

.expertise-overview-card[data-variant="accent"] .expertise-overview-card__service::before {
  background-color: var(--color-cream);
}

.expertise-overview-card__cta {
  margin: var(--space-3) 0 0;
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  font-weight: 700;
  /* Petrol garantit un contraste ≥ 6:1 sur cream et sur sand (WCAG AA
     texte normal). Le devzair-blue tomberait sous 4.5:1 sur ces fonds. */
  color: var(--color-petrol);
}

.expertise-overview-card[data-variant="accent"] .expertise-overview-card__cta {
  color: var(--color-cream);
}

@media (prefers-reduced-motion: reduce) {
  .expertise-overview-card--interactive {
    transition: none;
  }

  .expertise-overview-card--interactive:hover,
  .expertise-overview-card--interactive:focus-visible {
    transform: none;
  }
}
</style>
