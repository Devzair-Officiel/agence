<script setup lang="ts">
import { ref } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import { expertisePillars } from "~/config/expertise-pillars"

/**
 * Section « Nos expertises — cinq pôles ». Grille détaillée des cinq pôles
 * de `expertise-pillars.ts` (source unique — aucune duplication éditoriale).
 *
 * Rendu :
 *   - <768px  : accordéon vertical. Les 5 pôles sont visibles d'un coup en
 *     lignes compactes (numéro, nom, mots-clés) ; un tap déplie le détail
 *     (description, prestations, lien vers le pôle). Le premier pôle est
 *     ouvert par défaut, y compris dans le HTML serveur. Tout le contenu
 *     reste dans le DOM (indexable) : seul l'affichage est replié.
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
 *   - ≥768px : chaque carte est un lien vers `/expertises/{id}` via le
 *     pattern "stretched link" : seul le titre porte le `<NuxtLink>` (nom
 *     accessible court, unique par carte), un `::after` étend la zone
 *     cliquable à toute la carte ;
 *   - <768px : accordéon — un `<button>` `aria-expanded` / `aria-controls`
 *     recouvre l'en-tête de la carte (zone de tap pleine largeur). Il reste
 *     hors du H3 pour que le titre ne contienne qu'une fois le libellé dans
 *     le HTML brut. Le panneau replié est `visibility: hidden` (hors de
 *     l'ordre de tabulation) et porte le lien « Découvrir le pôle » ;
 *   - la liste des services est une vraie `<ul>` sémantique.
 *
 * Animation : JS ne fait que basculer `data-open` ; l'ouverture est une
 * transition CSS `grid-template-rows: 0fr → 1fr` (hauteur auto sans
 * mesure JS). Neutralisée sous `prefers-reduced-motion`.
 */

const pillars = [...expertisePillars].sort((a, b) => a.order - b.order)

// Ouvertures indépendantes : comparer deux pôles reste possible.
const openIds = ref<string[]>(pillars[0] ? [pillars[0].id] : [])

const isOpen = (id: string) => openIds.value.includes(id)

const toggle = (id: string) => {
  openIds.value = isOpen(id)
    ? openIds.value.filter((openId) => openId !== id)
    : [...openIds.value, id]
}
</script>

<template>
  <section
    id="expertises"
    class="home-pillars"
    aria-labelledby="home-pillars-title"
  >
    <BaseContainer width="wide" class="home-pillars__container">
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
        <NuxtLink to="/services" class="home-pillars__services-link">
          Voir les prestations détaillées
          <span aria-hidden="true">→</span>
        </NuxtLink>
      </header>

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
          :data-open="isOpen(pillar.id)"
        >
          <div class="home-pillars__card-header">
            <span class="home-pillars__card-index" aria-hidden="true">
              {{ String(pillar.order).padStart(2, "0") }}
            </span>
            <h3 class="home-pillars__card-title">
              <NuxtLink
                :to="`/expertises/${pillar.id}`"
                class="home-pillars__card-link"
              >
                {{ pillar.label }}
              </NuxtLink>
            </h3>
            <p class="home-pillars__card-tag">{{ pillar.description }}</p>
            <button
              type="button"
              class="home-pillars__card-toggle"
              :aria-label="`Détail du pôle ${pillar.label}`"
              :aria-expanded="isOpen(pillar.id)"
              :aria-controls="`home-pillar-panel-${pillar.id}`"
              @click="toggle(pillar.id)"
            >
              <span class="home-pillars__card-icon" aria-hidden="true" />
            </button>
          </div>
          <div
            :id="`home-pillar-panel-${pillar.id}`"
            class="home-pillars__card-panel"
          >
            <div class="home-pillars__card-panel-inner">
              <div class="home-pillars__card-panel-content">
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
                <NuxtLink
                  :to="`/expertises/${pillar.id}`"
                  class="home-pillars__card-more"
                >
                  Découvrir le pôle
                  <span class="sr-only">{{ pillar.label }}</span>
                  <span class="home-pillars__card-more-arrow" aria-hidden="true">→</span>
                </NuxtLink>
              </div>
            </div>
          </div>
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

