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
  <article class="am-page" aria-labelledby="am-title">

    <!-- Hero -->
    <div class="am-page__hero">
      <BaseContainer width="wide">
        <div class="am-page__hero-inner">
          <div class="am-page__hero-content">
            <BaseEyebrow>{{ study.category }}</BaseEyebrow>
            <h1 id="am-title" class="am-page__h1">{{ study.name }}</h1>
            <p class="am-page__lead">{{ study.longDescription }}</p>
            <ul class="am-page__tags" aria-label="Interventions">
              <li
                v-for="tag in study.tags"
                :key="tag"
                class="am-page__tag"
              >
                {{ tag }}
              </li>
            </ul>
          </div>
          <div class="am-page__hero-visual" aria-hidden="true">
            <img
              :src="study.imageSrc"
              :alt="study.imageAlt"
              class="am-page__hero-img"
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
    <div class="am-page__section am-page__section--subtle">
      <BaseContainer width="wide">
        <div class="am-page__section-inner">
          <header class="am-page__section-header">
            <BaseEyebrow>Le projet</BaseEyebrow>
            <h2 class="am-page__h2">
              Un site B2B pour un atelier artisanal de chachias.
            </h2>
          </header>
          <div class="am-page__prose">
            <p>
              Al Mumayiz est un atelier artisanal spécialisé dans la fabrication
              de chachias — coiffures traditionnelles portées en Afrique du Nord
              et au Moyen-Orient. Le site s'adresse exclusivement à un public
              professionnel : revendeurs, distributeurs et boutiques spécialisées.
            </p>
            <p>
              Le positionnement B2B impliquait une présentation centrée sur
              le savoir-faire de l'atelier, la constance des produits et la
              fiabilité de la relation commerciale — pas sur une boutique grand
              public. L'objectif principal était de simplifier la prise de
              contact pour ce public métier.
            </p>
          </div>
        </div>
      </BaseContainer>
    </div>

    <!-- Périmètre d'intervention -->
    <div class="am-page__section">
      <BaseContainer width="wide">
        <div class="am-page__section-inner">
          <header class="am-page__section-header">
            <BaseEyebrow>Périmètre d'intervention</BaseEyebrow>
            <h2 class="am-page__h2">Ce que nous avons conçu et livré.</h2>
          </header>
          <ul class="am-page__deliverables" role="list">
            <li class="am-page__deliverable">
              <span class="am-page__deliverable-num" aria-hidden="true">01</span>
              <div>
                <h3 class="am-page__deliverable-title">Architecture B2B</h3>
                <p class="am-page__deliverable-text">
                  Structure adaptée aux acheteurs professionnels — présentation
                  du savoir-faire, gammes de produits, conditions et parcours
                  de contact direct sans friction.
                </p>
              </div>
            </li>
            <li class="am-page__deliverable">
              <span class="am-page__deliverable-num" aria-hidden="true">02</span>
              <div>
                <h3 class="am-page__deliverable-title">Présentation du savoir-faire</h3>
                <p class="am-page__deliverable-text">
                  Mise en valeur des matières, du processus de fabrication et
                  de la constance qualitative — les critères décisifs pour
                  un revendeur ou distributeur.
                </p>
              </div>
            </li>
            <li class="am-page__deliverable">
              <span class="am-page__deliverable-num" aria-hidden="true">03</span>
              <div>
                <h3 class="am-page__deliverable-title">UX/UI design</h3>
                <p class="am-page__deliverable-text">
                  Identité sobre et artisanale. Interface épurée qui laisse le
                  produit s'exprimer — sans surcharge graphique, sans éléments
                  grand public.
                </p>
              </div>
            </li>
            <li class="am-page__deliverable">
              <span class="am-page__deliverable-num" aria-hidden="true">04</span>
              <div>
                <h3 class="am-page__deliverable-title">Formulaire de contact professionnel</h3>
                <p class="am-page__deliverable-text">
                  Formulaire adapté aux demandes B2B — type de structure,
                  volume estimé, localisation — pour qualifier le contact
                  avant le premier échange.
                </p>
              </div>
            </li>
          </ul>
        </div>
      </BaseContainer>
    </div>

    <!-- Liens -->
    <div class="am-page__section am-page__section--subtle">
      <BaseContainer width="wide">
        <div class="am-page__links-inner">
          <div class="am-page__links-group">
            <p class="am-page__links-label">Site en ligne</p>
            <a
              :href="study.href"
              target="_blank"
              rel="noopener noreferrer"
              class="am-page__ext-link"
            >
              Voir le site Al Mumayiz
              <span class="sr-only">(nouvelle fenêtre)</span>
              <span aria-hidden="true"> ↗</span>
            </a>
          </div>
          <div class="am-page__links-group">
            <p class="am-page__links-label">Service associé</p>
            <NuxtLink
              to="/services/creation-site-internet"
              class="am-page__int-link"
            >
              Création de site internet →
            </NuxtLink>
          </div>
          <div class="am-page__links-group">
            <p class="am-page__links-label">Toutes les réalisations</p>
            <NuxtLink
              to="/realisations"
              class="am-page__int-link"
            >
              Voir le portfolio →
            </NuxtLink>
          </div>
        </div>
      </BaseContainer>
    </div>

    <EditorialCallout
      eyebrow="Votre projet"
      title="Un site B2B pour présenter votre activité à un public professionnel ?"
      description="La structure d'un site B2B est différente d'un site grand public. Parlez-nous de votre cible et de ce qu'elle cherche en priorité."
      :primary="{ label: 'Nous contacter', to: '/contact' }"
      :secondary="{ label: 'Estimer mon projet', to: '/estimer-mon-projet' }"
    />

  </article>
