<script setup lang="ts">
import { computed } from "vue"
import type { ArticleSummary } from "~/types/editorial"

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

const DATE_FORMATTER = new Intl.DateTimeFormat("fr-FR", {
  day: "numeric",
  month: "long",
  year: "numeric",
})

const href = computed(() => `/ressources/${props.article.slug}`)

const publishedLabel = computed(() => {
  const date = new Date(props.article.publishedAt)
  if (Number.isNaN(date.getTime())) return props.article.publishedAt
  return DATE_FORMATTER.format(date)
})
</script>

<template>
  <article class="resource-list-item">
    <NuxtLink :to="href" class="resource-list-item__link">
      <p class="resource-list-item__meta">
        <span class="resource-list-item__author">{{ article.author.name }}</span>
        <span class="resource-list-item__separator" aria-hidden="true">·</span>
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
  padding: var(--space-6);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  background-color: var(--background-primary);
  color: var(--text-primary);
  text-decoration: none;
  transition: border-color var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out);
}

.resource-list-item__link:hover,
.resource-list-item__link:focus-visible {
  border-color: var(--color-devzair-blue);
  transform: translateY(-2px);
}

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

.resource-list-item__separator {
  color: var(--border-default);
}

.resource-list-item__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.375rem;
  line-height: 1.2;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
}

.resource-list-item__excerpt {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
}

.resource-list-item__cta {
  margin: var(--space-2) 0 0;
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--color-petrol);
}

@media (prefers-reduced-motion: reduce) {
  .resource-list-item__link {
    transition: none;
  }

  .resource-list-item__link:hover,
  .resource-list-item__link:focus-visible {
    transform: none;
  }
}
</style>
