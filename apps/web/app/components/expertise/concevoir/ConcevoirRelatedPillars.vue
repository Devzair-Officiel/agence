<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import { expertisePages } from "~/config/expertise-pages"

/**
 * Section « Aller plus loin » — variante propre à Concevoir.
 *
 * Reprend le contrat de `ExpertiseRelatedPillars` (deux pôles connexes)
 * mais avec un rendu propre à la page — cartes plus ancrées, marker
 * chevron typographique. Les données restent tirées de
 * `expertise-pages.ts`, aucune auto-référence.
 */

interface Props {
  pillarIds: readonly string[]
}

const props = defineProps<Props>()

const relatedPages = computed(() =>
  props.pillarIds
    .map((id) => expertisePages.find((p) => p.pillarId === id))
    .filter((p): p is (typeof expertisePages)[number] => Boolean(p))
    .filter((p) => p.status === "published"),
)

const generatedId = useId()
const titleId = computed(() => `concevoir-related-title-${generatedId}`)
</script>

<template>
  <section
    class="concevoir-related"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="concevoir-related__container">
      <header class="concevoir-related__header">
        <BaseEyebrow class="concevoir-related__eyebrow">
          Aller plus loin
        </BaseEyebrow>
        <h2 :id="titleId" class="concevoir-related__title">
          Pôles connexes à explorer.
        </h2>
      </header>

      <ul
        v-if="relatedPages.length > 0"
        class="concevoir-related__list"
        role="list"
      >
        <li
          v-for="page in relatedPages"
          :key="page.id"
          class="concevoir-related__item"
        >
          <NuxtLink
            :to="page.route"
            class="concevoir-related__link"
          >
            <p class="concevoir-related__link-eyebrow">{{ page.eyebrow }}</p>
            <h3 class="concevoir-related__link-title">{{ page.shortTitle }}</h3>
            <p class="concevoir-related__link-summary">{{ page.summary }}</p>
            <p class="concevoir-related__link-cta" aria-hidden="true">
              Découvrir ce pôle →
            </p>
          </NuxtLink>
        </li>
      </ul>
    </BaseContainer>
  </section>
</template>

<style scoped>
.concevoir-related {
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-14);
}

.concevoir-related__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.concevoir-related__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 40rem;
}

.concevoir-related__eyebrow {
  margin: 0;
}

.concevoir-related__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.625rem, 3.4vw, 2.25rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
}

.concevoir-related__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-5);
}

.concevoir-related__item {
  display: flex;
}

.concevoir-related__link {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding: var(--space-6);
  padding-left: calc(var(--space-6) + 6px);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  background-color: var(--color-cream-elevated);
  color: var(--text-primary);
  text-decoration: none;
  width: 100%;
  overflow: hidden;
  transition:
    border-color var(--duration-fast) var(--ease-out),
    transform var(--duration-fast) var(--ease-out);
}

.concevoir-related__link::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 6px;
  height: 100%;
  background-color: var(--color-petrol);
}

.concevoir-related__link:hover,
.concevoir-related__link:focus-visible {
  border-color: var(--color-devzair-blue);
  transform: translateY(-2px);
}

.concevoir-related__link-eyebrow {
  font-family: var(--font-family-mono);
  font-weight: 700;
  font-size: 0.75rem;
  letter-spacing: 0.06em;
  color: var(--color-petrol);
  margin: 0;
  text-transform: uppercase;
}

.concevoir-related__link-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.25rem;
  line-height: 1.2;
  color: var(--text-primary);
  margin: 0;
}

.concevoir-related__link-summary {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
}

.concevoir-related__link-cta {
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--color-petrol);
  margin: var(--space-2) 0 0;
}

@media (min-width: 720px) {
  .concevoir-related__list {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-6);
  }
}

@media (min-width: 1024px) {
  .concevoir-related {
    padding-block: var(--space-16) var(--space-16);
  }
}

@media (prefers-reduced-motion: reduce) {
  .concevoir-related__link {
    transition: none;
  }

  .concevoir-related__link:hover,
  .concevoir-related__link:focus-visible {
    transform: none;
  }
}
</style>
