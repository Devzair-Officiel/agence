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
  <article class="km-page" aria-labelledby="km-title">

    <!-- Hero -->
    <div class="km-page__hero">
      <BaseContainer width="wide">
        <div class="km-page__hero-inner">
          <div class="km-page__hero-content">
            <BaseEyebrow class="km-page__eyebrow">{{ study.category }}</BaseEyebrow>
            <h1 id="km-title" class="km-page__h1">{{ study.name }}</h1>
            <p class="km-page__lead">{{ study.longDescription }}</p>
            <ul class="km-page__tags" aria-label="Interventions">
              <li
                v-for="tag in study.tags"
                :key="tag"
                class="km-page__tag"
              >
                {{ tag }}
              </li>
            </ul>
          </div>
          <div class="km-page__hero-visual" aria-hidden="true">
            <img
              :src="study.imageSrc"
              :alt="study.imageAlt"
              class="km-page__hero-img"
              width="960"
              height="600"
              fetchpriority="high"
              decoding="async"
            >
          </div>
        </div>
      </BaseContainer>
    </div>

    <!-- Ce que nous avons conçu -->
    <div class="km-page__section km-page__section--subtle">
      <BaseContainer width="wide">
        <div class="km-page__section-inner">
          <header class="km-page__section-header">
            <BaseEyebrow>Le projet</BaseEyebrow>
            <h2 class="km-page__h2">Une vitrine pour un restaurant de grillades lyonnais.</h2>
          </header>
          <div class="km-page__prose">
            <p>
              Kitchen Meat est un restaurant lyonnais dédié à l'univers de la
              grillade. Le besoin était clair : une vitrine qui mette en avant
              les spécialités et l'identité du lieu, et qui facilite la
              réservation sans friction.
            </p>
            <p>
              La hiérarchie de contenu a été construite autour du parcours du
              visiteur — découverte des spécialités, ambiance du restaurant,
              accès rapide à la carte et à la réservation. L'ambiance
              chaleureuse guide visuellement vers l'action.
            </p>
          </div>
        </div>
      </BaseContainer>
    </div>

    <!-- Ce que nous avons travaillé -->
    <div class="km-page__section">
      <BaseContainer width="wide">
        <div class="km-page__section-inner">
          <header class="km-page__section-header">
            <BaseEyebrow>Périmètre d'intervention</BaseEyebrow>
            <h2 class="km-page__h2">Ce que nous avons conçu et livré.</h2>
          </header>
          <ul class="km-page__deliverables" role="list">
            <li class="km-page__deliverable">
              <span class="km-page__deliverable-num" aria-hidden="true">01</span>
              <div>
                <h3 class="km-page__deliverable-title">Identité visuelle</h3>
                <p class="km-page__deliverable-text">
                  Définition de la palette, typographie et direction artistique
                  alignées avec l'univers de la grillade — chaleur, matières,
                  caractère.
                </p>
              </div>
            </li>
            <li class="km-page__deliverable">
              <span class="km-page__deliverable-num" aria-hidden="true">02</span>
              <div>
                <h3 class="km-page__deliverable-title">UX et architecture de l'information</h3>
                <p class="km-page__deliverable-text">
                  Structure des pages, hiérarchie de contenu et parcours
                  visiteur optimisé pour la découverte de la carte et la
                  réservation.
                </p>
              </div>
            </li>
            <li class="km-page__deliverable">
              <span class="km-page__deliverable-num" aria-hidden="true">03</span>
              <div>
                <h3 class="km-page__deliverable-title">UI design responsive</h3>
                <p class="km-page__deliverable-text">
                  Maquettes desktop et mobile, composants visuels et animations
                  légères pour une expérience cohérente sur tous les appareils.
                </p>
              </div>
            </li>
            <li class="km-page__deliverable">
              <span class="km-page__deliverable-num" aria-hidden="true">04</span>
              <div>
                <h3 class="km-page__deliverable-title">Intégration réservation</h3>
                <p class="km-page__deliverable-text">
                  Connexion au système de réservation externe — accès direct,
                  sans rupture de parcours.
                </p>
              </div>
            </li>
          </ul>
        </div>
      </BaseContainer>
    </div>

    <!-- Voir le site + maillage -->
    <div class="km-page__section km-page__section--subtle">
      <BaseContainer width="wide">
        <div class="km-page__links-inner">
          <div class="km-page__links-group">
            <p class="km-page__links-label">Site en ligne</p>
            <a
              :href="study.href"
              target="_blank"
              rel="noopener noreferrer"
              class="km-page__ext-link"
            >
              Voir le site Kitchen Meat
              <span class="sr-only">(nouvelle fenêtre)</span>
              <span aria-hidden="true"> ↗</span>
            </a>
          </div>
          <div class="km-page__links-group">
            <p class="km-page__links-label">Services associés</p>
            <NuxtLink
              to="/services/creation-site-internet"
              class="km-page__int-link"
            >
              Création de site internet →
            </NuxtLink>
            <NuxtLink
              to="/services/design-ui-ux-identite-visuelle"
              class="km-page__int-link"
            >
              Design et identité visuelle →
            </NuxtLink>
          </div>
          <div class="km-page__links-group">
            <p class="km-page__links-label">Toutes les réalisations</p>
            <NuxtLink
              to="/realisations"
              class="km-page__int-link"
            >
              Voir le portfolio →
            </NuxtLink>
          </div>
        </div>
      </BaseContainer>
    </div>

    <EditorialCallout
      eyebrow="Votre projet"
      title="Un projet de site vitrine pour votre activité ?"
      description="Racontez-nous votre contexte — secteur, objectif, ce que vous avez déjà. Un échange court suffit pour évaluer ensemble ce qui serait utile."
      :primary="{ label: 'Nous contacter', to: '/contact' }"
      :secondary="{ label: 'Estimer mon projet', to: '/estimer-mon-projet' }"
    />

  </article>
