<script setup lang="ts">
/**
 * Visuel « plan d'architecte » propre à la page `/expertises/concevoir`.
 *
 * Décor pur : SVG inline, aucune donnée métier, aucun asset externe. Le
 * composant sert à porter visuellement la direction « Digital Blueprint »
 * — une grille technique, des repères, des zones nommées par des labels
 * courts extraits du système visuel Devzair.
 *
 * Accessibilité :
 *   - `aria-hidden="true"` sur le SVG (aucun texte porteur de sens
 *     éditorial : les libellés visibles doublent des mots déjà présents
 *     dans la page — pas d'information exclusive au visuel) ;
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
    class="concevoir-blueprint"
    aria-hidden="true"
    role="presentation"
  >
    <svg
      class="concevoir-blueprint__svg"
      viewBox="0 0 640 520"
      preserveAspectRatio="xMidYMid meet"
      focusable="false"
      aria-hidden="true"
    >
      <defs>
        <pattern
          id="concevoir-blueprint-grid"
          width="32"
          height="32"
          patternUnits="userSpaceOnUse"
        >
          <path
            d="M 32 0 L 0 0 0 32"
            fill="none"
            stroke="rgba(12, 91, 87, 0.14)"
            stroke-width="1"
          />
        </pattern>
        <pattern
          id="concevoir-blueprint-major"
          width="128"
          height="128"
          patternUnits="userSpaceOnUse"
        >
          <path
            d="M 128 0 L 0 0 0 128"
            fill="none"
            stroke="rgba(12, 91, 87, 0.22)"
            stroke-width="1"
          />
        </pattern>
      </defs>

      <rect x="0" y="0" width="640" height="520" class="fill-canvas" />
      <rect x="0" y="0" width="640" height="520" fill="url(#concevoir-blueprint-grid)" />
      <rect x="0" y="0" width="640" height="520" fill="url(#concevoir-blueprint-major)" />

      <!-- Cadre principal, comme un cartouche de plan -->
      <rect
        x="32"
        y="32"
        width="576"
        height="456"
        fill="none"
        stroke="rgba(12, 91, 87, 0.5)"
        stroke-width="1.5"
      />
      <rect
        x="40"
        y="40"
        width="560"
        height="440"
        fill="none"
        stroke="rgba(12, 91, 87, 0.18)"
        stroke-width="1"
        stroke-dasharray="4 6"
      />

      <!-- Zone Structure -->
      <rect
        x="64"
        y="80"
        width="240"
        height="128"
        fill="rgba(46, 134, 217, 0.08)"
        stroke="rgba(46, 134, 217, 0.55)"
        stroke-width="1.5"
      />
      <text x="80" y="104" class="label-mono">01 · STRUCTURE</text>
      <line x1="80" y1="132" x2="288" y2="132" stroke="rgba(46, 134, 217, 0.35)" stroke-width="1" />
      <line x1="80" y1="152" x2="240" y2="152" stroke="rgba(46, 134, 217, 0.3)" stroke-width="1" />
      <line x1="80" y1="172" x2="272" y2="172" stroke="rgba(46, 134, 217, 0.3)" stroke-width="1" />

      <!-- Zone Interface (colonne droite haut) -->
      <rect
        x="336"
        y="80"
        width="240"
        height="200"
        fill="rgba(12, 91, 87, 0.06)"
        stroke="rgba(12, 91, 87, 0.55)"
        stroke-width="1.5"
      />
      <text x="352" y="104" class="label-mono">02 · INTERFACE</text>
      <rect
        x="352"
        y="120"
        width="80"
        height="80"
        fill="rgba(12, 91, 87, 0.14)"
        stroke="rgba(12, 91, 87, 0.4)"
        stroke-width="1"
      />
      <rect
        x="448"
        y="120"
        width="112"
        height="24"
        fill="rgba(46, 134, 217, 0.18)"
        stroke="rgba(46, 134, 217, 0.5)"
        stroke-width="1"
      />
      <rect x="448" y="156" width="112" height="12" fill="rgba(22, 25, 28, 0.12)" />
      <rect x="448" y="174" width="88" height="12" fill="rgba(22, 25, 28, 0.1)" />
      <rect x="448" y="192" width="72" height="8" fill="rgba(22, 25, 28, 0.08)" />
      <line
        x1="352"
        y1="224"
        x2="560"
        y2="224"
        stroke="rgba(12, 91, 87, 0.25)"
        stroke-width="1"
        stroke-dasharray="3 4"
      />
      <rect x="352" y="240" width="44" height="24" rx="12" class="fill-petrol" />
      <rect
        x="408"
        y="240"
        width="44"
        height="24"
        rx="12"
        fill="none"
        stroke="rgba(12, 91, 87, 0.6)"
        stroke-width="1.5"
      />

      <!-- Zone Système (bas) -->
      <rect
        x="64"
        y="240"
        width="240"
        height="200"
        fill="rgba(12, 91, 87, 0.05)"
        stroke="rgba(12, 91, 87, 0.55)"
        stroke-width="1.5"
      />
      <text x="80" y="264" class="label-mono">03 · SYSTÈME</text>
      <rect x="80" y="280" width="32" height="32" class="fill-petrol" />
      <rect x="116" y="280" width="32" height="32" class="fill-devzair-blue" />
      <rect x="152" y="280" width="32" height="32" class="fill-navy" />
      <rect
        x="188"
        y="280"
        width="32"
        height="32"
        class="fill-sand"
        stroke="rgba(12, 91, 87, 0.3)"
        stroke-width="1"
      />
      <rect
        x="224"
        y="280"
        width="32"
        height="32"
        class="fill-cream"
        stroke="rgba(12, 91, 87, 0.3)"
        stroke-width="1"
      />
      <text x="80" y="350" class="type-display">Aa</text>
      <text x="140" y="350" class="type-body">Aa</text>
      <text x="188" y="350" class="type-mono">01</text>
      <line x1="80" y1="376" x2="288" y2="376" stroke="rgba(12, 91, 87, 0.2)" stroke-width="1" />
      <text x="80" y="404" class="label-mono label-mono--soft">TOKENS · GRILLE · ÉTATS</text>

      <!-- Zone Parcours (bas droite) -->
      <rect
        x="336"
        y="304"
        width="240"
        height="136"
        fill="rgba(46, 134, 217, 0.06)"
        stroke="rgba(46, 134, 217, 0.5)"
        stroke-width="1.5"
      />
      <text x="352" y="328" class="label-mono">04 · PARCOURS</text>
      <circle cx="368" cy="360" r="10" class="fill-petrol" />
      <circle cx="368" cy="360" r="4" class="fill-cream" />
      <line x1="378" y1="360" x2="428" y2="360" stroke="rgba(12, 91, 87, 0.45)" stroke-width="1.5" />
      <circle cx="438" cy="360" r="10" fill="none" class="stroke-petrol" stroke-width="1.5" />
      <line
        x1="448"
        y1="360"
        x2="498"
        y2="360"
        stroke="rgba(12, 91, 87, 0.25)"
        stroke-width="1.5"
        stroke-dasharray="3 3"
      />
      <circle cx="508" cy="360" r="10" fill="none" stroke="rgba(12, 91, 87, 0.35)" stroke-width="1.5" />
      <rect
        x="352"
        y="392"
        width="208"
        height="32"
        fill="none"
        stroke="rgba(46, 134, 217, 0.4)"
        stroke-width="1"
        stroke-dasharray="2 4"
      />

      <!-- Cartouche coin bas droit (référence de plan) -->
      <rect x="464" y="448" width="144" height="32" class="fill-navy" />
      <text x="476" y="468" class="cartouche-label">CONCEVOIR · 01/01</text>

      <!-- Repères d'échelle sur les bords -->
      <g stroke="rgba(12, 91, 87, 0.5)" stroke-width="1">
        <line x1="32" y1="16" x2="32" y2="28" />
        <line x1="160" y1="16" x2="160" y2="28" />
        <line x1="320" y1="16" x2="320" y2="28" />
        <line x1="480" y1="16" x2="480" y2="28" />
        <line x1="608" y1="16" x2="608" y2="28" />

        <line x1="16" y1="32" x2="28" y2="32" />
        <line x1="16" y1="160" x2="28" y2="160" />
        <line x1="16" y1="260" x2="28" y2="260" />
        <line x1="16" y1="360" x2="28" y2="360" />
        <line x1="16" y1="488" x2="28" y2="488" />
      </g>
    </svg>
  </div>
</template>

<style scoped>
.concevoir-blueprint {
  position: relative;
  width: 100%;
  aspect-ratio: 640 / 520;
  border-radius: var(--radius-lg);
  overflow: hidden;
  border: 1px solid rgba(12, 91, 87, 0.24);
  box-shadow: var(--shadow-md);
  background-color: var(--color-cream-elevated);
}

.concevoir-blueprint__svg {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  display: block;
}

.concevoir-blueprint__svg .fill-canvas { fill: var(--color-cream-elevated); }
.concevoir-blueprint__svg .fill-petrol { fill: var(--color-petrol); }
.concevoir-blueprint__svg .fill-devzair-blue { fill: var(--color-devzair-blue); }
.concevoir-blueprint__svg .fill-navy { fill: var(--color-navy); }
.concevoir-blueprint__svg .fill-sand { fill: var(--color-sand); }
.concevoir-blueprint__svg .fill-cream { fill: var(--color-cream); }
.concevoir-blueprint__svg .stroke-petrol { stroke: var(--color-petrol); }

.concevoir-blueprint__svg .label-mono {
  font-family: var(--font-family-mono);
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  fill: var(--color-petrol);
}

.concevoir-blueprint__svg .label-mono--soft {
  fill: rgba(12, 91, 87, 0.65);
  font-size: 10px;
}

.concevoir-blueprint__svg .type-display {
  font-family: var(--font-family-heading);
  font-size: 28px;
  font-weight: 700;
  fill: var(--color-navy);
}

.concevoir-blueprint__svg .type-body {
  font-family: var(--font-family-body);
  font-size: 20px;
  font-weight: 400;
  fill: var(--color-navy);
}

.concevoir-blueprint__svg .type-mono {
  font-family: var(--font-family-mono);
  font-size: 16px;
  font-weight: 700;
  letter-spacing: 0.06em;
  fill: var(--color-petrol);
}

.concevoir-blueprint__svg .cartouche-label {
  font-family: var(--font-family-mono);
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.12em;
  fill: var(--color-cream);
}
</style>
