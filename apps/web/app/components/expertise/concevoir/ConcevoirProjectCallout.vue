<script setup lang="ts">
import { computed, useId } from "vue"
import BaseButton from "~/components/base/BaseButton.vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Callout de fin de page Concevoir.
 *
 * Titre et CTA validés par le brief éditorial :
 *   - H2 : « Vous avez le projet. Construisons d'abord sa direction. »
 *   - description : « Avant de parler technologie, nous pouvons clarifier
 *     les usages, les contenus, l'expérience et l'identité qui donneront
 *     sa cohérence au projet. » ;
 *   - primary CTA → /contact  « Parler de votre projet » ;
 *   - secondary CTA → /expertises/construire  « Découvrir Construire ».
 *
 * Accessibilité :
 *   - `<section>` + `aria-labelledby` sur l'id du H2 ;
 *   - le fond utilise le token cream chaud (contraste WCAG AA vérifié).
 */

const generatedId = useId()
const titleId = computed(() => `concevoir-callout-title-${generatedId}`)
</script>

<template>
  <section
    class="concevoir-callout"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="concevoir-callout__container">
      <div class="concevoir-callout__text">
        <BaseEyebrow class="concevoir-callout__eyebrow">
          Parler de votre projet
        </BaseEyebrow>
        <h2 :id="titleId" class="concevoir-callout__title">
          Vous avez le projet. Construisons d'abord sa direction.
        </h2>
        <p class="concevoir-callout__description">
          Avant de parler technologie, nous pouvons clarifier les usages, les
          contenus, l'expérience et l'identité qui donneront sa cohérence au
          projet.
        </p>
      </div>
      <div class="concevoir-callout__actions">
        <BaseButton
          to="/contact"
          variant="primary"
          class="concevoir-callout__cta"
        >
          Parler de votre projet
        </BaseButton>
        <BaseButton
          to="/expertises/construire"
          variant="secondary"
          class="concevoir-callout__cta"
        >
          Découvrir Construire
        </BaseButton>
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.concevoir-callout {
  position: relative;
  background-color: var(--color-sand);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-16);
}

/* Trait de séparation façon plan, en tête de bloc. */
.concevoir-callout::before {
  content: "";
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 49rem;
  max-width: calc(100% - 2rem);
  height: 2px;
  background-color: rgba(12, 91, 87, 0.35);
}

.concevoir-callout__container {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
  align-items: center;
}

.concevoir-callout__text {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 48rem;
}

.concevoir-callout__eyebrow {
  margin: 0;
}

.concevoir-callout__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.5rem, 3vw, 2rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 30ch;
}

.concevoir-callout__description {
  font-family: var(--font-family-body);
  font-size: clamp(0.9375rem, 1.2vw, 1rem);
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

.concevoir-callout__actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-3);
}

@media (min-width: 768px) {
  .concevoir-callout {
    padding-block: var(--space-16) var(--space-20);
  }
}

@media (min-width: 900px) {
  .concevoir-callout__container {
    grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);
    gap: var(--space-12);
  }

  .concevoir-callout__actions {
    justify-content: flex-end;
  }
}
</style>
