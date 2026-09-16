<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import { legalConfig } from "~/config/legal"

// Page non indexée : URL purement réglementaire, pas de valeur SEO organique.
// `robots: "noindex, follow"` (et non `noindex: true`) pour conserver le
// canonical et rester conforme à la politique routeRules (cf. nuxt.config).
usePageSeo({
  title: "Mentions légales",
  description: "Mentions légales de l'agence digitale Devzair : éditeur, hébergeur et informations réglementaires.",
  path: "/mentions-legales",
  robots: "noindex, follow",
})
</script>

<template>
  <div class="legal-page">
    <BaseContainer>
      <div class="legal-page__wrapper">
        <header class="legal-page__header">
          <h1 class="legal-page__title">Mentions légales</h1>
          <p class="legal-page__intro">
            Conformément aux dispositions de la loi n° 2004-575 du 21 juin 2004
            pour la confiance dans l'économie numérique (LCEN), nous vous
            informons des mentions légales relatives au site
            <strong>devzair.fr</strong>.
          </p>
        </header>

        <div class="legal-page__content">

          <!-- Éditeur -->
          <section class="legal-section" aria-labelledby="section-editeur">
            <h2 id="section-editeur" class="legal-section__title">Éditeur du site</h2>
            <dl class="legal-dl">
              <div class="legal-dl__row">
                <dt>Nom commercial</dt>
                <dd>Devzair</dd>
              </div>
              <div class="legal-dl__row">
                <dt>Forme juridique</dt>
                <dd>Entrepreneur individuel</dd>
              </div>
              <div v-if="legalConfig.publisherName" class="legal-dl__row">
                <dt>Éditeur</dt>
                <dd>{{ legalConfig.publisherName }}</dd>
              </div>
              <div class="legal-dl__row">
                <dt>SIREN</dt>
                <dd>{{ legalConfig.siren }}</dd>
              </div>
              <div class="legal-dl__row">
                <dt>SIRET</dt>
                <dd>{{ legalConfig.siret }}</dd>
              </div>
              <div class="legal-dl__row">
                <dt>Immatriculation</dt>
                <dd>{{ legalConfig.rcs }}</dd>
              </div>
              <div class="legal-dl__row">
                <dt>Code NAF / APE</dt>
                <dd>{{ legalConfig.naf }}</dd>
              </div>
              <div class="legal-dl__row">
                <dt>Numéro de TVA intracommunautaire</dt>
                <dd>{{ legalConfig.vatNumber }}</dd>
              </div>
              <div v-if="legalConfig.publisherAddress" class="legal-dl__row">
                <dt>Adresse</dt>
                <dd>{{ legalConfig.publisherAddress }}</dd>
              </div>
              <div v-if="legalConfig.publisherPhone" class="legal-dl__row">
                <dt>Téléphone</dt>
                <dd>{{ legalConfig.publisherPhone }}</dd>
              </div>
              <div v-if="legalConfig.publicationDirector" class="legal-dl__row">
                <dt>Directeur de la publication</dt>
                <dd>{{ legalConfig.publicationDirector }}</dd>
              </div>
            </dl>
            <p v-if="!legalConfig.publisherName || !legalConfig.publisherAddress || !legalConfig.publisherPhone" class="legal-section__notice">
              Les coordonnées complètes de l'éditeur seront publiées dès leur
              validation définitive.
            </p>
          </section>

          <!-- Hébergeur -->
          <section class="legal-section" aria-labelledby="section-hebergeur">
            <h2 id="section-hebergeur" class="legal-section__title">Hébergeur</h2>
            <dl class="legal-dl">
              <div class="legal-dl__row">
                <dt>Société</dt>
                <dd>{{ legalConfig.hostName }}</dd>
              </div>
              <div class="legal-dl__row">
                <dt>Adresse</dt>
                <dd>{{ legalConfig.hostAddress }}</dd>
              </div>
              <div class="legal-dl__row">
                <dt>Site web</dt>
                <dd>
                  <a :href="legalConfig.hostWebsite" target="_blank" rel="noopener noreferrer" class="legal-link">
                    {{ legalConfig.hostWebsite }}
                  </a>
                </dd>
              </div>
              <div v-if="legalConfig.hostPhone" class="legal-dl__row">
                <dt>Téléphone</dt>
                <dd>{{ legalConfig.hostPhone }}</dd>
              </div>
            </dl>
          </section>

          <!-- Propriété intellectuelle -->
          <section class="legal-section" aria-labelledby="section-pi">
            <h2 id="section-pi" class="legal-section__title">Propriété intellectuelle</h2>
            <p>
              L'ensemble des contenus présents sur ce site (textes, images,
              graphismes, logotypes) est la propriété exclusive de Devzair ou
              de ses partenaires, et est protégé par les lois françaises et
              internationales relatives à la propriété intellectuelle.
            </p>
            <p>
              Toute reproduction, représentation ou diffusion, totale ou
              partielle, sans autorisation préalable écrite est strictement
              interdite.
            </p>
          </section>

          <!-- Données personnelles -->
          <section class="legal-section" aria-labelledby="section-donnees">
            <h2 id="section-donnees" class="legal-section__title">Données personnelles</h2>
            <p>
              Les informations recueillies via le formulaire de contact sont
              utilisées exclusivement pour répondre à votre demande. Pour en
              savoir plus sur la gestion de vos données, consultez notre
              <NuxtLink to="/politique-de-confidentialite" class="legal-link">
                politique de confidentialité
              </NuxtLink>.
            </p>
          </section>

          <!-- Liens hypertexte -->
          <section class="legal-section" aria-labelledby="section-liens">
            <h2 id="section-liens" class="legal-section__title">Liens hypertexte</h2>
            <p>
              Ce site peut contenir des liens vers d'autres sites. Devzair
              n'exerce aucun contrôle sur ces sites tiers et décline toute
              responsabilité quant à leur contenu ou leur politique de
              confidentialité.
            </p>
          </section>

          <!-- Droit applicable -->
          <section class="legal-section" aria-labelledby="section-droit">
            <h2 id="section-droit" class="legal-section__title">Droit applicable</h2>
            <p>
              Les présentes mentions légales sont soumises au droit français.
              En cas de litige, les juridictions compétentes seront déterminées
              conformément aux règles de droit applicables.
            </p>
          </section>

        </div>
      </div>
    </BaseContainer>
  </div>
