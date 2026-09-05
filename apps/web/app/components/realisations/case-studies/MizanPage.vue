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
  <article class="mz-page" aria-labelledby="mz-title">

    <!-- Hero -->
    <div class="mz-page__hero">
      <BaseContainer width="wide">
        <div class="mz-page__hero-inner">
          <div class="mz-page__hero-content">
            <BaseEyebrow>{{ study.category }}</BaseEyebrow>
            <h1 id="mz-title" class="mz-page__h1">{{ study.name }}</h1>
            <p class="mz-page__lead">{{ study.longDescription }}</p>
            <ul class="mz-page__tags" aria-label="Interventions">
              <li
                v-for="tag in study.tags"
                :key="tag"
                class="mz-page__tag"
              >
                {{ tag }}
              </li>
            </ul>
          </div>
          <div class="mz-page__hero-visual" aria-hidden="true">
            <img
              :src="study.imageSrc"
              :alt="study.imageAlt"
              class="mz-page__hero-img"
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
    <div class="mz-page__section mz-page__section--subtle">
      <BaseContainer width="wide">
        <div class="mz-page__section-inner">
          <header class="mz-page__section-header">
            <BaseEyebrow>Le projet</BaseEyebrow>
            <h2 class="mz-page__h2">
              Une application SaaS pour piloter un commerce au quotidien.
            </h2>
          </header>
          <div class="mz-page__prose">
            <p>
              Mizan est une application SaaS mobile-first pensée pour les
              commerçants qui veulent centraliser la gestion de leur activité
              sans multiplier les outils. Stock, commandes, clients, messages
              et calcul de zakat depuis une seule interface.
            </p>
            <p>
              L'enjeu de conception était double : rendre chaque module
              utilisable rapidement sur mobile — contexte d'usage terrain —
              tout en proposant une vue synthétique lisible sur écran plus
              large. Le tableau de bord a été conçu pour répondre à la
              question « où en suis-je ? » en un coup d'œil.
            </p>
          </div>
        </div>
      </BaseContainer>
    </div>

    <!-- Périmètre d'intervention -->
    <div class="mz-page__section">
      <BaseContainer width="wide">
        <div class="mz-page__section-inner">
          <header class="mz-page__section-header">
            <BaseEyebrow>Périmètre d'intervention</BaseEyebrow>
            <h2 class="mz-page__h2">Ce que nous avons conçu et livré.</h2>
          </header>
          <ul class="mz-page__deliverables" role="list">
            <li class="mz-page__deliverable">
              <span class="mz-page__deliverable-num" aria-hidden="true">01</span>
              <div>
                <h3 class="mz-page__deliverable-title">Tableau de bord synthétique</h3>
                <p class="mz-page__deliverable-text">
                  Vue d'ensemble : ventes hebdomadaires, stock bas, commandes
                  en attente et indicateurs métier clés — tout en un coup d'œil.
                </p>
              </div>
            </li>
            <li class="mz-page__deliverable">
              <span class="mz-page__deliverable-num" aria-hidden="true">02</span>
              <div>
                <h3 class="mz-page__deliverable-title">Gestion des commandes</h3>
                <p class="mz-page__deliverable-text">
                  Interface de suivi des commandes par statut, filtres
                  rapides et accès direct aux détails — pensée pour un
                  usage terrain sur mobile.
                </p>
              </div>
            </li>
            <li class="mz-page__deliverable">
              <span class="mz-page__deliverable-num" aria-hidden="true">03</span>
              <div>
                <h3 class="mz-page__deliverable-title">Module stock et catalogue</h3>
                <p class="mz-page__deliverable-text">
                  Gestion du catalogue produit, alertes de stock bas et mise
                  à jour rapide des disponibilités.
                </p>
              </div>
            </li>
            <li class="mz-page__deliverable">
              <span class="mz-page__deliverable-num" aria-hidden="true">04</span>
              <div>
                <h3 class="mz-page__deliverable-title">Design system mobile-first</h3>
                <p class="mz-page__deliverable-text">
                  Vocabulaire visuel cohérent avec l'identité Mizan —
                  composants accessibles, états d'interaction définis,
                  lisibles sur petits comme grands écrans.
                </p>
              </div>
            </li>
          </ul>
        </div>
      </BaseContainer>
    </div>

    <!-- Liens -->
    <div class="mz-page__section mz-page__section--subtle">
      <BaseContainer width="wide">
        <div class="mz-page__links-inner">
          <div class="mz-page__links-group">
            <p class="mz-page__links-label">Site en ligne</p>
            <a
              :href="study.href"
              target="_blank"
              rel="noopener noreferrer"
              class="mz-page__ext-link"
            >
              Voir le site Mizan
              <span class="sr-only">(nouvelle fenêtre)</span>
              <span aria-hidden="true"> ↗</span>
            </a>
          </div>
          <div class="mz-page__links-group">
            <p class="mz-page__links-label">Service associé</p>
            <NuxtLink
              to="/services/application-web-metier"
              class="mz-page__int-link"
            >
              Application web métier →
            </NuxtLink>
          </div>
          <div class="mz-page__links-group">
            <p class="mz-page__links-label">Pôle d'expertise</p>
            <NuxtLink
              to="/expertises/concevoir"
              class="mz-page__int-link"
            >
              Concevoir →
            </NuxtLink>
          </div>
          <div class="mz-page__links-group">
            <p class="mz-page__links-label">Toutes les réalisations</p>
            <NuxtLink
              to="/realisations"
              class="mz-page__int-link"
            >
              Voir le portfolio →
            </NuxtLink>
          </div>
        </div>
      </BaseContainer>
    </div>

    <EditorialCallout
      eyebrow="Votre projet"
      title="Une interface applicative à concevoir ou à restructurer ?"
      description="Racontez-nous le contexte d'usage — terrain, bureau, mobile — et les modules prioritaires. Nous identifions ensemble ce qui doit être conçu en premier."
      :primary="{ label: 'Nous contacter', to: '/contact' }"
      :secondary="{ label: 'Estimer mon projet', to: '/estimer-mon-projet' }"
    />

  </article>