.home-pillars__services-link {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  align-self: flex-start;
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 0.9375rem;
  color: var(--text-accent);
  text-decoration: none;
  border-bottom: 1px solid currentColor;
  padding-bottom: 2px;
  transition: color var(--duration-fast) var(--ease-out);
}

.home-pillars__services-link:hover {
  color: var(--text-primary);
}

.home-pillars__services-link:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
  border-radius: 2px;
}

.home-pillars__grid {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.home-pillars__card {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-6);
  background-color: var(--surface-primary);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-sm);
  transition:
    transform var(--duration-base) var(--ease-out),
    border-color var(--duration-base) var(--ease-out),
    box-shadow var(--duration-base) var(--ease-out);
}

/*
 * Les enveloppes de l'accordéon n'existent que pour le mobile : au-delà,
 * `display: contents` rend leurs enfants à la colonne flex de la carte,
 * dont la mise en page reste inchangée.
 */
.home-pillars__card-header,
.home-pillars__card-panel,
.home-pillars__card-panel-inner,
.home-pillars__card-panel-content {
  display: contents;
}

.home-pillars__card-toggle,
.home-pillars__card-more {
  display: none;
}

/*
 * Pattern "stretched link" : le titre porte le `<NuxtLink>` (nom accessible
 * court), un pseudo-élément étend la zone cliquable à toute la carte. Les
 * autres éléments restent sélectionnables au clic-glisser en dehors du
 * texte du titre grâce à `z-index: 1` sur ::after — les éléments texte
 * sous-jacents captent quand même le mouse-down d'une sélection.
 */
.home-pillars__card-link {
  color: inherit;
  text-decoration: none;
  outline: none;
}

.home-pillars__card-link::after {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: inherit;
  z-index: 1;
}

@media (hover: hover) and (min-width: 768px) {
  .home-pillars__card:hover {
    transform: translateY(-4px);
    border-color: var(--color-petrol);
    box-shadow: var(--shadow-md);
  }
}

.home-pillars__card:has(
    .home-pillars__card-link:focus-visible,
    .home-pillars__card-toggle:focus-visible
  ) {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
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

  .home-pillars__grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-6);
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

