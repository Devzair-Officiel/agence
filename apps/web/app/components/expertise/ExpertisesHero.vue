<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import { expertisePillars } from "~/config/expertise-pillars"

/**
 * En-tête dédié à `/expertises` — variante « index typographique ».
 *
 * Rôle :
 *   - présenter la promesse éditoriale (eyebrow, H1, lead) à gauche ;
 *   - afficher à droite un sommaire numéroté (01 → 05) des cinq pôles,
 *     chaque entrée étant un lien vers l'ancre `#pillar-{id}` de la carte
 *     correspondante plus bas dans la page.
 *
 * Différencié du `HomeHero` : pas de fond sombre, pas de SVG animé, pas de
 * réassurance chiffrée. Le vocabulaire visuel est purement typographique
 * (grand chiffre + grand libellé) et sert la navigation.
 *
 * Accessibilité :
 *   - un seul H1 par page (celui-ci) ;
 *   - la liste des pôles est une vraie `<ol>` sémantique, chaque item est
 *     un lien vers l'ancre correspondante — les numéros 01…05 sont générés
 *     par la donnée `order`, pas par un compteur CSS, pour rester lisibles
 *     par les lecteurs d'écran ;
 *   - `aria-labelledby` sur la `<section>` cible le H1.
 */
</script>

<template>
  <section class="expertises-hero" aria-labelledby="expertises-hero-title">
    <div class="expertises-hero__backdrop" aria-hidden="true" />
    <BaseContainer width="wide" class="expertises-hero__container">
      <div class="expertises-hero__intro">
        <BaseEyebrow class="expertises-hero__eyebrow">
          Nos expertises
        </BaseEyebrow>
        <h1 id="expertises-hero-title" class="expertises-hero__title">
          Cinq pôles complémentaires pour construire une présence digitale
          cohérente.
        </h1>
        <p class="expertises-hero__lead">
          Chaque entreprise possède des besoins différents. Nous réunissons les
          expertises adaptées pour concevoir, construire, valoriser, rendre
          visible et faire évoluer votre projet.
        </p>
      </div>

      <nav class="expertises-hero__index" aria-label="Sommaire des cinq pôles">
        <ol class="expertises-hero__list" role="list">
          <li
            v-for="pillar in expertisePillars"
            :key="pillar.id"
            class="expertises-hero__item"
          >
            <a
              :href="`#pillar-${pillar.id}`"
              class="expertises-hero__link"
              :data-variant="pillar.variant"
            >
              <span class="expertises-hero__order" aria-hidden="true">
                {{ String(pillar.order).padStart(2, "0") }}
              </span>
              <span class="expertises-hero__label">{{ pillar.label }}</span>
            </a>
          </li>
        </ol>
      </nav>
    </BaseContainer>
  </section>
</template>

<style scoped>
.expertises-hero {
  position: relative;
  isolation: isolate;
  overflow: hidden;
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-16) var(--space-12);
}

/*
 * Motif « papier millimétré » : deux dégradés linéaires 1 px croisés,
 * répétés en 32×32 px, teinte petrol à 6 %. Reste sous le seuil de
 * lecture (le texte prime toujours) mais donne au hero une texture
 * éditoriale/technique cohérente avec le vocabulaire mono déjà installé
 * (Space Mono, numérotation 01…05 du sommaire). Superposé aux deux halos
 * radiaux (petrol haut-droite, devzair-blue bas-gauche) déjà en place :
 * grille sous les halos pour rester purement décorative en fond.
 */
.expertises-hero__backdrop {
  position: absolute;
  inset: 0;
  z-index: -1;
  pointer-events: none;
  background-color: transparent;
  background-image:
    radial-gradient(
      520px 420px at 92% 12%,
      rgba(12, 91, 87, 0.12),
      transparent 65%
    ),
    radial-gradient(
      420px 380px at 6% 92%,
      rgba(46, 134, 217, 0.10),
      transparent 68%
    ),
    linear-gradient(to right, rgba(12, 91, 87, 0.06) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(12, 91, 87, 0.06) 1px, transparent 1px);
  background-size:
    auto,
    auto,
    32px 32px,
    32px 32px;
}

