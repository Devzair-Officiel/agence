<script setup lang="ts">
import { computed, useId } from "vue"
import BaseButton from "~/components/base/BaseButton.vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Callout de fin de page Visibilité — bande cartographique horizontale.
 *
 * Distinct volontairement du panneau navy-elevated de Construire et de
 * la couverture éditoriale cream-elevated de Valoriser :
 *   - fond cream général de section pour la transition avec `SiteFooter`
 *     (navy-deep) ;
 *   - à l'intérieur, une bande cream-elevated à liseré petrol dominant
 *     en haut, évoquant un plan technique refermé ;
 *   - repère mono type coordonnées à droite (rappel visuel du hero) ;
 *   - CTA primaire pétrole → `/contact` ;
 *   - CTA secondaire → `/expertises/faire-evoluer` (aval).
 *
 * Contenu validé :
 *   - H2 : « Développer la visibilité de votre entreprise, sans dépendre
 *     de la publicité payante. » ;
 *   - description : rappel des trois angles (SEO / local / éditorial) et
 *     positionnement (durée > pics artificiels).
 */

const generatedId = useId()
const titleId = computed(() => `visibilite-callout-title-${generatedId}`)
</script>

<template>
  <section
    class="visibilite-callout"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="visibilite-callout__frame">
      <div class="visibilite-callout__band">
        <div class="visibilite-callout__rule" aria-hidden="true">
          <span class="visibilite-callout__rule-tag">Signal · Callout</span>
          <span class="visibilite-callout__rule-line" />
          <span class="visibilite-callout__rule-coord">04 / 05</span>
        </div>

        <div class="visibilite-callout__text">
          <BaseEyebrow class="visibilite-callout__eyebrow">
            Parler de votre projet
          </BaseEyebrow>
          <h2 :id="titleId" class="visibilite-callout__title">
            Développer la visibilité de votre entreprise, sans dépendre
            de la publicité payante.
          </h2>
          <p class="visibilite-callout__description">
            SEO technique, présence locale et stratégie éditoriale : nous
            travaillons ces trois axes ensemble, pour construire une
            visibilité qui s'ancre dans la durée.
          </p>
        </div>

        <div class="visibilite-callout__actions">
          <BaseButton
            to="/contact"
            variant="primary"
            class="visibilite-callout__cta"
          >
            Parler de votre visibilité
          </BaseButton>
          <BaseButton
            to="/services/seo-referencement-naturel"
            variant="secondary"
            class="visibilite-callout__cta"
          >
            SEO et référencement naturel
          </BaseButton>
          <BaseButton
            to="/services/visibilite-locale"
            variant="secondary"
            class="visibilite-callout__cta"
          >
            Visibilité locale
          </BaseButton>
        </div>

        <div class="visibilite-callout__rule visibilite-callout__rule--bottom" aria-hidden="true">
          <span class="visibilite-callout__rule-coord">SIG · MAP</span>
          <span class="visibilite-callout__rule-line" />
          <span class="visibilite-callout__rule-tag">Fin · Section</span>
        </div>
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
/*
 * Bande cream pleine largeur ; la bande intérieure cream-elevated est
 * bordée par un large liseré petrol en haut (comme un plan technique).
 * Le fond cream assure la transition visuelle avec le footer navy-deep.
 */
.visibilite-callout {
  background-color: var(--background-primary);
  padding-block: var(--space-14) var(--space-16);
}

.visibilite-callout__frame {
  display: block;
}

.visibilite-callout__band {
  position: relative;
  background-color: var(--color-cream-elevated);
  padding: var(--space-10) var(--space-6);
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
  border: 1px solid var(--border-default);
}

.visibilite-callout__band::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(
    to right,
    var(--color-petrol) 0%,
    var(--color-petrol) 55%,
    transparent 55%,
    transparent 62%,
    var(--color-petrol) 62%,
    var(--color-petrol) 100%
  );
  opacity: 0.7;
}

.visibilite-callout__rule {
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

.visibilite-callout__rule--bottom {
  justify-content: flex-end;
}

.visibilite-callout__rule-line {
  flex: 1;
  height: 1px;
  background-color: var(--color-petrol);
  opacity: 0.55;
}

.visibilite-callout__rule-coord {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  color: var(--color-ink-subtle);
}

.visibilite-callout__text {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 46rem;
}

.visibilite-callout__eyebrow {
  margin: 0;
}

.visibilite-callout__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.5rem, 3.4vw, 2.375rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 26ch;
}

.visibilite-callout__description {
  font-family: var(--font-family-body);
  font-size: clamp(0.9375rem, 1.2vw, 1rem);
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 58ch;
}

.visibilite-callout__actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-3);
}

@media (min-width: 768px) {
  .visibilite-callout {
    padding-block: var(--space-16) var(--space-20);
  }

  .visibilite-callout__band {
    padding: var(--space-12) var(--space-10);
    gap: var(--space-8);
  }
}

/*
 * Desktop : composition horizontale — texte à gauche, actions à droite
 * dans la bande, règles cartographiques encadrent l'ensemble.
 */
@media (min-width: 900px) {
  .visibilite-callout__band {
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

  .visibilite-callout__band > .visibilite-callout__rule:first-child {
    grid-area: rule;
  }

  .visibilite-callout__text {
    grid-area: text;
  }

  .visibilite-callout__actions {
    grid-area: actions;
    justify-content: flex-end;
    flex-direction: column;
    align-items: flex-end;
    gap: var(--space-3);
  }

  .visibilite-callout__cta {
    width: fit-content;
  }

  .visibilite-callout__rule--bottom {
    grid-area: rule-bottom;
  }
}

@media (min-width: 1200px) {
  .visibilite-callout__band {
    padding: var(--space-14) var(--space-12);
  }
}

@media (prefers-reduced-motion: reduce) {
  .visibilite-callout__cta {
    transition: none;
  }
}
</style>
