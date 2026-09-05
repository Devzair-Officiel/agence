<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import SiteBreadcrumb from "~/components/layout/SiteBreadcrumb.vue"
import CaseStudyCard from "~/components/realisations/CaseStudyCard.vue"
import { caseStudies } from "~/config/case-studies"

/**
 * Page hub /realisations — SEO-COM-4.
 *
 * Rôle :
 *   - liste éditoriale des études de cas publiées (status === "published") ;
 *   - point d'entrée SEO sur les intentions "réalisations", "portfolio",
 *     "exemples de sites" ;
 *   - maillage vers les pages détail /realisations/[slug] et vers
 *     /services/creation-site-internet (seul service publié).
 *
 * Schema.org : BreadcrumbList uniquement (via useBreadcrumb).
 * Aucun `CreativeWork`, `Review` ou `Product` inventé.
 *
 * Prerender configuré dans nuxt.config.ts.
 */

const publishedStudies = caseStudies.filter((cs) => cs.status === "published")

const breadcrumbItems = [
  { label: "Accueil", to: "/" },
  { label: "Réalisations" },
]

usePageSeo({
  title: "Nos réalisations — projets web conçus par Devzair",
  description:
    "Découvrez les projets web réalisés par Devzair : sites vitrines, boutiques e-commerce, applications SaaS. Des études de cas honnêtes, sans chiffre inventé.",
  path: "/realisations",
  type: "website",
})

useBreadcrumb(breadcrumbItems)
</script>

<template>
  <div class="realisations-page">
    <SiteBreadcrumb :items="breadcrumbItems" />

    <!-- Hero éditorial -->
    <section class="rp__hero" aria-labelledby="rp-hero-title">
      <BaseContainer width="wide">
        <div class="rp__hero-inner">
          <BaseEyebrow class="rp__eyebrow">Réalisations</BaseEyebrow>
          <h1 id="rp-hero-title" class="rp__h1">
            Des projets web conçus pour un besoin réel.
          </h1>
          <p class="rp__lead">
            Sites vitrines, boutiques e-commerce, applications SaaS — chaque
            projet présenté ici répond à une contrainte métier précise.
            Ni promesse, ni chiffre inventé : ce que vous voyez est ce que nous
            avons livré.
          </p>
        </div>
      </BaseContainer>
    </section>

    <!-- Grille des études de cas -->
    <section class="rp__grid-section" aria-labelledby="rp-grid-title">
      <BaseContainer width="wide">
        <h2 id="rp-grid-title" class="rp__grid-title">
          {{ publishedStudies.length }} études de cas publiées
        </h2>
        <ul class="rp__grid" role="list">
          <li
            v-for="study in publishedStudies"
            :key="study.id"
            class="rp__grid-item"
          >
            <CaseStudyCard :study="study" />
          </li>
        </ul>
      </BaseContainer>
    </section>

    <!-- Section éditoriale : notre approche -->
    <section class="rp__editorial" aria-labelledby="rp-editorial-title">
      <BaseContainer width="wide">
        <div class="rp__editorial-inner">
          <div class="rp__editorial-text">
            <BaseEyebrow>Notre approche</BaseEyebrow>
            <h2 id="rp-editorial-title" class="rp__editorial-h2">
              Ce que ces projets ont en commun.
            </h2>
            <p class="rp__editorial-para">
              Chaque projet démarre par une question simple : à quoi sert ce
              site ou cette application ? La réponse détermine la structure,
              les pages, les interactions — et ce qui peut attendre. Nous ne
              construisons pas de fonctionnalité que vous n'utiliserez pas dans
              les six premiers mois.
            </p>
            <p class="rp__editorial-para">
              Nos réalisations couvrent des secteurs variés — restauration,
              artisanat, commerce, services — mais partagent la même exigence :
              rendre l'information lisible, la navigation fluide et la prise de
              contact évidente. La forme découle du fond, jamais l'inverse.
            </p>
            <p class="rp__editorial-para">
              Pour un projet de site vitrine, la page
              <NuxtLink
                to="/services/creation-site-internet"
                class="rp__inline-link"
              >
                création de site internet
              </NuxtLink>
              détaille notre méthode et les périmètres typiques selon votre
              contexte.
            </p>
          </div>

          <aside class="rp__editorial-aside" aria-label="Point de départ">
            <p class="rp__aside-label">Vous avez un projet en tête ?</p>
            <p class="rp__aside-text">
              L'estimateur vous donne une fourchette en quelques minutes, sans
              engagement. Un échange suit si le contexte s'y prête.
            </p>
            <NuxtLink to="/estimer-mon-projet" class="rp__aside-cta">
              Estimer mon projet →
            </NuxtLink>
          </aside>
        </div>
      </BaseContainer>
    </section>

    <EditorialCallout
      eyebrow="Parler de votre projet"
      title="Un besoin similaire à l'un de ces projets ?"
      description="Racontez-nous votre contexte en quelques lignes. Un échange court suffit pour évaluer si notre façon de travailler correspond à votre situation."
      :primary="{ label: 'Nous contacter', to: '/contact' }"
      :secondary="{ label: 'Estimer mon projet', to: '/estimer-mon-projet' }"
    />
  </div>
