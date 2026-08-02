<script setup lang="ts">
import { computed } from "vue"
import BaseButton from "~/components/base/BaseButton.vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import { site } from "~/config/site"

/**
 * Section finale « Parlons de votre projet » — CTA de clôture de l'accueil.
 *
 * Ancre : `id="contact"`. Cible du CTA hero primaire, du CTA header/mobile
 * (via `primaryCta.to = "/#contact"`) et du footer. Un seul point d'atterrissage
 * pour la conversion tant que la Phase 6 (formulaire dédié, protection antispam,
 * traitement serveur) n'est pas livrée.
 *
 * Stratégie de contact conditionnelle :
 *   - si `site.contact.email` est renseigné → BaseButton « Nous écrire »
 *     avec `href="mailto:..."` (a rel=noopener, ouvre le client mail natif) ;
 *   - sinon → aucun bouton, aucun href="#", aucun libellé fictif. Un indicateur
 *     discret « Le moyen de contact en ligne sera activé avant la mise en
 *     production. » n'apparaît qu'en environnement non-indexable
 *     (`siteIndexable === false`), pour rassurer l'équipe interne sans polluer
 *     la production quand celle-ci sera activée.
 *
 * On ne lit jamais `process.env` : la source runtime est
 * `useRuntimeConfig().public.siteIndexable`, alignée sur la même variable
 * `NUXT_PUBLIC_SITE_INDEXABLE` que le reste de la politique d'indexation.
 *
 * Contraste :
 *   - fond `--background-inverse` (navy), texte cream ;
 *   - CTA primaire réutilise `BaseButton[variant=primary]` (petrol),
 *     contrast AA sur cream text embedded.
 *
 * Accessibilité :
 *   - un H2 unique pour la section, ancre `id="contact"` sur `<section>` ;
 *   - eyebrow décoratif (déjà tagué eyebrow, pas un titre) ;
 *   - l'indicateur non-indexable est un `<p>` sémantique, pas un `<span>`
 *     décoratif, pour rester lu par les lecteurs d'écran en preprod.
 */

const runtime = useRuntimeConfig()

const contactEmail = computed(() => site.contact.email)
const hasContactEmail = computed(() => Boolean(contactEmail.value))
const showPreprodNotice = computed(
  () => !hasContactEmail.value && runtime.public.siteIndexable === false,
)
</script>

<template>
  <section
    id="contact"
    class="home-cta"
    aria-labelledby="home-cta-title"
  >
    <BaseContainer class="home-cta__container">
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

      <div v-if="hasContactEmail" class="home-cta__actions">
        <BaseButton
          :href="`mailto:${contactEmail}`"
          variant="primary"
        >
          Nous écrire
          <template #icon>→</template>
        </BaseButton>
      </div>

      <p v-if="showPreprodNotice" class="home-cta__notice">
        Le moyen de contact en ligne sera activé avant la mise en production.
      </p>
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

.home-cta__actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-3);
  margin-top: var(--space-4);
}

.home-cta__notice {
  margin: var(--space-4) 0 0;
  padding-top: var(--space-4);
  border-top: 1px solid var(--border-inverse);
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.04em;
  color: var(--color-cream-muted);
  max-width: 60ch;
}

@media (min-width: 768px) {
  .home-cta {
    padding-block: var(--space-20) var(--space-24);
  }
}
</style>
