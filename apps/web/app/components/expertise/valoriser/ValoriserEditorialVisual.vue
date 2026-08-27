<script setup lang="ts">
/**
 * Visuel décoratif du hero Valoriser — direction « Editorial Studio ».
 *
 * Composition planche-de-travail éditoriale entièrement SVG/CSS :
 *   - cadre principal évoquant un cliché en cours de cadrage (crop marks) ;
 *   - légende IMAGE / 01 en Space Mono ;
 *   - grille typographique fictive (lignes) évoquant une maquette texte ;
 *   - petits blocs numérotés (structure d'offre) ;
 *   - annotations mono style planche.
 *
 * Aucun logiciel réel, aucun mockup produit : uniquement des primitives
 * abstraites (rectangles, lignes, texte typographique). Rien n'évoque un
 * client, un shooting, une campagne réels.
 *
 * Accessibilité : marqué `aria-hidden="true"` — l'information portée est
 * décorative et redondante avec le hero textuel. Le SVG est
 * `focusable="false"` et `role="presentation"` pour ne pas polluer l'AT.
 */
</script>

<template>
  <div class="valoriser-visual" aria-hidden="true">
    <div class="valoriser-visual__frame">
      <!-- Cliché en cadrage : rectangle principal avec crop marks -->
      <div class="valoriser-visual__image">
        <div class="valoriser-visual__crop valoriser-visual__crop--tl" />
        <div class="valoriser-visual__crop valoriser-visual__crop--tr" />
        <div class="valoriser-visual__crop valoriser-visual__crop--bl" />
        <div class="valoriser-visual__crop valoriser-visual__crop--br" />

        <svg
          class="valoriser-visual__image-svg"
          viewBox="0 0 400 260"
          role="presentation"
          focusable="false"
          aria-hidden="true"
        >
          <!-- surface (sable clair sur cream) -->
          <rect x="0" y="0" width="400" height="260" fill="#e6e0d3" />
          <!-- règles de tiers (photo) -->
          <line x1="133" y1="0" x2="133" y2="260" stroke="#0c5b57" stroke-width="0.6" stroke-dasharray="3 4" opacity="0.45" />
          <line x1="266" y1="0" x2="266" y2="260" stroke="#0c5b57" stroke-width="0.6" stroke-dasharray="3 4" opacity="0.45" />
          <line x1="0" y1="87" x2="400" y2="87" stroke="#0c5b57" stroke-width="0.6" stroke-dasharray="3 4" opacity="0.45" />
          <line x1="0" y1="173" x2="400" y2="173" stroke="#0c5b57" stroke-width="0.6" stroke-dasharray="3 4" opacity="0.45" />
          <!-- horizon / repères de composition -->
          <path d="M0 200 Q100 180 200 190 T400 175" stroke="#16191c" stroke-width="1.2" fill="none" opacity="0.4" />
          <!-- petit rectangle indiquant un sujet -->
          <rect x="240" y="100" width="80" height="80" fill="none" stroke="#0c5b57" stroke-width="1.2" opacity="0.7" />
          <!-- point focal -->
          <circle cx="280" cy="140" r="3" fill="#0c5b57" />
        </svg>

        <p class="valoriser-visual__caption">IMAGE / 01</p>
      </div>

      <!-- Bloc de composition typographique (message) -->
      <div class="valoriser-visual__message">
        <p class="valoriser-visual__caption">MESSAGE / 02</p>
        <div class="valoriser-visual__typography">
          <span class="valoriser-visual__line valoriser-visual__line--h" />
          <span class="valoriser-visual__line valoriser-visual__line--h" />
          <span class="valoriser-visual__line valoriser-visual__line--h valoriser-visual__line--short" />
        </div>
      </div>

      <!-- Bloc structuration offre -->
      <div class="valoriser-visual__structure">
        <p class="valoriser-visual__caption">STRUCTURE / 03</p>
        <ul class="valoriser-visual__index">
          <li><span class="valoriser-visual__index-num">01</span><span class="valoriser-visual__index-label">Montrer</span></li>
          <li><span class="valoriser-visual__index-num">02</span><span class="valoriser-visual__index-label">Expliquer</span></li>
          <li><span class="valoriser-visual__index-num">03</span><span class="valoriser-visual__index-label">Organiser</span></li>
        </ul>
      </div>

      <!-- Annotations bord de planche -->
      <p class="valoriser-visual__annot valoriser-visual__annot--top">Planche · 03 / 05</p>
      <p class="valoriser-visual__annot valoriser-visual__annot--bottom">Studio éditorial</p>
    </div>
  </div>
