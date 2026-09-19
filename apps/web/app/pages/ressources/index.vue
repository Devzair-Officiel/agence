<script setup lang="ts">
import '~/assets/css/routes/resources-index.css'
import { computed } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import ResourceEmptyState from "~/components/resources/ResourceEmptyState.vue"
import ResourceExpertiseFilter from "~/components/resources/ResourceExpertiseFilter.vue"
import ResourceListItem from "~/components/resources/ResourceListItem.vue"
import ResourcePagination from "~/components/resources/ResourcePagination.vue"
import ResourcesPointerTrail from "~/components/resources/ResourcesPointerTrail.vue"
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

const requestedPage = computed(parseRequestedPage)

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

const requestedExpertise = computed(parseRequestedExpertise)

// Redirect canonique `?page=1` → `/ressources` (correction 6). Préserve
// le filtre expertise s'il est présent.
if (requestedPage.value === 1 && route.query.page !== undefined) {
  const target = requestedExpertise.value
    ? `/ressources?expertise=${requestedExpertise.value}`
    : "/ressources"
  await navigateTo(target, { redirectCode: 301 })
}

const { items, pagination, pending } = await useResourceList({
  page: requestedPage,
  perPage: PER_PAGE,
  expertise: requestedExpertise,
})

// Garde de cohérence : la page demandée doit exister. Si le back renvoie
// une pagination cohérente mais que la page dépasse le total, on
// transforme en 404 fatal — le canonical de la page N n'existe pas. Une
// vue filtrée vide (total = 0) reste 200 (état valide, comme la liste
// globale vide) : c'est le cas `isEmpty` géré plus bas.
if (
  pagination.value.totalPages > 0 &&
  requestedPage.value > pagination.value.totalPages
) {
  throw createError({
    statusCode: 404,
    statusMessage: "Page introuvable",
    fatal: true,
  })
}

const activeExpertisePage = computed(() =>
  requestedExpertise.value
    ? expertisePages.find((page) => page.id === requestedExpertise.value) ?? null
    : null,
)

// Fabrique un href canonique pour la pagination — page 1 sans query, tout
// en préservant le filtre expertise actif.
function buildPageHref(page: number): string {
  const params = new URLSearchParams()
  if (page > 1) params.set("page", String(page))
  if (requestedExpertise.value) {
    params.set("expertise", requestedExpertise.value)
  }
  const qs = params.toString()
  return qs ? `/ressources?${qs}` : "/ressources"
}

const title = computed(() => {
  const base = activeExpertisePage.value
    ? `Ressources — ${activeExpertisePage.value.shortTitle}`
    : "Ressources"
  return requestedPage.value === 1
    ? base
    : `${base} — page ${requestedPage.value}`
})
const description =
  "Nos analyses, méthodes et retours d'expérience sur le web, le design, les contenus et la visibilité — publiés au rythme de nos projets."

// Politique SEO Phase 10A2 :
//   - liste canonique (`/ressources`, aucune query) → indexable, canonical
//     absolu vers elle-même ;
//   - variantes filtrées ou paginées → `noindex, follow` : on ne les fait
//     pas concurrencer la page canonique, mais on laisse le crawler suivre
//     les liens vers les articles individuels (qui restent indexables).
const isFiltered = computed(() => requestedExpertise.value !== null)
const isPaginated = computed(() => requestedPage.value > 1)
const shouldNoindex = computed(() => isFiltered.value || isPaginated.value)
const robotsDirective = computed(() =>
  shouldNoindex.value ? ("noindex, follow" as const) : undefined,
)

usePageSeo({
  title,
  description,
  path: "/ressources",
  type: "website",
  robots: robotsDirective,
})

const isEmpty = computed(() => pagination.value.total === 0)
</script>

