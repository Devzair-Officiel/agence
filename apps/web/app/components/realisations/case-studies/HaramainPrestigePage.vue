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
  <article class="hp-page" aria-labelledby="hp-title">

    <!-- Hero -->
    <div class="hp-page__hero">
      <BaseContainer width="wide">
        <div class="hp-page__hero-inner">
          <div class="hp-page__hero-content">
            <BaseEyebrow class="hp-page__eyebrow">{{ study.category }}</BaseEyebrow>
            <h1 id="hp-title" class="hp-page__h1">{{ study.name }}</h1>
            <p class="hp-page__lead">{{ study.longDescription }}</p>
            <ul class="hp-page__tags" aria-label="Interventions">
              <li
                v-for="tag in study.tags"
                :key="tag"
                class="hp-page__tag"
              >
                {{ tag }}
              </li>
            </ul>
          </div>
          <div class="hp-page__hero-visual" aria-hidden="true">
            <img
              :src="study.imageSrc"
              :alt="study.imageAlt"
              class="hp-page__hero-img"
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
    <div class="hp-page__section hp-page__section--subtle">
      <BaseContainer width="wide">
        <div class="hp-page__section-inner">
          <header class="hp-page__section-header">
            <BaseEyebrow>Le projet</BaseEyebrow>
            <h2 class="hp-page__h2">
              Rendre lisible un service de conciergerie à l'autre bout du monde.
            </h2>
          </header>
          <div class="hp-page__prose">
            <p>
              Haramain Prestige propose aux pèlerins et voyageurs des hôtels
              soigneusement sélectionnés à Makkah et Madinah, associés à un
              accompagnement local. Le service repose sur deux piliers : la
              qualité de la sélection hôtelière et la disponibilité d'un
              interlocuteur sur place.
            </p>
            <p>
              Le défi de la vitrine était de rendre ces deux piliers
              immédiatement lisibles — sans noyer le visiteur dans les détails
              — et de faciliter la prise de contact pour un public qui planifie
              souvent son séjour plusieurs mois à l'avance.
            </p>
          </div>
        </div>
      </BaseContainer>
    </div>

    <!-- Périmètre d'intervention -->
    <div class="hp-page__section">
      <BaseContainer width="wide">
        <div class="hp-page__section-inner">
          <header class="hp-page__section-header">
            <BaseEyebrow>Périmètre d'intervention</BaseEyebrow>
            <h2 class="hp-page__h2">Ce que nous avons conçu et livré.</h2>
          </header>
          <ul class="hp-page__deliverables" role="list">
            <li class="hp-page__deliverable">
              <span class="hp-page__deliverable-num" aria-hidden="true">01</span>
              <div>
                <h3 class="hp-page__deliverable-title">Architecture du service</h3>
                <p class="hp-page__deliverable-text">
                  Structure de la page principale pour rendre les trois piliers
                  du service lisibles — sélection des hôtels, tarifs négociés,
                  accompagnement sur place — sans encombrement.
                </p>
              </div>
            </li>
            <li class="hp-page__deliverable">
              <span class="hp-page__deliverable-num" aria-hidden="true">02</span>
              <div>
                <h3 class="hp-page__deliverable-title">Présentation de l'offre hôtelière</h3>
                <p class="hp-page__deliverable-text">
                  Module de présentation des hôtels sélectionnés : localisation,
                  niveau de gamme, proximité des lieux saints — les critères
                  qui comptent pour ce public.
                </p>
              </div>
            </li>
            <li class="hp-page__deliverable">
              <span class="hp-page__deliverable-num" aria-hidden="true">03</span>
              <div>
                <h3 class="hp-page__deliverable-title">UX/UI design</h3>
                <p class="hp-page__deliverable-text">
                  Identité visuelle sobre et rassurante, palette adaptée au
                  positionnement prestige, typographie lisible sur mobile
                  comme sur desktop.
                </p>
              </div>
            </li>
            <li class="hp-page__deliverable">
              <span class="hp-page__deliverable-num" aria-hidden="true">04</span>
              <div>
                <h3 class="hp-page__deliverable-title">Parcours de prise de contact</h3>
                <p class="hp-page__deliverable-text">
                  Formulaire de demande de réservation adapté aux séjours
                  planifiés longtemps à l'avance — dates, nombre de personnes,
                  type d'hébergement souhaité.
                </p>
              </div>
            </li>
          </ul>
        </div>
      </BaseContainer>
    </div>

    <!-- Liens -->
    <div class="hp-page__section hp-page__section--subtle">
      <BaseContainer width="wide">
        <div class="hp-page__links-inner">
          <div class="hp-page__links-group">
            <p class="hp-page__links-label">Service associé</p>
            <NuxtLink
              to="/services/creation-site-internet"
              class="hp-page__int-link"
            >
              Création de site internet →
            </NuxtLink>
          </div>
          <div class="hp-page__links-group">
            <p class="hp-page__links-label">Pôle d'expertise</p>
            <NuxtLink
              to="/expertises/concevoir"
              class="hp-page__int-link"
            >
              Concevoir →
            </NuxtLink>
          </div>
          <div class="hp-page__links-group">
            <p class="hp-page__links-label">Toutes les réalisations</p>
            <NuxtLink
              to="/realisations"
              class="hp-page__int-link"
            >
              Voir le portfolio →
            </NuxtLink>
          </div>
        </div>
      </BaseContainer>
    </div>

    <EditorialCallout
      eyebrow="Votre projet"
      title="Un service à rendre lisible et accessible en ligne ?"
      description="Racontez-nous ce que vous faites, pour qui et ce que vos clients cherchent d'abord. Nous construisons la structure autour de ça."
      :primary="{ label: 'Nous contacter', to: '/contact' }"
      :secondary="{ label: 'Estimer mon projet', to: '/estimer-mon-projet' }"
    />

  </article>
