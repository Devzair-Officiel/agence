<script setup lang="ts">
import { computed } from "vue"
import ResourceListItem from "~/components/resources/ResourceListItem.vue"
import { useResourceList } from "~/composables/useResources"

/**
 * Section « Ressources liées » d'une page `/expertises/{slug}`.
 *
 * Charge en SSR la première page (3 items) du listing filtré par cette
 * expertise, via le même composable/proxy que `/ressources` — pas de
 * `$fetch` direct depuis le composant (règle §7 : un composant ne parle
 * pas à une API distante).
 *
 * Cas d'usage :
 *   - au moins un article publié pour cette expertise → rend la section
 *     avec les cartes et un lien « Voir toutes les ressources » vers la
 *     variante filtrée ;
 *   - aucun article → le composant ne rend RIEN (pas de placeholder, pas
 *     de « bientôt disponible » — règle 1 et 11 du référentiel).
 *
 * Cette dépendance SSR justifie de retirer les pages `/expertises/{slug}`
 * du pré-rendu Nitro : voir DEC-095 dans `docs/10-TRACKING.md`.
 */

interface Props {
  /** Identifiant d'expertise validé par la page parente. */
  expertiseId: string
  /** Libellé court affiché dans le lien « voir toutes ». */
  expertiseLabel: string
}

const props = defineProps<Props>()

const filteredHref = computed(() => `/ressources?expertise=${props.expertiseId}`)

// Clé `useAsyncData` distincte de la liste principale : évite qu'un cache
// hérité de `/ressources` remplace ces 3 items par la page complète.
const { items } = await useResourceList({
  page: 1,
  perPage: 3,
  expertise: props.expertiseId,
})

const hasItems = computed(() => items.value.length > 0)
</script>

<template>
  <section
    v-if="hasItems"
    class="expertise-resources"
    aria-labelledby="expertise-resources-title"
  >
    <div class="expertise-resources__header">
      <p class="expertise-resources__eyebrow">Ressources liées</p>
      <h2
        id="expertise-resources-title"
        class="expertise-resources__title"
      >
        Nos ressources autour de l'expertise « {{ expertiseLabel }} ».
      </h2>
      <p class="expertise-resources__lead">
        Une sélection d'analyses et de retours d'expérience que nous avons
        publiés sur ce pôle.
      </p>
    </div>

    <ul class="expertise-resources__grid" role="list">
      <li
        v-for="article in items"
        :key="article.id"
        class="expertise-resources__grid-item"
      >
        <ResourceListItem :article="article" />
      </li>
    </ul>

    <p class="expertise-resources__cta">
      <NuxtLink :to="filteredHref" class="expertise-resources__cta-link">
        Voir toutes les ressources sur ce pôle →
      </NuxtLink>
    </p>
  </section>
</template>

<style scoped>
.expertise-resources {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
  padding-block: var(--space-10) var(--space-12);
  padding-inline: clamp(var(--space-4), 5vw, var(--space-8));
  max-width: 72rem;
  margin-inline: auto;
  width: 100%;
}

.expertise-resources__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  max-width: 46rem;
}

.expertise-resources__eyebrow {
  margin: 0;
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text-accent);
}

.expertise-resources__title {
  margin: 0;
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.5rem, 2.6vw, 2rem);
  line-height: 1.2;
  letter-spacing: -0.01em;
  color: var(--text-primary);
}

.expertise-resources__lead {
  margin: 0;
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.6;
  color: var(--text-secondary);
}

.expertise-resources__grid {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-5);
}

.expertise-resources__grid-item {
  display: flex;
}

.expertise-resources__cta {
  margin: 0;
}

.expertise-resources__cta-link {
  display: inline-flex;
  align-items: center;
  color: var(--color-petrol);
  font-family: var(--font-family-body);
  font-weight: 700;
  text-decoration: none;
}

.expertise-resources__cta-link:hover,
.expertise-resources__cta-link:focus-visible {
  text-decoration: underline;
}

@media (min-width: 720px) {
  .expertise-resources__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1080px) {
  .expertise-resources__grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
</style>
