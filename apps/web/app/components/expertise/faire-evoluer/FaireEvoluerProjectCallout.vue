<script setup lang="ts">
import { computed, useId } from "vue"
import BaseButton from "~/components/base/BaseButton.vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Callout de fin de page Faire évoluer — bande cream-elevated à liseré
 * petrol, en cohérence avec la couverture cream de tout le pôle et la
 * transition vers `SiteFooter` navy-deep.
 *
 * Direction volontairement distincte :
 *   - deux marqueurs mono type coordonnées (haut/bas) évoquent un cycle
 *     qui se referme, plutôt qu'un plan cartographique (Visibilité) ou
 *     un panneau sombre (Construire) ;
 *   - CTA primaire pétrole → `/contact` ;
 *   - CTA secondaire → `/expertises/construire` (amont — Faire évoluer
 *     n'a pas d'aval à proposer).
 *
 * Contenu validé :
 *   - H2 : « Votre site est en ligne. Gardons-le utile, fiable et
 *     capable d'évoluer. » ;
 *   - description : rappel du positionnement — maintenance en flux, pas
 *     comme un événement isolé.
 */

const generatedId = useId()
const titleId = computed(() => `faire-evoluer-callout-title-${generatedId}`)
</script>

<template>
  <section
    class="faire-evoluer-callout"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="faire-evoluer-callout__frame">
      <div class="faire-evoluer-callout__band">
        <div class="faire-evoluer-callout__rule" aria-hidden="true">
          <span class="faire-evoluer-callout__rule-tag">Cycle · Callout</span>
          <span class="faire-evoluer-callout__rule-line" />
          <span class="faire-evoluer-callout__rule-coord">05 / 05</span>
        </div>

        <div class="faire-evoluer-callout__text">
          <BaseEyebrow class="faire-evoluer-callout__eyebrow">
            Parler de votre suivi
          </BaseEyebrow>
          <h2 :id="titleId" class="faire-evoluer-callout__title">
            Votre site est en ligne. Gardons-le utile, fiable et capable
            d'évoluer.
          </h2>
          <p class="faire-evoluer-callout__description">
            Maintenance et sécurité, mesure d'usage, correctifs et
            évolutions fonctionnelles : nous entretenons votre produit
            comme un flux, pas comme un chantier annuel.
          </p>
        </div>

        <div class="faire-evoluer-callout__actions">
          <BaseButton
            to="/contact"
            variant="primary"
            class="faire-evoluer-callout__cta"
          >
            Parler de votre suivi
          </BaseButton>
          <BaseButton
            to="/services/maintenance-accompagnement"
            variant="secondary"
            class="faire-evoluer-callout__cta"
          >
            Maintenance et accompagnement
          </BaseButton>
        </div>

        <div class="faire-evoluer-callout__rule faire-evoluer-callout__rule--bottom" aria-hidden="true">
          <span class="faire-evoluer-callout__rule-coord">CYCLE · N+1</span>
          <span class="faire-evoluer-callout__rule-line" />
          <span class="faire-evoluer-callout__rule-tag">Fin · Section</span>
        </div>
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.faire-evoluer-callout {
  background-color: var(--background-primary);
  padding-block: var(--space-14) var(--space-16);
}

.faire-evoluer-callout__frame {
  display: block;
}

.faire-evoluer-callout__band {
  position: relative;
  background-color: var(--color-cream-elevated);
  padding: var(--space-10) var(--space-6);
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
  border: 1px solid var(--border-default);
}

/*
 * Liseré haut : deux segments qui laissent apparaître un point de
 * séparation — évoque le cycle qui se referme (fin d'un tour, début du
 * suivant), sans texte.
 */
.faire-evoluer-callout__band::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(
    to right,
    var(--color-petrol) 0%,
    var(--color-petrol) 48%,
    transparent 48%,
    transparent 52%,
    var(--color-petrol) 52%,
    var(--color-petrol) 100%
  );
  opacity: 0.7;
}

.faire-evoluer-callout__rule {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-petrol);
}

.faire-evoluer-callout__rule--bottom {
  justify-content: flex-end;
}

.faire-evoluer-callout__rule-line {
  flex: 1;
  height: 1px;
  background-color: var(--color-petrol);
  opacity: 0.55;
}

.faire-evoluer-callout__rule-coord {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  color: var(--color-ink-subtle);
}

.faire-evoluer-callout__text {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 46rem;
}

.faire-evoluer-callout__eyebrow {
  margin: 0;
}

.faire-evoluer-callout__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.5rem, 3.4vw, 2.375rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 28ch;
}

.faire-evoluer-callout__description {
  font-family: var(--font-family-body);
  font-size: clamp(0.9375rem, 1.2vw, 1rem);
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 58ch;
}

.faire-evoluer-callout__actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-3);
}

@media (min-width: 768px) {
  .faire-evoluer-callout {
    padding-block: var(--space-16) var(--space-20);
  }

  .faire-evoluer-callout__band {
    padding: var(--space-12) var(--space-10);
    gap: var(--space-8);
  }
}

@media (min-width: 900px) {
  .faire-evoluer-callout__band {
    display: grid;
    grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr);
    grid-template-areas:
      "rule rule"
      "text actions"
      "rule-bottom rule-bottom";
    column-gap: var(--space-10);
    row-gap: var(--space-8);
    align-items: center;
    padding: var(--space-12) var(--space-10);
  }

  .faire-evoluer-callout__band > .faire-evoluer-callout__rule:first-child {
    grid-area: rule;
  }

  .faire-evoluer-callout__text {
    grid-area: text;
  }

  .faire-evoluer-callout__actions {
    grid-area: actions;
    justify-content: flex-end;
    flex-direction: column;
    align-items: flex-end;
    gap: var(--space-3);
  }

  .faire-evoluer-callout__cta {
    width: fit-content;
  }

  .faire-evoluer-callout__rule--bottom {
    grid-area: rule-bottom;
  }
}

@media (min-width: 1200px) {
  .faire-evoluer-callout__band {
    padding: var(--space-14) var(--space-12);
  }
}

@media (prefers-reduced-motion: reduce) {
  .faire-evoluer-callout__cta {
    transition: none;
  }
}
</style>
