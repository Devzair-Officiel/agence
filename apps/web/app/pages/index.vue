<script setup lang="ts">
import HomeConnectedApproach from "~/components/home/HomeConnectedApproach.vue"
import HomeExpertisePillars from "~/components/home/HomeExpertisePillars.vue"
import HomeFeaturedCaseStudy from "~/components/home/HomeFeaturedCaseStudy.vue"
import HomeHero from "~/components/home/HomeHero.vue"
import HomeProblems from "~/components/home/HomeProblems.vue"
import HomeProcess from "~/components/home/HomeProcess.vue"
import HomeTrust from "~/components/home/HomeTrust.vue"

/**
 * Page d'accueil publique — orchestration uniquement.
 * Le contenu vit dans les composants `Home*` ; la page se contente de composer
 * les sections dans l'ordre attendu et de déclarer le SEO via le composable
 * dédié.
 *
 * Ordre éditorial (source `docs/01-CONTENT.md §7.1`) :
 *   1. Hero
 *   2. Constat            (HomeProblems)
 *   3. Réponse            (HomeConnectedApproach)
 *   4. Cinq pôles         (HomeExpertisePillars, ancre `#expertises`)
 *   5. Réalisations       (HomeFeaturedCaseStudy, ancre `#realisations`)
 *   6. Méthode            (HomeProcess)
 *   7. Pourquoi Devzair   (HomeTrust)
 *
 * Sections différées : FAQ éditoriale, CTA final, ancre `#contact`. Le CTA
 * hero primaire (`#contact`) restera inactif jusqu'à la Phase 5D.
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
    <HomeFeaturedCaseStudy />
    <HomeProcess />
    <HomeTrust />
  </div>
</template>
