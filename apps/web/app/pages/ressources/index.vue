<script setup lang="ts">
import { computed } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import EditorialHero from "~/components/editorial/EditorialHero.vue"
import ResourceEmptyState from "~/components/resources/ResourceEmptyState.vue"
import ResourceListItem from "~/components/resources/ResourceListItem.vue"
import ResourcePagination from "~/components/resources/ResourcePagination.vue"
import { useResourceList } from "~/composables/useResources"

/**
 * Page `/ressources` — liste paginée des ressources éditoriales.
 *
 * Contrat d'URL (correction obligatoire 6 du brief Phase 8B2) :
 *   - `/ressources`             → page 1 canonique ;
 *   - `/ressources?page=N` (N>1)→ page N ;
 *   - `/ressources?page=1`      → redirect 301 vers `/ressources` (évite
 *                                 le duplicate content avec la page 1) ;
 *   - `?page` invalide (non entier, ≤0, > totalPages ou hors bornes)
 *                                → 404 explicite fatal.
 *
 * Contrat d'état applicatif (correction 6) :
 *   - `pagination.total === 0`  → HTTP 200 + `ResourceEmptyState`
 *                                 (pas de 404 : la liste vide est un état
 *                                 valide, pas une erreur) ;
 *   - API 404 sur pagination hors bornes → 404 fatal (via `useResourceList`) ;
 *   - API 502 (payload invalide) → 502 fatal (via `useResourceList`) ;
 *   - API 503 (indisponible)    → 503 fatal (via `useResourceList`).
 *
 * Le canonical est bâti par `usePageSeo` en supprimant la query (règle du
 * builder canonical). Aucun `<link rel="prev/next">` : ces balises sont
 * dépréciées côté Google (2019) et ajoutent de la surface d'incohérence.
 *
 * Choix : pas de `prerender` — l'inventaire des pages varie avec les
 * publications côté back-office. Le SSR à la volée est acceptable
 * grâce au cache serveur `editorial-cache` posé par Nitro.
 */

const PER_PAGE = 6

const route = useRoute()
const router = useRouter()

// Parse `?page=` — on refuse tout ce qui n'est pas un entier ≥ 2. Sinon
// on rebascule sur la page 1. Une valeur explicite `=1` déclenche la
// redirect canonique côté serveur (voir plus bas).
function parseRequestedPage(): number {
  const raw = route.query.page
  if (raw === undefined) return 1
  const asString = Array.isArray(raw) ? raw[0] : raw
  if (typeof asString !== "string" || !/^\d+$/.test(asString)) {
    throw createError({
      statusCode: 404,
      statusMessage: "Page introuvable",
      fatal: true,
    })
  }
  const parsed = Number.parseInt(asString, 10)
  if (parsed < 1 || parsed > 10_000) {
    throw createError({
      statusCode: 404,
      statusMessage: "Page introuvable",
      fatal: true,
    })
  }
  return parsed
}

const requestedPage = parseRequestedPage()

// Redirect canonique `?page=1` → `/ressources` (correction 6).
// `await` pour bloquer le rendu SSR ; navigateTo pose un 301 côté serveur.
if (requestedPage === 1 && route.query.page !== undefined) {
  await navigateTo("/ressources", { redirectCode: 301 })
}

const { items, pagination } = await useResourceList({
  page: requestedPage,
  perPage: PER_PAGE,
})

// Garde de cohérence : la page demandée doit exister. Si le back renvoie
// une pagination cohérente mais que la page dépasse le total, on
// transforme en 404 fatal — le canonical de la page N n'existe pas.
if (pagination.value.totalPages > 0 && requestedPage > pagination.value.totalPages) {
  throw createError({
    statusCode: 404,
    statusMessage: "Page introuvable",
    fatal: true,
  })
}

const canonicalPath = computed(() =>
  requestedPage === 1 ? "/ressources" : `/ressources?page=${requestedPage}`,
)

const canonicalPathForSeo = computed(() => "/ressources")

// Fabrique un href canonique pour la pagination — page 1 sans query.
function buildPageHref(page: number): string {
  return page === 1 ? "/ressources" : `/ressources?page=${page}`
}

const title =
  requestedPage === 1
    ? "Ressources"
    : `Ressources — page ${requestedPage}`
const description =
  "Nos analyses, méthodes et retours d'expérience sur le web, le design, les contenus et la visibilité — publiés au rythme de nos projets."

usePageSeo({
  title,
  description,
  path: canonicalPathForSeo.value,
  type: "website",
})

const isEmpty = computed(() => pagination.value.total === 0)

// `router` est présent pour usage futur (highlight actif). `canonicalPath`
// est référencé dans le template implicite via le title mais utile aussi
// pour un usage dérivé — on l'annote pour éviter un warning `no-unused`.
void router
void canonicalPath.value
</script>

<template>
  <div class="resources-index">
    <EditorialHero
      eyebrow="Ressources"
      title="Ce que nous apprenons, mis à disposition."
      lead="Nous publions ici les analyses, méthodes et retours d'expérience qui ont émergé de nos projets. Aucune publication de remplissage : chaque ressource est écrite parce qu'elle avait quelque chose à documenter."
    />

    <section class="resources-index__section" aria-labelledby="resources-list-title">
      <BaseContainer class="resources-index__container">
        <header class="resources-index__header">
          <h2 id="resources-list-title" class="resources-index__title">
            Toutes les ressources
          </h2>
          <p v-if="!isEmpty" class="resources-index__count">
            {{ pagination.total }} ressource{{ pagination.total > 1 ? "s" : "" }} publiée{{ pagination.total > 1 ? "s" : "" }}
          </p>
        </header>

        <ResourceEmptyState v-if="isEmpty" />

        <template v-else>
          <ul class="resources-index__grid" role="list">
            <li
              v-for="article in items"
              :key="article.id"
              class="resources-index__grid-item"
            >
              <ResourceListItem :article="article" />
            </li>
          </ul>

          <ResourcePagination
            :current-page="pagination.page"
            :total-pages="pagination.totalPages"
            :build-href="buildPageHref"
          />
        </template>
      </BaseContainer>
    </section>

    <EditorialCallout
      eyebrow="Vous avez un projet"
      title="Ces sujets résonnent avec le vôtre ?"
      description="Racontez-nous votre contexte — nous vous dirons franchement si nous sommes le bon partenaire, et sinon vers qui vous orienter."
      :primary="{ label: 'Nous contacter', to: '/contact' }"
      :secondary="{ label: 'Découvrir l’agence', to: '/agence' }"
    />
  </div>
</template>

<style scoped>
.resources-index {
  display: flex;
  flex-direction: column;
}

.resources-index__section {
  padding-block: var(--space-12) var(--space-14);
  background-color: var(--background-primary);
}

.resources-index__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.resources-index__header {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  justify-content: space-between;
  gap: var(--space-3);
}

.resources-index__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.625rem, 3vw, 2rem);
  line-height: 1.2;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
}

.resources-index__count {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.06em;
  color: var(--text-muted);
  margin: 0;
}

.resources-index__grid {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-5);
}

.resources-index__grid-item {
  display: flex;
}

@media (min-width: 720px) {
  .resources-index__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1080px) {
  .resources-index__grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
</style>
