<script setup lang="ts">
import { computed } from "vue"
import ApplicationWebMetierPage from "~/components/services/application-web-metier/ApplicationWebMetierPage.vue"
import CreationSiteInternetPage from "~/components/services/creation-site-internet/CreationSiteInternetPage.vue"
import SiteEcommercePage from "~/components/services/site-e-commerce/SiteEcommercePage.vue"
import SiteBreadcrumb from "~/components/layout/SiteBreadcrumb.vue"
import { servicePages } from "~/config/service-pages"

/**
 * Route dynamique `/services/{slug}` — pages détaillées des services commerciaux.
 *
 * Choix architectural : route unique qui dispatche vers un composant par service
 * publié, calqué sur `pages/expertises/[slug].vue`.
 *
 * Guard 404 : tout slug non publié (planned ou inexistant) retourne un 404
 * explicite via `createError({ fatal: true })` — aucune page placeholder
 * ne peut fuiter (règle 11 du référentiel).
 *
 * SEO : `usePageSeo` pour title/description/canonical/OG, `useServiceSchema`
 * pour le JSON-LD Service, `useBreadcrumb` pour le JSON-LD BreadcrumbList.
 * Ces trois appels sont posés dans ce dispatcher — jamais dupliqués dans
 * les composants fils.
 */

const route = useRoute()

const slugParam = computed(() => {
  const raw = route.params.slug
  return Array.isArray(raw) ? raw[0] : raw
})

const page = computed(() => {
  const slug = slugParam.value
  if (!slug || typeof slug !== "string") return undefined
  return servicePages.find((p) => p.slug === slug && p.status === "published")
})

if (!page.value) {
  throw createError({
    statusCode: 404,
    statusMessage: "Service introuvable",
    fatal: true,
  })
}

const resolvedPage = computed(() => page.value!)

const breadcrumbItems = computed(() => [
  { label: "Accueil", to: "/" },
  { label: "Services", to: "/services" },
  { label: resolvedPage.value.shortTitle },
])

usePageSeo({
  title: resolvedPage.value.seoTitle,
  description: resolvedPage.value.seoDescription,
  path: resolvedPage.value.route,
  type: "website",
})

useServiceSchema({
  title: resolvedPage.value.title,
  description: resolvedPage.value.seoDescription,
  path: resolvedPage.value.route,
  serviceType: resolvedPage.value.shortTitle,
})

useBreadcrumb(breadcrumbItems.value)

const isCreationSiteInternet = computed(() => resolvedPage.value.id === "creation-site-internet")
const isSiteEcommerce = computed(() => resolvedPage.value.id === "site-e-commerce")
const isApplicationWebMetier = computed(() => resolvedPage.value.id === "application-web-metier")
</script>

<template>
  <div class="service-page">
    <SiteBreadcrumb :items="breadcrumbItems" />

    <CreationSiteInternetPage
      v-if="isCreationSiteInternet"
      :page="resolvedPage"
    />
    <SiteEcommercePage
      v-else-if="isSiteEcommerce"
      :page="resolvedPage"
    />
    <ApplicationWebMetierPage
      v-else-if="isApplicationWebMetier"
      :page="resolvedPage"
    />
  </div>
</template>

<style scoped>
.service-page {
  display: flex;
  flex-direction: column;
}
</style>
