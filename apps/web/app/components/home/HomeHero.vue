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
 *     pointe sur la page dédiée `/contact` ; « Découvrir nos réalisations »
 *     pointe sur l'ancre locale `#realisations` tant que la page cible
 *     n'existe pas (cf. AGENTS.md — pas de placeholder de route).
 *
 * Responsive :
 *   - ≥1100px : deux colonnes équilibrées (~55/45), graphe posé sur un halo
 *     dédié pour ne plus "flotter" dans le vide ;
 *   - <1100px : une seule colonne, graphe sous le bloc éditorial pour
 *     préserver la hiérarchie de lecture.
 */
</script>

<template>
  <section class="home-hero" aria-labelledby="home-hero-title">
    <div class="home-hero__backdrop" aria-hidden="true" />
    <BaseContainer width="full" class="home-hero__container">
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
          <BaseButton to="/contact" variant="primary">
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
        <div class="home-hero__visual-frame">
          <HomeEcosystemGraph />
        </div>
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.home-hero {
  position: relative;
  isolation: isolate;
  display: flex;
  align-items: center;
  background-color: var(--background-inverse);
  color: var(--text-inverse);
  padding-block: var(--space-12);
  overflow: hidden;
  /* Occupe la hauteur restante sous le header sticky. */
  min-height: calc(100svh - var(--site-header-height));
}

/* Fallback pour navigateurs sans small-viewport-height. */
@supports not (min-height: 100svh) {
  .home-hero {
    min-height: calc(100vh - var(--site-header-height));
  }
}

.home-hero__backdrop {
  position: absolute;
  inset: 0;
  z-index: -1;
  background:
    radial-gradient(
      1200px 600px at 80% 20%,
      rgba(46, 134, 217, 0.18),
      transparent 60%
    ),
    radial-gradient(
      900px 500px at 10% 90%,
      rgba(12, 91, 87, 0.35),
      transparent 65%
    );
  pointer-events: none;
}

.home-hero__container {
  width: 100%;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: center;
}

.home-hero__content {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
  max-width: 44rem;
}

.home-hero__eyebrow {
  margin-bottom: var(--space-2);
}

.home-hero__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2.125rem, 4.8vw, 3.5rem);
  line-height: 1.07;
  letter-spacing: -0.015em;
  color: var(--text-inverse);
  margin: 0;
  max-width: 22ch;
  text-wrap: balance;
}

/*
 * Écho au « Z » chromé du logo : dégradé argenté → bleu Devzair.
 * `background-clip: text` fait le rendu métallique tout en gardant la
 * chaîne accessible (le mot reste lisible pour les lecteurs d'écran ;
 * `color: transparent` n'agit que sur le glyphe visuel).
 * Fallback pour navigateurs sans background-clip: text : `color` reste
 * défini via `-webkit-text-fill-color` uniquement quand supporté.
 */
.home-hero__title-emphasis {
  color: var(--color-devzair-blue);
}

@supports (
    (-webkit-background-clip: text) or (background-clip: text)
  ) {
  .home-hero__title-emphasis {
    background: linear-gradient(
      120deg,
      #dfe6ef 0%,
      var(--color-devzair-blue) 55%,
      #eef3f9 100%
    );
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    color: transparent;
  }
}

.home-hero__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1.0625rem, 1.4vw, 1.1875rem);
  line-height: 1.6;
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
  max-width: 48rem;
  margin-inline: auto;
}

.home-hero__visual-frame {
  position: relative;
  border-radius: var(--radius-xl);
  background:
    radial-gradient(
      circle at 50% 45%,
      rgba(46, 134, 217, 0.14),
      transparent 68%
    );
}

@media (min-width: 560px) {
  .home-hero__reassurance {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-6);
  }
}

@media (min-width: 768px) {
  .home-hero {
    padding-block: var(--space-16);
  }
}

@media (min-width: 1100px) {
  .home-hero__container {
    grid-template-columns: minmax(0, 6fr) minmax(0, 5fr);
    gap: var(--space-16);
  }

  .home-hero__visual {
    max-width: 100%;
  }
}

@media (min-width: 1280px) {
  .home-hero {
    padding-block: var(--space-20);
  }

  .home-hero__container {
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: var(--space-20);
  }
}
</style>
