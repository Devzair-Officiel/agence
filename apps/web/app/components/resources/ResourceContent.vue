<script setup lang="ts">
/**
 * ResourceContent — SEUL composant du projet autorisé à utiliser `v-html`.
 *
 * L'HTML injecté ici provient EXCLUSIVEMENT du champ `content_html` de
 * l'API Symfony, produit par `CommonMarkArticleRenderer` avec la
 * politique de sécurité `MarkdownSecurityPolicy` (ADR-010, ADR-011) :
 *   - `html_input = strip` (HTML brut du Markdown source neutralisé) ;
 *   - `allow_unsafe_links = false` (schémas dangereux neutralisés) ;
 *   - bornes de complexité (nesting, delimiters).
 *
 * Le champ transite validé par le mapper `mapArticleDetailResponse` puis
 * par l'endpoint Nitro interne `_editorial/detail/[slug]` — aucun autre
 * chemin ne peut y insérer de HTML arbitraire.
 *
 * Toute évolution de ce composant qui étendrait `v-html` à un autre champ
 * ou à un autre attribut est un changement de frontière de confiance :
 * elle doit être documentée dans une nouvelle ADR.
 */

interface Props {
  contentHtml: string
}

defineProps<Props>()
</script>

<template>
  <!--
    Le conteneur `<div>` est la surface unique de rendu HTML éditorial.
    Les styles typographiques ci-dessous ciblent les balises produites par
    CommonMark (h2/h3/p/ul/ol/blockquote/pre/table/a) pour un rendu
    cohérent avec le design system Devzair.

    eslint-disable-next-line vue/no-v-html — voir en-tête du composant :
    c'est LA frontière de confiance HTML éditoriale (ADR-011).
  -->
  <!-- eslint-disable-next-line vue/no-v-html -->
  <div class="resource-content" v-html="contentHtml" />
</template>

<style scoped>
.resource-content {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.7;
  color: var(--text-primary);
  max-width: 62ch;
}

.resource-content :deep(h2) {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.5rem, 2.6vw, 2rem);
  line-height: 1.2;
  letter-spacing: -0.01em;
  margin: var(--space-10) 0 var(--space-3);
}

.resource-content :deep(h3) {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.25rem, 2vw, 1.5rem);
  line-height: 1.25;
  margin: var(--space-8) 0 var(--space-2);
}

.resource-content :deep(p) {
  margin: 0 0 var(--space-4);
}

.resource-content :deep(a) {
  color: var(--text-primary);
  text-decoration: underline;
  text-underline-offset: 0.15em;
}

.resource-content :deep(a:hover),
.resource-content :deep(a:focus-visible) {
  text-decoration-thickness: 2px;
}

.resource-content :deep(ul),
.resource-content :deep(ol) {
  margin: 0 0 var(--space-4);
  padding-left: 1.25rem;
}

.resource-content :deep(li + li) {
  margin-top: var(--space-2);
}

.resource-content :deep(blockquote) {
  margin: var(--space-6) 0;
  padding-left: var(--space-4);
  border-left: 3px solid var(--border-default);
  color: var(--text-secondary);
  font-style: italic;
}

.resource-content :deep(code) {
  font-family: var(--font-family-mono);
  font-size: 0.9em;
  background-color: var(--background-secondary);
  padding: 0.1em 0.3em;
  border-radius: 3px;
}

.resource-content :deep(pre) {
  font-family: var(--font-family-mono);
  background-color: var(--background-secondary);
  color: var(--text-primary);
  padding: var(--space-4);
  border-radius: 6px;
  overflow-x: auto;
  margin: var(--space-6) 0;
  font-size: 0.9rem;
  line-height: 1.5;
}

.resource-content :deep(pre code) {
  background: transparent;
  padding: 0;
}

.resource-content :deep(table) {
  border-collapse: collapse;
  margin: var(--space-6) 0;
  width: 100%;
}

.resource-content :deep(th),
.resource-content :deep(td) {
  border: 1px solid var(--border-default);
  padding: var(--space-2) var(--space-3);
  text-align: left;
}

.resource-content :deep(th) {
  background-color: var(--background-secondary);
  font-weight: 600;
}
</style>
