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
 * `processHeadings` (utils/toc.ts) peut ajouter des attributs `id` sur
 * les H2/H3 avant que le HTML n'arrive ici — ce sont les seules
 * modifications tolérées en amont, sans injection de contenu.
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
/* ── Base typographique ─────────────────────────────────────────────── */

.resource-content {
  font-family: var(--font-family-body);
  font-size: 1.125rem;
  line-height: 1.75;
  color: var(--text-primary);
  max-width: 80ch;
}

/* ── Titres ─────────────────────────────────────────────────────────── */

.resource-content :deep(h2) {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.5rem, 2.5vw, 2.125rem);
  line-height: 1.2;
  letter-spacing: -0.015em;
  color: var(--text-primary);
  margin: var(--space-12) 0 var(--space-4);
  /* Compense le header sticky pour la navigation par ancre */
  scroll-margin-top: calc(var(--site-header-height) + var(--space-6));
}

.resource-content :deep(h2:first-child) {
  margin-top: 0;
}

.resource-content :deep(h3) {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.125rem, 2vw, 1.5rem);
  line-height: 1.25;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: var(--space-10) 0 var(--space-3);
  scroll-margin-top: calc(var(--site-header-height) + var(--space-6));
}

/* ── Paragraphes ─────────────────────────────────────────────────────── */

.resource-content :deep(p) {
  margin: 0 0 var(--space-5);
}

.resource-content :deep(p:last-child) {
  margin-bottom: 0;
}

/* ── Liens ───────────────────────────────────────────────────────────── */

.resource-content :deep(a) {
  color: var(--text-primary);
  text-decoration: underline;
  text-decoration-color: var(--color-petrol);
  text-underline-offset: 0.18em;
  text-decoration-thickness: 1px;
  transition: color 150ms var(--ease-out), text-decoration-color 150ms var(--ease-out);
}

.resource-content :deep(a:hover),
.resource-content :deep(a:focus-visible) {
  color: var(--color-petrol);
  text-decoration-thickness: 2px;
}

/* ── Listes ──────────────────────────────────────────────────────────── */

.resource-content :deep(ul),
.resource-content :deep(ol) {
  margin: 0 0 var(--space-5);
  padding-left: 1.5rem;
}

.resource-content :deep(li) {
  margin-bottom: var(--space-2);
  line-height: 1.65;
}

.resource-content :deep(li:last-child) {
  margin-bottom: 0;
}

.resource-content :deep(ul > li) {
  list-style-type: disc;
}

.resource-content :deep(ul > li::marker) {
  color: var(--color-petrol);
}

/* ── Citation (blockquote) ───────────────────────────────────────────── */

.resource-content :deep(blockquote) {
  margin: var(--space-8) 0;
  padding: var(--space-4) var(--space-6);
  border-left: 3px solid var(--color-petrol);
  background-color: var(--background-secondary);
  border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
  color: var(--text-secondary);
  font-style: italic;
}

.resource-content :deep(blockquote p:last-child) {
  margin-bottom: 0;
}

/* ── Code inline ─────────────────────────────────────────────────────── */

.resource-content :deep(code) {
  font-family: var(--font-family-mono);
  font-size: 0.875em;
  background-color: var(--background-secondary);
  color: var(--text-primary);
  padding: 0.125em 0.35em;
  border-radius: 3px;
  border: 1px solid var(--border-default);
}

/* ── Bloc de code ────────────────────────────────────────────────────── */

.resource-content :deep(pre) {
  font-family: var(--font-family-mono);
  background-color: var(--background-secondary);
  color: var(--text-primary);
  padding: var(--space-5);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-default);
  overflow-x: auto;
  margin: var(--space-6) 0;
  font-size: 0.875rem;
  line-height: 1.6;
}

.resource-content :deep(pre code) {
  background: transparent;
  border: none;
  padding: 0;
  font-size: inherit;
}

/* ── Tableaux ────────────────────────────────────────────────────────── */

.resource-content :deep(table) {
  border-collapse: collapse;
  margin: var(--space-8) 0;
  width: 100%;
  font-size: 1rem;
}

.resource-content :deep(th),
.resource-content :deep(td) {
  border: 1px solid var(--border-default);
  padding: var(--space-3) var(--space-4);
  text-align: left;
  vertical-align: top;
  line-height: 1.5;
}

.resource-content :deep(th) {
  background-color: var(--background-secondary);
  font-weight: 600;
  font-size: 0.9375rem;
}

.resource-content :deep(tr:nth-child(even) td) {
  background-color: color-mix(in srgb, var(--background-secondary) 40%, transparent);
}

/* ── Mouvement réduit ────────────────────────────────────────────────── */

@media (prefers-reduced-motion: reduce) {
  .resource-content :deep(a) {
    transition: none;
  }
}
</style>
