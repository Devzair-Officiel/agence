<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import ResourceMeta from "~/components/resources/ResourceMeta.vue"
import type { ArticleDetail } from "~/types/editorial"

/**
 * En-tête d'une page détail `/ressources/{slug}`.
 *
 * Composition éditoriale centrée sur fond navy (`var(--color-navy)`).
 * L'image principale est rendue dans `[slug].vue` (div bridge avec
 * margin-top négatif pour chevaucher cette section) — ce composant ne
 * gère que le texte du hero.
 *
 * `imageBelow` : quand vrai, augmente le `padding-bottom` de la section
 * afin de laisser de l'espace pour que le bridge puisse mordre sur le fond
 * sans couvrir le contenu textuel.
 *
 * Accessibilité :
 *   - `<section aria-labelledby="resource-hero-title">` référence le H1 ;
 *   - `BaseEyebrow` en tone="inverse" (bleu Devzair sur navy) ;
 *   - contrastes AA vérifiés : cream/navy ≫ 7:1, cream-muted/navy > 4.5:1.
 */

interface Props {
  article: ArticleDetail
  imageBelow?: boolean
}

withDefaults(defineProps<Props>(), {
  imageBelow: false,
})
</script>

<template>
  <section
    class="resource-hero"
    :class="{ 'resource-hero--image-below': imageBelow }"
    aria-labelledby="resource-hero-title"
  >
    <BaseContainer width="wide" class="resource-hero__container">
      <div class="resource-hero__content">
        <BaseEyebrow tone="inverse" class="resource-hero__eyebrow">
          Ressource
        </BaseEyebrow>
        <h1 id="resource-hero-title" class="resource-hero__title">
          {{ article.title }}
        </h1>
        <p class="resource-hero__excerpt">{{ article.excerpt }}</p>
        <div class="resource-hero__meta-wrapper">
          <ResourceMeta
            class="resource-hero__meta"
            :author="article.author"
            :published-at="article.publishedAt"
            :updated-at="article.updatedAt"
            :expertise-ids="article.expertiseIds"
          />
        </div>
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
/* ── Section hero ───────────────────────────────────────────────────── */

.resource-hero {
  background-color: var(--color-navy);
  color: var(--text-inverse);
  padding-block: var(--space-16) var(--space-12);
}

/*
 * Halos lumineux subtils : radial-gradient CSS pur, activés seulement
 * sur desktop où ils sont perceptibles. Devzair-blue centré en haut,
 * petrol discret en bas à droite. Navy reste largement dominant.
 */
@media (min-width: 1024px) {
  .resource-hero {
    background:
      radial-gradient(
        ellipse 70% 55% at 50% 5%,
        rgba(46, 134, 217, 0.09) 0%,
        transparent 65%
      ),
      radial-gradient(
        ellipse 42% 48% at 78% 82%,
        rgba(12, 91, 87, 0.07) 0%,
        transparent 60%
      ),
      var(--color-navy);
  }
}

/* Padding-bottom augmenté pour laisser de la place au chevauchement image */
.resource-hero--image-below {
  padding-bottom: var(--space-20);
}

/* ── Container centré ───────────────────────────────────────────────── */

.resource-hero__container {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.resource-hero__content {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: var(--space-5);
  width: 100%;
  max-width: min(100%, 70rem);
}

/* ── Eyebrow ────────────────────────────────────────────────────────── */

.resource-hero__eyebrow {
  margin: 0;
}

/* ── Titre H1 ───────────────────────────────────────────────────────── */

.resource-hero__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2rem, 4.5vw, 3.5rem);
  line-height: 1.08;
  letter-spacing: -0.025em;
  color: var(--text-inverse);
  margin: 0;
  max-width: 30ch;
}

/* ── Chapô ──────────────────────────────────────────────────────────── */

.resource-hero__excerpt {
  font-family: var(--font-family-body);
  font-size: clamp(1.0625rem, 1.8vw, 1.25rem);
  line-height: 1.65;
  color: var(--text-inverse-muted);
  margin: 0;
  max-width: 54ch;
}

/* ── Bandeau méta ───────────────────────────────────────────────────── */

.resource-hero__meta-wrapper {
  padding-top: var(--space-5);
  border-top: 1px solid var(--border-inverse);
  width: 100%;
  display: flex;
  justify-content: center;
}

/* ── Overrides ResourceMeta sur fond navy ───────────────────────────── */

.resource-hero :deep(.resource-meta) {
  justify-content: center;
}

.resource-hero :deep(.resource-meta__row--full) {
  justify-content: center;
}

.resource-hero :deep(.resource-meta__chips) {
  justify-content: center;
}

.resource-hero :deep(.resource-meta__label) {
  color: var(--color-cream-subtle);
}

.resource-hero :deep(.resource-meta__value) {
  color: var(--text-inverse-muted);
}

/*
 * Le pseudo-élément séparateur "·" entre les blocs meta inline.
 * Le sélecteur de ResourceMeta ne peut être surchargé qu'avec :deep().
 */
.resource-hero :deep(
  .resource-meta__row:not(.resource-meta__row--full)
  + .resource-meta__row:not(.resource-meta__row--full)::before
) {
  color: var(--color-cream-subtle);
}

.resource-hero :deep(.resource-meta__chip-link),
.resource-hero :deep(.resource-meta__chip-static) {
  border-color: rgba(255, 255, 255, 0.14);
  background-color: rgba(255, 255, 255, 0.06);
  color: var(--text-inverse-muted);
}

.resource-hero :deep(.resource-meta__chip-link:hover),
.resource-hero :deep(.resource-meta__chip-link:focus-visible) {
  border-color: rgba(255, 255, 255, 0.35);
  background-color: rgba(255, 255, 255, 0.12);
  color: var(--text-inverse);
}

/* ── Responsive ─────────────────────────────────────────────────────── */

@media (min-width: 768px) {
  .resource-hero {
    padding-block: var(--space-20) var(--space-14);
  }

  .resource-hero--image-below {
    padding-bottom: var(--space-24);
  }
}
</style>