/* ----- Mobile : accordéon ----- */
@media (max-width: 767.98px) {
  .home-pillars__card {
    gap: 0;
    padding: 0;
  }

  .home-pillars__card[data-open="true"] {
    border-color: var(--color-petrol);
    box-shadow: var(--shadow-md);
  }

  .home-pillars__card[data-variant="primary"][data-open="true"] {
    border-left-color: var(--color-petrol);
  }

  .home-pillars__card-header {
    position: relative;
    display: grid;
    grid-template-columns: auto minmax(0, 1fr);
    column-gap: var(--space-3);
    row-gap: var(--space-1);
    align-items: baseline;
    padding: var(--space-4) calc(var(--space-5) + 2.25rem) var(--space-4)
      var(--space-5);
  }

  .home-pillars__card-tag {
    grid-column: 2;
  }

  /* Le panneau porte son propre lien : pas de lien étiré sur mobile. */
  .home-pillars__card-link::after {
    content: none;
  }

  /*
   * Le bouton recouvre tout l'en-tête (au-dessus du lien du titre) pour
   * une zone de tap pleine largeur ; l'icône se cale à droite.
   */
  .home-pillars__card-toggle {
    display: block;
    position: absolute;
    inset: 0;
    z-index: 1;
    width: 100%;
    padding: 0;
    border: 0;
    background: none;
    color: inherit;
    border-radius: var(--radius-md) var(--radius-md) 0 0;
    cursor: pointer;
    outline: none;
    -webkit-tap-highlight-color: transparent;
  }

  .home-pillars__card-toggle::after {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: inherit;
    background-color: currentColor;
    opacity: 0;
    transition: opacity var(--duration-fast) var(--ease-out);
  }

  .home-pillars__card-toggle:active::after {
    opacity: 0.04;
  }

  /* Plus → moins : la barre verticale pivote et se couche sur l'horizontale. */
  .home-pillars__card-icon {
    position: absolute;
    top: 50%;
    right: var(--space-4);
    width: 2rem;
    height: 2rem;
    margin-top: -1rem;
    border: 1px solid currentColor;
    border-radius: 50%;
    opacity: 0.72;
    transition:
      opacity var(--duration-base) var(--ease-out),
      transform var(--duration-slow) var(--ease-out);
  }

  .home-pillars__card-icon::before,
  .home-pillars__card-icon::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0.75rem;
    height: 1.5px;
    margin: -0.75px 0 0 -0.375rem;
    border-radius: 1px;
    background-color: currentColor;
    transition: transform var(--duration-slow) var(--ease-out);
  }

  .home-pillars__card-icon::after {
    transform: rotate(90deg);
  }

  .home-pillars__card[data-open="true"] .home-pillars__card-icon {
    opacity: 1;
    transform: rotate(180deg);
  }

  .home-pillars__card[data-open="true"] .home-pillars__card-icon::after {
    transform: rotate(0deg);
  }

  /*
   * Hauteur auto animée sans mesure JS : la piste passe de 0fr à 1fr.
   * Replié, le contenu est `visibility: hidden` (hors tabulation et
   * arbre d'accessibilité) ; la bascule de visibilité est retardée à la
   * fermeture pour laisser la transition se terminer.
   */
  .home-pillars__card-panel {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows var(--duration-slow) var(--ease-out);
  }

  .home-pillars__card[data-open="true"] .home-pillars__card-panel {
    grid-template-rows: 1fr;
  }

  .home-pillars__card-panel-inner {
    display: block;
    min-height: 0;
    overflow: hidden;
    visibility: hidden;
    opacity: 0;
    transform: translateY(-0.5rem);
    transition:
      opacity var(--duration-base) var(--ease-out),
      transform var(--duration-slow) var(--ease-out),
      visibility 0s linear var(--duration-slow);
  }

  .home-pillars__card[data-open="true"] .home-pillars__card-panel-inner {
    visibility: visible;
    opacity: 1;
    transform: none;
    transition:
      opacity var(--duration-slow) var(--ease-out) var(--duration-instant),
      transform var(--duration-slow) var(--ease-out),
      visibility 0s;
  }

  .home-pillars__card-panel-content {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
    padding: 0 var(--space-5) var(--space-5);
  }

  .home-pillars__services {
    margin-top: 0;
  }

  .home-pillars__card-more {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    align-self: flex-start;
    margin-top: var(--space-1);
    font-family: var(--font-family-body);
    font-weight: var(--font-weight-body-strong);
    font-size: 0.9375rem;
    color: var(--text-accent);
    text-decoration: none;
    border-bottom: 1px solid currentColor;
    padding-bottom: 2px;
  }

  .home-pillars__card-more:focus-visible {
    outline: var(--focus-ring-width) solid var(--focus-ring);
    outline-offset: -1px;
    border-radius: 2px;
  }

  .home-pillars__card-more-arrow {
    transition: transform var(--duration-fast) var(--ease-out);
  }

  .home-pillars__card-more:active .home-pillars__card-more-arrow {
    transform: translateX(4px);
  }

  .home-pillars__card[data-variant="accent"] .home-pillars__card-more {
    color: var(--color-cream);
  }
}

@media (prefers-reduced-motion: reduce) {
  .home-pillars__card,
  .home-pillars__card-panel,
  .home-pillars__card-panel-inner,
  .home-pillars__card[data-open="true"] .home-pillars__card-panel-inner,
  .home-pillars__card-icon,
  .home-pillars__card-icon::before,
  .home-pillars__card-icon::after,
  .home-pillars__card-toggle::after,
  .home-pillars__card-more-arrow {
    transition: none;
  }

  .home-pillars__card-panel-inner {
    transform: none;
  }
}
</style>
