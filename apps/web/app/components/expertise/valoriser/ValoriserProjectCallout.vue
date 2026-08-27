<script setup lang="ts">
import { computed, useId } from "vue"
import BaseButton from "~/components/base/BaseButton.vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Callout de fin de page Valoriser — direction éditoriale lumineuse.
 *
 * Distinct volontairement du panneau navy-elevated de Construire :
 *   - fond cream général de section pour la transition avec `SiteFooter`
 *     (navy-deep) ;
 *   - à l'intérieur, une « couverture éditoriale » cream-elevated cadrée
 *     par un mince liseré petrol en haut et en bas — plus proche d'un
 *     imprimé que d'un module logiciel ;
 *   - typographie forte à gauche, actions à droite en desktop ;
 *   - CTA primaire pétrole (action-primary), CTA secondaire en contour
 *     `ink` sur fond cream — pas de rappel du secondary cream/navy de
 *     Construire.
 *
 * Contenu validé par le brief :
 *   - H2 « Votre activité a de la valeur. Donnons-lui la forme qu'elle
 *     mérite. » ;
 *   - description verbatim ;
 *   - CTA primaire → /contact (« Parler de vos contenus ») ;
 *   - CTA secondaire → /expertises/visibilite (aval).
 */

const generatedId = useId()
const titleId = computed(() => `valoriser-callout-title-${generatedId}`)
</script>

<template>
  <section
    class="valoriser-callout"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="valoriser-callout__frame">
      <div class="valoriser-callout__cover">
        <div class="valoriser-callout__rule" aria-hidden="true">
          <span class="valoriser-callout__rule-tag">Édition · Callout</span>
          <span class="valoriser-callout__rule-line" />
        </div>

        <div class="valoriser-callout__text">
          <BaseEyebrow class="valoriser-callout__eyebrow">
            Parler de votre projet
          </BaseEyebrow>
          <h2 :id="titleId" class="valoriser-callout__title">
            Votre activité a de la valeur. Donnons-lui la forme qu'elle mérite.
          </h2>
          <p class="valoriser-callout__description">
            Photographies, textes et structuration de l'offre : nous pouvons
            construire un ensemble de contenus cohérent, professionnel et
            prêt à être utilisé sur vos supports.
          </p>
        </div>

        <div class="valoriser-callout__actions">
          <BaseButton
            to="/contact"
            variant="primary"
            class="valoriser-callout__cta"
          >
            Parler de vos contenus
          </BaseButton>
          <BaseButton
            to="/expertises/visibilite"
            variant="secondary"
            class="valoriser-callout__cta"
          >
            Découvrir Visibilité
          </BaseButton>
        </div>

        <div class="valoriser-callout__rule valoriser-callout__rule--bottom" aria-hidden="true">
          <span class="valoriser-callout__rule-line" />
          <span class="valoriser-callout__rule-tag">Fin · Section</span>
        </div>
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
/*
 * Bande cream pleine largeur ; le module intérieur est une couverture
 * cream-elevated cadrée par deux règles éditoriales petrol. La bande de
 * fond assure la transition visuelle avec le footer navy-deep.
 */
.valoriser-callout {
  background-color: var(--background-primary);
  padding-block: var(--space-14) var(--space-16);
}

.valoriser-callout__frame {
  display: block;
}

.valoriser-callout__cover {
  position: relative;
  background-color: var(--color-cream-elevated);
  padding: var(--space-10) var(--space-6);
  border-top: 1px solid var(--border-default);
  border-bottom: 1px solid var(--border-default);
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

/*
 * Règles éditoriales — mono petit tag + ligne horizontale. Signale
 * l'entrée et la sortie du bloc comme dans un magazine.
 */
.valoriser-callout__rule {
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

.valoriser-callout__rule--bottom {
  justify-content: flex-end;
}

.valoriser-callout__rule-line {
  flex: 1;
  height: 1px;
  background-color: var(--color-petrol);
  opacity: 0.55;
}

.valoriser-callout__text {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 44rem;
}

.valoriser-callout__eyebrow {
  margin: 0;
}

.valoriser-callout__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.5rem, 3.4vw, 2.375rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 24ch;
}

.valoriser-callout__description {
  font-family: var(--font-family-body);
  font-size: clamp(0.9375rem, 1.2vw, 1rem);
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 58ch;
}

.valoriser-callout__actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-3);
}

@media (min-width: 768px) {
  .valoriser-callout {
    padding-block: var(--space-16) var(--space-20);
  }

  .valoriser-callout__cover {
    padding: var(--space-12) var(--space-10);
    gap: var(--space-8);
  }
}

/*
 * Desktop : composition 2 colonnes texte / actions dans la couverture,
 * les règles éditoriales encadrent l'ensemble.
 */
@media (min-width: 900px) {
  .valoriser-callout__cover {
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

  .valoriser-callout__cover > .valoriser-callout__rule:first-child {
    grid-area: rule;
  }

  .valoriser-callout__text {
    grid-area: text;
  }

  .valoriser-callout__actions {
    grid-area: actions;
    justify-content: flex-end;
    flex-direction: column;
    align-items: flex-end;
    gap: var(--space-3);
  }

  .valoriser-callout__cta {
    width: fit-content;
  }

  .valoriser-callout__rule--bottom {
    grid-area: rule-bottom;
  }
}

@media (min-width: 1200px) {
  .valoriser-callout__cover {
    padding: var(--space-14) var(--space-12);
  }
}

@media (prefers-reduced-motion: reduce) {
  .valoriser-callout__cta {
    transition: none;
  }
}
</style>
