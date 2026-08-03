<script setup lang="ts">
import type { ExpertisePillar } from "~/config/expertise-pillars"

/**
 * Carte d'aperçu pour la page `/expertises` — présentation « fiche » d'un
 * pôle. **Distincte** de `HomeExpertisePillars` par la sémantique et la
 * mise en page :
 *   - `HomeExpertisePillars` construit une **grille asymétrique** dense sur
 *     l'accueil, orientée densité d'information ;
 *   - `ExpertiseOverviewCard` construit une **fiche uniforme** en 5 cartes
 *     alignées, orientée lisibilité et cadrage éditorial.
 *
 * Aucun lien sortant : Phase 7A ne livre pas les pages `/expertises/{slug}`.
 * Le composant expose donc uniquement du contenu narratif, sans zone
 * interactive. Il deviendra un lien vers la page fille en Phase 7B, sous
 * condition que la définition correspondante dans `expertise-pages.ts` ait
 * `status === "published"`.
 *
 * Accessibilité :
 *   - la carte porte un H3 (la page `/expertises` porte le H2 unique de la
 *     section « Cinq pôles complémentaires… ») ;
 *   - le badge d'ordre (« 01 »…« 05 ») est décoratif (`aria-hidden`).
 */

interface Props {
  pillar: ExpertisePillar
}

defineProps<Props>()
</script>

<template>
  <article class="expertise-overview-card" :data-variant="pillar.variant">
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
</style>
