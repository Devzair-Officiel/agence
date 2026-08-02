<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import { expertisePillars } from "~/config/expertise-pillars"

/**
 * Section « Nos expertises — cinq pôles ». Grille détaillée des cinq pôles
 * de `expertise-pillars.ts` (source unique — aucune duplication éditoriale).
 *
 * Rendu :
 *   - <768px  : carrousel scroll-snap CSS-natif (aucun JS, aucun bouton).
 *     Les 5 pôles sont dans le DOM, indexables et lisibles sans JS.
 *   - 768–1023px : grille 2 colonnes standard, ordre naturel.
 *   - ≥1024px : grille asymétrique 3 colonnes × 3 lignes.
 *     Concevoir (variant "default", grand format) occupe 2 lignes en
 *     colonne 1. Faire évoluer (variant "accent") reçoit un fond petrol
 *     inversé pour marquer la clôture du parcours.
 *
 * Ancre : `id="expertises"` — cible du lien de nav header (Phase 5B).
 *
 * Accessibilité :
 *   - un H2 unique, un H3 par pôle ;
 *   - carte non focusable (aucun `tabindex`, aucun rôle interactif) : les
 *     cartes n'ont pas d'action ; les liens vers les pages détaillées
 *     arriveront en Phase 7 (services) ;
 *   - la liste des services est une vraie `<ul>` sémantique ;
 *   - hint visuel + texte sr-only pour indiquer le geste de scroll sur
 *     mobile, sans être répétitif sur desktop (`@media` masque).
 *
 * Défense en profondeur reduced-motion : aucun mouvement introduit ici,
 * mais on neutralise explicitement `scroll-behavior: smooth` si un
 * ancêtre le forçait.
 */

const pillars = [...expertisePillars].sort((a, b) => a.order - b.order)
</script>

<template>
  <section
    id="expertises"
    class="home-pillars"
    aria-labelledby="home-pillars-title"
  >
    <BaseContainer class="home-pillars__container">
      <header class="home-pillars__intro">
        <BaseEyebrow class="home-pillars__eyebrow">
          Nos expertises · 5 pôles
        </BaseEyebrow>
        <h2 id="home-pillars-title" class="home-pillars__title">
          Des expertises complémentaires, cinq pôles, une même démarche.
        </h2>
        <p class="home-pillars__lead">
          Chaque pôle porte ses propres livrables, ses propres compétences, ses
          propres critères de qualité. Ensemble, ils couvrent tout le cycle de
          vie d'une présence digitale, du premier trait de crayon aux
          évolutions post-lancement.
        </p>
      </header>

      <p class="home-pillars__hint" aria-hidden="true">
        Faites défiler horizontalement pour parcourir les cinq pôles
      </p>
      <p class="sr-only">
        La liste ci-dessous se parcourt horizontalement au doigt ou au trackpad
        sur mobile. Chaque pôle regroupe trois prestations principales.
      </p>

      <ul
        class="home-pillars__grid"
        aria-label="Les cinq pôles d'expertise Devzair"
      >
        <li
          v-for="pillar in pillars"
          :key="pillar.id"
          class="home-pillars__card"
          :data-variant="pillar.variant"
          :data-order="pillar.order"
        >
          <span class="home-pillars__card-index" aria-hidden="true">
            {{ String(pillar.order).padStart(2, "0") }}
          </span>
          <h3 class="home-pillars__card-title">{{ pillar.label }}</h3>
          <p class="home-pillars__card-tag">{{ pillar.description }}</p>
          <p class="home-pillars__card-description">
            {{ pillar.longDescription }}
          </p>
          <ul
            class="home-pillars__services"
            :aria-label="`Prestations pour ${pillar.label}`"
          >
            <li
              v-for="service in pillar.services"
              :key="service"
              class="home-pillars__service"
            >
              {{ service }}
            </li>
          </ul>
        </li>
      </ul>
    </BaseContainer>
  </section>
</template>

<style scoped>
.home-pillars {
  background-color: var(--background-secondary);
  color: var(--text-primary);
  padding-block: var(--space-16);
}

.home-pillars__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.home-pillars__intro {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 48rem;
}

.home-pillars__eyebrow {
  margin-bottom: var(--space-2);
}

.home-pillars__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 4vw, 2.5rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 24ch;
}

.home-pillars__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

