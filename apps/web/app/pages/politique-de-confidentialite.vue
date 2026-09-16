<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import { legalConfig } from "~/config/legal"
import { useCookieConsent } from "~/composables/useCookieConsent"

const { openPreferences } = useCookieConsent()

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
  <div class="legal-page">
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
                <dt>Nom commercial</dt>
                <dd>Devzair</dd>
              </div>
              <div class="legal-dl__row">
                <dt>SIREN</dt>
                <dd>{{ legalConfig.siren }}</dd>
              </div>
              <div v-if="legalConfig.publisherName" class="legal-dl__row">
                <dt>Responsable</dt>
                <dd>{{ legalConfig.publisherName }}</dd>
              </div>
              <div v-if="legalConfig.publisherAddress" class="legal-dl__row">
                <dt>Adresse</dt>
                <dd>{{ legalConfig.publisherAddress }}</dd>
              </div>
            </dl>
          </section>

          <!-- Données collectées par traitement -->
          <section class="legal-section" aria-labelledby="section-collecte">
            <h2 id="section-collecte" class="legal-section__title">Données collectées</h2>
            <p>
              Devzair collecte des données personnelles dans le cadre de trois
              traitements distincts décrits ci-dessous.
            </p>

            <h3 class="legal-section__subtitle">Formulaire de contact</h3>
            <p>
              Lorsque vous soumettez le formulaire de contact, nous collectons :
            </p>
            <ul class="legal-list">
              <li>votre nom,</li>
              <li>votre adresse e-mail,</li>
              <li>votre société (facultatif),</li>
              <li>votre numéro de téléphone (facultatif),</li>
              <li>le type de projet concerné,</li>
              <li>le contenu de votre message,</li>
              <li>votre prise de connaissance de la présente politique (case à cocher).</li>
            </ul>
            <p>
              Ces données sont transmises par e-mail à l'équipe Devzair et ne
              sont pas enregistrées dans une base de données.
            </p>

            <h3 class="legal-section__subtitle">Estimateur de projet — formulaire de suivi</h3>
            <p>
              Si vous demandez à être recontacté après avoir utilisé notre
              estimateur de projet, nous collectons et conservons en base de données :
            </p>
            <ul class="legal-list">
              <li>votre nom,</li>
              <li>votre adresse e-mail,</li>
              <li>votre numéro de téléphone (facultatif),</li>
              <li>votre société (facultatif),</li>
              <li>vos réponses au questionnaire de l'estimateur,</li>
              <li>le résultat de l'estimation recalculé côté serveur.</li>
            </ul>

            <h3 class="legal-section__subtitle">Formulaire de proposition de partenariat</h3>
            <p>
              Si vous soumettez une proposition de partenariat, nous collectons
              et conservons en base de données, en plus des données de contact
              ci-dessus :
            </p>
            <ul class="legal-list">
              <li>la nature du partenariat envisagé,</li>
              <li>le stade d'avancement de votre projet,</li>
              <li>des informations sur les revenus générés (facultatif),</li>
              <li>la description de votre proposition,</li>
              <li>tout complément d'information (facultatif),</li>
              <li>vos réponses au questionnaire de l'estimateur et le résultat associé.</li>
            </ul>
          </section>

          <!-- Finalité et base légale -->
          <section class="legal-section" aria-labelledby="section-finalite">
            <h2 id="section-finalite" class="legal-section__title">Finalités et bases légales</h2>
            <p>
              Les données sont collectées pour les finalités suivantes :
            </p>
            <ul class="legal-list">
              <li>répondre à votre demande de contact et assurer le suivi précontractuel ;</li>
              <li>produire une estimation indicative du budget de votre projet ;</li>
              <li>vous recontacter au sujet de votre projet si vous en faites la demande ;</li>
              <li>étudier et traiter une proposition de partenariat commercial ;</li>
              <li>protéger nos formulaires contre les soumissions abusives et automatisées.</li>
            </ul>
            <p>
              Les bases légales applicables sont l'<strong>intérêt légitime</strong>
              de Devzair à répondre aux demandes entrantes et à protéger son service,
              et les <strong>mesures précontractuelles</strong> prises à la demande
              de la personne concernée (RGPD, art. 6-1-b et 6-1-f).
            </p>
          </section>

          <!-- Destinataires et infrastructure -->
          <section class="legal-section" aria-labelledby="section-destinataires">
            <h2 id="section-destinataires" class="legal-section__title">Destinataires et infrastructure</h2>
            <p>
              Vos données ne sont ni vendues, ni cédées, ni partagées avec des
              tiers à des fins commerciales.
            </p>
            <p>
              Selon le formulaire utilisé, vos données sont traitées comme suit :
            </p>
            <ul class="legal-list">
              <li>
                <strong>Formulaire de contact :</strong> vos données sont transmises par
                e-mail à l'équipe Devzair via un serveur de messagerie hébergé par
                {{ legalConfig.hostName }}. Elles ne sont pas conservées dans notre
                base de données applicative.
              </li>
              <li>
                <strong>Estimateur / suivi de projet et partenariat :</strong> vos données
                sont enregistrées dans une base de données PostgreSQL hébergée par
                {{ legalConfig.hostName }} ({{ legalConfig.hostAddress }}). Une
                notification e-mail est également envoyée à l'équipe Devzair.
              </li>
            </ul>
            <p>
              {{ legalConfig.hostName }} intervient en qualité de sous-traitant
              au sens du RGPD pour l'hébergement de l'infrastructure.
            </p>
          </section>

          <!-- Protection anti-abus -->
          <section class="legal-section" aria-labelledby="section-anti-abus">
            <h2 id="section-anti-abus" class="legal-section__title">Protection contre les abus</h2>
            <p>
              Nos formulaires sont protégés par des mécanismes techniques
              anti-abus : limitation de débit par adresse IP et champ leurre
              (honeypot) invisible des utilisateurs humains.
            </p>
            <p>
              Lorsqu'il est activé en production, Cloudflare Turnstile peut
              également être utilisé. Ce service de vérification anti-robots
              de Cloudflare, Inc. traite des données techniques (empreinte de
              navigateur, adresse IP) selon sa propre politique de confidentialité.
              Cloudflare intervient alors en qualité de sous-traitant.
              Par défaut, ce service est désactivé.
            </p>
          </section>

          <!-- Durée de conservation -->
          <section class="legal-section" aria-labelledby="section-conservation">
            <h2 id="section-conservation" class="legal-section__title">Durée de conservation</h2>
            <p>
              Les durées de conservation varient selon le traitement :
            </p>
            <ul class="legal-list">
              <li>
                <strong>Formulaire de contact :</strong> vos données ne sont pas
                conservées dans notre base de données. Elles sont présentes dans la
                messagerie de l'équipe Devzair le temps nécessaire au traitement de
                votre demande, sans excéder <strong>trente-six (36) mois</strong>.
              </li>
              <li>
                <strong>Estimateur — suivi de projet :</strong> les données enregistrées
                en base sont supprimées automatiquement au bout de
                <strong>vingt-quatre (24) mois</strong> à compter de la date de
                soumission.
              </li>
              <li>
                <strong>Propositions de partenariat :</strong> même durée, soit
                <strong>vingt-quatre (24) mois</strong>, avec purge automatique.
              </li>
            </ul>
            <p>
              Ces durées peuvent être réduites sur demande de suppression de votre part.
            </p>
          </section>

          <!-- Cookies et traceurs -->
          <section class="legal-section" aria-labelledby="section-cookies">
            <h2 id="section-cookies" class="legal-section__title">Cookies et traceurs</h2>
            <p>
              Devzair n'utilise actuellement pas de traceurs publicitaires ou
              de mesure d'audience nécessitant un consentement préalable.
              Aucun cookie de traçage, script d'analyse d'audience tiers ni
              pixel publicitaire n'est actif sur ce site.
            </p>

            <h3 class="legal-section__subtitle">Cookies strictement nécessaires</h3>
            <p>
              Certains mécanismes strictement nécessaires au fonctionnement ou
              à la sécurité du site peuvent utiliser des données techniques
              (session de navigation, préférences de consentement stockées
              localement). Ces mécanismes ne nécessitent pas de consentement
              préalable.
            </p>
            <p>
              Les préférences de cookies sont conservées dans le stockage local
              de votre navigateur (<em>localStorage</em>) sous la clé
              <code>dz_cookie_prefs</code>. Elles ne contiennent aucune donnée
              personnelle et peuvent être modifiées ou supprimées à tout moment.
            </p>

            <h3 class="legal-section__subtitle">Cloudflare Turnstile</h3>
            <p>
              Lorsque Cloudflare Turnstile est activé (désactivé par défaut),
              un script tiers de Cloudflare est chargé et peut déposer un
              cookie technique lié à la vérification anti-robots. Ce cookie est
              strictement nécessaire à la protection du service contre les
              soumissions automatisées. Cloudflare intervient alors en qualité
              de sous-traitant.
            </p>

            <h3 class="legal-section__subtitle">Gestion de vos préférences</h3>
            <p>
              Vous pouvez consulter et modifier vos préférences de cookies à
              tout moment via le lien
              <button
                class="legal-link legal-link--btn"
                type="button"
                @click="openPreferences"
              >Gérer mes cookies</button>
              disponible dans le pied de page.
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

.legal-section__subtitle {
  font-family: var(--font-family-heading);
  font-size: 0.9375rem;
  font-weight: var(--font-weight-body-strong);
  color: var(--color-ink);
  margin: 0;
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

.legal-link--btn {
  appearance: none;
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  font: inherit;
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
