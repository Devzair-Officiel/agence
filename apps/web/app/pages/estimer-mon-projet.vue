<script setup lang="ts">
import EstimatorHero from "~/components/estimator/EstimatorHero.vue"
import EstimatorShell from "~/components/estimator/EstimatorShell.vue"

usePageSeo({
  title: "Estimer mon projet digital",
  description:
    "Estimez le budget de votre projet digital en quelques minutes. Site vitrine, e-commerce, application métier : obtenez une première fourchette sans engagement.",
  path: "/estimer-mon-projet",
  noindex: false,
})
</script>

<template>
  <main class="estimator-page" data-hydrated="false">
    <EstimatorHero />
    <EstimatorShell />
  </main>
</template>

<style scoped>
.estimator-page {
  min-height: calc(100dvh - var(--site-header-height));
}
</style>
