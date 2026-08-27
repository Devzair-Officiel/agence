<script setup lang="ts">
import ExpertiseRelatedResources from "~/components/expertise/ExpertiseRelatedResources.vue"
import FaireEvoluerCadence from "~/components/expertise/faire-evoluer/FaireEvoluerCadence.vue"
import FaireEvoluerContinuousLoop from "~/components/expertise/faire-evoluer/FaireEvoluerContinuousLoop.vue"
import FaireEvoluerHero from "~/components/expertise/faire-evoluer/FaireEvoluerHero.vue"
import FaireEvoluerNeedSection from "~/components/expertise/faire-evoluer/FaireEvoluerNeedSection.vue"
import FaireEvoluerPrinciples from "~/components/expertise/faire-evoluer/FaireEvoluerPrinciples.vue"
import FaireEvoluerProjectCallout from "~/components/expertise/faire-evoluer/FaireEvoluerProjectCallout.vue"
import FaireEvoluerRelatedPillars from "~/components/expertise/faire-evoluer/FaireEvoluerRelatedPillars.vue"
import type { ExpertisePageDefinition } from "~/config/expertise-pages"

/**
 * Orchestrateur de la page dédiée `/expertises/faire-evoluer`.
 *
 * Direction « Living System / Continuous Care » — un produit numérique
 * vivant se maintient, s'observe et s'améliore. La composition évoque un
 * cycle plutôt qu'une timeline : hero orbital cream/petrol → trois phases
 * cycliques (MAINTENIR / COMPRENDRE / ADAPTER) → boucle méthode
 * (OBSERVER → RÉÉVALUER qui reconnecte à OBSERVER) → cadence des cinq
 * livrables → principes SANTÉ / CLARTÉ / RYTHME → flux amont Construire +
 * Visibilité vers Faire évoluer (nœud central) → ressources → callout final.
 *
 * Le composant NE fait AUCUN appel réseau et NE définit AUCUN SEO :
 *   - le SEO (title/description/canonical) et le JSON-LD Service sont
 *     posés dans `pages/expertises/[slug].vue` avant le rendu ;
 *   - les données éditoriales viennent en props depuis la page qui
 *     référence `expertise-pages.ts` — pas d'import direct ici pour
 *     éviter le couplage.
 *
 * Contrat : `page.id === "faire-evoluer"` — garanti par le branch appelant.
 */

interface Props {
  page: ExpertisePageDefinition
}

defineProps<Props>()
</script>

<template>
  <div class="faire-evoluer-page">
    <FaireEvoluerHero
      :eyebrow="page.eyebrow"
      :title="page.title"
      :introduction="page.introduction"
    />

    <FaireEvoluerNeedSection :need-description="page.needDescription" />

    <FaireEvoluerContinuousLoop
      :approach-description="page.approachDescription"
    />

    <FaireEvoluerCadence :deliverables="page.deliverables" />

    <FaireEvoluerPrinciples :benefits="page.benefits" />

    <FaireEvoluerRelatedPillars :pillar-ids="page.relatedPillarIds" />

    <ExpertiseRelatedResources
      :expertise-id="page.id"
      :expertise-label="page.shortTitle"
    />

    <FaireEvoluerProjectCallout />
  </div>
</template>

<style scoped>
.faire-evoluer-page {
  display: flex;
  flex-direction: column;
}
</style>
