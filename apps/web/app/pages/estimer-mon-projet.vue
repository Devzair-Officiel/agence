<script setup lang="ts">
import EstimatorHero from "~/components/estimator/EstimatorHero.vue"
import EstimatorShell from "~/components/estimator/EstimatorShell.vue"
import EstimatorEditorial from "~/components/estimator/EstimatorEditorial.vue"

usePageSeo({
  title: "Budget projet web : estimez votre fourchette en quelques questions",
  description:
    "Quel budget pour votre site internet, boutique ou application web ? Quelques questions pour obtenir une première fourchette réaliste. Sans engagement.",
  path: "/estimer-mon-projet",
  noindex: false,
})
</script>

<template>
  <main class="estimator-page" data-hydrated="false">
    <EstimatorHero />
    <EstimatorShell />
    <EstimatorEditorial />
  </main>
</template>

<style scoped>
.estimator-page {
  min-height: calc(100dvh - var(--site-header-height));
}
</style>
