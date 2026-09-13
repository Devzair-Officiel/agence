<script setup lang="ts">
import { computed } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import ResourceContent from "~/components/resources/ResourceContent.vue"
import ResourceHero from "~/components/resources/ResourceHero.vue"
import ResourceToc from "~/components/resources/ResourceToc.vue"
import SiteBreadcrumb from "~/components/layout/SiteBreadcrumb.vue"
import { useArticleSeo } from "~/composables/useArticleSeo"
import { useResourceDetail } from "~/composables/useResources"
import { processHeadings } from "~/utils/toc"

/**
 * Route dynamique `/ressources/{slug}` — page détail d'une ressource
 * éditoriale (Phase 8B2).
 *
 * Contrat d'état applicatif (correction obligatoire 6) :
 *   - slug invalide (format non conforme) → 400 côté endpoint Nitro,
 *     remonté ici en 503 par le composable (protection : ce cas ne peut
 *     survenir que si vue-router a laissé passer un segment non nettoyé) ;
 *   - slug inconnu (404 amont)            → 404 fatal ;
 *   - payload invalide (502 amont)        → 502 fatal ;
 *   - API indisponible (503 amont)        → 503 fatal.
 *
 * Le composable `useResourceDetail` prend en charge la traduction des
 * statuts amont en `createError({fatal: true})`. La page se contente
 * d'orchestrer.
 *
 * `ResourceContent` est le SEUL composant du projet autorisé à utiliser
 * `v-html`. Il consomme `article.contentHtml` produit et sécurisé côté
 * Symfony (`MarkdownSecurityPolicy`, ADR-010/011). Toute autre stratégie
 * de rendu HTML est un changement de frontière de confiance.
 *
 * `processHeadings` (utils/toc.ts) injecte les IDs sur les H2/H3 et
 * produit le tableau TOC en un seul passage, côté SSR. Le HTML modifié
 * reste dans la frontière de confiance Symfony — seuls des attributs `id`
 * sont ajoutés, aucun contenu HTML n'est injecté.
 */

const route = useRoute()

const slug = computed(() => {
  const raw = route.params.slug
  return Array.isArray(raw) ? raw[0] : raw
})

// Défense en profondeur : le format du slug est déjà validé côté Nitro
// (`SLUG_PATTERN` dans `/_editorial/detail/[slug]`). Ici on rejette
// simplement l'absence — vue-router garantit le reste.
if (!slug.value || typeof slug.value !== "string") {
  throw createError({
    statusCode: 404,
    statusMessage: "Ressource introuvable",
    fatal: true,
  })
}

const { article } = await useResourceDetail(slug.value)

const path = computed(() => `/ressources/${article.value.slug}`)

const breadcrumbItems = computed(() => [
  { label: "Accueil", to: "/" },
  { label: "Ressources", to: "/ressources" },
  { label: article.value.title },
])

// Injection des IDs H2/H3 et extraction du TOC — passe SSR unique.
const processed = computed(() => processHeadings(article.value.contentHtml))
const contentHtmlWithIds = computed(() => processed.value.html)
const tocEntries = computed(() => processed.value.toc)

useArticleSeo({ article: article.value, path: path.value })
</script>

