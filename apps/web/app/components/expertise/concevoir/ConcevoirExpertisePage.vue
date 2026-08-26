<script setup lang="ts">
import ExpertiseRelatedResources from "~/components/expertise/ExpertiseRelatedResources.vue"
import ConcevoirBenefits from "~/components/expertise/concevoir/ConcevoirBenefits.vue"
import ConcevoirDecisionSection from "~/components/expertise/concevoir/ConcevoirDecisionSection.vue"
import ConcevoirDeliverablesBoard from "~/components/expertise/concevoir/ConcevoirDeliverablesBoard.vue"
import ConcevoirHero from "~/components/expertise/concevoir/ConcevoirHero.vue"
import ConcevoirProcessMap from "~/components/expertise/concevoir/ConcevoirProcessMap.vue"
import ConcevoirProjectCallout from "~/components/expertise/concevoir/ConcevoirProjectCallout.vue"
import ConcevoirRelatedPillars from "~/components/expertise/concevoir/ConcevoirRelatedPillars.vue"
import type { ExpertisePageDefinition } from "~/config/expertise-pages"

/**
 * Orchestrateur de la page dédiée `/expertises/concevoir`.
 *
 * Rôle : composer les sections spécifiques à la direction « Digital
 * Blueprint » — hero + carte de décision + processus 5 étapes + livrables
 * en board + bénéfices + ressources liées (générique) + pôles connexes +
 * callout final.
 *
 * Le composant NE fait AUCUN appel réseau et NE définit AUCUN SEO :
 *   - le SEO (title/description/canonical) et le JSON-LD Service sont
 *     posés dans `pages/expertises/[slug].vue` avant le rendu ;
 *   - les données éditoriales viennent en props depuis la page qui
 *     référence `expertise-pages.ts` — pas d'import direct ici pour
 *     éviter le couplage.
 *
 * Contrat : `page.id === "concevoir"` — garanti par le branch appelant.
 */

interface Props {
  page: ExpertisePageDefinition
}

defineProps<Props>()
</script>

<template>
  <div class="concevoir-page">
    <ConcevoirHero
      :eyebrow="page.eyebrow"
      :title="page.title"
      :introduction="page.introduction"
    />

    <ConcevoirDecisionSection
      :need-title="page.needTitle"
      :need-description="page.needDescription"
      :approach-title="page.approachTitle"
      :approach-description="page.approachDescription"
    />

    <ConcevoirProcessMap />

    <ConcevoirDeliverablesBoard :deliverables="page.deliverables" />

    <ConcevoirBenefits :benefits="page.benefits" />

    <ConcevoirRelatedPillars :pillar-ids="page.relatedPillarIds" />

    <ExpertiseRelatedResources
      :expertise-id="page.id"
      :expertise-label="page.shortTitle"
    />

    <ConcevoirProjectCallout />
  </div>
</template>

<style scoped>
.concevoir-page {
  display: flex;
  flex-direction: column;
}
</style>
