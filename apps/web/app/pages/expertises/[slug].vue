<script setup lang="ts">
import { computed } from "vue"
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import EditorialSection from "~/components/editorial/EditorialSection.vue"
import ExpertiseBenefits from "~/components/expertise/ExpertiseBenefits.vue"
import ExpertiseDeliverables from "~/components/expertise/ExpertiseDeliverables.vue"
import ExpertisePageHero from "~/components/expertise/ExpertisePageHero.vue"
import ExpertiseRelatedPillars from "~/components/expertise/ExpertiseRelatedPillars.vue"
import ExpertiseRelatedResources from "~/components/expertise/ExpertiseRelatedResources.vue"
import ConcevoirExpertisePage from "~/components/expertise/concevoir/ConcevoirExpertisePage.vue"
import ConstruireExpertisePage from "~/components/expertise/construire/ConstruireExpertisePage.vue"
import FaireEvoluerExpertisePage from "~/components/expertise/faire-evoluer/FaireEvoluerExpertisePage.vue"
import ValoriserExpertisePage from "~/components/expertise/valoriser/ValoriserExpertisePage.vue"
import VisibiliteExpertisePage from "~/components/expertise/visibilite/VisibiliteExpertisePage.vue"
import SiteBreadcrumb from "~/components/layout/SiteBreadcrumb.vue"
import { expertisePages } from "~/config/expertise-pages"
import { buildCanonical } from "~/utils/canonical"
import { normalizeSiteUrl } from "~/utils/site-url"

/**
 * Route dynamique `/expertises/{slug}` — pages détaillées des cinq pôles
 * d'expertise (Phase 7B).
 *
 * Choix architectural : une route dynamique unique plutôt que cinq pages
 * statiques dupliquées. Justification :
 *   - les cinq pages partagent 100 % de leur squelette (hero + besoin +
 *     approche + livrables + bénéfices + pôles liés + callout) et ne
 *     diffèrent que par leurs données typées ;
 *   - toute évolution du gabarit s'applique alors uniformément ;
 *   - Nuxt pré-rend explicitement chaque slug via `routeRules`, ce qui
 *     produit le même HTML statique que des pages séparées ;
 *   - un slug inconnu retourne un 404 explicite via `createError`, sans
 *     fallback silencieux (règle 11 du référentiel : ne rien publier de
 *     placeholder).
 *
 * SEO :
 *   - `usePageSeo` pour title/description/canonical/OG/Twitter ;
 *   - `useExpertiseServiceSchema` pour un JSON-LD `Service` référençant
 *     l'`Organization` globale via `@id`.
 *
 * Accessibilité :
 *   - un unique H1 par page (porté par `ExpertisePageHero`) ;
 *   - un `<nav aria-label="Fil d'Ariane">` en tête, dernier item marqué
 *     `aria-current="page"` ;
 *   - les H2 sont portés par chaque `EditorialSection` via `useId()`.
 */

const route = useRoute()

const slugParam = computed(() => {
  const raw = route.params.slug
  return Array.isArray(raw) ? raw[0] : raw
})

const page = computed(() => {
  const slug = slugParam.value
  if (!slug || typeof slug !== "string") return undefined
  return expertisePages.find(
    (p) => p.slug === slug && p.status === "published",
  )
})

// 404 explicite pour tout slug inconnu ou non publié. `fatal: true`
// interrompt le rendu et déclenche la page d'erreur Nuxt avec le bon code
// HTTP côté SSR et côté sitemap.
if (!page.value) {
  throw createError({
    statusCode: 404,
    statusMessage: "Expertise introuvable",
    fatal: true,
  })
}

// À partir d'ici, `page.value` est garanti non-undefined par le guard 404.
// On tire une référence non-null typée pour éviter les `page.value?.` en
// cascade dans le template.
const resolvedPage = computed(() => page.value!)

const breadcrumbItems = computed(() => [
  { label: "Accueil", to: "/" },
  { label: "Expertises", to: "/expertises" },
  { label: resolvedPage.value.shortTitle },
])

usePageSeo({
  title: resolvedPage.value.seoTitle,
  description: resolvedPage.value.seoDescription,
  path: resolvedPage.value.route,
  type: "website",
})

useExpertiseServiceSchema({
  title: resolvedPage.value.title,
  description: resolvedPage.value.seoDescription,
  path: resolvedPage.value.route,
  serviceType: resolvedPage.value.shortTitle,
})

// BreadcrumbList JSON-LD — correspond au fil d'Ariane HTML visible (SiteBreadcrumb).
const config = useRuntimeConfig()
const siteUrl = config.public.siteUrl as string
const origin = normalizeSiteUrl(siteUrl)

