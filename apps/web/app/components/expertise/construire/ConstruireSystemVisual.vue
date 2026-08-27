<script setup lang="ts">
/**
 * Visuel « architecture technique » propre à la page `/expertises/construire`.
 *
 * Décor pur : SVG inline, aucun asset externe, aucune donnée métier. Le
 * composant sert à porter visuellement la direction « Product Assembly » —
 * un empilement de couches (interfaces, logique métier, API, données,
 * intégrations) reliées par des connecteurs, avec des repères techniques
 * discrets. Aucun cliché « écran d'IDE / dashboard / terminal ».
 *
 * Accessibilité :
 *   - `aria-hidden="true"` sur le SVG (les libellés visibles doublent des
 *     mots déjà présents dans la page — pas d'info exclusive au visuel) ;
 *   - `focusable="false"` pour éviter la capture clavier ;
 *   - `role="presentation"` pour redoubler l'intention purement décorative.
 *
 * Note technique : les couleurs sont posées via des classes CSS (fill),
 * pas via `fill="var(--color-*)"` — les valeurs CSS custom properties ne
 * sont pas interprétées dans les attributs de présentation SVG.
 */
</script>

<template>
  <div
    class="construire-system"
    aria-hidden="true"
    role="presentation"
  >
    <svg
      class="construire-system__svg"
      viewBox="0 0 640 560"
      preserveAspectRatio="xMidYMid meet"
      focusable="false"
      aria-hidden="true"
    >
      <defs>
        <pattern
          id="construire-system-grid"
          width="32"
          height="32"
          patternUnits="userSpaceOnUse"
        >
          <path
            d="M 32 0 L 0 0 0 32"
            fill="none"
            stroke="rgba(46, 134, 217, 0.10)"
            stroke-width="1"
          />
        </pattern>
      </defs>

      <rect x="0" y="0" width="640" height="560" class="fill-canvas" />
      <rect x="0" y="0" width="640" height="560" fill="url(#construire-system-grid)" />

      <!-- Repères d'échelle discrets -->
      <g stroke="rgba(46, 134, 217, 0.35)" stroke-width="1">
        <line x1="24" y1="16" x2="24" y2="28" />
        <line x1="200" y1="16" x2="200" y2="28" />
        <line x1="400" y1="16" x2="400" y2="28" />
        <line x1="600" y1="16" x2="600" y2="28" />
      </g>

      <!-- Couche 01 · INTERFACES -->
      <g class="layer">
        <rect
          x="40"
          y="56"
          width="560"
          height="72"
          class="fill-layer stroke-devzair-blue"
          stroke-width="1.5"
          rx="2"
        />
        <text x="56" y="80" class="label-mono label-mono--strong">01 · INTERFACES</text>
        <rect x="56" y="94" width="120" height="22" class="fill-module-strong" />
        <rect x="184" y="94" width="72" height="22" class="fill-module" />
        <rect x="264" y="94" width="96" height="22" class="fill-module" />
        <rect x="368" y="94" width="56" height="22" class="fill-module" />
        <rect x="432" y="94" width="152" height="22" class="fill-module" />
      </g>

      <!-- Connecteurs 01 → 02 -->
      <g stroke="rgba(46, 134, 217, 0.55)" stroke-width="1">
        <line x1="116" y1="128" x2="116" y2="152" />
        <line x1="220" y1="128" x2="220" y2="152" />
        <line x1="312" y1="128" x2="312" y2="152" />
        <line x1="508" y1="128" x2="508" y2="152" />
      </g>

      <!-- Couche 02 · LOGIQUE MÉTIER -->
      <g class="layer">
        <rect
          x="40"
          y="152"
          width="560"
          height="88"
          class="fill-layer-accent stroke-petrol"
          stroke-width="1.5"
          rx="2"
        />
        <text x="56" y="176" class="label-mono">02 · LOGIQUE MÉTIER</text>
        <rect x="56" y="192" width="152" height="34" class="fill-block" />
        <rect x="216" y="192" width="216" height="34" class="fill-block-alt" />
        <rect x="440" y="192" width="144" height="34" class="fill-block" />
        <line x1="132" y1="192" x2="132" y2="226" class="stroke-inner" stroke-width="1" />
        <line x1="324" y1="192" x2="324" y2="226" class="stroke-inner" stroke-width="1" />
        <line x1="512" y1="192" x2="512" y2="226" class="stroke-inner" stroke-width="1" />
      </g>

      <!-- Connecteurs 02 → 03 -->
      <g stroke="rgba(46, 134, 217, 0.4)" stroke-width="1" stroke-dasharray="2 3">
        <line x1="132" y1="240" x2="132" y2="272" />
        <line x1="324" y1="240" x2="324" y2="272" />
        <line x1="512" y1="240" x2="512" y2="272" />
      </g>

      <!-- Couche 03 · API / SERVICES -->
      <g class="layer">
        <rect
          x="40"
          y="272"
          width="560"
          height="64"
          class="fill-layer stroke-devzair-blue"
          stroke-width="1.5"
          rx="2"
        />
        <text x="56" y="296" class="label-mono">03 · API · SERVICES</text>
        <rect x="56" y="308" width="88" height="18" class="fill-endpoint" />
        <rect x="152" y="308" width="88" height="18" class="fill-endpoint" />
        <rect x="248" y="308" width="88" height="18" class="fill-endpoint" />
        <rect x="344" y="308" width="88" height="18" class="fill-endpoint" />
        <rect x="440" y="308" width="88" height="18" class="fill-endpoint" />
        <rect x="536" y="308" width="48" height="18" class="fill-endpoint-alt" />
      </g>

      <!-- Connecteurs 03 → 04 -->
      <g stroke="rgba(46, 134, 217, 0.55)" stroke-width="1">
        <line x1="100" y1="336" x2="100" y2="368" />
        <line x1="292" y1="336" x2="292" y2="368" />
        <line x1="484" y1="336" x2="484" y2="368" />
      </g>

      <!-- Couche 04 · DATA -->
      <g class="layer">
        <rect
          x="40"
          y="368"
          width="560"
          height="88"
          class="fill-layer-deep stroke-petrol"
          stroke-width="1.5"
          rx="2"
        />
        <text x="56" y="392" class="label-mono">04 · DATA</text>
        <!-- Cylindres stylisés (rectangles + arcs, pas de 3D gratuit) -->
        <g>
          <rect x="56" y="404" width="152" height="42" class="fill-data" />
          <path d="M 56 404 L 208 404" stroke="rgba(46, 134, 217, 0.5)" stroke-width="1" />
          <path d="M 56 414 L 208 414" stroke="rgba(46, 134, 217, 0.3)" stroke-width="1" />
          <path d="M 56 424 L 208 424" stroke="rgba(46, 134, 217, 0.2)" stroke-width="1" />
        </g>
        <g>
          <rect x="224" y="404" width="152" height="42" class="fill-data" />
          <path d="M 224 404 L 376 404" stroke="rgba(46, 134, 217, 0.5)" stroke-width="1" />
          <path d="M 224 414 L 376 414" stroke="rgba(46, 134, 217, 0.3)" stroke-width="1" />
          <path d="M 224 424 L 376 424" stroke="rgba(46, 134, 217, 0.2)" stroke-width="1" />
        </g>
        <g>
          <rect x="392" y="404" width="192" height="42" class="fill-data" />
          <path d="M 392 404 L 584 404" stroke="rgba(46, 134, 217, 0.5)" stroke-width="1" />
          <path d="M 392 414 L 584 414" stroke="rgba(46, 134, 217, 0.3)" stroke-width="1" />
          <path d="M 392 424 L 584 424" stroke="rgba(46, 134, 217, 0.2)" stroke-width="1" />
        </g>
      </g>

      <!-- Bande latérale 05 · INTÉGRATIONS -->
      <g class="layer">
        <rect
          x="40"
          y="472"
          width="560"
          height="64"
          class="fill-layer stroke-devzair-blue-soft"
          stroke-width="1"
          stroke-dasharray="4 4"
          rx="2"
        />
        <text x="56" y="494" class="label-mono label-mono--soft">05 · INTÉGRATIONS · OUTILS EXISTANTS</text>
        <g>
          <circle cx="72" cy="516" r="6" class="fill-marker" />
          <line x1="80" y1="516" x2="152" y2="516" class="stroke-connector" />
          <circle cx="160" cy="516" r="6" class="fill-marker" />
          <line x1="168" y1="516" x2="240" y2="516" class="stroke-connector" />
          <circle cx="248" cy="516" r="6" class="fill-marker" />
          <line x1="256" y1="516" x2="328" y2="516" class="stroke-connector" />
          <circle cx="336" cy="516" r="6" class="fill-marker" />
          <line x1="344" y1="516" x2="416" y2="516" class="stroke-connector" />
          <circle cx="424" cy="516" r="6" class="fill-marker" />
          <line x1="432" y1="516" x2="512" y2="516" class="stroke-connector" />
          <circle cx="520" cy="516" r="6" class="fill-marker" />
        </g>
      </g>

      <!-- Cartouche coin bas droit -->
      <rect x="464" y="544" width="144" height="14" class="fill-cartouche" />
      <text x="476" y="554" class="cartouche-label">CONSTRUIRE · 02/05</text>
    </svg>
  </div>
