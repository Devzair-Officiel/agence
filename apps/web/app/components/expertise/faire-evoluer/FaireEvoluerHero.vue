<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import FaireEvoluerCycleVisual from "~/components/expertise/faire-evoluer/FaireEvoluerCycleVisual.vue"

/**
 * Hero de la page `/expertises/faire-evoluer` — direction « Living System /
 * Continuous Care ».
 *
 * Distinct des quatre autres pôles :
 *   - fond cream + géométrie navy/petrol (jamais navy plein) ;
 *   - grille 52 / 48 desktop (texte / visuel), colonne unique mobile ;
 *   - à gauche : eyebrow + H1 verbatim + introduction + capacités mono
 *     (MAINTENANCE & SÉCURITÉ / MESURE & AMÉLIORATION / ÉVOLUTIONS
 *     FONCTIONNELLES) + marker 05 / 05 ;
 *   - à droite : `FaireEvoluerCycleVisual` — système orbital SVG,
 *     aria-hidden.
 *
 * Accessibilité :
 *   - un unique H1 par page, id `expertise-page-hero-title` (identique
 *     aux autres directions — la page ne rend qu'un hero à la fois) ;
 *   - `aria-labelledby` sur `<section>` ;
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
    class="faire-evoluer-hero"
    aria-labelledby="expertise-page-hero-title"
  >
    <BaseContainer width="wide" class="faire-evoluer-hero__container">
      <div class="faire-evoluer-hero__text">
        <BaseEyebrow class="faire-evoluer-hero__eyebrow">
          {{ eyebrow }}
        </BaseEyebrow>
        <h1
          id="expertise-page-hero-title"
          class="faire-evoluer-hero__title"
        >
          {{ title }}
        </h1>
        <p class="faire-evoluer-hero__introduction">
          {{ introduction }}
        </p>
        <ul
          class="faire-evoluer-hero__capabilities"
          aria-label="Capacités du pôle"
        >
          <li>Maintenance &amp; sécurité</li>
          <li aria-hidden="true" class="faire-evoluer-hero__capabilities-sep">/</li>
          <li>Mesure &amp; amélioration</li>
          <li aria-hidden="true" class="faire-evoluer-hero__capabilities-sep">/</li>
          <li>Évolutions fonctionnelles</li>
        </ul>
        <p class="faire-evoluer-hero__marker" aria-hidden="true">
          Pôle 05 / 05
        </p>
      </div>
      <div class="faire-evoluer-hero__visual">
        <FaireEvoluerCycleVisual />
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
/*
 * Fond cream. Halo radial petrol côté visuel + fine grille horizontale
 * pour rappeler la cadence, sans concurrencer le texte.
 */
.faire-evoluer-hero {
  position: relative;
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-12) var(--space-12);
  overflow: hidden;
}

/*
 * Halo radial petrol très diffus derrière le cycle. Aucune grille
 * orthogonale : la direction Living System exclut le rendu « interface ».
 */
.faire-evoluer-hero::before {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  background-image:
    radial-gradient(
      circle at 78% 46%,
      rgba(12, 91, 87, 0.11) 0%,
      rgba(12, 91, 87, 0.05) 34%,
      transparent 68%
    );
}

.faire-evoluer-hero__container {
  position: relative;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: center;
}

.faire-evoluer-hero__text {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 44rem;
}

.faire-evoluer-hero__eyebrow {
  margin: 0;
}

.faire-evoluer-hero__title {
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

.faire-evoluer-hero__introduction {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.125rem);
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

.faire-evoluer-hero__capabilities {
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

.faire-evoluer-hero__capabilities li {
  margin: 0;
  padding: 0;
}

.faire-evoluer-hero__capabilities-sep {
  color: rgba(22, 25, 28, 0.32);
}

.faire-evoluer-hero__marker {
  font-family: var(--font-family-mono);
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-ink-subtle);
  margin: var(--space-3) 0 0;
}

.faire-evoluer-hero__visual {
  width: 100%;
  max-width: 520px;
  margin-inline: auto;
}

@media (min-width: 768px) {
  .faire-evoluer-hero {
    padding-block: var(--space-16) var(--space-16);
  }
}

@media (min-width: 1024px) {
  .faire-evoluer-hero__container {
    grid-template-columns: minmax(0, 52fr) minmax(0, 48fr);
    gap: var(--space-10);
    align-items: center;
  }

  .faire-evoluer-hero__visual {
    max-width: none;
    margin-inline: 0;
  }
}

@media (min-width: 1440px) {
  .faire-evoluer-hero {
    padding-block: var(--space-20) var(--space-20);
  }
}
</style>
