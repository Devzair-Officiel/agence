<script setup lang="ts">
import HomeConnectedApproach from "~/components/home/HomeConnectedApproach.vue"
import HomeExpertisePillars from "~/components/home/HomeExpertisePillars.vue"
import HomeHero from "~/components/home/HomeHero.vue"
import HomeProblems from "~/components/home/HomeProblems.vue"

/**
 * Page d'accueil publique — orchestration uniquement.
 * Le contenu vit dans les composants `Home*` ; la page se contente de composer
 * les sections dans l'ordre attendu et de déclarer le SEO via le composable
 * dédié.
 *
 * Ordre éditorial (source `docs/01-CONTENT.md §7.1`) :
 *   1. Hero
 *   2. Constat  (HomeProblems)
 *   3. Réponse  (HomeConnectedApproach)
 *   4. Cinq pôles détaillés  (HomeExpertisePillars, ancre `#expertises`)
 *
 * Sections différées : réalisations, méthode, pourquoi Devzair, FAQ, CTA final.
 * Les CTA hero (`#contact`, `#realisations`) restent des ancres inactives tant
 * que ces sections n'existent pas — remplaçables en une ligne.
 */

usePageSeo({
  title: "Agence digitale pour sites web, applications et visibilité",
  description:
    "Devzair accompagne les entreprises dans la création de sites internet, applications métier, identités visuelles, contenus professionnels et stratégies SEO sur mesure.",
  path: "/",
  type: "website",
})
</script>

<template>
  <div class="home-page">
    <HomeHero />
    <HomeProblems />
    <HomeConnectedApproach />
    <HomeExpertisePillars />
  </div>
</template>