</template>

<style scoped>
.am-page {
  display: flex;
  flex-direction: column;
}

.am-page__hero {
  background-color: var(--background-secondary);
  border-bottom: 1px solid var(--border-default);
  padding-block: var(--space-16);
}

.am-page__hero-inner {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: center;
}

.am-page__hero-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}

.am-page__h1 {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2.25rem, 6vw, 3.5rem);
  line-height: 1.05;
  letter-spacing: -0.03em;
  color: var(--text-primary);
  margin: 0;
}

.am-page__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.5vw, 1.0625rem);
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 56ch;
}

.am-page__tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
  list-style: none;
  padding: 0;
  margin: 0;
}

.am-page__tag {
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

.am-page__hero-visual {
  border-radius: var(--radius-lg);
  overflow: hidden;
  line-height: 0;
}

.am-page__hero-img {
  width: 100%;
  height: auto;
  display: block;
  object-fit: cover;
}

@media (min-width: 900px) {
  .am-page__hero {
    padding-block: var(--space-20);
  }

  .am-page__hero-inner {
    grid-template-columns: 1fr 1fr;
    gap: var(--space-16);
  }
}

.am-page__section {
  padding-block: var(--space-16);
  background-color: var(--surface-primary);
}

.am-page__section--subtle {
  background-color: var(--background-secondary);
}

.am-page__section-inner {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.am-page__section-header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 40ch;
}

.am-page__h2 {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.375rem, 3vw, 2rem);
  line-height: 1.15;
  letter-spacing: -0.02em;
  color: var(--text-primary);
  margin: 0;
}

.am-page__prose {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 62ch;
}

.am-page__prose p {
  font-size: 1rem;
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0;
}

.am-page__deliverables {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: 0;
}

.am-page__deliverable {
  display: grid;
  grid-template-columns: 3rem 1fr;
  gap: var(--space-4);
  padding-block: var(--space-6);
  border-top: 1px solid var(--border-default);
  align-items: start;
}

.am-page__deliverable-num {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.05em;
  color: var(--text-muted);
  padding-top: 3px;
}

.am-page__deliverable-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: clamp(0.9375rem, 1.4vw, 1.0625rem);
  line-height: 1.3;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0 0 var(--space-2);
}

.am-page__deliverable-text {
  font-size: 0.9375rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
}

@media (min-width: 768px) {
  .am-page__deliverables {
    grid-template-columns: repeat(2, 1fr);
    column-gap: var(--space-10);
  }

  .am-page__section {
    padding-block: var(--space-20);
  }
}

.am-page__links-inner {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.am-page__links-group {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.am-page__links-label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--text-muted);
  margin: 0;
}

.am-page__ext-link,
.am-page__int-link {
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

.am-page__ext-link:hover,
.am-page__int-link:hover {
  color: var(--text-primary);
}

.am-page__ext-link:focus-visible,
.am-page__int-link:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
  border-radius: 2px;
}

@media (min-width: 600px) {
  .am-page__links-inner {
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
  .am-page__ext-link,
  .am-page__int-link {
    transition: none;
  }
}
</style>
