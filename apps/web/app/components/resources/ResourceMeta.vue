<script setup lang="ts">
import { computed } from "vue"
import type { ArticleAuthor } from "~/types/editorial"
import { expertisePillars } from "~/config/expertise-pillars"
import { expertisePages } from "~/config/expertise-pages"
import { formatEditorialDate } from "~/utils/editorial-date"

/**
 * Bandeau de métadonnées d'un article éditorial.
 *
 * Rôle strict : afficher auteur, dates (publication + mise à jour) et
 * puces d'expertises rattachées. Les puces d'expertise pointent vers la
 * page fille `/expertises/{slug}` **uniquement** si le pillar existe
 * ET si la page correspondante est publiée. Si l'`expertiseId` reçu par
 * l'API ne correspond à aucun pilier connu (contrat désynchronisé), il
 * est ignoré silencieusement — on n'invente pas de lien mort ni de
 * libellé fabriqué.
 *
 * `updatedAt` n'est affiché que s'il diffère de `publishedAt` : afficher
 * les deux dates identiques serait du bruit visuel sans information.
 *
 * Accessibilité :
 *   - conteneur `<dl>` (definition list) pour la relation label/valeur ;
 *   - `<time datetime="ISO">` pour chaque date, lisible par les
 *     assistances technologiques comme date machine ET humaine ;
 *   - la liste des puces est une `<ul>` avec `role="list"` (ne se
 *     dégrade pas silencieusement si `list-style: none` est appliqué).
 */

interface Props {
  author: ArticleAuthor
  publishedAt: string
  updatedAt: string
  expertiseIds: readonly string[]
}

const props = defineProps<Props>()

interface ExpertiseLink {
  readonly id: string
  readonly label: string
  readonly route: string | null
}

const publishedLabel = computed(() => formatEditorialDate(props.publishedAt))
const updatedLabel = computed(() => formatEditorialDate(props.updatedAt))
const showUpdated = computed(() => props.updatedAt !== props.publishedAt)

const expertiseLinks = computed<readonly ExpertiseLink[]>(() => {
  const pageByPillarId = new Map(expertisePages.map((page) => [page.pillarId, page]))
  const pillarById = new Map(expertisePillars.map((pillar) => [pillar.id, pillar]))
  const links: ExpertiseLink[] = []
  for (const id of props.expertiseIds) {
    const pillar = pillarById.get(id)
    if (!pillar) continue
    const page = pageByPillarId.get(id)
    const route =
      page && page.status === "published" ? page.route : null
    links.push({ id, label: pillar.label, route })
  }
  return links
})
</script>

<template>
  <dl class="resource-meta">
    <div class="resource-meta__row">
      <dt class="resource-meta__label">Par</dt>
      <dd class="resource-meta__value">{{ author.name }}</dd>
    </div>
    <div class="resource-meta__row">
      <dt class="resource-meta__label">Publié le</dt>
      <dd class="resource-meta__value">
        <time :datetime="publishedAt">{{ publishedLabel }}</time>
      </dd>
    </div>
    <div v-if="showUpdated" class="resource-meta__row">
      <dt class="resource-meta__label">Mis à jour le</dt>
      <dd class="resource-meta__value">
        <time :datetime="updatedAt">{{ updatedLabel }}</time>
      </dd>
    </div>
    <div v-if="expertiseLinks.length > 0" class="resource-meta__row resource-meta__row--full">
      <dt class="resource-meta__label">Expertises</dt>
      <dd class="resource-meta__value">
        <ul class="resource-meta__chips" role="list">
          <li
            v-for="link in expertiseLinks"
            :key="link.id"
            class="resource-meta__chip"
          >
            <NuxtLink
              v-if="link.route"
              :to="link.route"
              class="resource-meta__chip-link"
            >
              {{ link.label }}
            </NuxtLink>
            <span v-else class="resource-meta__chip-static">
              {{ link.label }}
            </span>
          </li>
        </ul>
      </dd>
    </div>
  </dl>
</template>

<style scoped>
/* Layout horizontal : auteur + dates en ligne, expertises en dessous */
.resource-meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: var(--space-1) var(--space-5);
  margin: 0;
  padding: 0;
}

.resource-meta__row {
  display: flex;
  align-items: baseline;
  gap: var(--space-2);
}

/* La ligne d'expertises prend toute la largeur */
.resource-meta__row--full {
  flex-basis: 100%;
  align-items: center;
  margin-top: var(--space-2);
}

.resource-meta__label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--text-muted);
  margin: 0;
  flex-shrink: 0;
}

.resource-meta__value {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.4;
  color: var(--text-secondary);
  margin: 0;
}

/* Séparateur visuel entre items (sauf premier) */
.resource-meta__row:not(.resource-meta__row--full) + .resource-meta__row:not(.resource-meta__row--full)::before {
  content: "·";
  color: var(--text-muted);
  margin-right: calc(-1 * var(--space-3));
  font-size: 1rem;
  line-height: 1;
  align-self: center;
}

.resource-meta__chips {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
}

.resource-meta__chip {
  display: inline-flex;
}

.resource-meta__chip-link,
.resource-meta__chip-static {
  display: inline-flex;
  align-items: center;
  padding: 0.2rem 0.75rem;
  border-radius: 999px;
  border: 1px solid var(--border-default);
  background-color: var(--background-secondary);
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  line-height: 1.4;
  color: var(--text-secondary);
}

.resource-meta__chip-link {
  text-decoration: none;
  transition: border-color var(--duration-fast) var(--ease-out),
    color var(--duration-fast) var(--ease-out),
    background-color var(--duration-fast) var(--ease-out);
}

.resource-meta__chip-link:hover,
.resource-meta__chip-link:focus-visible {
  border-color: var(--color-devzair-blue);
  color: var(--text-accent);
  background-color: color-mix(in srgb, var(--color-devzair-blue) 6%, var(--background-secondary));
}

@media (prefers-reduced-motion: reduce) {
  .resource-meta__chip-link {
    transition: none;
  }
}
</style>
