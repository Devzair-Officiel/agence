<script setup lang="ts">
import ExpertiseRelatedResources from "~/components/expertise/ExpertiseRelatedResources.vue"
import ConstruireBuildFlow from "~/components/expertise/construire/ConstruireBuildFlow.vue"
import ConstruireDeliverables from "~/components/expertise/construire/ConstruireDeliverables.vue"
import ConstruireHero from "~/components/expertise/construire/ConstruireHero.vue"
import ConstruireNeedSection from "~/components/expertise/construire/ConstruireNeedSection.vue"
import ConstruireProjectCallout from "~/components/expertise/construire/ConstruireProjectCallout.vue"
import ConstruireQuality from "~/components/expertise/construire/ConstruireQuality.vue"
import ConstruireRelatedPillars from "~/components/expertise/construire/ConstruireRelatedPillars.vue"
import type { ExpertisePageDefinition } from "~/config/expertise-pages"

/**
 * Orchestrateur de la page dédiée `/expertises/construire`.
 *
 * Rôle : composer les sections spécifiques à la direction « Product
 * Assembly » — hero technique + section audience + pipeline de fabrication
 * + livrables asymétriques + contrôles qualité + ressources liées
 * (générique) + cycle des pôles + callout final.
 *
 * Le composant NE fait AUCUN appel réseau et NE définit AUCUN SEO :
 *   - le SEO (title/description/canonical) et le JSON-LD Service sont
 *     posés dans `pages/expertises/[slug].vue` avant le rendu ;
 *   - les données éditoriales viennent en props depuis la page qui
 *     référence `expertise-pages.ts` — pas d'import direct ici pour
 *     éviter le couplage.
 *
 * Contrat : `page.id === "construire"` — garanti par le branch appelant.
 */

interface Props {
  page: ExpertisePageDefinition
}

defineProps<Props>()
</script>

<template>
  <div class="construire-page">
    <ConstruireHero
      :eyebrow="page.eyebrow"
      :title="page.title"
      :introduction="page.introduction"
    />

    <ConstruireNeedSection :need-description="page.needDescription" />

    <ConstruireBuildFlow :approach-description="page.approachDescription" />

    <ConstruireDeliverables :deliverables="page.deliverables" />

    <ConstruireQuality :benefits="page.benefits" />

    <ConstruireRelatedPillars :pillar-ids="page.relatedPillarIds" />

    <ExpertiseRelatedResources
      :expertise-id="page.id"
      :expertise-label="page.shortTitle"
    />

    <ConstruireProjectCallout />
  </div>
</template>

<style scoped>
.construire-page {
  display: flex;
  flex-direction: column;
}
</style>
