<script setup lang="ts">
import { computed } from "vue"

/**
 * Pagination accessible pour la liste `/ressources`.
 *
 * Contrat d'URL (correction obligatoire 6 du brief 8B2) :
 *   - la page 1 est servie sur `/ressources` **sans** `?page=1` ;
 *   - les pages suivantes sur `/ressources?page=N` ;
 *   - toute URL `/ressources?page=1` est redirigée par la page vers le
 *     canonique (voir `pages/ressources/index.vue`). Ce composant se
 *     contente donc de générer les bonnes cibles.
 *
 * Accessibilité :
 *   - `<nav aria-label="Pagination des ressources">` — nom explicite pour
 *     les navigateurs qui listent les régions ;
 *   - liens masquant les états inactifs via `aria-disabled` + `tabindex`
 *     plutôt que d'utiliser des `<button disabled>` (on est en navigation) ;
 *   - la page courante est un `<span aria-current="page">` — pas de lien
 *     vers soi-même, comportement conforme WCAG SC 2.4.8.
 *
 * Le composant est **présentation pure** : il ne connaît pas le total
 * absolu d'items, seulement le nombre de pages et la page courante,
 * calculés par la page depuis la pagination renvoyée par l'API.
 */

interface Props {
  currentPage: number
  totalPages: number
  /** Fabrique du chemin canonique pour une page cible. */
  buildHref: (page: number) => string
}

const props = defineProps<Props>()

const hasPrev = computed(() => props.currentPage > 1)
const hasNext = computed(() => props.currentPage < props.totalPages)

const prevHref = computed(() =>
  hasPrev.value ? props.buildHref(props.currentPage - 1) : null,
)
const nextHref = computed(() =>
  hasNext.value ? props.buildHref(props.currentPage + 1) : null,
)

interface PageEntry {
  readonly kind: "page"
  readonly page: number
  readonly href: string
  readonly current: boolean
}
interface EllipsisEntry {
  readonly kind: "ellipsis"
  readonly key: string
}
type Entry = PageEntry | EllipsisEntry

// Fenêtre glissante : premières, dernières, +/- 1 autour de la courante.
// On borne à 7 éléments visuels (2 pages fin + 2 pages début + 3 autour).
const entries = computed<readonly Entry[]>(() => {
  const total = props.totalPages
  const current = props.currentPage
  if (total <= 1) return []

  const pages = new Set<number>()
  pages.add(1)
  pages.add(total)
  for (const p of [current - 1, current, current + 1]) {
    if (p >= 1 && p <= total) pages.add(p)
  }
  const ordered = [...pages].sort((a, b) => a - b)

  const result: Entry[] = []
  let previous = 0
  for (const page of ordered) {
    if (previous > 0 && page - previous > 1) {
      result.push({ kind: "ellipsis", key: `ellipsis-${previous}-${page}` })
    }
    result.push({
      kind: "page",
      page,
      href: props.buildHref(page),
      current: page === current,
    })
    previous = page
  }
  return result
})
</script>

<template>
  <nav
    v-if="totalPages > 1"
    class="resource-pagination"
    aria-label="Pagination des ressources"
  >
    <NuxtLink
      v-if="prevHref"
      :to="prevHref"
      class="resource-pagination__control"
      rel="prev"
    >
      <span aria-hidden="true">←</span>
      <span>Précédente</span>
    </NuxtLink>
    <span
      v-else
      class="resource-pagination__control resource-pagination__control--disabled"
      aria-disabled="true"
    >
      <span aria-hidden="true">←</span>
      <span>Précédente</span>
    </span>

    <ol class="resource-pagination__list">
      <li
        v-for="entry in entries"
        :key="entry.kind === 'page' ? `page-${entry.page}` : entry.key"
        class="resource-pagination__item"
      >
        <span
          v-if="entry.kind === 'ellipsis'"
          class="resource-pagination__ellipsis"
          aria-hidden="true"
        >…</span>
        <span
          v-else-if="entry.current"
          class="resource-pagination__page resource-pagination__page--current"
          aria-current="page"
        >{{ entry.page }}</span>
        <NuxtLink
          v-else
          :to="entry.href"
          class="resource-pagination__page"
          :aria-label="`Aller à la page ${entry.page}`"
        >{{ entry.page }}</NuxtLink>
      </li>
    </ol>

    <NuxtLink
      v-if="nextHref"
      :to="nextHref"
      class="resource-pagination__control"
      rel="next"
    >
      <span>Suivante</span>
      <span aria-hidden="true">→</span>
    </NuxtLink>
    <span
      v-else
      class="resource-pagination__control resource-pagination__control--disabled"
      aria-disabled="true"
    >
      <span>Suivante</span>
      <span aria-hidden="true">→</span>
    </span>
  </nav>
</template>

<style scoped>
.resource-pagination {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: var(--space-3);
  padding-block: var(--space-6);
}

.resource-pagination__control {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  padding: 0.5rem 0.9rem;
  border: 1px solid var(--border-default);
  border-radius: var(--radius-md);
  background-color: var(--background-primary);
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--text-primary);
  text-decoration: none;
  transition: border-color var(--duration-fast) var(--ease-out),
    color var(--duration-fast) var(--ease-out);
}

.resource-pagination__control:hover,
.resource-pagination__control:focus-visible {
  border-color: var(--color-devzair-blue);
  color: var(--text-accent);
}

.resource-pagination__control--disabled {
  color: var(--text-muted);
  cursor: not-allowed;
}

.resource-pagination__control--disabled:hover,
.resource-pagination__control--disabled:focus-visible {
  border-color: var(--border-default);
  color: var(--text-muted);
}

.resource-pagination__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  align-items: center;
  gap: var(--space-1);
}

.resource-pagination__item {
  display: inline-flex;
}

.resource-pagination__page {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 2.25rem;
  height: 2.25rem;
  padding: 0 0.5rem;
  border: 1px solid transparent;
  border-radius: var(--radius-md);
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  color: var(--text-primary);
  text-decoration: none;
  transition: border-color var(--duration-fast) var(--ease-out),
    background-color var(--duration-fast) var(--ease-out);
}

.resource-pagination__page:hover,
.resource-pagination__page:focus-visible {
  border-color: var(--color-devzair-blue);
  color: var(--text-accent);
}

.resource-pagination__page--current {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  font-weight: 700;
}

.resource-pagination__page--current:hover,
.resource-pagination__page--current:focus-visible {
  border-color: transparent;
  color: var(--color-cream);
}

.resource-pagination__ellipsis {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 2.25rem;
  height: 2.25rem;
  color: var(--text-muted);
}

@media (prefers-reduced-motion: reduce) {
  .resource-pagination__control,
  .resource-pagination__page {
    transition: none;
  }
}
</style>
