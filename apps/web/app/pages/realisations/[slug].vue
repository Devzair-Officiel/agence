<script setup lang="ts">
import { computed } from "vue"
import SiteBreadcrumb from "~/components/layout/SiteBreadcrumb.vue"
import AlMumayizPage from "~/components/realisations/case-studies/AlMumayizPage.vue"
import HaramainPrestigePage from "~/components/realisations/case-studies/HaramainPrestigePage.vue"
import KitchenMeatPage from "~/components/realisations/case-studies/KitchenMeatPage.vue"
import MizanPage from "~/components/realisations/case-studies/MizanPage.vue"
import NidemielPage from "~/components/realisations/case-studies/NidemielPage.vue"
import { caseStudies } from "~/config/case-studies"

/**
 * Route dynamique `/realisations/[slug]` — aiguilleur vers les quatre études
 * de cas publiées (SEO-COM-4).
 *
 * Choix architectural : même dispatcher pattern que `/expertises/[slug].vue`.
 *   - guard 404 pour tout slug inconnu ou `status !== "published"` ;
 *   - SEO (usePageSeo) et BreadcrumbList (useBreadcrumb) posés une seule
 *     fois ici — jamais dupliqués dans les composants filles ;
 *   - chaque composant fille ne porte que son contenu éditorial.
 *
 * Schema.org : BreadcrumbList uniquement (via useBreadcrumb).
 * Décision documentée : ni `CreativeWork`, ni `Review`, ni `Service` —
 * aucun de ces types ne décrit fidèlement une étude de cas d'agence web
 * sans risquer d'inventer des attributs (author, datePublished, rating…).
 *
 * Prerender : nuxt.config.ts énumère explicitement les cinq routes publiées.
 */

const route = useRoute()

const slugParam = computed(() => {
  const raw = route.params.slug
  return Array.isArray(raw) ? raw[0] : raw
})

const study = computed(() => {
  const slug = slugParam.value
  if (!slug || typeof slug !== "string") return undefined
  return caseStudies.find(
    (cs) => cs.slug === slug && cs.status === "published",
  )
})

if (!study.value) {
  throw createError({
    statusCode: 404,
    statusMessage: "Réalisation introuvable",
    fatal: true,
  })
}

const resolvedStudy = computed(() => study.value!)

const breadcrumbItems = computed(() => [
  { label: "Accueil", to: "/" },
  { label: "Réalisations", to: "/realisations" },
  { label: resolvedStudy.value.name },
])

usePageSeo({
  title: resolvedStudy.value.seoTitle,
  description: resolvedStudy.value.seoDescription,
  path: resolvedStudy.value.route,
  type: "website",
})

useBreadcrumb(breadcrumbItems.value)

const isKitchenMeat = computed(() => resolvedStudy.value.id === "kitchen-meat")
const isNidemiel = computed(() => resolvedStudy.value.id === "nidemiel")
const isMizan = computed(() => resolvedStudy.value.id === "mizan")
const isHaramainPrestige = computed(
  () => resolvedStudy.value.id === "haramain-prestige",
)
const isAlMumayiz = computed(() => resolvedStudy.value.id === "al-mumayiz")
</script>

<template>
  <div class="realisation-page">
    <SiteBreadcrumb :items="breadcrumbItems" />

    <KitchenMeatPage v-if="isKitchenMeat" :study="resolvedStudy" />
    <NidemielPage v-else-if="isNidemiel" :study="resolvedStudy" />
    <MizanPage v-else-if="isMizan" :study="resolvedStudy" />
    <HaramainPrestigePage v-else-if="isHaramainPrestige" :study="resolvedStudy" />
    <AlMumayizPage v-else-if="isAlMumayiz" :study="resolvedStudy" />
  </div>
</template>

<style scoped>
.realisation-page {
  display: flex;
  flex-direction: column;
}
</style>