</template>

<style scoped>
.km-page {
  display: flex;
  flex-direction: column;
}

/* ─── Hero ─── */
.km-page__hero {
  background-color: var(--background-inverse);
  padding-block: var(--space-16);
}

.km-page__hero-inner {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: center;
}

.km-page__hero-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.km-page__eyebrow {
  color: var(--text-inverse-muted);
}

.km-page__h1 {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2.25rem, 6vw, 3.5rem);
  line-height: 1.05;
  letter-spacing: -0.03em;
  color: var(--text-inverse);
  margin: 0;
}

.km-page__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.5vw, 1.0625rem);
  line-height: 1.65;
  color: var(--text-inverse-muted);
  margin: 0;
  max-width: 56ch;
}

.km-page__tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
  list-style: none;
  padding: 0;
  margin: 0;
}

.km-page__tag {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.625rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--text-inverse-muted);
  padding: 4px 10px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: var(--radius-pill);
}

.km-page__hero-visual {
  border-radius: var(--radius-lg);
  overflow: hidden;
  line-height: 0;
}

.km-page__hero-img {
  width: 100%;
  height: auto;
  display: block;
  object-fit: cover;
}

@media (min-width: 900px) {
  .km-page__hero {
    padding-block: var(--space-20);
  }

  .km-page__hero-inner {
    grid-template-columns: 1fr 1fr;
    gap: var(--space-16);
  }
}

/* ─── Sections ─── */
.km-page__section {
  padding-block: var(--space-16);
  background-color: var(--surface-primary);
}

.km-page__section--subtle {
  background-color: var(--background-secondary);
}

.km-page__section-inner {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.km-page__section-header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 40ch;
}

.km-page__h2 {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.375rem, 3vw, 2rem);
  line-height: 1.15;
  letter-spacing: -0.02em;
  color: var(--text-primary);
  margin: 0;
}

.km-page__prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 62ch;
}

.km-page__prose p {
  font-size: 1rem;
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0;
}

/* ─── Livrables ─── */
.km-page__deliverables {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: 0;
}

.km-page__deliverable {
  display: grid;
  grid-template-columns: 3rem 1fr;
  gap: var(--space-4);
  padding-block: var(--space-6);
  border-top: 1px solid var(--border-default);
  align-items: start;
}

.km-page__deliverable-num {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.05em;
  color: var(--text-muted);
  padding-top: 3px;
}

.km-page__deliverable-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: clamp(0.9375rem, 1.4vw, 1.0625rem);
  line-height: 1.3;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0 0 var(--space-2);
}

.km-page__deliverable-text {
  font-size: 0.9375rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
}

@media (min-width: 768px) {
  .km-page__deliverables {
    grid-template-columns: repeat(2, 1fr);
    column-gap: var(--space-10);
  }

  .km-page__section {
    padding-block: var(--space-20);
  }
}

/* ─── Liens ─── */
.km-page__links-inner {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.km-page__links-group {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.km-page__links-label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--text-muted);
  margin: 0;
}

.km-page__ext-link,
.km-page__int-link {
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

.km-page__ext-link:hover,
.km-page__int-link:hover {
  color: var(--text-primary);
}

.km-page__ext-link:focus-visible,
.km-page__int-link:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
  border-radius: 2px;
}

@media (min-width: 600px) {
  .km-page__links-inner {
    flex-direction: row;
    gap: var(--space-12);
    flex-wrap: wrap;
  }
}

/* a11y */
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
  .km-page__ext-link,
  .km-page__int-link {
    transition: none;
  }
}
</style>
