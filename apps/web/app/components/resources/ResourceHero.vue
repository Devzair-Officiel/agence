<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import ResourceMeta from "~/components/resources/ResourceMeta.vue"
import type { ArticleDetail } from "~/types/editorial"

/**
 * En-tête d'une page détail `/ressources/{slug}`.
 *
 * Contient le H1 unique de la page, l'eyebrow « Ressource » et le
 * bandeau de métadonnées (`ResourceMeta`). N'inclut PAS `contentHtml`,
 * qui est le domaine de `ResourceContent` — seule frontière de confiance
 * autorisée à utiliser `v-html` (ADR-011).
 *
 * Accessibilité :
 *   - `<section aria-labelledby="resource-hero-title">` référence le H1 ;
 *   - eyebrow reste un `<p>` typé, jamais un titre.
 */

interface Props {
  article: ArticleDetail
}

defineProps<Props>()
</script>

<template>
  <section class="resource-hero" aria-labelledby="resource-hero-title">
    <BaseContainer class="resource-hero__container">
      <div class="resource-hero__content">
        <BaseEyebrow class="resource-hero__eyebrow">Ressource</BaseEyebrow>
        <h1 id="resource-hero-title" class="resource-hero__title">
          {{ article.title }}
        </h1>
        <p class="resource-hero__excerpt">{{ article.excerpt }}</p>
        <ResourceMeta
          class="resource-hero__meta"
          :author="article.author"
          :published-at="article.publishedAt"
          :updated-at="article.updatedAt"
          :expertise-ids="article.expertiseIds"
        />
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.resource-hero {
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-10);
}

.resource-hero__container {
  display: flex;
  flex-direction: column;
}

.resource-hero__content {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  max-width: 52rem;
}

.resource-hero__eyebrow {
  margin: 0;
}

.resource-hero__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.875rem, 4vw, 2.75rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 26ch;
}

.resource-hero__excerpt {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.5vw, 1.125rem);
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

.resource-hero__meta {
  margin-top: var(--space-2);
}

@media (min-width: 768px) {
  .resource-hero {
    padding-block: var(--space-16) var(--space-12);
  }
}
</style>
