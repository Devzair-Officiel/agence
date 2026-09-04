<script setup lang="ts">
import EditorialCallout from "~/components/editorial/EditorialCallout.vue"
import EditorialHero from "~/components/editorial/EditorialHero.vue"
import EditorialSection from "~/components/editorial/EditorialSection.vue"
import { servicePages } from "~/config/service-pages"

/**
 * Page `/services` — hub commercial orienté intentions de recherche.
 *
 * Rôle d'orchestration :
 *   - un H1 unique porté par EditorialHero ;
 *   - une section éditoriale d'orientation qui aide le prospect à se situer
 *     selon son contexte (création, visibilité, maintenance) ;
 *   - une grille des huit services, liés vers leur page dédiée uniquement
 *     si `status === "published"` — jamais pour `planned` ;
 *   - un EditorialCallout vers l'estimateur et le contact.
 *
 * Distinction avec `/expertises` :
 *   - `/expertises/**` répond à « comment Devzair travaille-t-il ? » ;
 *   - `/services/**` répond à « de quoi mon projet a-t-il besoin ? ».
 *
 * SEO : `usePageSeo` seul. Prerender configuré dans `nuxt.config.ts`.
 * Le hub est la seule page publiée de SEO-COM-1 — les huit pages filles
 * restent `planned` et n'apparaissent ni dans le sitemap ni dans les liens.
 */

const publishedServices = servicePages.filter((s) => s.status === "published")

usePageSeo({
  title: "Nos services web : création, visibilité et accompagnement",
  description:
    "Création de site internet, e-commerce, application web, SEO, design, contenu et maintenance. Devzair couvre l'ensemble du cycle numérique selon le besoin réel de votre projet.",
  path: "/services",
  type: "website",
})
</script>

<template>
  <div class="services-page">
    <EditorialHero
      eyebrow="Nos services"
      title="Créer, rendre visible et faire évoluer votre présence en ligne."
      lead="Selon votre situation — un premier site à lancer, un référencement à améliorer, une maintenance à structurer — nous définissons le périmètre utile avant de nous engager sur quoi que ce soit."
    >
      <template #actions>
        <NuxtLink
          to="/estimer-mon-projet"
          class="services-page__hero-cta services-page__hero-cta--primary"
        >
          Estimer mon projet
        </NuxtLink>
        <NuxtLink
          to="/contact"
          class="services-page__hero-cta services-page__hero-cta--secondary"
        >
          Parler de votre projet
        </NuxtLink>
      </template>
    </EditorialHero>

    <EditorialSection
      tone="subtle"
      eyebrow="Trouver le bon point de départ"
      title="Chaque situation appelle une réponse différente."
      section-id="orientation"
    >
      <div class="services-page__orientation">
        <div class="services-page__orientation-block">
          <h3 class="services-page__orientation-title">
            Vous n'avez pas encore de site — ou votre site actuel ne vous représente plus.
          </h3>
          <p class="services-page__orientation-text">
            Les services de création de site, e-commerce et identité visuelle correspondent à ce point de départ : poser une base solide, lisible et cohérente avec ce que vous faites réellement. Avant le développement, il y a souvent une phase de conception qui conditionne la suite.
          </p>
        </div>

        <div class="services-page__orientation-block">
          <h3 class="services-page__orientation-title">
            Vous avez un site, mais peu de visiteurs qualifiés.
          </h3>
          <p class="services-page__orientation-text">
            Le SEO, le référencement local et la création de contenu travaillent sur la visibilité organique et la crédibilité du message. Ce sont des chantiers de fond qui demandent plusieurs mois pour produire des résultats durables — pas des leviers immédiats.
          </p>
        </div>

        <div class="services-page__orientation-block">
          <h3 class="services-page__orientation-title">
            Vous avez un site en ligne et avez besoin d'un suivi dans le temps.
          </h3>
          <p class="services-page__orientation-text">
            La maintenance, les mises à jour de sécurité et les petites évolutions fonctionnelles ne se traitent pas efficacement en urgence. Un cadre régulier vaut mieux qu'une réaction tardive : moins coûteux, plus prévisible, plus serein.
          </p>
        </div>
      </div>
    </EditorialSection>

    <EditorialSection
      tone="default"
      eyebrow="Huit services"
      title="Ce que nous faisons concrètement."
      intro="Ces services peuvent se combiner selon l'avancement de votre projet. Plusieurs entreprises commencent par la création d'un site, ajoutent le SEO quelques mois plus tard et consolident avec une maintenance régulière."
      section-id="liste-des-services"
    >
      <ul class="services-page__grid" role="list">
        <li
          v-for="service in servicePages"
          :key="service.id"
          class="services-page__grid-item"
        >
          <NuxtLink
            v-if="service.status === 'published'"
            :to="service.route"
            class="services-page__service-card services-page__service-card--link"
          >
            <h3 class="services-page__service-title">{{ service.title }}</h3>
            <p class="services-page__service-summary">{{ service.summary }}</p>
            <span class="services-page__service-link-hint" aria-hidden="true">
              En savoir plus →
            </span>
          </NuxtLink>
          <article v-else class="services-page__service-card">
            <h3 class="services-page__service-title">{{ service.title }}</h3>
            <p class="services-page__service-summary">{{ service.summary }}</p>
          </article>
        </li>
      </ul>
      <p
        v-if="publishedServices.length === 0"
        class="services-page__coming-soon"
      >
        Les pages détaillées de chaque service sont en cours de rédaction et seront publiées progressivement.
      </p>
    </EditorialSection>

    <EditorialCallout
      eyebrow="Évaluer votre projet"
      title="Pas encore sûr de ce dont vous avez besoin ?"
      description="L'estimateur de projet vous aide à identifier les composantes utiles à votre situation en quelques minutes, sans engagement."
      :primary="{ label: 'Estimer mon projet', to: '/estimer-mon-projet' }"
      :secondary="{ label: 'Parler de votre projet', to: '/contact' }"
    />
  </div>
