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
