<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"

/**
 * Fil d'Ariane accessible pour les pages filles institutionnelles.
 *
 * Contrat volontairement minimal :
 *   - une liste ordonnée `<ol>` dans un `<nav aria-label="Fil d'Ariane">` ;
 *   - chaque `BreadcrumbItem` a soit un `to` (rendu `<NuxtLink>`), soit
 *     aucun (rendu texte simple avec `aria-current="page"`) ;
 *   - le dernier item DOIT être la page courante — c'est la seule entrée
 *     qui reçoit `aria-current="page"` et qui n'est PAS un lien.
 *
 * Rendu SSR uniquement, aucun JS, aucun état.
 *
 * Accessibilité :
 *   - `nav` étiquetté explicitement en français (« Fil d'Ariane ») pour les
 *     lecteurs d'écran ;
 *   - séparateurs `›` masqués via `aria-hidden` pour éviter la répétition
 *     vocale ;
 *   - la page courante n'est pas un lien (recommandation WAI-ARIA APG).
 */

export interface BreadcrumbItem {
  readonly label: string
  /** Route Nuxt existante ; omis pour la page courante. */
  readonly to?: string
}

interface Props {
  items: readonly BreadcrumbItem[]
}

defineProps<Props>()
</script>

<template>
  <nav class="site-breadcrumb" aria-label="Fil d'Ariane">
    <BaseContainer class="site-breadcrumb__container">
      <ol class="site-breadcrumb__list" role="list">
        <li
          v-for="(item, index) in items"
          :key="item.label"
          class="site-breadcrumb__item"
        >
          <NuxtLink
            v-if="item.to && index < items.length - 1"
            :to="item.to"
            class="site-breadcrumb__link"
          >
            {{ item.label }}
          </NuxtLink>
          <span
            v-else
            class="site-breadcrumb__current"
            aria-current="page"
          >
            {{ item.label }}
          </span>
          <span
            v-if="index < items.length - 1"
            class="site-breadcrumb__separator"
            aria-hidden="true"
          >
            ›
          </span>
        </li>
      </ol>
    </BaseContainer>
  </nav>
</template>

<style scoped>
.site-breadcrumb {
  background-color: var(--background-primary);
  padding-block: var(--space-4);
  border-bottom: 1px solid var(--border-subtle, var(--border-default));
}

.site-breadcrumb__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: var(--space-2);
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  line-height: 1.4;
}

.site-breadcrumb__item {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
}

.site-breadcrumb__link {
  color: var(--text-secondary);
  text-decoration: none;
  border-bottom: 1px solid transparent;
  transition: color 120ms ease, border-color 120ms ease;
}

.site-breadcrumb__link:hover,
.site-breadcrumb__link:focus-visible {
  color: var(--text-primary);
  border-bottom-color: currentColor;
}

.site-breadcrumb__current {
  color: var(--text-primary);
  font-weight: 600;
}

.site-breadcrumb__separator {
  color: var(--text-muted);
  user-select: none;
}
</style>