</template>

<style scoped>
.legal-page {
  padding-block: var(--space-16) var(--space-20);
  background: var(--background-primary);
  min-height: 60vh;
}

.legal-page__wrapper {
  max-width: 720px;
}

.legal-page__header {
  margin-block-end: var(--space-12);
}

.legal-page__title {
  font-family: var(--font-family-heading);
  font-size: clamp(1.75rem, 4vw, 2.5rem);
  font-weight: var(--font-weight-heading);
  color: var(--color-ink);
  line-height: 1.2;
  margin: 0 0 var(--space-4);
}

.legal-page__intro {
  font-size: 1rem;
  color: var(--color-ink-muted);
  line-height: 1.65;
  margin: 0;
}

.legal-page__content {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.legal-section {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.legal-section__title {
  font-family: var(--font-family-heading);
  font-size: 1.125rem;
  font-weight: var(--font-weight-body-strong);
  color: var(--color-ink);
  margin: 0;
  padding-block-end: var(--space-2);
  border-bottom: 1px solid var(--border-default);
}

.legal-section p {
  font-size: 0.9375rem;
  color: var(--color-ink-muted);
  line-height: 1.65;
  margin: 0;
}

.legal-section__notice {
  font-size: 0.875rem;
  color: var(--color-ink-subtle);
  font-style: italic;
}

.legal-dl {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  margin: 0;
}

.legal-dl__row {
  display: grid;
  grid-template-columns: minmax(180px, 240px) 1fr;
  gap: var(--space-3);
  font-size: 0.9375rem;
  line-height: 1.5;
}

.legal-dl__row dt {
  color: var(--color-ink-subtle);
  font-weight: var(--font-weight-body-strong);
}

.legal-dl__row dd {
  color: var(--color-ink);
  margin: 0;
}

.legal-link {
  color: var(--color-petrol);
  text-decoration: underline;
  text-underline-offset: 2px;
}

.legal-link:hover {
  color: var(--color-petrol-hover);
}

.legal-link:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
  border-radius: 2px;
}

@media (max-width: 540px) {
  .legal-dl__row {
    grid-template-columns: 1fr;
    gap: var(--space-1);
  }
}
</style>