<template>
  <div class="resource-detail">
    <SiteBreadcrumb :items="breadcrumbItems" />

    <!-- Hero texte centré sur fond navy ; padding-bottom augmenté si image -->
    <ResourceHero :article="article" :image-below="!!article.heroImage" />

    <!--
      Bridge image : div sans background, entre la zone navy et la zone sand.
      Le margin-top négatif tire l'image vers le haut pour qu'elle chevauche
      la bordure basse du hero navy — effet de transition éditorial.
      La classe resource-hero__image est conservée pour la compatibilité
      avec le test E2E hero-image-lifecycle.spec.ts (ligne 267).
    -->
    <div v-if="article.heroImage" class="resource-detail__image-bridge">
      <BaseContainer width="wide">
        <figure class="resource-detail__hero-figure">
          <img
            class="resource-hero__image"
            :src="article.heroImage.url"
            :alt="article.heroImage.alt"
            :width="article.heroImage.width"
            :height="article.heroImage.height"
            loading="eager"
            decoding="async"
            fetchpriority="high"
          >
        </figure>
      </BaseContainer>
    </div>

    <section class="resource-detail__section" aria-labelledby="resource-body-title">
      <BaseContainer width="wide" class="resource-detail__container">
        <h2 id="resource-body-title" class="resource-detail__visually-hidden">
          Contenu de la ressource
        </h2>

        <!-- TOC accordéon : visible sur mobile/tablette, masqué sur desktop -->
        <details
          v-if="tocEntries.length > 0"
          class="resource-detail__toc-disclosure"
        >
          <summary class="resource-detail__toc-summary">
            Dans cet article
            <span class="resource-detail__toc-chevron" aria-hidden="true">↓</span>
          </summary>
          <div class="resource-detail__toc-disclosure-body">
            <ResourceToc :entries="tocEntries" />
          </div>
        </details>

        <!-- Grille éditoriale : prose + rail de sommaire -->
        <div class="resource-detail__body">
          <div class="resource-detail__prose">
            <ResourceContent :content-html="contentHtmlWithIds" />
          </div>
          <!-- Sidebar TOC : visible sur desktop uniquement (≥ 1024 px) -->
          <aside
            v-if="tocEntries.length > 0"
            class="resource-detail__sidebar"
            aria-label="Sommaire de l'article"
          >
            <div class="resource-detail__sidebar-scroll">
              <ResourceToc :entries="tocEntries" />
            </div>
          </aside>
        </div>
      </BaseContainer>
    </section>

    <EditorialCallout
      eyebrow="Aller plus loin"
      title="Un besoin qui rejoint ces sujets ?"
      description="Racontez-nous votre contexte — nous vous dirons franchement si nous pouvons vous aider, et sur quel périmètre."
      :primary="{ label: 'Nous contacter', to: '/contact' }"
      :secondary="{ label: 'Voir toutes les ressources', to: '/ressources' }"
    />
  </div>
</template>

<style scoped>
.resource-detail {
  display: flex;
  flex-direction: column;
  background-color: var(--background-primary);
}

/* ── Bridge image ───────────────────────────────────────────────────── */

/*
 * Pas de background : le fond navy du hero s'affiche derrière la portion
 * haute de l'image, la couleur sable de .resource-detail derrière la
 * portion basse — créant la transition visuelle souhaitée.
 *
 * margin-top négatif = chevauchement du fond navy.
 * padding-bottom = respiration avant la section corps.
 */
.resource-detail__image-bridge {
  margin-top: calc(-1 * var(--space-10));
  padding-bottom: var(--space-8);
}

@media (min-width: 768px) {
  .resource-detail__image-bridge {
    margin-top: calc(-1 * var(--space-16));
    padding-bottom: var(--space-10);
  }
}

/*
 * Réduit les gouttières du BaseContainer image-bridge pour aligner l'image
 * sur la largeur de la section corps (même override que resource-detail__section).
 * À 1440px : 1440 - 2×24 = 1392 px (vs 1312 px avec gutters par défaut).
 */
@media (min-width: 1024px) {
  .resource-detail__image-bridge {
    --container-gutter-desktop: var(--space-6);
  }
}

@media (min-width: 1440px) {
  .resource-detail__image-bridge {
    --container-gutter-wide: var(--space-6);
  }
}

.resource-detail__hero-figure {
  margin: 0;
  width: 100%;
  aspect-ratio: 16 / 9;
  overflow: hidden; /* nécessaire pour border-radius — ne clip pas box-shadow */
  border-radius: var(--radius-xl);
  background-color: var(--background-secondary);
  /*
   * Double couche légère : donne de la profondeur sans bord franc.
   * La section suivante n'ayant plus de background-color propre, l'ombre
   * se fond naturellement dans le sable du parent .resource-detail.
   */
  box-shadow:
    0 8px 30px -18px rgba(14, 22, 34, 0.32),
    0 20px 64px -36px rgba(14, 22, 34, 0.70);
}

