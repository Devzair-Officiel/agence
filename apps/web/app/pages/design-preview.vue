<script setup lang="ts">
/**
 * Page interne temporaire pour vérifier visuellement les fondations du design.
 * Non indexable, non listée dans le sitemap. Sera supprimée à la fin de la phase
 * design system.
 */

usePageSeo({
  title: "Design preview",
  description:
    "Page interne de vérification visuelle des fondations du design Devzair. Non destinée aux résultats de recherche.",
  path: "/design-preview",
  noindex: true,
})
</script>

<template>
  <div class="preview">
    <BaseContainer as="section" class="preview__section">
      <BaseEyebrow>Design preview</BaseEyebrow>
      <h1 class="preview__title">Fondations du design Devzair</h1>
      <p class="preview__lead">
        Page interne de vérification visuelle. Elle est marquée
        <code>noindex, nofollow</code> et sera retirée à la clôture du lot
        design&nbsp;system.
      </p>
    </BaseContainer>

    <BaseContainer as="section" class="preview__section">
      <h2>Couleurs</h2>
      <div class="preview__swatches">
        <div class="preview__swatch" style="background-color: var(--color-sand)">
          <span>Sand<br ><code>#EFEBE2</code></span>
        </div>
        <div
          class="preview__swatch"
          style="background-color: var(--color-cream)"
        >
          <span>Cream<br ><code>#F4F1EA</code></span>
        </div>
        <div
          class="preview__swatch preview__swatch--dark"
          style="background-color: var(--color-ink)"
        >
          <span>Ink<br ><code>#16191C</code></span>
        </div>
        <div
          class="preview__swatch preview__swatch--dark"
          style="background-color: var(--color-navy)"
        >
          <span>Navy<br ><code>#0E1622</code></span>
        </div>
        <div
          class="preview__swatch preview__swatch--dark"
          style="background-color: var(--color-petrol)"
        >
          <span>Petrol<br ><code>#0C5B57</code></span>
        </div>
        <!-- Devzair blue est notre teinte d'accent (focus, flèches).
             Sur fond bleu clair, un texte gris foncé ne satisfait pas 4.5:1 ;
             on force donc du noir pur pour cette swatch afin de rester AA. -->
        <div
          class="preview__swatch preview__swatch--accent"
          style="background-color: var(--color-devzair-blue)"
        >
          <span>Devzair blue<br ><code>#2E86D9</code></span>
        </div>
      </div>
    </BaseContainer>

    <BaseContainer as="section" class="preview__section">
      <h2>Typographie</h2>
      <!-- Démonstration de l'échelle : on ne réutilise pas <h1>/<h2>/<h3>
           réels pour ne pas casser la hiérarchie sémantique de la page
           (un seul H1, celui du hero de la preview). Les échantillons sont
           donc rendus avec des classes visuellement identiques. -->
      <div class="preview__type">
        <BaseEyebrow>Eyebrow · Space Mono 700</BaseEyebrow>
        <p class="preview__type-sample preview__type-sample--h1">
          Titre H1 · Schibsted Grotesk 700
        </p>
        <p class="preview__type-sample preview__type-sample--h2">
          Titre H2 · Schibsted Grotesk 700
        </p>
        <p class="preview__type-sample preview__type-sample--h3">
          Titre H3 · Schibsted Grotesk 600
        </p>
        <p>
          Paragraphe standard · Hanken Grotesk 400. Sites internet, applications
          métier, identité visuelle, contenus professionnels et référencement.
        </p>
      </div>
    </BaseContainer>

    <BaseContainer as="section" class="preview__section">
      <h2>Boutons</h2>
      <div class="preview__row">
        <BaseButton variant="primary">Primary</BaseButton>
        <BaseButton variant="primary" disabled>Primary désactivé</BaseButton>
        <BaseButton variant="primary" loading>Chargement</BaseButton>
        <BaseButton variant="secondary">Secondary</BaseButton>
        <BaseButton variant="secondary" disabled>Secondary désactivé</BaseButton>
      </div>

      <div class="preview__row">
        <BaseButton to="/design-preview" variant="primary">
          Lien interne (NuxtLink)
          <template #icon>→</template>
        </BaseButton>
        <BaseButton
          href="https://www.w3.org/WAI/WCAG22/quickref/"
          variant="secondary"
        >
          Lien externe
          <template #icon>↗</template>
        </BaseButton>
      </div>
    </BaseContainer>

    <BaseContainer as="section" class="preview__section">
      <h2>Liens éditoriaux</h2>
      <div class="preview__row">
        <BaseLink to="/design-preview">Retour au top de la preview</BaseLink>
        <BaseLink href="https://www.w3.org/">Lire l'article sur le W3C</BaseLink>
      </div>
    </BaseContainer>

    <BaseContainer as="section" class="preview__section">
      <h2>Header et menu mobile</h2>
      <p>
        Le header est affiché en haut de la page. Réduisez la fenêtre en-dessous
        de 1024&nbsp;px puis cliquez sur le bouton menu pour tester l'ouverture,
        la fermeture (croix, <kbd>Échap</kbd>, clic sur un lien) et le verrou du
        scroll.
      </p>
    </BaseContainer>

    <BaseContainer as="section" class="preview__section">
      <h2>États interactifs</h2>
      <p>
        Naviguez au clavier avec <kbd>Tab</kbd> pour vérifier l'anneau de focus
        bleu Devzair sur les liens et boutons.
      </p>
    </BaseContainer>
  </div>