</template>

<style scoped>
.mz-page {
  display: flex;
  flex-direction: column;
}

.mz-page__hero {
  background-color: var(--surface-primary);
  border-bottom: 1px solid var(--border-default);
  padding-block: var(--space-16);
}

.mz-page__hero-inner {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: center;
}

.mz-page__hero-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.mz-page__h1 {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2.25rem, 6vw, 3.5rem);
  line-height: 1.05;
  letter-spacing: -0.03em;
  color: var(--text-primary);
  margin: 0;
}

.mz-page__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.5vw, 1.0625rem);
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 56ch;
}

.mz-page__tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
  list-style: none;
  padding: 0;
  margin: 0;
}

.mz-page__tag {
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

.mz-page__hero-visual {
  border-radius: var(--radius-lg);
  overflow: hidden;
  line-height: 0;
}

.mz-page__hero-img {
  width: 100%;
  height: auto;
  display: block;
  object-fit: cover;
}

@media (min-width: 900px) {
  .mz-page__hero {
    padding-block: var(--space-20);
  }

  .mz-page__hero-inner {
    grid-template-columns: 1fr 1fr;
    gap: var(--space-16);
  }
}

.mz-page__section {
  padding-block: var(--space-16);
  background-color: var(--background-primary);
}

.mz-page__section--subtle {
  background-color: var(--background-secondary);
}

.mz-page__section-inner {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.mz-page__section-header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 40ch;
}

.mz-page__h2 {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.375rem, 3vw, 2rem);
  line-height: 1.15;
  letter-spacing: -0.02em;
  color: var(--text-primary);
  margin: 0;
}

.mz-page__prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 62ch;
}

.mz-page__prose p {
  font-size: 1rem;
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0;
}

.mz-page__deliverables {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: 0;
}

.mz-page__deliverable {
  display: grid;
  grid-template-columns: 3rem 1fr;
  gap: var(--space-4);
  padding-block: var(--space-6);
  border-top: 1px solid var(--border-default);
  align-items: start;
}

.mz-page__deliverable-num {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.05em;
  color: var(--text-muted);
  padding-top: 3px;
}

.mz-page__deliverable-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: clamp(0.9375rem, 1.4vw, 1.0625rem);
  line-height: 1.3;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0 0 var(--space-2);
}

.mz-page__deliverable-text {
  font-size: 0.9375rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
}

@media (min-width: 768px) {
  .mz-page__deliverables {
    grid-template-columns: repeat(2, 1fr);
    column-gap: var(--space-10);
  }

  .mz-page__section {
    padding-block: var(--space-20);
  }
}

.mz-page__links-inner {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.mz-page__links-group {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.mz-page__links-label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--text-muted);
  margin: 0;
}

.mz-page__ext-link,
.mz-page__int-link {
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

.mz-page__ext-link:hover,
.mz-page__int-link:hover {
  color: var(--text-primary);
}

.mz-page__ext-link:focus-visible,
.mz-page__int-link:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
  border-radius: 2px;
}

@media (min-width: 600px) {
  .mz-page__links-inner {
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
  .mz-page__ext-link,
  .mz-page__int-link {
    transition: none;
  }
}
</style>
