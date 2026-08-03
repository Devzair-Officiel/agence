<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import ContactForm from "~/components/contact/ContactForm.vue"

/**
 * Page dédiée `/contact` — accueille le formulaire complet extrait de la
 * section CTA de la page d'accueil (auparavant `#contact` sur `/`).
 *
 * Composition — colonne unique centrée, ambiance chaude (fond sable +
 * carte cream élevée). L'ancien split navy/cream écrasait l'intro et
 * cassait la lecture du titre ; on préfère un rythme vertical simple :
 *
 *   1. hero centré : eyebrow, H1 (accent métallique sur « présence
 *      digitale »), lead ;
 *   2. frise « 1 / 2 / 3 » sur trois cartes horizontales sur desktop
 *      (empilées sur mobile) — même pastille numérotée que le mock, mais
 *      posée sur des cartes cream pour rester dans la palette chaude ;
 *   3. carte formulaire cream centrée (≤ 780 px) — le champ d'attention
 *      principal est le form, tout le reste sert de mise en confiance.
 *
 * Contraintes éditoriales (AGENTS.md §1) : pas d'engagement chiffré non
 * validé (« 24 h ouvrées », « proposition chiffrée »). Pas de bloc
 * « coordonnées » (site.contact null → règle §11 interdit tout
 * placeholder public).
 *
 * Accessibilité :
 *   - un unique H1 par page (celui-ci) ;
 *   - la frise est un `<ol>` sémantique ;
 *   - les pastilles chiffrées sont `aria-hidden` : le contenu textuel
 *     du `<li>` porte déjà l'information pour AT.
 *
 * SEO :
 *   - `usePageSeo` pour title/description/canonical/OG, `type: website` ;
 *   - `noindex` non appliqué : « devzair contact » est une requête
 *     légitime.
 */

const runtime = useRuntimeConfig()

const apiBaseUrl =
  typeof runtime.public.apiBaseUrl === "string" ? runtime.public.apiBaseUrl : "/api"
const turnstileSiteKey =
  typeof runtime.public.turnstileSiteKey === "string"
    ? runtime.public.turnstileSiteKey
    : ""
const turnstileEnabled = runtime.public.turnstileEnabled === true
const contactEndpoint = `${apiBaseUrl.replace(/\/$/, "")}/contact`

const timeline: readonly { title: string; detail: string }[] = [
  {
    title: "Une réponse humaine",
    detail: "Nous lisons chaque message. Pas d'accusé automatique.",
  },
  {
    title: "Un échange pour cadrer votre besoin",
    detail: "Appel ou visio, sans engagement.",
  },
  {
    title: "Une direction claire",
    detail: "Priorités, étapes et suite définies ensemble.",
  },
]

usePageSeo({
  title: "Contact — parlons de votre projet",
  description:
    "Contactez Devzair pour discuter de votre projet digital : site, application, identité, contenu ou visibilité. Un premier échange pour comprendre votre besoin et clarifier les priorités.",
  path: "/contact",
  type: "website",
})
</script>

<template>
  <main class="contact-page">
    <section class="contact-page__section" aria-labelledby="contact-page-title">
      <BaseContainer class="contact-page__container">
        <header class="contact-page__hero">
          <BaseEyebrow class="contact-page__eyebrow">
            Parlons de votre projet
          </BaseEyebrow>
          <h1 id="contact-page-title" class="contact-page__title">
            Construisons une
            <span class="contact-page__title-emphasis">présence digitale</span>
            à la hauteur de votre entreprise.
          </h1>
          <p class="contact-page__lead">
            Un premier échange nous permettra de comprendre votre besoin, de
            clarifier les priorités et de définir une direction adaptée à
            votre activité.
          </p>
        </header>

        <!--
          Frise « ce qui se passe ensuite » — un <ol> pour l'ordre
          significatif, cartes horizontales sur desktop, empilées sur
          mobile. Les pastilles numérotées sont aria-hidden car le texte
          du <li> porte déjà l'information séquentielle.
        -->
        <ol class="contact-page__timeline" aria-label="Ce qui se passe ensuite">
          <li
            v-for="(step, index) in timeline"
            :key="step.title"
            class="contact-page__step"
          >
            <span class="contact-page__step-marker" aria-hidden="true">
              {{ index + 1 }}
            </span>
            <div class="contact-page__step-body">
              <p class="contact-page__step-title">{{ step.title }}</p>
              <p class="contact-page__step-detail">{{ step.detail }}</p>
            </div>
          </li>
        </ol>

        <div class="contact-page__form-card">
          <ContactForm
            :endpoint="contactEndpoint"
            :turnstile-site-key="turnstileSiteKey"
            :turnstile-enabled="turnstileEnabled"
          />
        </div>
      </BaseContainer>
    </section>
  </main>