useHead({
  script: [
    {
      id: "devzair-expertise-breadcrumb",
      type: "application/ld+json",
      innerHTML: () =>
        JSON.stringify({
          "@context": "https://schema.org",
          "@type": "BreadcrumbList",
          itemListElement: [
            {
              "@type": "ListItem",
              position: 1,
              name: "Accueil",
              item: origin,
            },
            {
              "@type": "ListItem",
              position: 2,
              name: "Expertises",
              item: buildCanonical({ siteUrl, path: "/expertises" }),
            },
            {
              "@type": "ListItem",
              position: 3,
              name: resolvedPage.value.shortTitle,
              item: buildCanonical({ siteUrl, path: resolvedPage.value.route }),
            },
          ],
        }),
    },
  ],
})

// Direction visuelle propre à chaque pôle : les cinq pôles publiés basculent
// désormais tous sur leur gabarit dédié — `Concevoir` (Digital Blueprint),
// `Construire` (Product Assembly), `Valoriser` (Editorial Studio),
// `Visibilité` (Search Territory / Signal Map) et `Faire évoluer` (Living
// System / Continuous Care). Le fallback générique reste en place comme
// filet de sécurité pour un futur pôle avant refonte dédiée.
// Ce branch conserve `[slug].vue` en simple aiguilleur : SEO, JSON-LD,
// breadcrumb et guard 404 sont posés en amont, jamais dupliqués.
const isConcevoir = computed(() => resolvedPage.value.id === "concevoir")
const isConstruire = computed(() => resolvedPage.value.id === "construire")
const isValoriser = computed(() => resolvedPage.value.id === "valoriser")
const isVisibilite = computed(() => resolvedPage.value.id === "visibilite")
const isFaireEvoluer = computed(() => resolvedPage.value.id === "faire-evoluer")
</script>

<template>
  <div class="expertise-page">
    <SiteBreadcrumb :items="breadcrumbItems" />

    <ConcevoirExpertisePage
      v-if="isConcevoir"
      :page="resolvedPage"
    />

    <ConstruireExpertisePage
      v-else-if="isConstruire"
      :page="resolvedPage"
    />

    <ValoriserExpertisePage
      v-else-if="isValoriser"
      :page="resolvedPage"
    />

    <VisibiliteExpertisePage
      v-else-if="isVisibilite"
      :page="resolvedPage"
    />

    <FaireEvoluerExpertisePage
      v-else-if="isFaireEvoluer"
      :page="resolvedPage"
    />

    <template v-else>
      <ExpertisePageHero
        :eyebrow="resolvedPage.eyebrow"
        :title="resolvedPage.title"
        :introduction="resolvedPage.introduction"
      />

      <EditorialSection
        tone="subtle"
        eyebrow="À qui cela s'adresse"
        :title="resolvedPage.needTitle"
      >
        <p class="expertise-page__paragraph">
          {{ resolvedPage.needDescription }}
        </p>
      </EditorialSection>

      <EditorialSection
        tone="default"
        eyebrow="Notre approche"
        :title="resolvedPage.approachTitle"
      >
        <p class="expertise-page__paragraph">
          {{ resolvedPage.approachDescription }}
        </p>
      </EditorialSection>

      <EditorialSection
        tone="subtle"
        eyebrow="Prestations"
        title="Les livrables associés à ce pôle."
        intro="Chaque livrable est cadré au démarrage. Le périmètre exact est arbitré avec vous en fonction de vos objectifs et de votre calendrier."
      >
        <ExpertiseDeliverables :deliverables="resolvedPage.deliverables" />
      </EditorialSection>

      <EditorialSection
        tone="default"
        eyebrow="Bénéfices concrets"
        title="Ce que vous obtenez à l'issue du projet."
        intro="Des résultats vérifiables, décrits sans effet de manche. Nous ne promettons ni chiffre ni délai qu'un travail digital ne peut sérieusement garantir."
      >
        <ExpertiseBenefits :benefits="resolvedPage.benefits" />
      </EditorialSection>

      <EditorialSection
        tone="subtle"
        eyebrow="Aller plus loin"
        title="Pôles connexes à explorer."
        intro="Ce pôle prend tout son sens articulé avec d'autres domaines. Voici deux pôles qui se combinent naturellement avec celui-ci."
      >
        <ExpertiseRelatedPillars :pillar-ids="resolvedPage.relatedPillarIds" />
      </EditorialSection>

      <ExpertiseRelatedResources
        :expertise-id="resolvedPage.id"
        :expertise-label="resolvedPage.shortTitle"
      />

      <EditorialCallout
        eyebrow="Parler de votre projet"
        title="Un besoin sur ce pôle ? Racontez-nous votre contexte."
        description="Un échange court suffit pour cadrer si ce pôle correspond à votre situation et quelles étapes seraient utiles."
        :primary="{ label: 'Nous contacter', to: '/contact' }"
        :secondary="{ label: 'Voir tous les pôles', to: '/expertises' }"
      />
    </template>
  </div>
</template>

<style scoped>
.expertise-page {
  display: flex;
  flex-direction: column;
}

.expertise-page__paragraph {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}
</style>