</template>

<style scoped>
.hp-page {
  display: flex;
  flex-direction: column;
}

.hp-page__hero {
  background-color: var(--background-primary);
  border-bottom: 1px solid var(--border-default);
  padding-block: var(--space-16);
}

.hp-page__hero-inner {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: center;
}

.hp-page__hero-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.hp-page__eyebrow {
  margin-bottom: 0;
}

.hp-page__h1 {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2.25rem, 6vw, 3.5rem);
  line-height: 1.05;
  letter-spacing: -0.03em;
  color: var(--text-primary);
  margin: 0;
}

.hp-page__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.5vw, 1.0625rem);
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 56ch;
}

.hp-page__tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
  list-style: none;
  padding: 0;
  margin: 0;
}

.hp-page__tag {
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

.hp-page__hero-visual {
  border-radius: var(--radius-lg);
  overflow: hidden;
  line-height: 0;
}

.hp-page__hero-img {
  width: 100%;
  height: auto;
  display: block;
  object-fit: cover;
}

@media (min-width: 900px) {
  .hp-page__hero {
    padding-block: var(--space-20);
  }

  .hp-page__hero-inner {
    grid-template-columns: 1fr 1fr;
    gap: var(--space-16);
  }
}

.hp-page__section {
  padding-block: var(--space-16);
  background-color: var(--surface-primary);
}

.hp-page__section--subtle {
  background-color: var(--background-secondary);
}

.hp-page__section-inner {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.hp-page__section-header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 40ch;
}

.hp-page__h2 {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.375rem, 3vw, 2rem);
  line-height: 1.15;
  letter-spacing: -0.02em;
  color: var(--text-primary);
  margin: 0;
}

.hp-page__prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 62ch;
}

.hp-page__prose p {
  font-size: 1rem;
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0;
}

.hp-page__deliverables {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: 0;
}

.hp-page__deliverable {
  display: grid;
  grid-template-columns: 3rem 1fr;
  gap: var(--space-4);
  padding-block: var(--space-6);
  border-top: 1px solid var(--border-default);
  align-items: start;
}

.hp-page__deliverable-num {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.05em;
  color: var(--text-muted);
  padding-top: 3px;
}

.hp-page__deliverable-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: clamp(0.9375rem, 1.4vw, 1.0625rem);
  line-height: 1.3;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0 0 var(--space-2);
}

.hp-page__deliverable-text {
  font-size: 0.9375rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
}

@media (min-width: 768px) {
  .hp-page__deliverables {
    grid-template-columns: repeat(2, 1fr);
    column-gap: var(--space-10);
  }

  .hp-page__section {
    padding-block: var(--space-20);
  }
}

.hp-page__links-inner {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.hp-page__links-group {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.hp-page__links-label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--text-muted);
  margin: 0;
}

.hp-page__int-link {
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

.hp-page__int-link:hover {
  color: var(--text-primary);
}

.hp-page__int-link:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
  border-radius: 2px;
}

@media (min-width: 600px) {
  .hp-page__links-inner {
    flex-direction: row;
    gap: var(--space-12);
    flex-wrap: wrap;
  }
}

@media (prefers-reduced-motion: reduce) {
  .hp-page__int-link {
    transition: none;
  }
}
</style>