</template>

<style scoped>
.contact-page {
  background-color: #ded9ce;
  color: var(--text-primary);
}

.contact-page__section {
  padding-block: var(--space-12) var(--space-16);
  min-height: calc(100svh - var(--site-header-height));
}

.contact-page__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
  max-width: 1080px;
}

/*
 * Hero — texte centré, respirations généreuses. Le H1 utilise le même
 * dégradé métallique argent → bleu que le hero d'accueil, en fallback
 * couleur solide sur les navigateurs sans background-clip: text.
 */
.contact-page__hero {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: var(--space-4);
  max-width: 60rem;
  margin-inline: auto;
}

.contact-page__eyebrow {
  margin-bottom: var(--space-1);
}

.contact-page__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2.25rem, 5vw, 3.25rem);
  line-height: 1.08;
  letter-spacing: -0.02em;
  color: var(--text-primary);
  margin: 0;
  max-width: 22ch;
  text-wrap: balance;
}

.contact-page__title-emphasis {
  color: var(--color-devzair-blue);
}

@supports ((-webkit-background-clip: text) or (background-clip: text)) {
  .contact-page__title-emphasis {
    background: linear-gradient(
      120deg,
      var(--color-devzair-blue) 0%,
      var(--color-petrol) 100%
    );
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    color: transparent;
  }
}

.contact-page__lead {
  margin: 0;
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  line-height: 1.6;
  color: var(--text-secondary);
  max-width: 58ch;
  text-wrap: pretty;
}

/*
 * Frise « ce qui se passe ensuite » — trois cartes horizontales,
 * chacune avec sa pastille numérotée petrol et son mini-titre. Sur
 * mobile, empilée. Aucune surcharge visuelle : c'est un rappel de
 * confiance qui doit rester secondaire au form.
 */
.contact-page__timeline {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
}

@media (min-width: 720px) {
  .contact-page__timeline {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--space-5);
  }
}

.contact-page__step {
  display: flex;
  gap: var(--space-3);
  align-items: flex-start;
  padding: var(--space-4) var(--space-5);
  background: rgba(255, 255, 255, 0.55);
  border: 1px solid rgba(22, 25, 28, 0.08);
  border-radius: var(--radius-lg, 14px);
}

.contact-page__step-marker {
  flex: none;
  width: 2rem;
  height: 2rem;
  border-radius: 9px;
  background: var(--color-petrol);
  color: var(--color-cream);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-family-mono, ui-monospace, "Space Mono", monospace);
  font-size: 0.8125rem;
  font-weight: var(--font-weight-body-strong);
}

.contact-page__step-body {
  display: flex;
  flex-direction: column;
  gap: 0.1875rem;
}

.contact-page__step-title {
  margin: 0;
  font-family: var(--font-family-heading);
  font-weight: 600;
  font-size: 0.9375rem;
  line-height: 1.3;
  color: var(--text-primary);
}

.contact-page__step-detail {
  margin: 0;
  font-family: var(--font-family-body);
  font-size: 0.8125rem;
  line-height: 1.5;
  color: var(--text-secondary);
}

/*
 * Form card — surface cream élevée centrée, largeur maîtrisée pour
 * garder les lignes lisibles (ni trop étirées, ni trop compressées).
 */
.contact-page__form-card {
  width: 100%;
  max-width: 780px;
  margin-inline: auto;
  background: #f8f5ef;
  border: 1px solid rgba(22, 25, 28, 0.14);
  border-radius: 20px;
  padding: var(--space-6) var(--space-5);
  box-shadow: 0 24px 60px -34px rgba(22, 25, 28, 0.4);
}

@media (min-width: 640px) {
  .contact-page__form-card {
    padding: var(--space-8) var(--space-8);
  }
}

@media (min-width: 960px) {
  .contact-page__section {
    padding-block: var(--space-16) var(--space-20);
  }
  .contact-page__container {
    gap: var(--space-12);
  }
  .contact-page__form-card {
    padding: var(--space-10) var(--space-10);
  }
}
</style>
