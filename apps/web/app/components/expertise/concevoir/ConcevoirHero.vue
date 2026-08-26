<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import ConcevoirBlueprintVisual from "~/components/expertise/concevoir/ConcevoirBlueprintVisual.vue"

/**
 * Hero de la page `/expertises/concevoir` — direction « Digital Blueprint ».
 *
 * Composition volontairement différente du hero générique
 * `ExpertisePageHero` :
 *   - grille deux colonnes en desktop, colonne unique en mobile ;
 *   - à gauche : eyebrow + H1 + introduction (contenu métier issu de
 *     `expertise-pages.ts`) ;
 *   - à droite : visuel « plan » décoratif (SVG inline, aria-hidden).
 *
 * Accessibilité :
 *   - un unique H1 par page (garanti par la route dynamique) ;
 *   - `aria-labelledby` sur la `<section>` pointant sur l'id du H1 ;
 *   - le visuel de droite est déclaré comme purement décoratif.
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
    class="concevoir-hero"
    aria-labelledby="expertise-page-hero-title"
  >
    <BaseContainer
      width="wide"
      class="concevoir-hero__container"
    >
      <div class="concevoir-hero__text">
        <BaseEyebrow class="concevoir-hero__eyebrow">
          {{ eyebrow }}
        </BaseEyebrow>
        <h1
          id="expertise-page-hero-title"
          class="concevoir-hero__title"
        >
          {{ title }}
        </h1>
        <p class="concevoir-hero__introduction">
          {{ introduction }}
        </p>
        <dl class="concevoir-hero__meta" aria-label="Repères de la démarche">
          <div class="concevoir-hero__meta-row">
            <dt>Direction</dt>
            <dd>Décider avant de construire</dd>
          </div>
          <div class="concevoir-hero__meta-row">
            <dt>Étapes clés</dt>
            <dd>Objectifs · Structure · Parcours · Interface · Système</dd>
          </div>
        </dl>
      </div>
      <div class="concevoir-hero__visual">
        <ConcevoirBlueprintVisual />
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.concevoir-hero {
  position: relative;
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-12) var(--space-12);
  overflow: hidden;
}

/*
 * Grille très discrète en fond du hero pour prolonger la métaphore du plan
 * dans toute la section, sans écraser le texte. Rendue via un radial fade
 * pour ne pas surcharger les bords.
 */
.concevoir-hero::before {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  background-image:
    linear-gradient(to right, rgba(12, 91, 87, 0.09) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(12, 91, 87, 0.09) 1px, transparent 1px);
  background-size: 32px 32px;
  mask-image: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.7), transparent 75%);
  -webkit-mask-image: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.7), transparent 75%);
}

.concevoir-hero__container {
  position: relative;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: center;
}

.concevoir-hero__text {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 44rem;
}

.concevoir-hero__eyebrow {
  margin: 0;
}

.concevoir-hero__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2rem, 4.6vw, 3.25rem);
  line-height: 1.08;
  letter-spacing: -0.015em;
  color: var(--text-primary);
  margin: 0;
  max-width: 20ch;
}

.concevoir-hero__introduction {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.125rem);
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

.concevoir-hero__meta {
  margin: var(--space-4) 0 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-4) var(--space-5);
  border-left: 2px solid var(--color-petrol);
  background-color: rgba(12, 91, 87, 0.05);
  border-radius: 0 var(--radius-md) var(--radius-md) 0;
}

.concevoir-hero__meta-row {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.concevoir-hero__meta dt {
  font-family: var(--font-family-mono);
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
}

.concevoir-hero__meta dd {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.5;
  color: var(--text-primary);
  margin: 0;
}

.concevoir-hero__visual {
  width: 100%;
  max-width: 560px;
  margin-inline: auto;
}

@media (min-width: 768px) {
  .concevoir-hero {
    padding-block: var(--space-16) var(--space-16);
  }
}

@media (min-width: 1024px) {
  .concevoir-hero__container {
    grid-template-columns: minmax(0, 6fr) minmax(0, 5fr);
    gap: var(--space-12);
  }

  .concevoir-hero__visual {
    max-width: none;
    margin-inline: 0;
  }
}

@media (min-width: 1440px) {
  .concevoir-hero {
    padding-block: var(--space-20) var(--space-20);
  }
}
</style>
