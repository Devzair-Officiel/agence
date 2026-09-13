<script setup lang="ts">
import { computed } from "vue"
import type { ArticleSummary } from "~/types/editorial"
import { expertisePillars } from "~/config/expertise-pillars"
import { formatEditorialDate } from "~/utils/editorial-date"

/**
 * Carte d'une entrée dans la liste `/ressources`.
 *
 * Rend une balise `<article>` cliquable dans son intégralité via un
 * `NuxtLink` racine. Le lien inclut le titre, l'extrait et la date de
 * publication : le nom accessible est composé par la lecture naturelle
 * du contenu — pas d'`aria-label` redondant qui priverait les
 * utilisateurs de lecteur d'écran de l'extrait.
 *
 * Le titre est rendu en `<h3>` car la page liste porte déjà un `<h1>`
 * (bandeau intro) et un `<h2>` (section « Toutes les ressources »).
 *
 * L'excerpt provient du backend (`ArticleSummary.excerpt`) — jamais
 * généré côté front à partir du markdown pour éviter les divergences
 * (le backend applique la troncature et échappe les entités).
 */

interface Props {
  article: ArticleSummary
}

const props = defineProps<Props>()

const href = computed(() => `/ressources/${props.article.slug}`)

// La date lisible est calée sur `Europe/Paris` via `formatEditorialDate`
// pour garantir un rendu identique côté serveur (Docker, TZ système `UTC`)
// et côté client (navigateur). Voir `app/utils/editorial-date.ts`.
const publishedLabel = computed(() =>
  formatEditorialDate(props.article.publishedAt),
)

// Premier label d'expertise affiché en meta (si disponible).
const firstExpertiseLabel = computed(() => {
  const id = props.article.expertiseIds[0]
  if (!id) return null
  return expertisePillars.find((p) => p.id === id)?.label ?? null
})
</script>

<template>
  <article class="resource-list-item">
    <NuxtLink :to="href" class="resource-list-item__link">
      <!--
        Vignette (Phase 9B) : optionnelle — pas de placeholder. Lazy
        loading car les cartes hors viewport ne justifient pas de fetch
        prioritaire. L'overflow: hidden de la figure contient le zoom hover.
      -->
      <figure v-if="article.heroImage" class="resource-list-item__figure">
        <img
          class="resource-list-item__image"
          :src="article.heroImage.card?.url ?? article.heroImage.url"
          :alt="article.heroImage.alt"
          :width="article.heroImage.card?.width ?? article.heroImage.width"
          :height="article.heroImage.card?.height ?? article.heroImage.height"
          loading="lazy"
          decoding="async"
        >
      </figure>
      <p class="resource-list-item__meta">
        <span v-if="firstExpertiseLabel" class="resource-list-item__expertise">{{ firstExpertiseLabel }}</span>
        <span v-if="firstExpertiseLabel" class="resource-list-item__separator" aria-hidden="true">·</span>
        <time
          class="resource-list-item__date"
          :datetime="article.publishedAt"
        >{{ publishedLabel }}</time>
      </p>
      <h3 class="resource-list-item__title">{{ article.title }}</h3>
      <p class="resource-list-item__excerpt">{{ article.excerpt }}</p>
      <p class="resource-list-item__cta" aria-hidden="true">
        Lire la ressource →
      </p>
    </NuxtLink>
  </article>
</template>

<style scoped>
.resource-list-item {
  display: flex;
  height: 100%;
}

.resource-list-item__link {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  width: 100%;
  height: 100%;
  padding: var(--space-3);
  background-color: var(--background-secondary);
  border-radius: var(--radius-md);
  /*
   * Ombre micro au repos pour délimiter la carte sur fond sable.
   * Hover : légère élévation, sans translateY (layout stable).
   */
  box-shadow: 0 1px 6px rgba(22, 25, 28, 0.07);
  color: var(--text-primary);
  text-decoration: none;
  transition:
    box-shadow var(--duration-fast) var(--ease-out),
    background-color var(--duration-fast) var(--ease-out);
}

.resource-list-item__link:hover,
.resource-list-item__link:focus-visible {
  box-shadow: 0 4px 16px -4px rgba(22, 25, 28, 0.13);
  background-color: var(--color-cream-elevated);
}

/* ── Vignette ────────────────────────────────────────────────────── */

.resource-list-item__figure {
  /* Marges négatives = l'image déborde jusqu'aux bords de la carte */
  margin: calc(-1 * var(--space-3)) calc(-1 * var(--space-3)) 0;
  overflow: hidden;
  /* Coins hauts = radius de la carte ; coins bas = 0 (jonction avec le texte) */
  border-radius: var(--radius-md) var(--radius-md) 0 0;
  aspect-ratio: 16 / 9;
  background-color: var(--background-primary);
  flex-shrink: 0;
}

.resource-list-item__image {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 400ms var(--ease-out);
}

.resource-list-item__link:hover .resource-list-item__image,
.resource-list-item__link:focus-visible .resource-list-item__image {
  transform: scale(1.04);
}

/* ── Métadonnées ──────────────────────────────────────────────────── */

.resource-list-item__meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: var(--space-2);
  margin: 0;
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text-muted);
}

.resource-list-item__expertise {
  color: var(--color-petrol);
}

.resource-list-item__separator {
  color: var(--border-default);
}

/* ── Titre + excerpt ──────────────────────────────────────────────── */

.resource-list-item__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.375rem;
  line-height: 1.2;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  transition: color var(--duration-fast) var(--ease-out);
}

.resource-list-item__link:hover .resource-list-item__title,
.resource-list-item__link:focus-visible .resource-list-item__title {
  color: var(--color-petrol);
}

.resource-list-item__excerpt {
  /* flex-grow pousse le CTA vers le bas quelle que soit la hauteur du titre */
  flex-grow: 1;
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
}

.resource-list-item__cta {
  margin: 0;
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--color-petrol);
}

@media (prefers-reduced-motion: reduce) {
  .resource-list-item__link {
    transition: none;
  }

  .resource-list-item__image {
    transition: none;
  }

  .resource-list-item__link:hover .resource-list-item__image,
  .resource-list-item__link:focus-visible .resource-list-item__image {
    transform: none;
  }

  .resource-list-item__title {
    transition: none;
  }
}
</style>