.resource-hero__image {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* ── Section corps ─────────────────────────────────────────────────── */

.resource-detail__section {
  padding-block: var(--space-10) var(--space-16);
  /*
   * Pas de background-color explicite : le parent .resource-detail et le body
   * fournissent tous deux var(--background-primary). Sans background propre,
   * la section ne peint pas par-dessus le box-shadow de .resource-detail__hero-figure
   * qui s'étend dans sa zone — c'est ce qui éliminait la coupure franche.
   */
}

/*
 * Réduit les gouttières du BaseContainer dans la section corps afin
 * d'utiliser davantage la largeur disponible (même technique que footer).
 */
@media (min-width: 1024px) {
  .resource-detail__section {
    --container-gutter-desktop: var(--space-6);
  }
}

@media (min-width: 1440px) {
  .resource-detail__section {
    --container-gutter-wide: var(--space-6);
  }
}

.resource-detail__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

/* ── TOC accordéon mobile/tablette ─────────────────────────────────── */

.resource-detail__toc-disclosure {
  border: 1px solid var(--border-default);
  border-radius: var(--radius-md);
  padding: var(--space-4);
}

.resource-detail__toc-summary {
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  list-style: none;
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.875rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--text-secondary);
  user-select: none;
}

.resource-detail__toc-summary::-webkit-details-marker {
  display: none;
}

.resource-detail__toc-chevron {
  transition: transform 200ms var(--ease-out);
  display: inline-block;
}

details[open] .resource-detail__toc-chevron {
  transform: rotate(180deg);
}

.resource-detail__toc-disclosure-body {
  margin-top: var(--space-4);
  padding-top: var(--space-3);
  border-top: 1px solid var(--border-default);
}

/* Sur desktop : le TOC accordéon est remplacé par la sidebar sticky */
@media (min-width: 1024px) {
  .resource-detail__toc-disclosure {
    display: none;
  }
}

/* ── Grille éditoriale ─────────────────────────────────────────────── */

.resource-detail__body {
  display: flex;
  flex-direction: column;
}

@media (min-width: 1024px) {
  .resource-detail__body {
    display: grid;
    /*
     * 1fr absorbe tout l'espace disponible ; la sidebar est compacte.
     * Résultat : ~75-80 % pour l'article, ~20-25 % pour le sommaire.
     */
    grid-template-columns: minmax(0, 1fr) minmax(260px, 300px);
    gap: var(--space-12);
    /* Pas d'align-items : chaque item s'étire par défaut (stretch).
       align-self: start sur la sidebar seule garantit que le sticky fonctionne. */
  }
}

/* ── Sidebar sticky ────────────────────────────────────────────────── */

.resource-detail__sidebar {
  display: none;
}

@media (min-width: 1024px) {
  /*
   * position: sticky sur le GRID ITEM (pas sur le wrapper interne).
   * Règle : ne jamais combiner position: sticky et overflow sur le même
   * élément — l'overflow crée un scroll container qui neutralise le sticky.
   * L'overflow-y: auto est délégué au wrapper interne .resource-detail__sidebar-scroll.
   */
  .resource-detail__sidebar {
    display: block;
    align-self: start;
    position: sticky;
    top: calc(var(--site-header-height) + var(--space-8));
  }

  .resource-detail__sidebar-scroll {
    max-height: calc(100dvh - var(--site-header-height) - var(--space-16));
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: var(--border-default) transparent;
  }
}

/* ── Titre de section masqué visuellement ──────────────────────────── */

.resource-detail__visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

@media (prefers-reduced-motion: reduce) {
  .resource-detail__toc-chevron {
    transition: none;
  }
}
</style>