<template>
  <div class="resources-index">
    <section
      class="resources-hero"
      aria-labelledby="resources-hero-title"
    >
      <!-- Halos ambiants CSS — bleu Devzair coin sup-droit + pétrole coin inf-gauche -->
      <div class="resources-hero__glow" aria-hidden="true" />
      <!-- Motif éditorial : grille de points + graphe de nœuds, desktop uniquement -->
      <div class="resources-hero__decor" aria-hidden="true">
        <svg viewBox="0 0 520 400" fill="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <pattern id="rh-dots" x="0" y="0" width="22" height="22" patternUnits="userSpaceOnUse">
              <circle cx="0.5" cy="0.5" r="1" fill="currentColor" />
            </pattern>
          </defs>
          <rect width="520" height="400" fill="url(#rh-dots)" opacity="0.55" />
          <g stroke="currentColor" stroke-width="0.75">
            <line x1="110" y1="75" x2="255" y2="45" />
            <line x1="255" y1="45" x2="400" y2="90" />
            <line x1="400" y1="90" x2="465" y2="45" />
            <line x1="110" y1="75" x2="55" y2="200" />
            <line x1="110" y1="75" x2="175" y2="210" />
            <line x1="255" y1="45" x2="175" y2="210" />
            <line x1="255" y1="45" x2="310" y2="225" />
            <line x1="400" y1="90" x2="310" y2="225" />
            <line x1="400" y1="90" x2="445" y2="195" />
            <line x1="55" y1="200" x2="120" y2="330" />
            <line x1="175" y1="210" x2="120" y2="330" />
            <line x1="175" y1="210" x2="260" y2="340" />
            <line x1="310" y1="225" x2="260" y2="340" />
            <line x1="310" y1="225" x2="395" y2="355" />
            <line x1="445" y1="195" x2="395" y2="355" />
            <line x1="120" y1="330" x2="260" y2="340" />
            <line x1="260" y1="340" x2="395" y2="355" />
            <line x1="465" y1="45" x2="520" y2="10" stroke-dasharray="5 9" opacity="0.5" />
            <line x1="445" y1="195" x2="520" y2="200" stroke-dasharray="5 9" opacity="0.5" />
            <line x1="395" y1="355" x2="465" y2="400" stroke-dasharray="5 9" opacity="0.5" />
          </g>
          <circle cx="255" cy="45" r="5" stroke="currentColor" stroke-width="1.5" />
          <circle cx="400" cy="90" r="5" stroke="currentColor" stroke-width="1.5" />
          <g fill="currentColor">
            <circle cx="175" cy="210" r="3" />
            <circle cx="310" cy="225" r="3" />
            <circle cx="120" cy="330" r="2.5" />
            <circle cx="260" cy="340" r="3" />
            <circle cx="395" cy="355" r="2.5" />
          </g>
          <g stroke="currentColor" fill="none" stroke-width="1">
            <circle cx="110" cy="75" r="2.5" />
            <circle cx="465" cy="45" r="2" />
            <circle cx="55" cy="200" r="2" />
            <circle cx="445" cy="195" r="2" />
          </g>
          <g stroke="currentColor" stroke-width="0.75" stroke-linecap="round">
            <line x1="248" y1="45" x2="262" y2="45" />
            <line x1="255" y1="38" x2="255" y2="52" />
          </g>
        </svg>
      </div>
      <ResourcesPointerTrail />
      <BaseContainer width="wide" class="resources-hero__container">
        <BaseEyebrow class="resources-hero__eyebrow">
          Ressources
        </BaseEyebrow>
        <h1 id="resources-hero-title" class="resources-hero__title">
          Ce que nous apprenons,
          <span class="resources-hero__title-emphasis">mis à disposition.</span>
        </h1>
        <p class="resources-hero__lead">
          Nous publions ici les analyses, méthodes et retours d'expérience qui
          ont émergé de nos projets. Aucune publication de remplissage : chaque
          ressource est écrite parce qu'elle avait quelque chose à documenter.
        </p>
        <ul class="resources-hero__tags" role="list">
          <li class="resources-hero__tag">Analyses</li>
          <li class="resources-hero__tag">Méthodes</li>
          <li class="resources-hero__tag">Retours d'expérience</li>
        </ul>
      </BaseContainer>
    </section>

    <section
      class="resources-index__section"
      aria-labelledby="resources-list-title"
      :aria-busy="pending"
    >
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
          <!--
            Transition inter-pages : la clé sur `pagination.page` force
            Vue à démonter/remonter le <ul>, ce qui déclenche le fade
            croisé (mode `out-in` évite la superposition qui casserait
            la mise en page grid). Le viewport `data-pending` grise
            légèrement pendant le fetch pour indiquer que la nouvelle
            page arrive, sans layout shift.
          -->
          <div class="resources-index__viewport" :data-pending="pending || undefined">
            <Transition name="resources-page" mode="out-in">
              <ul
                :key="pagination.page"
                class="resources-index__grid"
                role="list"
              >
                <li
                  v-for="article in items"
                  :key="article.id"
                  class="resources-index__grid-item"
                >
                  <ResourceListItem :article="article" />
                </li>
              </ul>
            </Transition>
          </div>

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

/*
 * Hero ressources — "Système éditorial"
 *   `__glow`  : halos ambiants CSS (bleu + pétrole), sans asset externe.
 *   `__decor` : SVG inline nœuds/connexions + grille de points, desktop.
 *   `__tags`  : labels thématiques monospace sous le lead.
 */
