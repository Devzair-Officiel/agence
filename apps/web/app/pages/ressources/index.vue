<script setup lang="ts">
import { computed } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import EditorialHero from "~/components/editorial/EditorialHero.vue"
import ResourceEmptyState from "~/components/resources/ResourceEmptyState.vue"
import ResourceExpertiseFilter from "~/components/resources/ResourceExpertiseFilter.vue"
import ResourceListItem from "~/components/resources/ResourceListItem.vue"
import ResourcePagination from "~/components/resources/ResourcePagination.vue"
import { EXPERTISE_IDS, expertisePages } from "~/config/expertise-pages"
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
 * Phase 10A2 — filtre expertise :
 *   - `/ressources?expertise=<id>` → même page filtrée sur les articles de
 *     l'expertise ; combinable avec `?page=N`.
 *   - `?expertise=<id>` inconnu → 404 explicite (pas de silent-fallback qui
 *     laisserait indexer une variante non voulue).
 *   - Les variantes filtrées portent `<meta robots="noindex, follow">` et
 *     un canonical vers `/ressources` — elles ne doivent pas concurrencer
 *     la liste canonique dans les résultats de recherche. Ce sont des
 *     vues de navigation, pas des pages d'atterrissage éditoriales.
 *   - Le sitemap dynamique n'énumère que les URLs `/ressources/<slug>` ;
 *     aucune URL filtrée n'y est ajoutée (cf. `server/routes/__sitemap__/`).
 *
 * Contrat d'état applicatif (correction 6) :
 *   - `pagination.total === 0`  → HTTP 200 + `ResourceEmptyState`
 *                                 (pas de 404 : la liste vide est un état
 *                                 valide, pas une erreur) ;
 *   - API 404 sur pagination hors bornes → 404 fatal (via `useResourceList`) ;
 *   - API 502 (payload invalide) → 502 fatal (via `useResourceList`) ;
 *   - API 503 (indisponible)    → 503 fatal (via `useResourceList`).
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

// Le filtre expertise est validé strictement contre l'allowlist. Une valeur
// inconnue devient 404 : mieux vaut refuser explicitement une URL fabriquée
// que renvoyer silencieusement la liste complète — cela évite qu'un bot
// n'indexe des variantes invalides.
function parseRequestedExpertise(): string | null {
  const raw = route.query.expertise
  if (raw === undefined) return null
  const asString = Array.isArray(raw) ? raw[0] : raw
  if (typeof asString !== "string" || asString.length === 0) {
    throw createError({
      statusCode: 404,
      statusMessage: "Expertise inconnue",
      fatal: true,
    })
  }
  if (!EXPERTISE_IDS.includes(asString)) {
    throw createError({
      statusCode: 404,
      statusMessage: "Expertise inconnue",
      fatal: true,
    })
  }
  return asString
}

const requestedExpertise = parseRequestedExpertise()

// Redirect canonique `?page=1` → `/ressources` (correction 6). Préserve
// le filtre expertise s'il est présent.
if (requestedPage === 1 && route.query.page !== undefined) {
  const target = requestedExpertise
    ? `/ressources?expertise=${requestedExpertise}`
    : "/ressources"
  await navigateTo(target, { redirectCode: 301 })
}

const { items, pagination } = await useResourceList({
  page: requestedPage,
  perPage: PER_PAGE,
  expertise: requestedExpertise,
})

// Garde de cohérence : la page demandée doit exister. Si le back renvoie
// une pagination cohérente mais que la page dépasse le total, on
// transforme en 404 fatal — le canonical de la page N n'existe pas. Une
// vue filtrée vide (total = 0) reste 200 (état valide, comme la liste
// globale vide) : c'est le cas `isEmpty` géré plus bas.
if (pagination.value.totalPages > 0 && requestedPage > pagination.value.totalPages) {
  throw createError({
    statusCode: 404,
    statusMessage: "Page introuvable",
    fatal: true,
  })
}

const activeExpertisePage = computed(() =>
  requestedExpertise
    ? expertisePages.find((page) => page.id === requestedExpertise) ?? null
    : null,
)

// Fabrique un href canonique pour la pagination — page 1 sans query, tout
// en préservant le filtre expertise actif.
function buildPageHref(page: number): string {
  const params = new URLSearchParams()
  if (page > 1) params.set("page", String(page))
  if (requestedExpertise) params.set("expertise", requestedExpertise)
  const qs = params.toString()
  return qs ? `/ressources?${qs}` : "/ressources"
}

const title = computed(() => {
  const base = activeExpertisePage.value
    ? `Ressources — ${activeExpertisePage.value.shortTitle}`
    : "Ressources"
  return requestedPage === 1 ? base : `${base} — page ${requestedPage}`
})
const description =
  "Nos analyses, méthodes et retours d'expérience sur le web, le design, les contenus et la visibilité — publiés au rythme de nos projets."

// Politique SEO Phase 10A2 :
//   - liste canonique (`/ressources`, aucune query) → indexable, canonical
//     absolu vers elle-même ;
//   - variantes filtrées ou paginées → `noindex, follow` : on ne les fait
//     pas concurrencer la page canonique, mais on laisse le crawler suivre
//     les liens vers les articles individuels (qui restent indexables).
const isFiltered = computed(() => requestedExpertise !== null)
const isPaginated = computed(() => requestedPage > 1)
const shouldNoindex = computed(() => isFiltered.value || isPaginated.value)

usePageSeo({
  title: title.value,
  description,
  path: "/ressources",
  type: "website",
  robots: shouldNoindex.value ? "noindex, follow" : undefined,
})

const isEmpty = computed(() => pagination.value.total === 0)

void router
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
            {{ activeExpertisePage
              ? `Ressources — ${activeExpertisePage.shortTitle}`
              : "Toutes les ressources" }}
          </h2>
          <p v-if="!isEmpty" class="resources-index__count">
            {{ pagination.total }} ressource{{ pagination.total > 1 ? "s" : "" }} publiée{{ pagination.total > 1 ? "s" : "" }}
          </p>
        </header>

        <ResourceExpertiseFilter :active-expertise="requestedExpertise" />

        <p
          v-if="isEmpty && activeExpertisePage"
          class="resources-index__filter-empty"
        >
          Aucune ressource n'est encore publiée pour l'expertise
          « {{ activeExpertisePage.shortTitle }} ». Consultez
          <NuxtLink to="/ressources">
            l'ensemble des ressources
          </NuxtLink>
          ou <NuxtLink :to="activeExpertisePage.route">
            découvrez cette expertise
          </NuxtLink>.
        </p>

        <ResourceEmptyState v-else-if="isEmpty" />

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

.resources-index__filter-empty {
  margin: 0;
  padding: var(--space-6);
  border: 1px dashed var(--border-default);
  border-radius: var(--radius-lg);
  background-color: var(--background-secondary);
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
}

.resources-index__filter-empty :where(a) {
  color: var(--color-petrol);
  font-weight: 600;
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
