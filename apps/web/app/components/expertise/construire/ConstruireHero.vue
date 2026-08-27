<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import ConstruireSystemVisual from "~/components/expertise/construire/ConstruireSystemVisual.vue"

/**
 * Hero de la page `/expertises/construire` — direction « Product Assembly ».
 *
 * Composition volontairement différente du hero générique
 * `ExpertisePageHero` et distincte de `ConcevoirHero` :
 *   - fond `navy-deep` (le pôle Construire porte la personnalité technique) ;
 *   - grille deux colonnes ~52 % / 48 % en desktop, colonne unique en mobile ;
 *   - à gauche : eyebrow + H1 + introduction + capabilities (mono, discrètes)
 *     + indicateur pôle 02/05 en pied de bloc ;
 *   - à droite : visuel « architecture technique » (SVG inline, aria-hidden).
 *
 * Accessibilité :
 *   - un unique H1 par page (garanti par la route dynamique) ;
 *   - `aria-labelledby` sur la `<section>` pointant sur l'id du H1 (id
 *     partagé `expertise-page-hero-title` : la page ne rend qu'un seul
 *     hero à la fois) ;
 *   - le visuel de droite est déclaré comme purement décoratif ;
 *   - contrastes cream/navy-deep vérifiés WCAG AA (ratio > 14).
 *
 * SEO : le H1 est rendu verbatim tel que défini dans `expertise-pages.ts`,
 * comme sur les autres pages fille — évite toute divergence entre le
 * schéma JSON-LD `Service.name` et le contenu visible.
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
    class="construire-hero"
    aria-labelledby="expertise-page-hero-title"
  >
    <BaseContainer
      width="wide"
      class="construire-hero__container"
    >
      <div class="construire-hero__text">
        <BaseEyebrow tone="inverse" class="construire-hero__eyebrow">
          {{ eyebrow }}
        </BaseEyebrow>
        <h1
          id="expertise-page-hero-title"
          class="construire-hero__title"
        >
          {{ title }}
        </h1>
        <p class="construire-hero__introduction">
          {{ introduction }}
        </p>
        <ul
          class="construire-hero__capabilities"
          aria-label="Capacités du pôle"
        >
          <li>Site vitrine</li>
          <li aria-hidden="true" class="construire-hero__capabilities-sep">/</li>
          <li>E-commerce</li>
          <li aria-hidden="true" class="construire-hero__capabilities-sep">/</li>
          <li>Application métier</li>
        </ul>
        <p class="construire-hero__marker" aria-hidden="true">
          Pôle 02 / 05
        </p>
      </div>
      <div class="construire-hero__visual">
        <ConstruireSystemVisual />
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.construire-hero {
  position: relative;
  background-color: var(--color-navy-deep);
  color: var(--text-inverse);
  padding-block: var(--space-12) var(--space-12);
  overflow: hidden;
}

/*
 * Grille technique discrète en fond du hero, atténuée sur les bords pour
 * ne pas concurrencer le texte. Radial mask pour un rendu propre en dark.
 */
.construire-hero::before {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  background-image:
    linear-gradient(to right, rgba(46, 134, 217, 0.10) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(46, 134, 217, 0.10) 1px, transparent 1px);
  background-size: 40px 40px;
  mask-image: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.75), transparent 78%);
  -webkit-mask-image: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.75), transparent 78%);
}

.construire-hero__container {
  position: relative;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: center;
}

.construire-hero__text {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 44rem;
}

.construire-hero__eyebrow {
  margin: 0;
}

.construire-hero__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2rem, 5vw, 3.75rem);
  line-height: 1.06;
  letter-spacing: -0.02em;
  color: var(--color-cream);
  margin: 0;
  max-width: 20ch;
  overflow-wrap: anywhere;
}

.construire-hero__introduction {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.125rem);
  line-height: 1.65;
  color: var(--text-inverse-muted);
  margin: 0;
  max-width: 62ch;
}

.construire-hero__capabilities {
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
  color: var(--color-devzair-blue);
}

.construire-hero__capabilities li {
  margin: 0;
  padding: 0;
}

.construire-hero__capabilities-sep {
  color: rgba(196, 203, 211, 0.4);
}

.construire-hero__marker {
  font-family: var(--font-family-mono);
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: rgba(196, 203, 211, 0.55);
  margin: var(--space-3) 0 0;
}

.construire-hero__visual {
  width: 100%;
  max-width: 560px;
  margin-inline: auto;
}

@media (min-width: 768px) {
  .construire-hero {
    padding-block: var(--space-16) var(--space-16);
  }
}

@media (min-width: 1024px) {
  .construire-hero__container {
    grid-template-columns: minmax(0, 52fr) minmax(0, 48fr);
    gap: var(--space-12);
  }

  .construire-hero__visual {
    max-width: none;
    margin-inline: 0;
  }
}

@media (min-width: 1440px) {
  .construire-hero {
    padding-block: var(--space-20) var(--space-20);
  }
}
</style>
