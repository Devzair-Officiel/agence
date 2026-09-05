<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import type { CaseStudy } from "~/config/case-studies"

interface Props {
  study: CaseStudy
}

defineProps<Props>()
</script>

<template>
  <article class="nd-page" aria-labelledby="nd-title">

    <!-- Hero -->
    <div class="nd-page__hero">
      <BaseContainer width="wide">
        <div class="nd-page__hero-inner">
          <div class="nd-page__hero-content">
            <BaseEyebrow class="nd-page__eyebrow">{{ study.category }}</BaseEyebrow>
            <h1 id="nd-title" class="nd-page__h1">{{ study.name }}</h1>
            <p class="nd-page__lead">{{ study.longDescription }}</p>
            <ul class="nd-page__tags" aria-label="Interventions">
              <li
                v-for="tag in study.tags"
                :key="tag"
                class="nd-page__tag"
              >
                {{ tag }}
              </li>
            </ul>
          </div>
          <div class="nd-page__hero-visual" aria-hidden="true">
            <img
              :src="study.imageSrc"
              :alt="study.imageAlt"
              class="nd-page__hero-img"
              width="960"
              height="600"
              fetchpriority="high"
              decoding="async"
            >
          </div>
        </div>
      </BaseContainer>
    </div>

    <!-- Contexte projet -->
    <div class="nd-page__section nd-page__section--subtle">
      <BaseContainer width="wide">
        <div class="nd-page__section-inner">
          <header class="nd-page__section-header">
            <BaseEyebrow>Le projet</BaseEyebrow>
            <h2 class="nd-page__h2">
              Une boutique e-commerce construite autour du produit.
            </h2>
          </header>
          <div class="nd-page__prose">
            <p>
              Nidemiel commercialise des miels d'exception sélectionnés pour
              leur provenance, leur mode de récolte et leur profil sensoriel.
              L'enjeu éditorial était de rendre ces informations lisibles en
              quelques secondes — sans surcharger la fiche produit.
            </p>
            <p>
              Nous avons conçu une boutique où chaque pot est présenté avec sa
              provenance, sa flore, ses notes de dégustation et le récit de
              l'apiculteur. Le tunnel d'achat a été simplifié pour limiter les
              abandons de panier, et la base éditoriale a été construite pour
              accueillir facilement de nouvelles récoltes.
            </p>
          </div>
        </div>
      </BaseContainer>
    </div>

    <!-- Périmètre d'intervention -->
    <div class="nd-page__section">
      <BaseContainer width="wide">
        <div class="nd-page__section-inner">
          <header class="nd-page__section-header">
            <BaseEyebrow>Périmètre d'intervention</BaseEyebrow>
            <h2 class="nd-page__h2">Ce que nous avons conçu et livré.</h2>
          </header>
          <ul class="nd-page__deliverables" role="list">
            <li class="nd-page__deliverable">
              <span class="nd-page__deliverable-num" aria-hidden="true">01</span>
              <div>
                <h3 class="nd-page__deliverable-title">Architecture e-commerce</h3>
                <p class="nd-page__deliverable-text">
                  Structuration du catalogue, des fiches produit et des
                  collections thématiques. Logique de navigation par terroir et
                  par type de miel.
                </p>
              </div>
            </li>
            <li class="nd-page__deliverable">
              <span class="nd-page__deliverable-num" aria-hidden="true">02</span>
              <div>
                <h3 class="nd-page__deliverable-title">Éditorial produit</h3>
                <p class="nd-page__deliverable-text">
                  Gabarit de fiche sensorielle : origine, floraison, texture,
                  notes de dégustation et histoire de l'apiculteur — le tout
                  en un format lisible d'un coup d'œil.
                </p>
              </div>
            </li>
            <li class="nd-page__deliverable">
              <span class="nd-page__deliverable-num" aria-hidden="true">03</span>
              <div>
                <h3 class="nd-page__deliverable-title">UX/UI design</h3>
                <p class="nd-page__deliverable-text">
                  Interface sobre centrée sur le produit — palette neutre pour
                  laisser la photographie dominer, typographie lisible et
                  navigation sans distraction.
                </p>
              </div>
            </li>
            <li class="nd-page__deliverable">
              <span class="nd-page__deliverable-num" aria-hidden="true">04</span>
              <div>
                <h3 class="nd-page__deliverable-title">Tunnel d'achat</h3>
                <p class="nd-page__deliverable-text">
                  Panier simplifié, checkout optimisé pour limiter les
                  abandons, intégration paiement sécurisé.
                </p>
              </div>
            </li>
          </ul>
        </div>
      </BaseContainer>
    </div>

    <!-- Liens -->
    <div class="nd-page__section nd-page__section--subtle">
      <BaseContainer width="wide">
        <div class="nd-page__links-inner">
          <div class="nd-page__links-group">
            <p class="nd-page__links-label">Site en ligne</p>
            <a
              :href="study.href"
              target="_blank"
              rel="noopener noreferrer"
              class="nd-page__ext-link"
            >
              Voir le site Nidemiel
              <span class="sr-only">(nouvelle fenêtre)</span>
              <span aria-hidden="true"> ↗</span>
            </a>
          </div>
          <div class="nd-page__links-group">
            <p class="nd-page__links-label">Service associé</p>
            <NuxtLink
              to="/services/site-e-commerce"
              class="nd-page__int-link"
            >
              Site e-commerce →
            </NuxtLink>
          </div>
          <div class="nd-page__links-group">
            <p class="nd-page__links-label">Pôle d'expertise</p>
            <NuxtLink
              to="/expertises/concevoir"
              class="nd-page__int-link"
            >
              Concevoir →
            </NuxtLink>
          </div>
          <div class="nd-page__links-group">
            <p class="nd-page__links-label">Toutes les réalisations</p>
            <NuxtLink
              to="/realisations"
              class="nd-page__int-link"
            >
              Voir le portfolio →
            </NuxtLink>
          </div>
        </div>
      </BaseContainer>
    </div>

    <EditorialCallout
      eyebrow="Votre projet"
      title="Un projet e-commerce à concevoir ou à refondre ?"
      description="Parlez-nous de votre catalogue, de votre clientèle cible et de vos contraintes. Nous évaluons ensemble ce qui est utile de faire — et ce qui peut attendre."
      :primary="{ label: 'Nous contacter', to: '/contact' }"
      :secondary="{ label: 'Estimer mon projet', to: '/estimer-mon-projet' }"
    />

  </article>