</template>

<style scoped>
.realisations-page {
  display: flex;
  flex-direction: column;
}

/* ─── Hero ─── */
.rp__hero {
  background-color: var(--background-secondary);
  padding-block: var(--space-16);
  border-bottom: 1px solid var(--border-default);
}

.rp__hero-inner {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 52rem;
}

.rp__eyebrow {
  margin-bottom: var(--space-2);
}

.rp__h1 {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2rem, 5vw, 3rem);
  line-height: 1.1;
  letter-spacing: -0.025em;
  color: var(--text-primary);
  margin: 0;
  max-width: 22ch;
}

.rp__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.5vw, 1.125rem);
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

@media (min-width: 768px) {
  .rp__hero {
    padding-block: var(--space-20);
  }
}

/* ─── Grille études de cas ─── */
.rp__grid-section {
  background-color: var(--surface-primary);
  padding-block: var(--space-16);
}

.rp__grid-title {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text-muted);
  margin: 0 0 var(--space-10);
}

.rp__grid {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-6);
}

.rp__grid-item {
  display: flex;
  flex-direction: column;
}

@media (min-width: 640px) {
  .rp__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 768px) {
  .rp__grid-section {
    padding-block: var(--space-20);
  }
}

/* ─── Section éditoriale ─── */
.rp__editorial {
  background-color: var(--background-primary);
  padding-block: var(--space-16);
  border-top: 1px solid var(--border-default);
  border-bottom: 1px solid var(--border-default);
}

.rp__editorial-inner {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: start;
}

.rp__editorial-text {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.rp__editorial-h2 {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.375rem, 3vw, 2rem);
  line-height: 1.15;
  letter-spacing: -0.02em;
  color: var(--text-primary);
  margin: 0;
}

.rp__editorial-para {
  font-size: var(--font-size-base, 1rem);
  line-height: 1.7;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

.rp__inline-link {
  color: var(--text-accent);
  text-decoration: underline;
  text-underline-offset: 2px;
  font-weight: var(--font-weight-body-strong);
}

.rp__inline-link:hover {
  color: var(--text-primary);
}

.rp__editorial-aside {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  padding: var(--space-6);
  background-color: var(--background-secondary);
  border: 1px solid var(--border-default);
  border-left: 3px solid var(--color-petrol);
  border-radius: 0 var(--radius-md) var(--radius-md) 0;
}

.rp__aside-label {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1rem;
  color: var(--text-primary);
  margin: 0;
}

.rp__aside-text {
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
}

.rp__aside-cta {
  display: inline-flex;
  align-items: center;
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 0.9375rem;
  color: var(--text-accent);
  text-decoration: none;
  border-bottom: 1px solid currentColor;
  padding-bottom: 2px;
  transition: color var(--duration-fast) var(--ease-out);
}

.rp__aside-cta:hover {
  color: var(--text-primary);
}

.rp__aside-cta:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
  border-radius: 2px;
}

@media (min-width: 900px) {
  .rp__editorial-inner {
    grid-template-columns: 1fr 22rem;
    gap: var(--space-16);
  }

  .rp__editorial-aside {
    align-self: start;
    position: sticky;
    top: calc(var(--site-header-height) + var(--space-6));
  }

  .rp__editorial {
    padding-block: var(--space-20);
  }
}

@media (prefers-reduced-motion: reduce) {
  .rp__aside-cta,
  .rp__inline-link {
    transition: none;
  }
}
</style>