.expertises-hero__container {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  align-items: start;
}

.expertises-hero__intro {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  max-width: 40rem;
}

.expertises-hero__eyebrow {
  margin-bottom: var(--space-2);
}

.expertises-hero__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2rem, 4.6vw, 3.25rem);
  line-height: 1.1;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 22ch;
  text-wrap: balance;
}

.expertises-hero__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.5vw, 1.125rem);
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 52ch;
}

.expertises-hero__index {
  width: 100%;
  max-width: 34rem;
  margin-left: auto;
}

/*
 * Timeline verticale : une ligne fine petrol court sur toute la hauteur de
 * la liste, et chaque item porte une pastille pleine positionnée au-dessus
 * de la ligne. Les cinq pastilles sont **toutes de la même couleur** (choix
 * pris avec l'utilisateur) pour ne pas introduire de hiérarchie visuelle
 * entre les pôles — ils restent égaux.
 */
.expertises-hero__list {
  list-style: none;
  margin: 0;
  padding: 0;
  position: relative;
  display: flex;
  flex-direction: column;
}

.expertises-hero__list::before {
  content: "";
  position: absolute;
  left: 10px;
  top: var(--space-6);
  bottom: var(--space-6);
  width: 1px;
  background-color: var(--color-petrol);
  opacity: 0.35;
}

.expertises-hero__item {
  position: relative;
  padding-left: var(--space-8);
}

.expertises-hero__item::before {
  content: "";
  position: absolute;
  left: 5px;
  top: 50%;
  width: 11px;
  height: 11px;
  border-radius: 50%;
  background-color: var(--color-petrol);
  transform: translateY(-50%);
  z-index: 1;
  transition: transform 120ms ease, background-color 120ms ease;
}

.expertises-hero__link {
  display: grid;
  grid-template-columns: auto 1fr;
  align-items: baseline;
  gap: var(--space-4);
  padding: var(--space-4) 0;
  text-decoration: none;
  color: var(--text-primary);
  transition: color 120ms ease, transform 120ms ease;
}

.expertises-hero__link:hover,
.expertises-hero__link:focus-visible {
  color: var(--color-petrol);
  transform: translateX(var(--space-2));
}

.expertises-hero__link[data-variant="primary"]:hover,
.expertises-hero__link[data-variant="primary"]:focus-visible {
  color: var(--color-devzair-blue);
}

/*
 * Pastille correspondante mise en avant au survol : légère mise à l'échelle
 * pour signaler la relation « item actif » sans changer la couleur globale
 * (on garde une seule teinte petrol partout).
 */
.expertises-hero__item:hover::before,
.expertises-hero__item:focus-within::before {
  transform: translateY(-50%) scale(1.35);
}

.expertises-hero__order {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.8125rem;
  letter-spacing: 0.08em;
  color: var(--text-accent);
  align-self: center;
}

.expertises-hero__label {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.5rem, 3.4vw, 2.25rem);
  line-height: 1.1;
  letter-spacing: -0.01em;
  color: inherit;
  text-wrap: balance;
}

@media (prefers-reduced-motion: reduce) {
  .expertises-hero__link,
  .expertises-hero__item::before {
    transition: none;
  }
  .expertises-hero__link:hover,
  .expertises-hero__link:focus-visible {
    transform: none;
  }
  .expertises-hero__item:hover::before,
  .expertises-hero__item:focus-within::before {
    transform: translateY(-50%);
  }
}

@media (min-width: 768px) {
  .expertises-hero {
    padding-block: var(--space-20) var(--space-16);
  }
}

@media (min-width: 1024px) {
  .expertises-hero__container {
    grid-template-columns: minmax(0, 5fr) minmax(0, 6fr);
    gap: var(--space-16);
  }
}
</style>
