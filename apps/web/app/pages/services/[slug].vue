<script setup lang="ts">
import type { Component } from "vue"
import ApplicationWebMetierPage from "~/components/services/application-web-metier/ApplicationWebMetierPage.vue"
import CreationSiteInternetPage from "~/components/services/creation-site-internet/CreationSiteInternetPage.vue"
import DesignUiUxIdentiteVisuellePage from "~/components/services/design-ui-ux-identite-visuelle/DesignUiUxIdentiteVisuellePage.vue"
import MaintenanceAccompagnementPage from "~/components/services/maintenance-accompagnement/MaintenanceAccompagnementPage.vue"
import PhotographieCreationContenuPage from "~/components/services/photographie-creation-contenu/PhotographieCreationContenuPage.vue"
import SeoReferencementNaturelPage from "~/components/services/seo-referencement-naturel/SeoReferencementNaturelPage.vue"
import SiteEcommercePage from "~/components/services/site-e-commerce/SiteEcommercePage.vue"
import VisibiliteLocalePage from "~/components/services/visibilite-locale/VisibiliteLocalePage.vue"
import SiteBreadcrumb from "~/components/layout/SiteBreadcrumb.vue"
import { servicePages } from "~/config/service-pages"

/**
 * Route dynamique `/services/{slug}` — pages détaillées des services commerciaux.
 *
 * Dispatcher via table typée `componentMap` : plus lisible à 8 composants
 * qu'une cascade v-if / v-else-if. Comportement identique.
 *
 * Guard 404 : tout slug non publié (planned ou inexistant) retourne un 404
 * explicite via `createError({ fatal: true })`.
 *
 * SEO : `usePageSeo`, `useServiceSchema`, `useBreadcrumb` posés ici —
 * jamais dupliqués dans les composants fils.
 */

const componentMap: Record<string, Component> = {
  "creation-site-internet": CreationSiteInternetPage,
  "site-e-commerce": SiteEcommercePage,
  "application-web-metier": ApplicationWebMetierPage,
  "design-ui-ux-identite-visuelle": DesignUiUxIdentiteVisuellePage,
  "photographie-creation-contenu": PhotographieCreationContenuPage,
  "seo-referencement-naturel": SeoReferencementNaturelPage,
  "visibilite-locale": VisibiliteLocalePage,
  "maintenance-accompagnement": MaintenanceAccompagnementPage,
}

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
const currentComponent = computed(() => componentMap[resolvedPage.value.id])

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
</script>

<template>
  <div class="service-page">
    <SiteBreadcrumb :items="breadcrumbItems" />
    <component
      :is="currentComponent"
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
