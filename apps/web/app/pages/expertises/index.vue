<script setup lang="ts">
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import EditorialHero from "~/components/editorial/EditorialHero.vue"
import EditorialSection from "~/components/editorial/EditorialSection.vue"
import ExpertiseOverviewCard from "~/components/expertise/ExpertiseOverviewCard.vue"
import { expertisePillars } from "~/config/expertise-pillars"

/**
 * Page `/expertises` — vue d'ensemble des cinq pôles d'expertise (Phase 7A).
 *
 * Rôle d'orchestration :
 *   - un H1 unique porté par EditorialHero ;
 *   - un EditorialSection narratif rappelant l'approche connectée (pas de
 *     duplication de HomeConnectedApproach : on ne réutilise pas la mise en
 *     page « parcours numéroté » de l'accueil, on cadre le sujet avec un
 *     texte court avant la grille) ;
 *   - une grille de cinq ExpertiseOverviewCard alimentée par la source de
 *     vérité `expertisePillars`. Aucun lien vers `/expertises/{slug}` tant
 *     que la Phase 7B n'a pas livré les pages filles (voir
 *     `app/config/expertise-pages.ts` — toutes les entrées ont
 *     `status: "planned"`).
 *   - un EditorialCallout de pont vers `/agence` + CTA `/#contact`.
 *
 * SEO : `usePageSeo` seul, canonical absolu dérivé de siteUrl + `/expertises`.
 * Prerender : la page est marquée `prerender: true` dans `nuxt.config.ts`.
 */

usePageSeo({
  title: "Expertises web, design, contenu et SEO",
  description:
    "Découvrez les cinq pôles d'expertise Devzair : stratégie et design, développement web, contenus, visibilité SEO, maintenance et évolution.",
  path: "/expertises",
  type: "website",
})
</script>

<template>
  <div class="expertises-page">
    <EditorialHero
      eyebrow="Nos expertises"
      title="Cinq pôles complémentaires pour construire une présence digitale cohérente."
      lead="Chaque entreprise possède des besoins différents. Nous réunissons les expertises adaptées pour concevoir, construire, valoriser, rendre visible et faire évoluer votre projet."
    />

    <EditorialSection
      tone="subtle"
      eyebrow="Notre approche"
      title="Des pôles pensés comme un ensemble, pas comme des prestations isolées."
      intro="Design, développement, contenu et visibilité s'alimentent mutuellement. Traiter un pôle sans les autres génère des angles morts — nous les rendons visibles dès le cadrage."
    >
      <p class="expertises-page__paragraph">
        Selon votre besoin, un projet peut mobiliser un seul pôle ou plusieurs.
        Le cadrage initial définit précisément ce qui est utile, ce qui peut
        être différé et ce qui n'a pas de raison d'être dans votre contexte.
      </p>
    </EditorialSection>

    <EditorialSection
      tone="default"
      eyebrow="Les cinq pôles"
      title="Cinq domaines d'expertise, un même standard éditorial et technique."
      intro="Chaque carte présente le rôle du pôle et les prestations associées. Les pages détaillées de chaque pôle seront publiées progressivement."
    >
      <ul class="expertises-page__grid" role="list">
        <li
          v-for="pillar in expertisePillars"
          :key="pillar.id"
          class="expertises-page__grid-item"
        >
          <ExpertiseOverviewCard :pillar="pillar" />
        </li>
      </ul>
    </EditorialSection>

    <EditorialCallout
      eyebrow="Aller plus loin"
      title="Découvrez comment nous travaillons."
      description="La page Agence présente notre positionnement, notre fonctionnement et les valeurs qui orientent chaque projet."
      :primary="{ label: 'Découvrir l’agence', to: '/agence' }"
      :secondary="{ label: 'Parler de votre projet', to: '/#contact', external: true }"
    />
  </div>
</template>

<style scoped>
.expertises-page {
  display: flex;
  flex-direction: column;
}

.expertises-page__paragraph {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

.expertises-page__grid {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-5);
}

.expertises-page__grid-item {
  display: flex;
}

@media (min-width: 720px) {
  .expertises-page__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1080px) {
  .expertises-page__grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
</style>
