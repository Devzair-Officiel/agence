<script setup lang="ts">
import { servicePages } from "~/config/service-pages"

/**
 * Route garde pour `/services/{slug}`.
 *
 * Retourne un 404 explicite pour tout slug dont le service correspondant
 * est `planned` ou inconnu. Cette page n'a pas encore de contenu éditorial :
 * le contenu sera ajouté service par service à partir de SEO-COM-2.
 *
 * Règle : une page `planned` ne doit jamais être accessible comme route
 * publique (AGENTS.md §11 + docs/13-SEO-COMMERCIAL.md §5).
 */

const route = useRoute()
const slug = route.params.slug as string

const page = servicePages.find((p) => p.slug === slug)

if (!page || page.status !== "published") {
  throw createError({ statusCode: 404, fatal: true })
}
</script>

<template>
  <div />
</template>
