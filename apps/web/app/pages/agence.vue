<script setup lang="ts">
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import EditorialHero from "~/components/editorial/EditorialHero.vue"
import EditorialSection from "~/components/editorial/EditorialSection.vue"

/**
 * Page `/agence` — présentation éditoriale de Devzair (Phase 7A).
 *
 * Rôle strict d'orchestration :
 *   - un H1 unique (EditorialHero) qui pose la mission ;
 *   - trois EditorialSection narratives : positionnement, fonctionnement,
 *     valeurs — sans faux organigramme, sans chiffre ni témoignage inventés
 *     (règle AGENTS.md §1, précisé par `docs/01-CONTENT.md §7.2`) ;
 *   - un EditorialCallout de pont vers `/expertises` + un CTA `/#contact`.
 *
 * SEO : `usePageSeo` seul, canonical absolu dérivé de siteUrl + path. Aucune
 * duplication de `useSeoMeta` ou de canonical à la main.
 * Prerender : la page est marquée `prerender: true` dans `nuxt.config.ts`.
 */

usePageSeo({
  title: "Agence digitale à taille humaine",
  description:
    "Découvrez l'approche Devzair : une agence digitale à taille humaine qui réunit stratégie, design, développement, contenus et SEO autour de votre projet.",
  path: "/agence",
  type: "website",
})
</script>

<template>
  <div class="agence-page">
    <EditorialHero
      eyebrow="L'agence"
      title="Une agence digitale à taille humaine, pensée pour accompagner les entreprises dans leur globalité."
      lead="Devzair réunit stratégie, design, développement, contenus et visibilité afin de construire des solutions digitales cohérentes, utiles et évolutives."
    />

    <EditorialSection
      tone="default"
      eyebrow="Positionnement"
      title="Une agence indépendante, engagée dans la durée avec chaque entreprise accompagnée."
      intro="Nous privilégions une relation directe, sans intermédiaire, entre l'équipe qui conçoit, celle qui construit et la personne qui décide côté client."
    >
      <p class="agence-page__paragraph">
        Notre positionnement repose sur trois principes : comprendre le
        contexte avant de proposer, aligner chaque décision technique sur un
        objectif éditorial et rendre le fonctionnement du projet lisible pour
        toutes les parties prenantes.
      </p>
      <p class="agence-page__paragraph">
        Cette exigence guide aussi les projets que nous acceptons. Nous
        travaillons avec les entreprises et organisations qui souhaitent
        piloter réellement leur présence digitale — pas déléguer un livrable
        isolé.
      </p>
    </EditorialSection>

    <EditorialSection
      tone="subtle"
      eyebrow="Comment nous fonctionnons"
      title="Nous réunissons les compétences adaptées à chaque projet."
      intro="Chaque mission mobilise les expertises utiles au périmètre défini — jamais plus. Cette approche évite les couches inutiles, garde la responsabilité côté client claire et rend le budget prévisible."
    >
      <ul class="agence-page__practices" role="list">
        <li class="agence-page__practice">
          <p class="agence-page__practice-title">Un pilotage direct</p>
          <p class="agence-page__practice-text">
            Un interlocuteur unique porte le projet de bout en bout : brief,
            cadrage, livraisons, décisions clés et suivi post-mise en ligne.
          </p>
        </li>
        <li class="agence-page__practice">
          <p class="agence-page__practice-title">Cinq pôles connectés</p>
          <p class="agence-page__practice-text">
            Stratégie, design, développement, contenus et visibilité sont
            traités comme un ensemble cohérent — pas comme des prestations
            juxtaposées.
          </p>
        </li>
        <li class="agence-page__practice">
          <p class="agence-page__practice-title">Un périmètre explicite</p>
          <p class="agence-page__practice-text">
            Ce qui est inclus est décrit sans ambiguïté ; ce qui est différé
            l'est aussi. Aucune option n'est ajoutée en cours de route sans
            décision formelle.
          </p>
        </li>
      </ul>
    </EditorialSection>

    <EditorialSection
      tone="default"
      eyebrow="Nos valeurs"
      title="Des choix éditoriaux et techniques que nous assumons."
      intro="Nos partis pris ne sont pas des slogans : ils orientent chaque livrable, du texte publié à la ligne de code déployée."
    >
      <ul class="agence-page__values" role="list">
        <li class="agence-page__value">
          <p class="agence-page__value-title">Honnêteté éditoriale</p>
          <p class="agence-page__value-text">
            Aucun contenu fictif, aucun témoignage inventé, aucune promesse de
            résultat qu'un travail digital ne peut sérieusement garantir.
          </p>
        </li>
        <li class="agence-page__value">
          <p class="agence-page__value-title">Robustesse technique</p>
          <p class="agence-page__value-text">
            Nous privilégions des bases stables, testées et documentées, pour
            que le site reste maintenable dans les années qui suivent la
            livraison.
          </p>
        </li>
        <li class="agence-page__value">
          <p class="agence-page__value-title">Accessibilité et sobriété</p>
          <p class="agence-page__value-text">
            Nous concevons pour toutes les situations d'usage — mobile,
            clavier, lecteur d'écran, connexion lente — et évitons le poids
            inutile dans les pages.
          </p>
        </li>
        <li class="agence-page__value">
          <p class="agence-page__value-title">Respect des données</p>
          <p class="agence-page__value-text">
            Ce que nous collectons, nous savons pourquoi ; ce qui n'est pas
            nécessaire, nous ne le collectons pas. Cette règle s'applique aussi
            à ce site.
          </p>
        </li>
      </ul>
    </EditorialSection>

    <EditorialCallout
      eyebrow="Aller plus loin"
      title="Découvrez nos cinq pôles d'expertise."
      description="Stratégie et design, développement, contenus, visibilité, maintenance : cinq domaines connectés qui portent chaque projet Devzair."
      :primary="{ label: 'Voir nos expertises', to: '/expertises' }"
      :secondary="{ label: 'Parler de votre projet', to: '/#contact', external: true }"
    />
  </div>
</template>

<style scoped>
.agence-page {
  display: flex;
  flex-direction: column;
}

.agence-page__paragraph {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

.agence-page__practices,
.agence-page__values {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-6);
}

.agence-page__practice,
.agence-page__value {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding: var(--space-5) var(--space-6);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  background-color: var(--background-secondary);
}

.editorial-section[data-tone="subtle"] .agence-page__practice {
  background-color: var(--background-primary);
}

.agence-page__practice-title,
.agence-page__value-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1.0625rem;
  line-height: 1.3;
  margin: 0;
  color: var(--text-primary);
}

.agence-page__practice-text,
.agence-page__value-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
  max-width: 56ch;
}

@media (min-width: 768px) {
  .agence-page__practices,
  .agence-page__values {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-5);
  }
}

@media (min-width: 1024px) {
  .agence-page__practices {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
</style>
