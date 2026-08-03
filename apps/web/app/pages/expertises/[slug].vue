<script setup lang="ts">
import { computed } from "vue"
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import EditorialSection from "~/components/editorial/EditorialSection.vue"
import ExpertiseBenefits from "~/components/expertise/ExpertiseBenefits.vue"
import ExpertiseDeliverables from "~/components/expertise/ExpertiseDeliverables.vue"
import ExpertisePageHero from "~/components/expertise/ExpertisePageHero.vue"
import ExpertiseRelatedPillars from "~/components/expertise/ExpertiseRelatedPillars.vue"
import SiteBreadcrumb from "~/components/layout/SiteBreadcrumb.vue"
import { expertisePages } from "~/config/expertise-pages"

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
</script>

<template>
  <div class="expertise-page">
    <SiteBreadcrumb :items="breadcrumbItems" />

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

    <EditorialCallout
      eyebrow="Parler de votre projet"
      title="Un besoin sur ce pôle ? Racontez-nous votre contexte."
      description="Un échange court suffit pour cadrer si ce pôle correspond à votre situation et quelles étapes seraient utiles."
      :primary="{ label: 'Nous contacter', to: '/#contact', external: true }"
      :secondary="{ label: 'Voir tous les pôles', to: '/expertises' }"
    />
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