</template>

<style scoped>
.services-page {
  display: flex;
  flex-direction: column;
}

/* Hero CTAs */
.services-page__hero-cta {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-3) var(--space-5);
  border-radius: var(--radius-md);
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  font-weight: 600;
  line-height: 1.2;
  text-decoration: none;
  transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}

.services-page__hero-cta--primary {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border: 2px solid var(--color-petrol);
}

.services-page__hero-cta--primary:hover {
  background-color: var(--color-navy);
  border-color: var(--color-navy);
}

.services-page__hero-cta--secondary {
  background-color: transparent;
  color: var(--text-primary);
  border: 2px solid currentColor;
}

.services-page__hero-cta--secondary:hover {
  background-color: var(--color-petrol);
  color: var(--color-cream);
  border-color: var(--color-petrol);
}

/* Section d'orientation : trois blocs contextuels */
.services-page__orientation {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.services-page__orientation-block {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding-left: var(--space-4);
  border-left: 2px solid var(--color-petrol);
}

.services-page__orientation-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.6vw, 1.125rem);
  line-height: 1.3;
  color: var(--text-primary);
  margin: 0;
}

.services-page__orientation-text {
  font-family: var(--font-family-body);
  font-size: clamp(0.9375rem, 1.2vw, 1rem);
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

/* Grille des huit services */
.services-page__grid {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
}

.services-page__grid-item {
  display: flex;
}

/* Carte service — base commune */
.services-page__service-card {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding: var(--space-5);
  background-color: var(--background-secondary);
  border: 1px solid rgba(12, 91, 87, 0.12);
  border-radius: var(--radius-md);
  width: 100%;
  text-decoration: none;
  color: inherit;
}

/* Variante lien — transition au hover uniquement sur les pages publiées */
.services-page__service-card--link {
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.services-page__service-card--link:hover {
  border-color: var(--color-petrol);
  box-shadow: 0 2px 8px rgba(12, 91, 87, 0.08);
}

.services-page__service-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  line-height: 1.25;
  color: var(--text-primary);
  margin: 0;
}

.services-page__service-summary {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
  flex: 1;
}

.services-page__service-link-hint {
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  color: var(--color-petrol);
  font-weight: 600;
  margin-top: var(--space-1);
}

/* Mention "en cours de rédaction" */
.services-page__coming-soon {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  color: var(--text-secondary);
  margin: 0;
  padding: var(--space-4);
  background-color: var(--background-secondary);
  border-radius: var(--radius-md);
  border-left: 2px solid rgba(12, 91, 87, 0.2);
}

@media (min-width: 600px) {
  .services-page__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 768px) {
  .services-page__orientation {
    gap: var(--space-10);
  }
}

@media (min-width: 1080px) {
  .services-page__grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}
</style>
