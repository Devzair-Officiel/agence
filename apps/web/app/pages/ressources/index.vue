<script setup lang="ts">
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
    <!--
      Hero éditorial spécifique aux ressources — fond cream (cohérent avec
      les autres pages institutionnelles) avec une carte du monde en
      pointillés en arrière-plan. L'image source est en dots blancs sur
      fond noir : `filter: invert(1)` la retourne en dots sombres sur
      blanc, puis `mix-blend-mode: multiply` fait disparaître le blanc
      dans le cream et ne laisse voir que les dots. Un voile radial
      allège les bords pour éviter tout effet « poster collé ».
    -->
    <section
      class="resources-hero"
      aria-labelledby="resources-hero-title"
    >
      <div class="resources-hero__backdrop" aria-hidden="true" />
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
 * Hero ressources — fond cream (cohérent avec les pages institutionnelles).
 *   1. `__backdrop` : la carte du monde en pointillés sert de MASK
 *      (image blanc / noir : blanc = visible). Le fond du backdrop est
 *      peint en `--color-petrol` — les dots héritent donc directement
 *      de la couleur de marque, aucune teinte parasite héritée du blend.
 *      Deux masques combinés en `intersect` : la carte + un fondu radial
 *      pour dissoudre les bords dans le cream.
 *   2. `__container` : texte au-dessus, centré et respirant.
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
  color: var(--text-primary);
  background-color: var(--background-primary);
  padding-block: var(--space-16) var(--space-16);
}

/*
 * La carte occupe tout le premier écran, mais ne se termine pas sur une
 * ligne horizontale. Ce voile reprend exactement le fond de la section
 * suivante et dissout progressivement les pointillés dans le flux de page.
 */
.resources-hero::after {
  content: "";
  position: absolute;
  z-index: 0;
  inset-inline: 0;
  bottom: -1px;
  height: clamp(9rem, 28%, 18rem);
  background: linear-gradient(
    to bottom,
    transparent 0%,
    var(--background-primary) 100%
  );
  pointer-events: none;
}

.resources-hero__backdrop {
  position: absolute;
  inset: 0;
  background-color: var(--color-devzair-blue);
  opacity: 0.75;
  pointer-events: none;
  z-index: 0;
  -webkit-mask-image: url("/brand/world-dots.webp"),
    radial-gradient(
      ellipse 90% 95% at 50% 50%,
      black 0%,
      black 55%,
      transparent 100%
    );
  mask-image: url("/brand/world-dots.webp"),
    radial-gradient(
      ellipse 90% 95% at 50% 50%,
      black 0%,
      black 55%,
      transparent 100%
    );
  -webkit-mask-repeat: no-repeat, no-repeat;
  mask-repeat: no-repeat, no-repeat;
  -webkit-mask-position: center, center;
  mask-position: center, center;
  -webkit-mask-size: cover, cover;
  mask-size: cover, cover;
  -webkit-mask-composite: source-in;
  mask-composite: intersect;
  /*
   * Modes distincts par couche :
   *   - image (dots) → `luminance` : les pixels blancs deviennent visibles ;
   *   - gradient radial → `alpha` : la couleur noire opaque = visible au
   *     centre, `transparent` = caché sur les bords (fondu doux).
   * Sans ceci, `luminance` sur le gradient rendrait `transparent` comme
   * `rgba(0,0,0,0)` → luminance 0 → tout caché.
   */
  -webkit-mask-mode: luminance, alpha;
  mask-mode: luminance, alpha;
}

.resources-hero__container {
  position: relative;
  z-index: 1;
  isolation: isolate;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: var(--space-4);
  max-width: 62rem;
}

/*
 * Halo de lecture : la carte reste perceptible autour du contenu, tandis
 * que sa densité baisse progressivement sous les titres et le paragraphe.
 * Le masque radial évite l'apparence d'une carte rectangulaire rapportée.
 */
.resources-hero__container::before {
  content: "";
  position: absolute;
  z-index: -1;
  inset: -4rem -2rem;
  background-color: var(--background-primary);
  opacity: 0.9;
  pointer-events: none;
  -webkit-mask-image: radial-gradient(
    ellipse at center,
    black 0%,
    black 42%,
    rgba(0, 0, 0, 0.78) 58%,
    transparent 82%
  );
  mask-image: radial-gradient(
    ellipse at center,
    black 0%,
    black 42%,
    rgba(0, 0, 0, 0.78) 58%,
    transparent 82%
  );
}

.resources-hero__eyebrow {
  margin-bottom: var(--space-1);
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
  color: var(--text-primary);
  max-width: 60ch;
  text-wrap: pretty;
}

@media (min-width: 768px) {
  .resources-hero {
    padding-block: var(--space-20) var(--space-20);
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