.home-pillars__hint {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

/* ----- Mobile : carrousel scroll-snap CSS-natif ----- */
.home-pillars__grid {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  gap: var(--space-4);
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  scroll-padding-inline: var(--container-gutter-mobile);
  scroll-behavior: smooth;
  /* Marges négatives pour laisser le premier item toucher le bord du viewport
     tout en conservant la gouttière du container au repos. */
  margin-inline: calc(-1 * var(--container-gutter-mobile));
  padding-inline: var(--container-gutter-mobile);
  padding-block: var(--space-2) var(--space-4);
  /* iOS momentum. */
  -webkit-overflow-scrolling: touch;
}

.home-pillars__card {
  flex: 0 0 min(85%, 22rem);
  scroll-snap-align: start;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-6);
  background-color: var(--surface-primary);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-sm);
}

.home-pillars__card-index {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.08em;
  color: var(--color-petrol);
}

.home-pillars__card-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1.25rem;
  line-height: 1.3;
  color: var(--text-primary);
  margin: 0;
}

.home-pillars__card-tag {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--text-muted);
  margin: 0;
}

.home-pillars__card-description {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
}

.home-pillars__services {
  list-style: none;
  margin: var(--space-2) 0 0 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
  border-top: 1px solid var(--border-default);
  padding-top: var(--space-3);
}

.home-pillars__service {
  position: relative;
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.5;
  color: var(--text-primary);
  padding-inline-start: var(--space-4);
}

.home-pillars__service::before {
  content: "";
  position: absolute;
  left: 0;
  top: 0.55em;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: var(--color-petrol);
}

/* Variante "primary" — Construire — accent latéral petrol. */
.home-pillars__card[data-variant="primary"] {
  border-left: 3px solid var(--color-petrol);
}

/* Variante "accent" — Faire évoluer — fond petrol, texte cream. */
.home-pillars__card[data-variant="accent"] {
  background-color: var(--color-petrol);
  border-color: var(--color-petrol);
  color: var(--text-inverse);
}

.home-pillars__card[data-variant="accent"] .home-pillars__card-title,
.home-pillars__card[data-variant="accent"] .home-pillars__service {
  color: var(--text-inverse);
}

/*
 * Sur fond petrol, la palette Devzair-blue tombe sous WCAG AA
 * (contraste 2.08:1 pour un petit texte gras). On garde donc cream/
 * cream pour le texte, et un cream-muted pour la puce décorative.
 * Cf. Axe rule color-contrast (WCAG 1.4.3).
 */
.home-pillars__card[data-variant="accent"] .home-pillars__card-index,
.home-pillars__card[data-variant="accent"] .home-pillars__card-tag {
  color: var(--color-cream);
}

.home-pillars__card[data-variant="accent"] .home-pillars__card-description {
  color: var(--text-inverse-muted);
}

.home-pillars__card[data-variant="accent"] .home-pillars__services {
  border-top-color: rgba(255, 255, 255, 0.24);
}

.home-pillars__card[data-variant="accent"] .home-pillars__service::before {
  background-color: var(--color-cream-muted);
}

@media (min-width: 768px) {
  .home-pillars {
    padding-block: var(--space-20);
  }

  .home-pillars__container {
    gap: var(--space-10);
  }

  .home-pillars__hint {
    display: none;
  }

  .home-pillars__grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-6);
    overflow: visible;
    scroll-snap-type: none;
    margin-inline: 0;
    padding-inline: 0;
    padding-block: 0;
  }

  .home-pillars__card {
    flex: initial;
  }
}

@media (min-width: 1024px) {
  .home-pillars {
    padding-block: var(--space-24);
  }

  .home-pillars__grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    grid-auto-rows: minmax(0, auto);
    gap: var(--space-6);
  }

  /*
   * Grille asymétrique :
   *   ┌──────────┬──────────┬──────────┐
   *   │ Concevoir│Construire│ Valoriser│
   *   │          ├──────────┼──────────┤
   *   │          │Visibilité│ FaireEvo │
   *   └──────────┴──────────┴──────────┘
   * Concevoir spans 2 rows en colonne 1.
   */
  .home-pillars__card[data-order="1"] {
    grid-column: 1;
    grid-row: 1 / span 2;
  }

  .home-pillars__card[data-order="2"] {
    grid-column: 2;
    grid-row: 1;
  }

  .home-pillars__card[data-order="3"] {
    grid-column: 3;
    grid-row: 1;
  }

  .home-pillars__card[data-order="4"] {
    grid-column: 2;
    grid-row: 2;
  }

  .home-pillars__card[data-order="5"] {
    grid-column: 3;
    grid-row: 2;
  }

  /* La carte pleine hauteur (Concevoir) mérite un peu plus d'air interne. */
  .home-pillars__card[data-order="1"] {
    padding: var(--space-8);
    gap: var(--space-4);
  }

  .home-pillars__card[data-order="1"] .home-pillars__card-title {
    font-size: 1.5rem;
  }
}

@media (prefers-reduced-motion: reduce) {
  .home-pillars__grid {
    scroll-behavior: auto;
  }
}
</style>
