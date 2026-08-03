<script setup lang="ts">
import { computed, useId } from "vue"
import BaseButton from "~/components/base/BaseButton.vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Encart d'appel (« callout ») — pont éditorial entre deux pages ou
 * relance vers `/#contact` sans dupliquer la section CTA finale de
 * l'accueil.
 *
 * Rôle strict :
 *   - guider vers **une** action clairement identifiée ;
 *   - servir de transition entre deux blocs de contenu narratif ;
 *   - respecter la hiérarchie H2 (encart = H2, pas de H1 secondaire).
 *
 * `link` accepte deux formes :
 *   - route interne existante → `to: "/agence"` (rendu NuxtLink) ;
 *   - route future non livrée → ajouter `external: true` pour forcer un
 *     `<a href>` et éviter le warning vue-router.
 *
 * Aucune animation, aucune interaction JS.
 */

interface CalloutLink {
  readonly label: string
  readonly to: string
  /** Force un rendu ancre plat quand la cible n'est pas encore une route Nuxt. */
  readonly external?: boolean
}

interface Props {
  eyebrow?: string
  title: string
  description?: string
  primary: CalloutLink
  secondary?: CalloutLink
  tone?: "inverse" | "accent"
}

const props = withDefaults(defineProps<Props>(), {
  eyebrow: undefined,
  description: undefined,
  secondary: undefined,
  tone: "inverse",
})

const generatedId = useId()
const titleId = computed(() => `editorial-callout-title-${generatedId}`)
</script>

<template>
  <section
    class="editorial-callout"
    :data-tone="tone"
    :aria-labelledby="titleId"
  >
    <BaseContainer class="editorial-callout__container">
      <div class="editorial-callout__text">
        <BaseEyebrow
          v-if="eyebrow"
          tone="inverse"
          class="editorial-callout__eyebrow"
        >
          {{ eyebrow }}
        </BaseEyebrow>
        <h2 :id="titleId" class="editorial-callout__title">
          {{ title }}
        </h2>
        <p v-if="description" class="editorial-callout__description">
          {{ description }}
        </p>
      </div>
      <div class="editorial-callout__actions">
        <BaseButton
          :to="props.primary.to"
          :external="props.primary.external"
          variant="primary"
          class="editorial-callout__cta"
        >
          {{ props.primary.label }}
        </BaseButton>
        <BaseButton
          v-if="props.secondary"
          :to="props.secondary.to"
          :external="props.secondary.external"
          variant="secondary"
          class="editorial-callout__cta"
        >
          {{ props.secondary.label }}
        </BaseButton>
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.editorial-callout {
  padding-block: var(--space-12) var(--space-14);
}

.editorial-callout[data-tone="inverse"] {
  background-color: var(--background-inverse);
  color: var(--text-inverse);
}

.editorial-callout[data-tone="accent"] {
  background-color: var(--color-petrol);
  color: var(--text-inverse);
}

.editorial-callout__container {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-8);
  align-items: center;
}

.editorial-callout__text {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 48rem;
}

.editorial-callout__eyebrow {
  margin: 0;
}

.editorial-callout__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.5rem, 3vw, 2rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-inverse);
  margin: 0;
  max-width: 28ch;
}

.editorial-callout__description {
  font-family: var(--font-family-body);
  font-size: clamp(0.9375rem, 1.2vw, 1rem);
  line-height: 1.6;
  color: var(--text-inverse-muted);
  margin: 0;
  max-width: 56ch;
}

.editorial-callout__actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-3);
}

.editorial-callout__actions :deep(.base-button[data-variant="secondary"]) {
  color: var(--color-cream);
  border-color: rgba(244, 241, 234, 0.28);
}

.editorial-callout__actions :deep(.base-button[data-variant="secondary"]:hover) {
  background-color: var(--color-cream);
  color: var(--color-navy);
  border-color: var(--color-cream);
}

@media (min-width: 900px) {
  .editorial-callout__container {
    grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);
    gap: var(--space-12);
  }

  .editorial-callout__actions {
    justify-content: flex-end;
  }
}
</style>
