<script setup lang="ts">
import { computed } from "vue"
import { expertisePages } from "~/config/expertise-pages"

/**
 * Barre de filtre par expertise pour `/ressources`.
 *
 * Rend une liste de liens (`NuxtLink`) — pas de boutons JS. Chaque lien est
 * une URL réelle (`/ressources` ou `/ressources?expertise=<id>`), donc :
 *   - le SSR livre déjà l'état filtré sans hydratation JS ;
 *   - la navigation clavier est native (Tab + Enter) ;
 *   - un utilisateur peut partager l'URL, la mettre en favori ou l'ouvrir
 *     dans un nouvel onglet.
 *
 * L'élément actif porte `aria-current="page"` pour être annoncé par les
 * lecteurs d'écran. Le libellé accessible du groupe est fourni par le
 * `<nav aria-label>` — pas d'`aria-label` redondant sur chaque lien.
 *
 * La liste des expertises est dérivée de `expertisePages` (5 pages
 * publiées), triées par `order` du pôle correspondant serait idéal mais
 * l'ordre déclaratif de `expertisePages` correspond déjà à l'ordre
 * narratif de la home et de `/expertises`.
 */

interface Props {
  /** Identifiant d'expertise actif ; `null` = « Toutes ». */
  activeExpertise: string | null
}

const props = defineProps<Props>()

interface FilterOption {
  readonly id: string | null
  readonly label: string
  readonly href: string
}

const options = computed<readonly FilterOption[]>(() => {
  const items: FilterOption[] = [
    { id: null, label: "Toutes", href: "/ressources" },
  ]
  for (const page of expertisePages) {
    items.push({
      id: page.id,
      label: page.shortTitle,
      href: `/ressources?expertise=${page.id}`,
    })
  }
  return items
})

function isActive(option: FilterOption): boolean {
  return option.id === props.activeExpertise
}
</script>

<template>
  <nav
    class="resource-filter"
    aria-label="Filtrer les ressources par expertise"
  >
    <ul class="resource-filter__list" role="list">
      <li
        v-for="option in options"
        :key="option.id ?? 'all'"
        class="resource-filter__item"
      >
        <NuxtLink
          :to="option.href"
          class="resource-filter__link"
          :class="{ 'resource-filter__link--active': isActive(option) }"
          :aria-current="isActive(option) ? 'page' : undefined"
        >
          {{ option.label }}
        </NuxtLink>
      </li>
    </ul>
  </nav>
</template>

<style scoped>
.resource-filter {
  display: block;
}

.resource-filter__list {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
  list-style: none;
  margin: 0;
  padding: 0;
}

.resource-filter__link {
  display: inline-flex;
  align-items: center;
  padding: var(--space-2) var(--space-4);
  border: 1px solid var(--border-default);
  border-radius: 999px;
  background-color: var(--background-primary);
  color: var(--text-secondary);
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  font-weight: 600;
  line-height: 1.2;
  text-decoration: none;
  transition:
    border-color var(--duration-fast) var(--ease-out),
    background-color var(--duration-fast) var(--ease-out),
    color var(--duration-fast) var(--ease-out);
}

.resource-filter__link:hover,
.resource-filter__link:focus-visible {
  border-color: var(--color-devzair-blue);
  color: var(--text-primary);
}

.resource-filter__link--active {
  background-color: var(--color-petrol);
  border-color: var(--color-petrol);
  color: var(--background-primary);
}

.resource-filter__link--active:hover,
.resource-filter__link--active:focus-visible {
  color: var(--background-primary);
  border-color: var(--color-petrol);
}

@media (prefers-reduced-motion: reduce) {
  .resource-filter__link {
    transition: none;
  }
}
</style>
