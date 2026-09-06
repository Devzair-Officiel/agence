<script setup lang="ts">
import AgenceHero from "~/components/agence/AgenceHero.vue"
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import EditorialSection from "~/components/editorial/EditorialSection.vue"

/**
 * Page `/agence` — présentation éditoriale de Devzair.
 *
 * Rôle strict d'orchestration :
 *   - un H1 unique (AgenceHero, variante « portrait » avec visuel d'atelier)
 *     qui pose la mission et les deux piliers synthétiques de l'agence ;
 *   - trois EditorialSection narratives : positionnement, fonctionnement,
 *     valeurs — sans faux organigramme, sans chiffre ni témoignage inventés
 *     (règle AGENTS.md §1, précisé par `docs/01-CONTENT.md §7.2`) ;
 *   - un EditorialCallout de pont vers `/expertises` + un CTA `/contact`.
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
    <AgenceHero />

    <EditorialSection
      class="agence-page__positionnement"
      tone="default"
      eyebrow="Positionnement"
      title="Une agence indépendante, engagée dans la durée avec chaque entreprise accompagnée."
      intro="Nous privilégions une relation directe, sans intermédiaire, avec la personne qui décide côté client."
    >
      <ol class="agence-page__principles" role="list">
        <li class="agence-page__principle">
          <span class="agence-page__principle-index" aria-hidden="true">01</span>
          <p class="agence-page__principle-title">
            Comprendre le contexte avant de proposer
          </p>
          <p class="agence-page__principle-text">
            Chaque projet commence par une lecture attentive du métier, des
            contraintes et des enjeux — avant toute recommandation technique
            ou éditoriale.
          </p>
        </li>
        <li class="agence-page__principle">
          <span class="agence-page__principle-index" aria-hidden="true">02</span>
          <p class="agence-page__principle-title">
            Aligner décision technique et objectif éditorial
          </p>
          <p class="agence-page__principle-text">
            Une architecture, un composant ou une balise n'ont de valeur que
            s'ils servent un message clair et une audience identifiée.
          </p>
        </li>
        <li class="agence-page__principle">
          <span class="agence-page__principle-index" aria-hidden="true">03</span>
          <p class="agence-page__principle-title">
            Rendre le projet lisible pour toutes les parties prenantes
          </p>
          <p class="agence-page__principle-text">
            Cadrage, arbitrages et livrables sont formulés dans un langage
            partagé — pas dans un jargon réservé à l'agence.
          </p>
        </li>
      </ol>
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
      <NuxtLink to="/services" class="agence-page__services-link">
        Voir le détail de nos services
        <span aria-hidden="true">→</span>
      </NuxtLink>
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
      :secondary="{ label: 'Voir nos réalisations', to: '/realisations' }"
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

.agence-page__positionnement :deep(.editorial-section__header) {
  align-items: center;
  text-align: center;
  margin-inline: auto;
}

.agence-page__positionnement :deep(.editorial-section__title),
.agence-page__positionnement :deep(.editorial-section__intro) {
  max-width: none;
}

.agence-page__principles {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
}

.agence-page__principle {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding-top: var(--space-4);
  border-top: 1px solid var(--border-default);
}

.agence-page__principle-index {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1rem;
  letter-spacing: 0.08em;
  color: var(--color-petrol);
}

.agence-page__principle-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1.125rem;
  line-height: 1.3;
  color: var(--text-primary);
  margin: 0;
  max-width: 28ch;
}

.agence-page__principle-text {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 44ch;
}

.agence-page__services-link {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  align-self: flex-start;
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 0.9375rem;
  color: var(--text-accent);
  text-decoration: none;
  border-bottom: 1px solid currentColor;
  padding-bottom: 2px;
  transition: color var(--duration-fast) var(--ease-out);
}

.agence-page__services-link:hover {
  color: var(--text-primary);
}

.agence-page__services-link:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
  border-radius: 2px;
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

  .agence-page__principles {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--space-6);
  }
}

@media (min-width: 1024px) {
  .agence-page__practices {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
</style>