</template>

<style scoped>
.construire-system {
  position: relative;
  width: 100%;
  aspect-ratio: 640 / 560;
  border-radius: var(--radius-lg);
  overflow: hidden;
  border: 1px solid rgba(46, 134, 217, 0.28);
  box-shadow: var(--shadow-md);
  background-color: var(--color-navy-deep);
}

.construire-system__svg {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  display: block;
}

.construire-system__svg .fill-canvas { fill: var(--color-navy-deep); }

.construire-system__svg .fill-layer { fill: rgba(20, 30, 44, 0.75); }
.construire-system__svg .fill-layer-accent { fill: rgba(12, 91, 87, 0.18); }
.construire-system__svg .fill-layer-deep { fill: rgba(10, 18, 28, 0.6); }

.construire-system__svg .fill-module { fill: rgba(46, 134, 217, 0.16); }
.construire-system__svg .fill-module-strong { fill: rgba(46, 134, 217, 0.34); }

.construire-system__svg .fill-block { fill: rgba(46, 134, 217, 0.22); }
.construire-system__svg .fill-block-alt { fill: rgba(12, 91, 87, 0.32); }

.construire-system__svg .fill-endpoint { fill: rgba(244, 241, 234, 0.10); }
.construire-system__svg .fill-endpoint-alt { fill: rgba(46, 134, 217, 0.30); }