.resources-hero {
  position: relative;
  isolation: isolate;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  min-height: calc(100vh - var(--site-header-height));
  min-height: calc(100svh - var(--site-header-height));
  overflow: hidden;
  background-color: var(--background-primary);
  padding-block: var(--space-16);
}

.resources-hero::after {
  content: "";
  position: absolute;
  z-index: 1;
  inset-inline: 0;
  bottom: 0;
  height: clamp(6rem, 20%, 12rem);
  background: linear-gradient(to bottom, transparent 0%, var(--background-primary) 100%);
  pointer-events: none;
}

.resources-hero__glow {
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background:
    radial-gradient(ellipse 60% 60% at 88% 12%, rgba(46, 134, 217, 0.10) 0%, transparent 70%),
    radial-gradient(ellipse 50% 70% at 6% 92%, rgba(12, 91, 87, 0.07) 0%, transparent 70%);
}

.resources-hero__decor {
  display: none;
  position: absolute;
  z-index: 0;
  pointer-events: none;
  inset-block: 0;
  inset-inline-end: -4%;
  width: 58%;
  color: var(--color-ink);
  opacity: 0.065;
}

.resources-hero__decor svg {
  width: 100%;
  height: 100%;
  display: block;
}

@media (min-width: 900px) {
  .resources-hero__decor {
    display: block;
  }
}

@media (prefers-reduced-motion: no-preference) {
  .resources-hero__decor {
    animation: rh-decor-in 1.4s var(--ease-out) both;
  }
}

@keyframes rh-decor-in {
  from {
    opacity: 0;
    transform: translateX(28px);
  }
  to {
    opacity: 0.065;
    transform: translateX(0);
  }
}

.resources-hero__container {
  position: relative;
  z-index: 2;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: var(--space-5);
}

@media (min-width: 900px) {
  .resources-hero__container {
    align-items: flex-start;
    text-align: left;
  }
}

.resources-hero__eyebrow {
  margin-bottom: 0;
}

.resources-hero__title {
  margin: 0;
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2.25rem, 5vw, 3.5rem);
  line-height: 1.08;
  letter-spacing: -0.02em;
  color: var(--text-primary);
  max-width: 22ch;
  text-wrap: balance;
}

.resources-hero__title-emphasis {
  color: var(--color-petrol);
}

@supports ((-webkit-background-clip: text) or (background-clip: text)) {
  .resources-hero__title-emphasis {
    background: linear-gradient(
      120deg,
      var(--color-petrol) 0%,
      var(--color-devzair-blue) 100%
    );
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    color: transparent;
  }
}

.resources-hero__lead {
  margin: 0;
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  line-height: 1.6;
  color: var(--text-secondary);
  max-width: 56ch;
  text-wrap: pretty;
}

.resources-hero__tags {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: var(--space-2);
  list-style: none;
  padding: 0;
  margin: var(--space-2) 0 0;
}

.resources-hero__tag {
  display: inline-flex;
  align-items: center;
  padding: 5px var(--space-3);
  border-radius: var(--radius-pill);
  border: 1px solid var(--border-strong);
  font-family: var(--font-family-mono);
  font-size: 0.6875rem;
  font-weight: var(--font-weight-mono);
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text-secondary);
}

@media (min-width: 900px) {
  .resources-hero__tags {
    justify-content: flex-start;
  }
}

@media (min-width: 768px) {
  .resources-hero {
    padding-block: var(--space-20);
  }
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

.resources-index__viewport {
  position: relative;
  transition: opacity 220ms var(--ease-out);
}

.resources-index__viewport[data-pending] {
  opacity: 0.7;
}

/*
 * Transition inter-pages (Vue `<Transition name="resources-page">`).
 * Fade + très léger slide vertical, `mode="out-in"` orchestre le retrait
 * complet avant l'entrée pour éviter un chevauchement dans la grid.
 * Durée courte (280 ms) pour ne pas ralentir la navigation.
 */
.resources-page-enter-active,
.resources-page-leave-active {
  transition:
    opacity 280ms var(--ease-out),
    transform 280ms cubic-bezier(0.22, 0.61, 0.36, 1);
}

.resources-page-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.resources-page-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

@media (prefers-reduced-motion: reduce) {
  .resources-index__viewport,
  .resources-page-enter-active,
  .resources-page-leave-active {
    transition: none;
  }
  .resources-page-enter-from,
  .resources-page-leave-to {
    transform: none;
  }
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
