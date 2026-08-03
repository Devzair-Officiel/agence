<script setup lang="ts">
import BaseButton from "~/components/base/BaseButton.vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Section finale « Parlons de votre projet » — CTA de clôture de l'accueil.
 *
 * Depuis le déplacement du formulaire vers la page dédiée `/contact`, cette
 * section joue le rôle d'un panneau CTA compact : eyebrow, H2, paragraphe,
 * bouton qui pointe explicitement sur `/contact`. Elle conserve son ancre
 * `id="contact"` pour que les anciens liens (`/#contact` en cache moteur,
 * signets, partages) atterrissent quand même sur la bonne section — Nuxt
 * ne se plaint pas d'une ancre qui ne correspond à aucune cible interne.
 *
 * L'eyebrow, le H2 et le paragraphe éditoriaux restent verbatim (source
 * unique `docs/01-CONTENT.md §7.1`).
 *
 * Accessibilité :
 *   - un H2 unique pour la section, ancre `id="contact"` sur `<section>` ;
 *   - un seul CTA visible, libellé identique au CTA header/mobile pour
 *     renforcer la mémorisation.
 */
</script>

<template>
  <section
    id="contact"
    class="home-cta"
    aria-labelledby="home-cta-title"
  >
    <!--
      Décor de fond « circuit-trace » — clin d'œil aux tracés bleus qui
      partent du Z dans le logo Devzair. Purement visuel donc aria-hidden
      et pointer-events: none via la feuille de style.
    -->
    <svg
      class="home-cta__circuit"
      viewBox="0 0 260 140"
      aria-hidden="true"
      focusable="false"
      xmlns="http://www.w3.org/2000/svg"
    >
      <g fill="none" stroke="var(--color-devzair-blue)" stroke-width="1.5">
        <path d="M0 20 H160 L200 60 H260" />
        <path d="M0 60 H120 L160 100 H260" />
        <path d="M0 100 H80 L120 140" />
      </g>
      <g fill="var(--color-devzair-blue)">
        <circle cx="0" cy="20" r="4" />
        <circle cx="0" cy="60" r="4" />
        <circle cx="0" cy="100" r="4" />
        <circle cx="200" cy="60" r="3" />
        <circle cx="160" cy="100" r="3" />
      </g>
    </svg>
    <BaseContainer width="full" class="home-cta__container">
      <div class="home-cta__intro">
        <BaseEyebrow tone="inverse" class="home-cta__eyebrow">
          Parlons de votre projet
        </BaseEyebrow>
        <h2 id="home-cta-title" class="home-cta__title">
          Construisons une présence digitale à la hauteur de votre entreprise.
        </h2>
        <p class="home-cta__lead">
          Un premier échange nous permettra de comprendre votre besoin, de
          clarifier les priorités et de définir une direction adaptée à votre
          activité.
        </p>
      </div>

      <div class="home-cta__actions">
        <BaseButton to="/contact" variant="primary" class="home-cta__button">
          Parler de votre projet
          <template #icon>→</template>
        </BaseButton>
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.home-cta {
  position: relative;
  isolation: isolate;
  overflow: hidden;
  background-color: var(--background-inverse);
  color: var(--text-inverse);
  padding-block: var(--space-16) var(--space-20);
}

/*
 * Décor circuit-trace inspiré du logo (traces bleues qui partent du Z).
 * Placé en haut à gauche, sous la couche éditoriale, invisible aux tests
 * d'interaction et aux lecteurs d'écran.
 */
.home-cta__circuit {
  position: absolute;
  top: var(--space-6);
  left: 0;
  width: min(220px, 28vw);
  height: auto;
  z-index: 0;
  opacity: 0.18;
  pointer-events: none;
}

@media (min-width: 1024px) {
  .home-cta__circuit {
    top: var(--space-10);
    width: 260px;
  }
}

.home-cta__container {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
  align-items: center;
  text-align: center;
}

.home-cta__intro {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--space-4);
  max-width: 55rem;
  margin-inline: auto;
}

.home-cta__eyebrow {
  margin-bottom: var(--space-2);
}

.home-cta__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2.25rem, 5.2vw, 3.5rem);
  line-height: 1.1;
  letter-spacing: -0.015em;
  color: var(--text-inverse);
  margin: 0;
  max-width: 28ch;
  text-wrap: balance;
}

.home-cta__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  line-height: 1.6;
  color: var(--text-inverse-muted);
  margin: 0;
  max-width: 56ch;
  text-wrap: pretty;
}

.home-cta__actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: var(--space-3);
  margin-top: var(--space-2);
}

@media (min-width: 1024px) {
  .home-cta {
    padding-block: var(--space-20) var(--space-24);
  }
}
</style>
