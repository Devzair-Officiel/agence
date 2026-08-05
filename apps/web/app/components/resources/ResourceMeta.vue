<script setup lang="ts">
import { computed } from "vue"
import type { ArticleAuthor } from "~/types/editorial"
import { expertisePillars } from "~/config/expertise-pillars"
import { expertisePages } from "~/config/expertise-pages"

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

const DATE_FORMATTER = new Intl.DateTimeFormat("fr-FR", {
  day: "numeric",
  month: "long",
  year: "numeric",
})

interface ExpertiseLink {
  readonly id: string
  readonly label: string
  readonly route: string | null
}

function formatDate(iso: string): string {
  const date = new Date(iso)
  if (Number.isNaN(date.getTime())) return iso
  return DATE_FORMATTER.format(date)
}

const publishedLabel = computed(() => formatDate(props.publishedAt))
const updatedLabel = computed(() => formatDate(props.updatedAt))
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
      <dt class="resource-meta__label">Auteur</dt>
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
    <div v-if="expertiseLinks.length > 0" class="resource-meta__row">
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
.resource-meta {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-3);
  margin: 0;
  padding: 0;
}

.resource-meta__row {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.resource-meta__label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text-muted);
  margin: 0;
}

.resource-meta__value {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.5;
  color: var(--text-primary);
  margin: 0;
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
  padding: 0.15rem 0.6rem;
  border-radius: 999px;
  border: 1px solid var(--border-default);
  background-color: var(--background-secondary);
  font-family: var(--font-family-body);
  font-size: 0.8125rem;
  line-height: 1.4;
  color: var(--text-primary);
}

.resource-meta__chip-link {
  text-decoration: none;
  transition: border-color var(--duration-fast) var(--ease-out),
    color var(--duration-fast) var(--ease-out);
}

.resource-meta__chip-link:hover,
.resource-meta__chip-link:focus-visible {
  border-color: var(--color-devzair-blue);
  color: var(--text-accent);
}

@media (min-width: 640px) {
  .resource-meta {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-3) var(--space-6);
  }
}

@media (prefers-reduced-motion: reduce) {
  .resource-meta__chip-link {
    transition: none;
  }
}
</style>