</template>

<style scoped>
.nd-page {
  display: flex;
  flex-direction: column;
}

.nd-page__hero {
  background-color: var(--background-secondary);
  border-bottom: 1px solid var(--border-default);
  padding-block: var(--space-16);
}

.nd-page__hero-inner {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: center;
}

.nd-page__hero-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.nd-page__eyebrow {
  margin-bottom: 0;
}

.nd-page__h1 {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2.25rem, 6vw, 3.5rem);
  line-height: 1.05;
  letter-spacing: -0.03em;
  color: var(--text-primary);
  margin: 0;
}

.nd-page__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.5vw, 1.0625rem);
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 56ch;
}

.nd-page__tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
  list-style: none;
  padding: 0;
  margin: 0;
}

.nd-page__tag {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.625rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--text-accent);
  padding: 4px 10px;
  border: 1px solid rgba(12, 91, 87, 0.3);
  border-radius: var(--radius-pill);
  background-color: rgba(12, 91, 87, 0.06);
}

.nd-page__hero-visual {
  border-radius: var(--radius-lg);
  overflow: hidden;
  line-height: 0;
}

.nd-page__hero-img {
  width: 100%;
  height: auto;
  display: block;
  object-fit: cover;
}

@media (min-width: 900px) {
  .nd-page__hero {
    padding-block: var(--space-20);
  }

  .nd-page__hero-inner {
    grid-template-columns: 1fr 1fr;
    gap: var(--space-16);
  }
}

.nd-page__section {
  padding-block: var(--space-16);
  background-color: var(--surface-primary);
}

.nd-page__section--subtle {
  background-color: var(--background-secondary);
}

.nd-page__section-inner {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.nd-page__section-header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 40ch;
}

.nd-page__h2 {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.375rem, 3vw, 2rem);
  line-height: 1.15;
  letter-spacing: -0.02em;
  color: var(--text-primary);
  margin: 0;
}

.nd-page__prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 62ch;
}

.nd-page__prose p {
  font-size: 1rem;
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0;
}

.nd-page__deliverables {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: 0;
}

.nd-page__deliverable {
  display: grid;
  grid-template-columns: 3rem 1fr;
  gap: var(--space-4);
  padding-block: var(--space-6);
  border-top: 1px solid var(--border-default);
  align-items: start;
}

.nd-page__deliverable-num {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.05em;
  color: var(--text-muted);
  padding-top: 3px;
}

.nd-page__deliverable-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: clamp(0.9375rem, 1.4vw, 1.0625rem);
  line-height: 1.3;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0 0 var(--space-2);
}

.nd-page__deliverable-text {
  font-size: 0.9375rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
}

@media (min-width: 768px) {
  .nd-page__deliverables {
    grid-template-columns: repeat(2, 1fr);
    column-gap: var(--space-10);
  }

  .nd-page__section {
    padding-block: var(--space-20);
  }
}

.nd-page__links-inner {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.nd-page__links-group {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.nd-page__links-label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--text-muted);
  margin: 0;
}

.nd-page__ext-link,
.nd-page__int-link {
  display: inline-flex;
  align-items: center;
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 1rem;
  color: var(--text-accent);
  text-decoration: none;
  border-bottom: 1px solid currentColor;
  padding-bottom: 2px;
  transition: color var(--duration-fast) var(--ease-out);
}

.nd-page__ext-link:hover,
.nd-page__int-link:hover {
  color: var(--text-primary);
}

.nd-page__ext-link:focus-visible,
.nd-page__int-link:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
  border-radius: 2px;
}

@media (min-width: 600px) {
  .nd-page__links-inner {
    flex-direction: row;
    gap: var(--space-12);
    flex-wrap: wrap;
  }
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

@media (prefers-reduced-motion: reduce) {
  .nd-page__ext-link,
  .nd-page__int-link {
    transition: none;
  }
}
</style>
