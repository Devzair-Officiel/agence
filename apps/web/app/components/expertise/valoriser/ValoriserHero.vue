<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import ValoriserEditorialVisual from "~/components/expertise/valoriser/ValoriserEditorialVisual.vue"

/**
 * Hero de la page `/expertises/valoriser` — direction « Editorial Studio ».
 *
 * Distinct des deux autres pôles :
 *   - fond cream/sand (pas navy comme Construire, pas blueprint clair
 *     comme Concevoir) — ambiance studio éditorial lumineuse ;
 *   - grille 52 / 48 en desktop (texte / visuel), colonne unique mobile ;
 *   - à gauche : eyebrow + H1 verbatim + introduction + capacités mono
 *     (Photographie / Contenus / Structuration) + indicateur Pôle 03 / 05 ;
 *   - à droite : `ValoriserEditorialVisual` — planche de travail abstraite
 *     purement SVG/CSS, aria-hidden.
 *
 * Accessibilité :
 *   - un unique H1 par page (id `expertise-page-hero-title` — identique
 *     aux autres directions, la page ne rend qu'un hero à la fois) ;
 *   - `aria-labelledby` sur `<section>` ;
 *   - contraste ink / cream vérifié > 12:1 sur le H1 ;
 *   - `--color-petrol` sur eyebrow et capacités mono (contraste ≥ 5:1
 *     sur cream — cf. tokens.css §accents).
 *
 * SEO : le H1 est rendu verbatim tel que défini dans `expertise-pages.ts`.
 */

interface Props {
  eyebrow: string
  title: string
  introduction: string
}

defineProps<Props>()
</script>

<template>
  <section
    class="valoriser-hero"
    aria-labelledby="expertise-page-hero-title"
  >
    <BaseContainer width="wide" class="valoriser-hero__container">
      <div class="valoriser-hero__text">
        <BaseEyebrow class="valoriser-hero__eyebrow">
          {{ eyebrow }}
        </BaseEyebrow>
        <h1
          id="expertise-page-hero-title"
          class="valoriser-hero__title"
        >
          {{ title }}
        </h1>
        <p class="valoriser-hero__introduction">
          {{ introduction }}
        </p>
        <ul
          class="valoriser-hero__capabilities"
          aria-label="Capacités du pôle"
        >
          <li>Photographie</li>
          <li aria-hidden="true" class="valoriser-hero__capabilities-sep">/</li>
          <li>Contenus</li>
          <li aria-hidden="true" class="valoriser-hero__capabilities-sep">/</li>
          <li>Structuration</li>
        </ul>
        <p class="valoriser-hero__marker" aria-hidden="true">
          Pôle 03 / 05
        </p>
      </div>
      <div class="valoriser-hero__visual">
        <ValoriserEditorialVisual />
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.valoriser-hero {
  position: relative;
  background-color: var(--background-secondary);
  color: var(--text-primary);
  padding-block: var(--space-12) var(--space-12);
  overflow: hidden;
}

/*
 * Fine grille éditoriale en fond, très discrète — évoque une planche de
 * mise en page vue de dessus. Masquée sur les bords pour ne pas
 * concurrencer le texte.
 */
.valoriser-hero::before {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  background-image:
    linear-gradient(to right, rgba(12, 91, 87, 0.06) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(12, 91, 87, 0.06) 1px, transparent 1px);
  background-size: 48px 48px;
  mask-image: radial-gradient(ellipse at 70% 40%, rgba(0, 0, 0, 0.55), transparent 75%);
  -webkit-mask-image: radial-gradient(ellipse at 70% 40%, rgba(0, 0, 0, 0.55), transparent 75%);
}

.valoriser-hero__container {
  position: relative;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: center;
}

.valoriser-hero__text {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 44rem;
}

.valoriser-hero__eyebrow {
  margin: 0;
}

.valoriser-hero__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2rem, 5vw, 3.75rem);
  line-height: 1.06;
  letter-spacing: -0.02em;
  color: var(--color-ink);
  margin: 0;
  max-width: 20ch;
  overflow-wrap: anywhere;
}

.valoriser-hero__introduction {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.125rem);
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

.valoriser-hero__capabilities {
  list-style: none;
  margin: var(--space-4) 0 0;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2) var(--space-3);
  font-family: var(--font-family-mono);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--color-petrol);
}

.valoriser-hero__capabilities li {
  margin: 0;
  padding: 0;
}

.valoriser-hero__capabilities-sep {
  color: rgba(22, 25, 28, 0.32);
}

.valoriser-hero__marker {
  font-family: var(--font-family-mono);
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-ink-subtle);
  margin: var(--space-3) 0 0;
}

.valoriser-hero__visual {
  width: 100%;
  max-width: 560px;
  margin-inline: auto;
}

@media (min-width: 768px) {
  .valoriser-hero {
    padding-block: var(--space-16) var(--space-16);
  }
}

@media (min-width: 1024px) {
  .valoriser-hero__container {
    grid-template-columns: minmax(0, 52fr) minmax(0, 48fr);
    gap: var(--space-12);
  }

  .valoriser-hero__visual {
    max-width: none;
    margin-inline: 0;
  }
}

@media (min-width: 1440px) {
  .valoriser-hero {
    padding-block: var(--space-20) var(--space-20);
  }
}
</style>
