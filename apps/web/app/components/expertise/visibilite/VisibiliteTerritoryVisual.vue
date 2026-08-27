<script setup lang="ts">
/**
 * Visuel décoratif du hero Visibilité — direction « Search Territory /
 * Signal Map ».
 *
 * Composition cartographique abstraite entièrement SVG/CSS. AUCUNE carte
 * géographique réelle, aucun Google Maps, aucun dashboard SEO, aucun
 * mockup SERP, aucun chiffre ni pourcentage. On rend uniquement des
 * primitives abstraites :
 *   - zones concentriques (rayonnement d'influence) ;
 *   - lignes topographiques (courbes de niveau abstraites) ;
 *   - trajectoires (chemins reliant des points d'intérêt) ;
 *   - nœuds numérotés (points-signaux) ;
 *   - repères de coordonnées mono NON géographiques ;
 *   - petites légendes techniques.
 *
 * Trois légendes reprises en Space Mono, calquées sur le brief §5 :
 *   SEARCH FIELD / 01
 *   LOCAL SIGNAL / 02
 *   CONTENT PATH / 03
 *
 * Accessibilité : marqué `aria-hidden="true"` — l'information portée est
 * décorative et redondante avec le hero textuel. Le SVG est
 * `focusable="false"` et `role="presentation"` pour ne pas polluer l'AT.
 */
</script>

