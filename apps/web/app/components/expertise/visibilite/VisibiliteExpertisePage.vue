<script setup lang="ts">
import ExpertiseRelatedResources from "~/components/expertise/ExpertiseRelatedResources.vue"
import VisibiliteHero from "~/components/expertise/visibilite/VisibiliteHero.vue"
import VisibiliteLayers from "~/components/expertise/visibilite/VisibiliteLayers.vue"
import VisibiliteNeedSection from "~/components/expertise/visibilite/VisibiliteNeedSection.vue"
import VisibilitePrinciples from "~/components/expertise/visibilite/VisibilitePrinciples.vue"
import VisibiliteProjectCallout from "~/components/expertise/visibilite/VisibiliteProjectCallout.vue"
import VisibiliteRelatedPillars from "~/components/expertise/visibilite/VisibiliteRelatedPillars.vue"
import VisibiliteSignalPath from "~/components/expertise/visibilite/VisibiliteSignalPath.vue"
import type { ExpertisePageDefinition } from "~/config/expertise-pages"

/**
 * Orchestrateur de la page dédiée `/expertises/visibilite`.
 *
 * Rôle : composer les sections spécifiques à la direction « Search
 * Territory / Signal Map » — hero cream/petrol + zones d'audience
 * cartographiques + parcours signal (5 étapes) + couches de visibilité
 * (5 livrables) + principes DURÉE/PERTINENCE/PROXIMITÉ + cycle de pôles
 * connexes (Valoriser → Visibilité → Faire évoluer) + ressources liées
 * (générique) + callout final horizontal.
 *
 * Le composant NE fait AUCUN appel réseau et NE définit AUCUN SEO :
 *   - le SEO (title/description/canonical) et le JSON-LD Service sont
 *     posés dans `pages/expertises/[slug].vue` avant le rendu ;
 *   - les données éditoriales viennent en props depuis la page qui
 *     référence `expertise-pages.ts` — pas d'import direct ici pour
 *     éviter le couplage.
 *
 * Contrat : `page.id === "visibilite"` — garanti par le branch appelant.
 */

interface Props {
  page: ExpertisePageDefinition
}

defineProps<Props>()
</script>

<template>
  <div class="visibilite-page">
    <VisibiliteHero
      :eyebrow="page.eyebrow"
      :title="page.title"
      :introduction="page.introduction"
    />

    <VisibiliteNeedSection :need-description="page.needDescription" />

    <VisibiliteSignalPath :approach-description="page.approachDescription" />

    <VisibiliteLayers :deliverables="page.deliverables" />

    <VisibilitePrinciples :benefits="page.benefits" />

    <VisibiliteRelatedPillars :pillar-ids="page.relatedPillarIds" />

    <ExpertiseRelatedResources
      :expertise-id="page.id"
      :expertise-label="page.shortTitle"
    />

    <VisibiliteProjectCallout />
  </div>
</template>

<style scoped>
.visibilite-page {
  display: flex;
  flex-direction: column;
}
</style>
