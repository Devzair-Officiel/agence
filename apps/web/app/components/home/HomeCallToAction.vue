<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import ContactForm from "~/components/contact/ContactForm.vue"

/**
 * Section finale « Parlons de votre projet » — CTA de clôture de l'accueil.
 *
 * Ancre : `id="contact"`. Cible du CTA hero primaire, du CTA header/mobile
 * (via `primaryCta.to = "/#contact"`) et du footer. Depuis la Phase 6B, cette
 * section héberge le vrai formulaire de contact (validation client + Turnstile
 * + POST /api/contact) : plus de mailto conditionnel, plus de notice preprod.
 *
 * L'eyebrow, le H2 et le paragraphe éditoriaux restent verbatim (source unique
 * `docs/01-CONTENT.md §7.1`). Le formulaire consomme les valeurs runtime
 * (`apiBaseUrl`, `turnstileSiteKey`) via `useRuntimeConfig().public`.
 *
 * Accessibilité :
 *   - un H2 unique pour la section, ancre `id="contact"` sur `<section>` ;
 *   - le formulaire est titré par un H3 en `sr-only` — la hiérarchie visuelle
 *     reste eyebrow → H2 → paragraphe → form ;
 *   - contraste garanti sur fond navy : champs cream + focus ring devzair-blue.
 */

const runtime = useRuntimeConfig()

const apiBaseUrl =
  typeof runtime.public.apiBaseUrl === "string" ? runtime.public.apiBaseUrl : "/api"
const turnstileSiteKey =
  typeof runtime.public.turnstileSiteKey === "string"
    ? runtime.public.turnstileSiteKey
    : ""
const contactEndpoint = `${apiBaseUrl.replace(/\/$/, "")}/contact`
</script>

<template>
  <section
    id="contact"
    class="home-cta"
    aria-labelledby="home-cta-title"
  >
    <BaseContainer class="home-cta__container">
      <div class="home-cta__intro">
        <BaseEyebrow tone="inverse" class="home-cta__eyebrow">
          Parlons de votre projet
        </BaseEyebrow>
        <h2 id="home-cta-title" class="home-cta__title">
          Construisons une présence digitale à la hauteur de votre entreprise.
        </h2>
        <p class="home-cta__lead">
          Un premier échange nous permettra de comprendre votre besoin, de
          clarifier les priorités et de définir une direction adaptée à votre
          activité.
        </p>
      </div>

      <div class="home-cta__form">
        <ContactForm
          :endpoint="contactEndpoint"
          :turnstile-site-key="turnstileSiteKey"
        />
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.home-cta {
  background-color: var(--background-inverse);
  color: var(--text-inverse);
  padding-block: var(--space-16) var(--space-20);
}

.home-cta__container {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  max-width: 68rem;
}

.home-cta__intro {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 44rem;
}

.home-cta__eyebrow {
  margin-bottom: var(--space-2);
}

.home-cta__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 4vw, 2.5rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-inverse);
  margin: 0;
  max-width: 24ch;
}

.home-cta__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  line-height: 1.6;
  color: var(--text-inverse-muted);
  margin: 0;
  max-width: 56ch;
}

@media (min-width: 960px) {
  .home-cta {
    padding-block: var(--space-20) var(--space-24);
  }
  .home-cta__container {
    grid-template-columns: minmax(0, 22rem) minmax(0, 1fr);
    gap: var(--space-12);
    align-items: start;
  }
}
</style>
