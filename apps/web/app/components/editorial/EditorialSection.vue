<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Section éditoriale générique consommée par les pages institutionnelles.
 *
 * Contrat volontairement minimal :
 *   - un eyebrow optionnel ;
 *   - un H2 obligatoire ;
 *   - une introduction optionnelle ;
 *   - un slot par défaut pour le contenu (listes, cartes, paragraphes…).
 *
 * Variantes visuelles (`tone`) :
 *   - `default`  → fond cream, texte sombre (WCAG AA vérifié) ;
 *   - `inverse`  → fond navy, texte cream ;
 *   - `subtle`   → fond sand, séparateur discret entre deux sections claires.
 *
 * Accessibilité :
 *   - la balise racine est `<section>` avec `aria-labelledby` pointant sur
 *     l'`id` généré du H2 (chaque instance a un id unique via `useId`) ;
 *   - un `id` de section peut être fourni via la prop `sectionId` pour
 *     ancrer un lien direct depuis la navigation ou une autre page.
 *
 * Aucune animation, aucun script hors setup — la section est SSR-only.
 */

type EditorialSectionTone = "default" | "inverse" | "subtle"

interface Props {
  title: string
  eyebrow?: string
  intro?: string
  tone?: EditorialSectionTone
  /** Ancre facultative : `<section id="…">`. */
  sectionId?: string
}

const props = withDefaults(defineProps<Props>(), {
  eyebrow: undefined,
  intro: undefined,
  tone: "default",
  sectionId: undefined,
})

// Un id stable par instance pour l'aria-labelledby.
const generatedId = useId()
const titleId = computed(() => `editorial-section-title-${generatedId}`)
const eyebrowTone = computed(() => (props.tone === "inverse" ? "inverse" : "default"))
</script>

<template>
  <section
    :id="sectionId"
    class="editorial-section"
    :data-tone="tone"
    :aria-labelledby="titleId"
  >
    <BaseContainer class="editorial-section__container">
      <header class="editorial-section__header">
        <BaseEyebrow
          v-if="eyebrow"
          :tone="eyebrowTone"
          class="editorial-section__eyebrow"
        >
          {{ eyebrow }}
        </BaseEyebrow>
        <h2 :id="titleId" class="editorial-section__title">
          {{ title }}
        </h2>
        <p v-if="intro" class="editorial-section__intro">{{ intro }}</p>
      </header>
      <div v-if="$slots.default" class="editorial-section__body">
        <slot />
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.editorial-section {
  padding-block: var(--space-14) var(--space-14);
}

.editorial-section[data-tone="default"] {
  background-color: var(--background-primary);
  color: var(--text-primary);
}

.editorial-section[data-tone="subtle"] {
  background-color: var(--background-secondary);
  color: var(--text-primary);
}

.editorial-section[data-tone="inverse"] {
  background-color: var(--background-inverse);
  color: var(--text-inverse);
}

.editorial-section__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.editorial-section__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 52rem;
}

.editorial-section__eyebrow {
  margin: 0;
}

.editorial-section__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.625rem, 3.4vw, 2.25rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  margin: 0;
  color: inherit;
  max-width: 26ch;
}

.editorial-section__intro {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  line-height: 1.6;
  margin: 0;
  max-width: 60ch;
  color: inherit;
}

.editorial-section[data-tone="default"] .editorial-section__intro,
.editorial-section[data-tone="subtle"] .editorial-section__intro {
  color: var(--text-secondary);
}

.editorial-section[data-tone="inverse"] .editorial-section__intro {
  color: var(--text-inverse-muted);
}

.editorial-section__body {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

@media (min-width: 768px) {
  .editorial-section {
    padding-block: var(--space-16) var(--space-16);
  }
}
</style>