</template>

<style scoped>
.preview__section {
  padding-block: var(--space-10);
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.preview__section + .preview__section {
  border-top: 1px solid var(--border-default);
}

.preview__title {
  margin-top: var(--space-2);
}

.preview__lead {
  max-width: 60ch;
}

.preview__swatches {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: var(--space-3);
}

.preview__swatch {
  min-height: 120px;
  padding: var(--space-4);
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-default);
  display: flex;
  align-items: flex-end;
  font-family: var(--font-family-mono);
  font-size: 0.75rem;
  color: var(--text-primary);
}

.preview__swatch--dark {
  color: var(--text-inverse);
  border-color: transparent;
}

.preview__swatch--accent {
  /* Fond Devzair blue : le noir garantit un contraste ≥ 5.9:1 contre #2E86D9,
     alors que la couleur d'encre standard n'atteint que 4.17:1 (WCAG AA échoué). */
  color: #000;
  border-color: transparent;
}

.preview__swatch code {
  font-size: 0.6875rem;
  /* Opacité maintenue à 1 pour garantir un contraste ≥ 4.5:1 sur toutes
     les swatches, y compris Devzair blue en fond clair. */
  opacity: 1;
}

.preview__type {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.preview__type-sample {
  margin: 0;
  color: var(--text-primary);
  font-family: var(--font-family-heading);
  letter-spacing: -0.02em;
  line-height: 1.1;
}

.preview__type-sample--h1 {
  font-size: clamp(2rem, 4.2vw, 3.25rem);
  font-weight: var(--font-weight-heading);
  letter-spacing: -0.032em;
  line-height: 1.05;
}

.preview__type-sample--h2 {
  font-size: clamp(1.75rem, 2.8vw, 2.5rem);
  font-weight: var(--font-weight-heading);
  letter-spacing: -0.03em;
}

.preview__type-sample--h3 {
  font-size: clamp(1.25rem, 1.6vw, 1.5rem);
  font-weight: var(--font-weight-heading-medium);
}

.preview__row {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-4);
  align-items: center;
}

kbd {
  display: inline-block;
  padding: 2px 6px;
  border-radius: 4px;
  background-color: var(--surface-primary);
  border: 1px solid var(--border-default);
  font-family: var(--font-family-mono);
  font-size: 0.8125rem;
}

code {
  font-family: var(--font-family-mono);
  font-size: 0.8125rem;
  background-color: rgba(22, 25, 28, 0.07);
  padding: 1px 5px;
  border-radius: 4px;
}
</style>