.construire-system__svg .fill-data { fill: rgba(46, 134, 217, 0.12); }

.construire-system__svg .fill-marker { fill: var(--color-devzair-blue); }
.construire-system__svg .fill-cartouche { fill: var(--color-navy-elevated); }

.construire-system__svg .stroke-devzair-blue { stroke: var(--color-devzair-blue); }
.construire-system__svg .stroke-devzair-blue-soft { stroke: rgba(46, 134, 217, 0.4); }
.construire-system__svg .stroke-petrol { stroke: rgba(12, 91, 87, 0.9); }
.construire-system__svg .stroke-inner { stroke: rgba(46, 134, 217, 0.35); }
.construire-system__svg .stroke-connector {
  stroke: rgba(46, 134, 217, 0.5);
  stroke-width: 1;
  stroke-dasharray: 2 3;
}

.construire-system__svg .label-mono {
  font-family: var(--font-family-mono);
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.10em;
  fill: rgba(196, 203, 211, 0.85);
}
.construire-system__svg .label-mono--strong { fill: var(--color-devzair-blue); }
.construire-system__svg .label-mono--soft {
  fill: rgba(196, 203, 211, 0.55);
  font-size: 9px;
}

.construire-system__svg .cartouche-label {
  font-family: var(--font-family-mono);
  font-size: 8px;
  font-weight: 700;
  letter-spacing: 0.14em;
  fill: var(--color-cream);
}
</style>
