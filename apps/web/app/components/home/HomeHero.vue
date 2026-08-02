<script setup lang="ts">
import BaseButton from "~/components/base/BaseButton.vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import HomeEcosystemGraph from "~/components/home/HomeEcosystemGraph.vue"

/**
 * Hero de la page d'accueil.
 *
 * Rôle :
 *   - orchestrer l'en-tête éditorial (eyebrow, H1, introduction) ;
 *   - présenter les deux appels à l'action ;
 *   - afficher la réassurance courte ;
 *   - intégrer le graphe SVG des cinq pôles.
 *
 * Accessibilité :
 *   - un seul H1 par page (celui-ci) ;
 *   - la mise en valeur graphique « visible, crédible et efficace » utilise
 *     un `<span>` interne : la phrase reste une seule chaîne pour le lecteur
 *     d'écran ;
 *   - la liste de réassurance est une vraie `<ul>` sémantique, chaque item
 *     ayant un libellé fort et un complément mesuré ;
 *   - les deux CTA sont des liens sémantiques : « Parler de votre projet »
 *     et « Découvrir nos réalisations » pointent vers des ancres locales
 *     tant que les pages cibles n'existent pas (cf. AGENTS.md — pas de
 *     placeholder de route).
 *
 * Responsive :
 *   - ≥1024px : deux colonnes, contenu à gauche (~55%), graphe à droite ;
 *   - 768–1023px : deux colonnes 60/40, graphe visuellement discret ;
 *   - <768px : une seule colonne, graphe sous le bloc éditorial pour
 *     préserver la hiérarchie de lecture.
 */
</script>

<template>
  <section class="home-hero" aria-labelledby="home-hero-title">
    <BaseContainer class="home-hero__container">
      <div class="home-hero__content">
        <BaseEyebrow tone="inverse" class="home-hero__eyebrow">
          Agence digitale à taille humaine
        </BaseEyebrow>
        <h1 id="home-hero-title" class="home-hero__title">
          Des solutions digitales complètes pour rendre votre entreprise
          <span class="home-hero__title-emphasis"
            >visible, crédible et efficace</span
          >
          en ligne.
        </h1>
        <p class="home-hero__lead">
          Sites internet, applications métier, identité visuelle, contenus
          professionnels et référencement&nbsp;: nous réunissons les expertises
          nécessaires pour construire une présence digitale cohérente et
          évolutive.
        </p>

        <div class="home-hero__ctas">
          <BaseButton to="#contact" external variant="primary">
            Parler de votre projet
            <template #icon>→</template>
          </BaseButton>
          <BaseButton to="#realisations" external variant="secondary">
            Découvrir nos réalisations
          </BaseButton>
        </div>

        <ul class="home-hero__reassurance" aria-label="Notre approche">
          <li class="home-hero__reassurance-item">
            <span class="home-hero__reassurance-title"
              >Un pilotage clair du projet</span
            >
            <span class="home-hero__reassurance-detail"
              >Du brief à l’évolution</span
            >
          </li>
          <li class="home-hero__reassurance-item">
            <span class="home-hero__reassurance-title">Plusieurs expertises</span>
            <span class="home-hero__reassurance-detail"
              >Réunies en cinq pôles</span
            >
          </li>
        </ul>
      </div>

      <div class="home-hero__visual">
        <HomeEcosystemGraph />
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.home-hero {
  background-color: var(--background-inverse);
  color: var(--text-inverse);
  padding-block: var(--space-16) var(--space-20);
}

.home-hero__container {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-12);
  align-items: center;
}

.home-hero__content {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
  max-width: 42rem;
}

.home-hero__eyebrow {
  margin-bottom: var(--space-2);
}

.home-hero__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2rem, 5.2vw, 3.5rem);
  line-height: 1.08;
  letter-spacing: -0.01em;
  color: var(--text-inverse);
  margin: 0;
  max-width: 22ch;
}

.home-hero__title-emphasis {
  color: var(--color-devzair-blue);
}

.home-hero__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.6vw, 1.125rem);
  line-height: 1.55;
  color: var(--text-inverse-muted);
  margin: 0;
  max-width: 56ch;
}

.home-hero__ctas {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-3);
  margin-top: var(--space-2);
}

.home-hero__ctas :deep(.base-button[data-variant="secondary"]) {
  color: var(--color-cream);
  border-color: rgba(244, 241, 234, 0.28);
}

.home-hero__ctas :deep(.base-button[data-variant="secondary"]:hover) {
  background-color: var(--color-cream);
  color: var(--color-navy);
  border-color: var(--color-cream);
}

.home-hero__reassurance {
  list-style: none;
  margin: var(--space-6) 0 0 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
  border-top: 1px solid var(--border-inverse);
  padding-top: var(--space-6);
}

.home-hero__reassurance-item {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.home-hero__reassurance-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1rem;
  color: var(--text-inverse);
}

.home-hero__reassurance-detail {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--color-devzair-blue);
}

.home-hero__visual {
  width: 100%;
  max-width: 34rem;
  margin-inline: auto;
}

@media (min-width: 560px) {
  .home-hero__reassurance {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-6);
  }
}

@media (min-width: 768px) {
  .home-hero {
    padding-block: var(--space-20) var(--space-24);
  }

  .home-hero__container {
    grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);
    gap: var(--space-16);
  }

  .home-hero__visual {
    max-width: 100%;
  }
}

@media (min-width: 1024px) {
  .home-hero__container {
    grid-template-columns: minmax(0, 11fr) minmax(0, 9fr);
    gap: var(--space-20);
  }
}
</style>