<template>
  <div class="visibilite-visual" aria-hidden="true">
    <div class="visibilite-visual__frame">
      <svg
        class="visibilite-visual__map"
        viewBox="0 0 480 360"
        role="presentation"
        focusable="false"
        aria-hidden="true"
        preserveAspectRatio="xMidYMid meet"
      >
        <!-- Fond sable pâle : le « terrain » -->
        <rect x="0" y="0" width="480" height="360" fill="#efebe2" />

        <!-- Grille de repérage abstraite (non géographique) -->
        <g opacity="0.22">
          <line x1="0" y1="60" x2="480" y2="60" stroke="#0c5b57" stroke-width="0.5" stroke-dasharray="2 5" />
          <line x1="0" y1="120" x2="480" y2="120" stroke="#0c5b57" stroke-width="0.5" stroke-dasharray="2 5" />
          <line x1="0" y1="180" x2="480" y2="180" stroke="#0c5b57" stroke-width="0.5" stroke-dasharray="2 5" />
          <line x1="0" y1="240" x2="480" y2="240" stroke="#0c5b57" stroke-width="0.5" stroke-dasharray="2 5" />
          <line x1="0" y1="300" x2="480" y2="300" stroke="#0c5b57" stroke-width="0.5" stroke-dasharray="2 5" />
          <line x1="80" y1="0" x2="80" y2="360" stroke="#0c5b57" stroke-width="0.5" stroke-dasharray="2 5" />
          <line x1="160" y1="0" x2="160" y2="360" stroke="#0c5b57" stroke-width="0.5" stroke-dasharray="2 5" />
          <line x1="240" y1="0" x2="240" y2="360" stroke="#0c5b57" stroke-width="0.5" stroke-dasharray="2 5" />
          <line x1="320" y1="0" x2="320" y2="360" stroke="#0c5b57" stroke-width="0.5" stroke-dasharray="2 5" />
          <line x1="400" y1="0" x2="400" y2="360" stroke="#0c5b57" stroke-width="0.5" stroke-dasharray="2 5" />
        </g>

        <!-- Repères de coordonnées fictifs (Space Mono petrol) -->
        <g font-family="Space Mono, monospace" font-size="7" fill="#0c5b57" opacity="0.55">
          <text x="6" y="10">N 00°</text>
          <text x="6" y="70">N 15°</text>
          <text x="6" y="130">N 30°</text>
          <text x="6" y="250">N 45°</text>
          <text x="440" y="356">E 60°</text>
        </g>

        <!-- Zones concentriques : rayonnement d'un signal principal -->
        <g fill="none" stroke="#0c5b57">
          <circle cx="180" cy="170" r="120" opacity="0.10" stroke-width="1" />
          <circle cx="180" cy="170" r="90" opacity="0.18" stroke-width="1" />
          <circle cx="180" cy="170" r="60" opacity="0.28" stroke-width="1" />
          <circle cx="180" cy="170" r="34" opacity="0.4" stroke-width="1.2" />
        </g>

        <!-- Zone partiellement remplie (aire d'influence) -->
        <path
          d="M180 170 m-60 0 a60 60 0 1 1 120 0 a60 60 0 1 1 -120 0"
          fill="#0c5b57"
          opacity="0.08"
        />

        <!-- Lignes topographiques abstraites (courbes de niveau) -->
        <g fill="none" stroke="#16191c" opacity="0.35" stroke-width="0.8">
          <path d="M0 100 Q90 90 180 100 T360 110 T480 100" />
          <path d="M0 140 Q90 130 180 140 T360 150 T480 140" />
          <path d="M0 220 Q90 210 180 220 T360 230 T480 220" />
          <path d="M0 280 Q90 270 180 280 T360 290 T480 280" />
        </g>

        <!-- Trajectoires reliant les trois signaux -->
        <g fill="none" stroke="#0c5b57" stroke-width="1.4" stroke-dasharray="4 3" opacity="0.75">
          <path d="M180 170 C 260 140, 320 130, 360 100" />
          <path d="M180 170 C 240 220, 300 260, 400 280" />
          <path d="M360 100 C 400 160, 400 220, 400 280" opacity="0.5" />
        </g>

        <!-- Nœud principal : signal 01 (Search Field) -->
        <g>
          <circle cx="180" cy="170" r="9" fill="#0c5b57" />
          <circle cx="180" cy="170" r="14" fill="none" stroke="#0c5b57" stroke-width="1.2" opacity="0.7" />
          <text
            x="200" y="165"
            font-family="Space Mono, monospace"
            font-size="9"
            font-weight="700"
            fill="#0c5b57"
          >01</text>
          <text
            x="200" y="177"
            font-family="Space Mono, monospace"
            font-size="7"
            fill="#0c5b57"
            opacity="0.7"
          >SEARCH FIELD</text>
        </g>

        <!-- Nœud 02 : Local Signal -->
        <g>
          <rect x="352" y="92" width="16" height="16" fill="none" stroke="#0c5b57" stroke-width="1.4" />
          <circle cx="360" cy="100" r="3" fill="#0c5b57" />
          <text
            x="372" y="97"
            font-family="Space Mono, monospace"
            font-size="9"
            font-weight="700"
            fill="#0c5b57"
          >02</text>
          <text
            x="372" y="109"
            font-family="Space Mono, monospace"
            font-size="7"
            fill="#0c5b57"
            opacity="0.7"
          >LOCAL SIGNAL</text>
        </g>

        <!-- Nœud 03 : Content Path -->
        <g>
          <polygon
            points="400,272 408,286 392,286"
            fill="none"
            stroke="#0c5b57"
            stroke-width="1.4"
          />
          <circle cx="400" cy="282" r="2.5" fill="#0c5b57" />
          <text
            x="342" y="298"
            font-family="Space Mono, monospace"
            font-size="9"
            font-weight="700"
            fill="#0c5b57"
          >03</text>
          <text
            x="326" y="310"
            font-family="Space Mono, monospace"
            font-size="7"
            fill="#0c5b57"
            opacity="0.7"
          >CONTENT PATH</text>
        </g>

        <!-- Petits repères secondaires (signaux ambiants) -->
        <g fill="#16191c" opacity="0.5">
          <circle cx="70" cy="240" r="1.6" />
          <circle cx="120" cy="80" r="1.6" />
          <circle cx="260" cy="60" r="1.6" />
          <circle cx="440" cy="180" r="1.6" />
          <circle cx="80" cy="320" r="1.6" />
          <circle cx="260" cy="330" r="1.6" />
        </g>

        <!-- Petite légende technique en bas -->
        <g font-family="Space Mono, monospace" font-size="7" fill="#0c5b57" opacity="0.65">
          <text x="6" y="352">SIG · MAP</text>
        </g>
      </svg>

      <!-- Repères d'échelle sur le bord droit -->
      <p class="visibilite-visual__annot visibilite-visual__annot--top">
        Territoire · Signaux
      </p>
      <p class="visibilite-visual__annot visibilite-visual__annot--bottom">
        Pôle · 04 / 05
      </p>
    </div>
  </div>
</template>

<style scoped>
/*
 * Cadre : cream-elevated, coins droits, mince liseré petrol au bord haut
 * pour évoquer un plan technique. Uniquement décoratif.
 */
.visibilite-visual {
  width: 100%;
  max-width: 560px;
  margin-inline: auto;
}

.visibilite-visual__frame {
  position: relative;
  background-color: var(--color-cream-elevated);
  border: 1px solid var(--border-default);
  padding: var(--space-4);
  aspect-ratio: 4 / 3;
  overflow: hidden;
}

.visibilite-visual__frame::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(
    to right,
    var(--color-petrol) 0%,
    var(--color-petrol) 40%,
    transparent 40%,
    transparent 60%,
    var(--color-petrol) 60%,
    var(--color-petrol) 100%
  );
  opacity: 0.7;
}

.visibilite-visual__map {
  width: 100%;
  height: 100%;
  display: block;
}

.visibilite-visual__annot {
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

.visibilite-visual__annot--top {
  top: calc(-1 * var(--space-3));
  left: var(--space-4);
  background-color: var(--background-secondary);
  padding: 0 var(--space-2);
}

.visibilite-visual__annot--bottom {
  bottom: calc(-1 * var(--space-3));
  right: var(--space-4);
  background-color: var(--background-secondary);
  padding: 0 var(--space-2);
}
</style>
