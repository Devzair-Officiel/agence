<script setup lang="ts">
import { computed, useId } from "vue"
import BaseButton from "~/components/base/BaseButton.vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Callout de fin de page Construire.
 *
 * Direction visuelle : panneau navy-elevated contenu dans un cadre cream,
 * afin de créer une rupture claire avec le footer navy-deep qui suit. Le
 * fond extérieur cream/sand offre une respiration avant le footer.
 *
 * Contenu validé par le brief éditorial :
 *   - H2 : « Vous avez le projet. Construisons le produit. » ;
 *   - description verbatim ;
 *   - CTA primaire → /contact ;
 *   - CTA secondaire → /expertises/concevoir (amont naturel de Construire).
 *
 * Accessibilité :
 *   - `<section>` + `aria-labelledby` sur l'id du H2 ;
 *   - panneau navy-elevated (#141e2c) + texte cream (contraste AA vérifié) ;
 *   - CTA secondaire cream avec border cream contrastée (contraste AA
 *     vérifié) ;
 *   - CTA hauteur ≥ 44px (imposé par `BaseButton`).
 */

const generatedId = useId()
const titleId = computed(() => `construire-callout-title-${generatedId}`)
</script>

<template>
  <section
    class="construire-callout"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="construire-callout__frame">
      <div class="construire-callout__panel">
        <div class="construire-callout__decor" aria-hidden="true">
          <svg
            class="construire-callout__decor-svg"
            viewBox="0 0 240 240"
            role="presentation"
            focusable="false"
            aria-hidden="true"
          >
            <g
              class="construire-callout__decor-lines"
              fill="none"
              stroke-width="1"
              stroke-linecap="square"
            >
              <path d="M60 0 L60 240" />
              <path d="M120 0 L120 240" />
              <path d="M180 0 L180 240" />
              <path d="M0 60 L240 60" />
              <path d="M0 120 L240 120" />
              <path d="M0 180 L240 180" />
            </g>
            <g
              class="construire-callout__decor-nodes"
              fill="currentColor"
            >
              <circle cx="60" cy="60" r="2.5" />
              <circle cx="120" cy="120" r="2.5" />
              <circle cx="180" cy="60" r="2.5" />
              <circle cx="60" cy="180" r="2.5" />
              <circle cx="180" cy="180" r="2.5" />
            </g>
          </svg>
        </div>

        <div class="construire-callout__text">
          <BaseEyebrow tone="inverse" class="construire-callout__eyebrow">
            Parler de votre projet
          </BaseEyebrow>
          <h2 :id="titleId" class="construire-callout__title">
            Vous avez le projet. Construisons le produit.
          </h2>
          <p class="construire-callout__description">
            Site, e-commerce ou application métier : nous pouvons transformer
            un besoin cadré en une solution technique claire, fiable et
            évolutive.
          </p>
          <p class="construire-callout__service-note">
            Vous démarrez avec un besoin de site vitrine ?
            <NuxtLink
              to="/services/creation-site-internet"
              class="construire-callout__service-link"
            >
              Voir notre service création de site
            </NuxtLink>.
          </p>
        </div>

        <div class="construire-callout__actions">
          <BaseButton
            to="/contact"
            variant="primary"
            class="construire-callout__cta"
          >
            Nous parler du projet
          </BaseButton>
          <!--
            Wrapper thématique : la classe `--secondary` que l'on posait
            directement sur BaseButton était fusionnée sur son root
            (`.base-button`) — le sélecteur `.…--secondary :deep(.base-button)`
            n'avait donc AUCUN descendant `.base-button` à cibler, et la
            surcharge cream ne s'appliquait jamais. Un span wrapper rend le
            `.base-button` réellement descendant du sélecteur ciblé.
          -->
          <span class="construire-callout__secondary-slot">
            <BaseButton
              to="/expertises/concevoir"
              variant="secondary"
              class="construire-callout__cta construire-callout__cta--secondary"
            >
              Découvrir Concevoir
            </BaseButton>
          </span>
        </div>
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
/*
 * Cadre extérieur cream : sépare visuellement le CTA du footer navy-deep
 * qui suit. Padding vertical généreux pour laisser respirer avant le
 * SiteFooter.
 */
.construire-callout {
  background-color: var(--background-secondary);
  padding-block: var(--space-12) var(--space-16);
}

.construire-callout__frame {
  display: block;
}

/*
 * Panneau navy-elevated (#141e2c) — distinct du navy-deep du footer.
 * Bordure petrol discrète pour cadrer l'ensemble comme un module unique.
 */
.construire-callout__panel {
  position: relative;
  overflow: hidden;
  background-color: var(--color-navy-elevated);
  color: var(--text-inverse);
  border: 1px solid rgba(12, 91, 87, 0.35);
  border-radius: var(--radius-lg);
  padding: var(--space-10) var(--space-6);
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.construire-callout__decor {
  position: absolute;
  top: 0;
  right: 0;
  width: 14rem;
  height: 14rem;
  pointer-events: none;
  color: rgba(46, 134, 217, 0.35);
  opacity: 0.55;
}

.construire-callout__decor-svg {
  display: block;
  width: 100%;
  height: 100%;
}

.construire-callout__decor-lines {
  stroke: rgba(196, 203, 211, 0.25);
}

.construire-callout__text {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 42rem;
  z-index: 1;
}

.construire-callout__eyebrow {
  margin: 0;
}

/*
 * Le tone=inverse par défaut (devzair-blue #2e86d9) tombe à 4.41:1 sur le
 * navy-elevated #141e2c (juste sous AA). On remonte à une teinte bleu
 * légèrement plus claire — même famille — pour assurer un contraste ≥
 * 5:1 sans changer la personnalité du composant. Le `:deep()` est
 * nécessaire pour percer le scope de BaseEyebrow dont la règle
 * `.base-eyebrow[data-tone="inverse"]` prime autrement.
 */
:deep(.base-eyebrow.construire-callout__eyebrow[data-tone="inverse"]) {
  color: #5aa0e6;
}

.construire-callout__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.5rem, 3vw, 2rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--color-cream);
  margin: 0;
  max-width: 24ch;
}

