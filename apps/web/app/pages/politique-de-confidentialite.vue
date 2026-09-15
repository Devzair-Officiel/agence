<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import { legalConfig } from "~/config/legal"

// Page non indexée : URL purement réglementaire, pas de valeur SEO organique.
// `robots: "noindex, follow"` pour conserver le canonical (cf. nuxt.config).
usePageSeo({
  title: "Politique de confidentialité",
  description: "Politique de confidentialité de l'agence digitale Devzair : traitement des données personnelles et droits des personnes.",
  path: "/politique-de-confidentialite",
  robots: "noindex, follow",
})
</script>

<template>
  <main id="main-content" class="legal-page">
    <BaseContainer>
      <div class="legal-page__wrapper">
        <header class="legal-page__header">
          <h1 class="legal-page__title">Politique de confidentialité</h1>
          <p class="legal-page__intro">
            Cette politique décrit comment Devzair collecte, utilise et protège
            les données personnelles que vous nous confiez via ce site.
          </p>
        </header>

        <div class="legal-page__content">

          <!-- Responsable du traitement -->
          <section class="legal-section" aria-labelledby="section-responsable">
            <h2 id="section-responsable" class="legal-section__title">Responsable du traitement</h2>
            <dl class="legal-dl">
              <div class="legal-dl__row">
                <dt>Raison sociale</dt>
                <dd>Devzair</dd>
              </div>
              <div class="legal-dl__row">
                <dt>SIREN</dt>
                <dd>{{ legalConfig.siren }}</dd>
              </div>
              <div v-if="legalConfig.publisherAddress" class="legal-dl__row">
                <dt>Adresse</dt>
                <dd>{{ legalConfig.publisherAddress }}</dd>
              </div>
            </dl>
          </section>

          <!-- Données collectées -->
          <section class="legal-section" aria-labelledby="section-collecte">
            <h2 id="section-collecte" class="legal-section__title">Données collectées</h2>
            <p>
              Lorsque vous soumettez le formulaire de contact, nous collectons :
            </p>
            <ul class="legal-list">
              <li>votre nom ou prénom,</li>
              <li>votre adresse e-mail,</li>
              <li>le contenu du message que vous nous transmettez.</li>
            </ul>
            <p>
              Aucune autre donnée n'est collectée sur ce site. Nous n'utilisons
              pas de cookies de traçage, de scripts d'analyse tiers ni de
              pixels publicitaires.
            </p>
          </section>

          <!-- Finalité et base légale -->
          <section class="legal-section" aria-labelledby="section-finalite">
            <h2 id="section-finalite" class="legal-section__title">Finalité et base légale</h2>
            <p>
              Les données collectées ont pour seule finalité de traiter votre
              demande de contact et d'assurer le suivi de notre relation commerciale.
            </p>
            <p>
              La base légale du traitement est l'<strong>intérêt légitime</strong>
              de Devzair à répondre aux demandes entrantes, et les
              <strong>mesures précontractuelles</strong> prises à la demande de
              la personne concernée (RGPD, art. 6-1-b et 6-1-f).
            </p>
          </section>

          <!-- Destinataires -->
          <section class="legal-section" aria-labelledby="section-destinataires">
            <h2 id="section-destinataires" class="legal-section__title">Destinataires des données</h2>
            <p>
              Vos données sont transmises exclusivement par e-mail à l'équipe
              Devzair. Elles ne sont ni vendues, ni cédées, ni partagées avec
              des tiers à des fins commerciales.
            </p>
            <p>
              Nos courriels transitent via un serveur hébergé par
              <strong>{{ legalConfig.hostName }}</strong>.
            </p>
          </section>

          <!-- Durée de conservation -->
          <section class="legal-section" aria-labelledby="section-conservation">
            <h2 id="section-conservation" class="legal-section__title">Durée de conservation</h2>
            <p>
              Nous conservons vos données pendant une durée maximale de
              <strong>trente-six (36) mois</strong> à compter de votre dernière
              prise de contact, sauf obligation légale contraire ou demande de
              suppression de votre part.
            </p>
          </section>

          <!-- Vos droits -->
          <section class="legal-section" aria-labelledby="section-droits">
            <h2 id="section-droits" class="legal-section__title">Vos droits</h2>
            <p>
              Conformément au RGPD (articles 15 à 22), vous disposez des droits
              suivants concernant vos données personnelles :
            </p>
            <ul class="legal-list">
              <li>droit d'accès,</li>
              <li>droit de rectification,</li>
              <li>droit à l'effacement (« droit à l'oubli »),</li>
              <li>droit à la limitation du traitement,</li>
              <li>droit à la portabilité,</li>
              <li>droit d'opposition.</li>
            </ul>
            <p>
              Pour exercer ces droits, adressez une demande à
              <NuxtLink to="/contact" class="legal-link">
                notre formulaire de contact
              </NuxtLink>.
              Nous nous engageons à vous répondre dans un délai d'un mois.
            </p>
            <p>
              Si vous estimez que vos droits ne sont pas respectés, vous pouvez
              introduire une réclamation auprès de la
              <a href="https://www.cnil.fr" target="_blank" rel="noopener noreferrer" class="legal-link">
                CNIL
              </a>
              (Commission Nationale de l'Informatique et des Libertés).
            </p>
          </section>

          <!-- Sécurité -->
          <section class="legal-section" aria-labelledby="section-securite">
            <h2 id="section-securite" class="legal-section__title">Sécurité</h2>
            <p>
              Nous mettons en œuvre des mesures techniques et organisationnelles
              appropriées pour protéger vos données contre tout accès non
              autorisé, altération, divulgation ou destruction.
            </p>
            <p>
              Les échanges entre votre navigateur et notre serveur sont chiffrés
              via le protocole HTTPS/TLS.
            </p>
          </section>

          <!-- Mise à jour -->
          <section class="legal-section" aria-labelledby="section-miseajour">
            <h2 id="section-miseajour" class="legal-section__title">Mise à jour</h2>
            <p>
              Cette politique de confidentialité peut être modifiée à tout moment
              pour s'adapter à l'évolution de nos pratiques ou des obligations
              légales. La version en vigueur est celle publiée sur cette page.
            </p>
            <p>
              Pour toute question, utilisez notre
              <NuxtLink to="/contact" class="legal-link">formulaire de contact</NuxtLink>.
            </p>
          </section>

        </div>
      </div>
    </BaseContainer>
  </main>
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

.legal-list {
  font-size: 0.9375rem;
  color: var(--color-ink-muted);
  line-height: 1.65;
  margin: 0;
  padding-inline-start: var(--space-5);
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
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

@media (max-width: 540px) {
  .legal-dl__row {
    grid-template-columns: 1fr;
    gap: var(--space-1);
  }
}
</style>