</template>

<style scoped>
/*
 * Planche de travail éditoriale : cream-elevated, coins droits, bordure fine.
 * Composition CSS grid dédiée (asymétrique) : image dominante en haut à
 * gauche, bloc typographique en haut à droite, structure en bas.
 */
.valoriser-visual {
  width: 100%;
  max-width: 560px;
  margin-inline: auto;
}

.valoriser-visual__frame {
  position: relative;
  background-color: var(--color-cream-elevated);
  border: 1px solid var(--border-default);
  padding: var(--space-4);
  display: grid;
  grid-template-columns: 1fr;
  grid-template-areas:
    "image"
    "message"
    "structure";
  gap: var(--space-3);
}

/*
 * Cliché : rectangle avec 4 crop marks au coins. Le SVG interne porte le
 * fond sable + repères de tiers ; les crop marks sont posées en absolute
 * pour signifier « image en cours de cadrage ».
 */
.valoriser-visual__image {
  grid-area: image;
  position: relative;
  aspect-ratio: 4 / 3;
  border: 1px solid var(--border-strong);
  padding: var(--space-2);
  background-color: var(--color-sand);
}

.valoriser-visual__image-svg {
  width: 100%;
  height: 100%;
  display: block;
}

.valoriser-visual__crop {
  position: absolute;
  width: 14px;
  height: 14px;
  border-color: var(--color-petrol);
  border-style: solid;
  border-width: 0;
}
.valoriser-visual__crop--tl {
  top: -3px; left: -3px;
  border-top-width: 2px; border-left-width: 2px;
}
.valoriser-visual__crop--tr {
  top: -3px; right: -3px;
  border-top-width: 2px; border-right-width: 2px;
}
.valoriser-visual__crop--bl {
  bottom: -3px; left: -3px;
  border-bottom-width: 2px; border-left-width: 2px;
}
.valoriser-visual__crop--br {
  bottom: -3px; right: -3px;
  border-bottom-width: 2px; border-right-width: 2px;
}

.valoriser-visual__caption {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.10em;
  color: var(--color-petrol);
  margin: 0 0 var(--space-2);
  text-transform: uppercase;
}

.valoriser-visual__image .valoriser-visual__caption {
  position: absolute;
  bottom: var(--space-2);
  right: var(--space-3);
  margin: 0;
  padding: 2px 6px;
  background-color: var(--color-cream-elevated);
  border: 1px solid var(--border-default);
}

.valoriser-visual__message {
  grid-area: message;
  border-top: 1px solid var(--border-default);
  padding-top: var(--space-3);
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.valoriser-visual__typography {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding-top: var(--space-1);
}

.valoriser-visual__line {
  display: block;
  height: 6px;
  background-color: var(--color-ink);
  opacity: 0.85;
  border-radius: 2px;
  width: 100%;
}
.valoriser-visual__line--h {
  height: 8px;
}
.valoriser-visual__line--short {
  width: 55%;
}

.valoriser-visual__structure {
  grid-area: structure;
  border-top: 1px solid var(--border-default);
  padding-top: var(--space-3);
}

.valoriser-visual__index {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.valoriser-visual__index li {
  display: grid;
  grid-template-columns: 2.25rem 1fr;
  align-items: baseline;
  gap: var(--space-3);
  padding-bottom: var(--space-1);
  border-bottom: 1px dotted var(--border-default);
}

.valoriser-visual__index-num {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  color: var(--color-petrol);
}

.valoriser-visual__index-label {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 0.9375rem;
  color: var(--text-primary);
}

.valoriser-visual__annot {
  position: absolute;
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-ink-subtle);
  margin: 0;
  pointer-events: none;
}
.valoriser-visual__annot--top {
  top: calc(-1 * var(--space-3));
  left: var(--space-4);
  background-color: var(--background-secondary);
  padding: 0 var(--space-2);
}
.valoriser-visual__annot--bottom {
  bottom: calc(-1 * var(--space-3));
  right: var(--space-4);
  background-color: var(--background-secondary);
  padding: 0 var(--space-2);
}

/* Tablette / desktop : composition asymétrique 2 colonnes. */
@media (min-width: 768px) {
  .valoriser-visual__frame {
    grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr);
    grid-template-areas:
      "image message"
      "image structure";
    gap: var(--space-4);
  }

  .valoriser-visual__image {
    aspect-ratio: auto;
    min-height: 100%;
  }
}
</style>
