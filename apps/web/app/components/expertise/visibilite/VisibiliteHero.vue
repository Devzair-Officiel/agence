<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import VisibiliteTerritoryVisual from "~/components/expertise/visibilite/VisibiliteTerritoryVisual.vue"

/**
 * Hero de la page `/expertises/visibilite` — direction « Search Territory /
 * Signal Map ».
 *
 * Distinct des autres pôles :
 *   - fond cream avec accent petrol dominant — évoque un plan
 *     cartographique clair, pas navy comme Construire ni sable/photo
 *     comme Valoriser ;
 *   - grille 52 / 48 desktop (texte / visuel), colonne unique mobile ;
 *   - à gauche : eyebrow + H1 verbatim + introduction + capacités mono
 *     (SEO / VISIBILITÉ LOCALE / STRATÉGIE ÉDITORIALE) + marker 04 / 05 ;
 *   - à droite : `VisibiliteTerritoryVisual` — carte abstraite purement
 *     SVG/CSS, aria-hidden.
 *
 * Accessibilité :
 *   - un unique H1 par page, id `expertise-page-hero-title` (identique
 *     aux autres directions — la page ne rend qu'un hero à la fois) ;
 *   - `aria-labelledby` sur `<section>` ;
 *   - contraste ink / cream vérifié > 12:1 sur le H1 ;
 *   - `--color-petrol` sur eyebrow et capacités mono (contraste ≥ 5:1
 *     sur cream, cf. tokens.css §accents).
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
    class="visibilite-hero"
    aria-labelledby="expertise-page-hero-title"
  >
    <BaseContainer width="wide" class="visibilite-hero__container">
      <div class="visibilite-hero__text">
        <BaseEyebrow class="visibilite-hero__eyebrow">
          {{ eyebrow }}
        </BaseEyebrow>
        <h1
          id="expertise-page-hero-title"
          class="visibilite-hero__title"
        >
          {{ title }}
        </h1>
        <p class="visibilite-hero__introduction">
          {{ introduction }}
        </p>
        <ul
          class="visibilite-hero__capabilities"
          aria-label="Capacités du pôle"
        >
          <li>SEO</li>
          <li aria-hidden="true" class="visibilite-hero__capabilities-sep">/</li>
          <li>Visibilité locale</li>
          <li aria-hidden="true" class="visibilite-hero__capabilities-sep">/</li>
          <li>Stratégie éditoriale</li>
        </ul>
        <p class="visibilite-hero__marker" aria-hidden="true">
          Pôle 04 / 05
        </p>
      </div>
      <div class="visibilite-hero__visual">
        <VisibiliteTerritoryVisual />
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
/*
 * Fond cream. Une discrète composition topographique en fond, indiquant
 * un « terrain » plutôt qu'un simple bloc UI. Faible opacité pour ne pas
 * concurrencer le texte.
 */
.visibilite-hero {
  position: relative;
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-12) var(--space-12);
  overflow: hidden;
}

.visibilite-hero::before {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  background-image:
    radial-gradient(
      circle at 82% 34%,
      rgba(12, 91, 87, 0.10) 0%,
      rgba(12, 91, 87, 0.06) 30%,
      transparent 60%
    ),
    linear-gradient(to right, rgba(12, 91, 87, 0.05) 1px, transparent 1px);
  background-size: 100% 100%, 96px 100%;
  mask-image: linear-gradient(to right, transparent 0%, black 20%, black 80%, transparent 100%);
  -webkit-mask-image: linear-gradient(to right, transparent 0%, black 20%, black 80%, transparent 100%);
}

.visibilite-hero__container {
  position: relative;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: center;
}

.visibilite-hero__text {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 44rem;
}

.visibilite-hero__eyebrow {
  margin: 0;
}

.visibilite-hero__title {
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

.visibilite-hero__introduction {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.125rem);
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

.visibilite-hero__capabilities {
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

.visibilite-hero__capabilities li {
  margin: 0;
  padding: 0;
}

.visibilite-hero__capabilities-sep {
  color: rgba(22, 25, 28, 0.32);
}

.visibilite-hero__marker {
  font-family: var(--font-family-mono);
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-ink-subtle);
  margin: var(--space-3) 0 0;
}

.visibilite-hero__visual {
  width: 100%;
  max-width: 560px;
  margin-inline: auto;
}

@media (min-width: 768px) {
  .visibilite-hero {
    padding-block: var(--space-16) var(--space-16);
  }
}

@media (min-width: 1024px) {
  .visibilite-hero__container {
    grid-template-columns: minmax(0, 52fr) minmax(0, 48fr);
    gap: var(--space-12);
  }

  .visibilite-hero__visual {
    max-width: none;
    margin-inline: 0;
  }
}

@media (min-width: 1440px) {
  .visibilite-hero {
    padding-block: var(--space-20) var(--space-20);
  }
}
</style>
