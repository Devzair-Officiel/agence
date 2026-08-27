<script setup lang="ts">
import ExpertiseRelatedResources from "~/components/expertise/ExpertiseRelatedResources.vue"
import ValoriserContentKit from "~/components/expertise/valoriser/ValoriserContentKit.vue"
import ValoriserEditorialFlow from "~/components/expertise/valoriser/ValoriserEditorialFlow.vue"
import ValoriserHero from "~/components/expertise/valoriser/ValoriserHero.vue"
import ValoriserNeedSection from "~/components/expertise/valoriser/ValoriserNeedSection.vue"
import ValoriserPrinciples from "~/components/expertise/valoriser/ValoriserPrinciples.vue"
import ValoriserProjectCallout from "~/components/expertise/valoriser/ValoriserProjectCallout.vue"
import ValoriserRelatedPillars from "~/components/expertise/valoriser/ValoriserRelatedPillars.vue"
import type { ExpertisePageDefinition } from "~/config/expertise-pages"

/**
 * Orchestrateur de la page dédiée `/expertises/valoriser`.
 *
 * Rôle : composer les sections spécifiques à la direction « Editorial
 * Studio » — hero cream + section audience éditoriale asymétrique + frise
 * de méthode 5 étapes + content kit + principes VOIX/IMAGE/CLARTÉ + cycle
 * des pôles connexes + ressources liées (générique) + callout final.
 *
 * Le composant NE fait AUCUN appel réseau et NE définit AUCUN SEO :
 *   - le SEO (title/description/canonical) et le JSON-LD Service sont
 *     posés dans `pages/expertises/[slug].vue` avant le rendu ;
 *   - les données éditoriales viennent en props depuis la page qui
 *     référence `expertise-pages.ts` — pas d'import direct ici pour
 *     éviter le couplage.
 *
 * Contrat : `page.id === "valoriser"` — garanti par le branch appelant.
 */

interface Props {
  page: ExpertisePageDefinition
}

defineProps<Props>()
</script>

<template>
  <div class="valoriser-page">
    <ValoriserHero
      :eyebrow="page.eyebrow"
      :title="page.title"
      :introduction="page.introduction"
    />

    <ValoriserNeedSection :need-description="page.needDescription" />

    <ValoriserEditorialFlow :approach-description="page.approachDescription" />

    <ValoriserContentKit :deliverables="page.deliverables" />

    <ValoriserPrinciples :benefits="page.benefits" />

    <ValoriserRelatedPillars :pillar-ids="page.relatedPillarIds" />

    <ExpertiseRelatedResources
      :expertise-id="page.id"
      :expertise-label="page.shortTitle"
    />

    <ValoriserProjectCallout />
  </div>
</template>

<style scoped>
.valoriser-page {
  display: flex;
  flex-direction: column;
}
</style>