.construire-callout__description {
  font-family: var(--font-family-body);
  font-size: clamp(0.9375rem, 1.2vw, 1rem);
  line-height: 1.6;
  color: var(--text-inverse-muted);
  margin: 0;
  max-width: 58ch;
}

.construire-callout__service-note {
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  line-height: 1.5;
  color: var(--text-inverse-muted);
  margin: 0;
  opacity: 0.8;
}

.construire-callout__service-link {
  color: var(--color-cream);
  text-decoration: underline;
  text-underline-offset: 2px;
  transition: opacity 0.12s ease;
}

.construire-callout__service-link:hover {
  opacity: 0.75;
}

.construire-callout__actions {
  position: relative;
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-3);
  z-index: 1;
}

.construire-callout__secondary-slot {
  display: inline-flex;
}

/*
 * CTA secondaire : cream sur navy-elevated. Border cream pleine opacité
 * pour contraste AA garanti (≈ 12:1 sur #141e2c). Le wrapper span
 * `.construire-callout__secondary-slot` sert de « thème » local : le
 * sélecteur `:deep(.base-button[data-variant="secondary"])` en est
 * réellement descendant (spécificité 0,4,0 — bat celle du scope de
 * BaseButton en 0,3,0). Hover : remplissage cream + texte navy-elevated.
 */
.construire-callout__secondary-slot :deep(.base-button[data-variant="secondary"]) {
  color: var(--color-cream);
  border-color: var(--color-cream);
  background-color: transparent;
}

.construire-callout__secondary-slot :deep(.base-button[data-variant="secondary"]:hover:not([disabled])) {
  background-color: var(--color-cream);
  color: var(--color-navy-elevated);
  border-color: var(--color-cream);
}

.construire-callout__secondary-slot :deep(.base-button[data-variant="secondary"]:focus-visible) {
  outline: 2px solid var(--color-cream);
  outline-offset: 3px;
}

@media (min-width: 768px) {
  .construire-callout {
    padding-block: var(--space-14) var(--space-20);
  }

  .construire-callout__panel {
    padding: var(--space-12) var(--space-10);
  }
}

/*
 * Desktop : composition 2 colonnes texte + actions dans le panneau.
 * On garde une distance mesurée entre les deux blocs pour lire l'ensemble
 * comme une seule composition.
 */
@media (min-width: 900px) {
  .construire-callout__panel {
    display: grid;
    grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr);
    align-items: center;
    gap: var(--space-10);
    padding: var(--space-14) var(--space-12);
  }

  .construire-callout__actions {
    justify-content: flex-end;
    flex-direction: column;
    align-items: flex-end;
    gap: var(--space-4);
  }

  .construire-callout__cta {
    width: fit-content;
  }
}

@media (min-width: 1200px) {
  .construire-callout__panel {
    padding: var(--space-16) var(--space-14);
  }
}

@media (prefers-reduced-motion: reduce) {
  .construire-callout__cta--secondary :deep(.base-button) {
    transition: none;
  }
}
</style>
