<script setup lang="ts">
import { useId } from "vue"
import {
  type ExpertisePillar,
  expertisePillars,
} from "~/config/expertise-pillars"

/**
 * Graphe SVG de l'écosystème Devzair — l'entreprise du client au centre,
 * les cinq pôles d'expertise reliés autour.
 *
 * Accessibilité :
 *   - `role="img"` + `<title>` + `<desc>` liés par `aria-labelledby`
 *     (une seule annonce, pas de lecture confuse des fragments internes).
 *   - Ids générés via `useId()` : sûrs si le composant est rendu plusieurs
 *     fois dans une même page (SSR-safe, pas de collision).
 *   - Groupes purement graphiques marqués `aria-hidden="true"`.
 *   - Aucun `tabindex` : le graphe est informatif, pas interactif ; il ne
 *     doit pas entrer dans le parcours clavier.
 *
 * Animation signature :
 *   - CSS pure (stroke-dashoffset + opacity + transform).
 *   - Séquencée par des `animation-delay` explicites, pas de JS.
 *   - S'arrête après ~1,4 s (aucune boucle, aucun `infinite`).
 *   - `prefers-reduced-motion` local remet l'état final (défense en
 *     profondeur au-dessus de la règle globale de animations.css).
 *
 * Positions graphiques :
 *   - Les positions (x, y) des pôles sont propres au rendu SVG, pas des
 *     données métier — elles vivent donc localement au composant. Les
 *     libellés, descriptions et ordre viennent d'expertisePillars.
 */

interface GraphPillarPosition {
  readonly pillarId: string
  readonly cx: number
  readonly cy: number
  readonly labelAnchor: "start" | "middle" | "end"
  readonly labelDx: number
  readonly labelDy: number
}

// Cercle de 5 points autour du centre (240,240), rayon 165.
// Angles choisis pour placer le premier pôle en haut, puis rotation
// horaire de 72°. Coordonnées calculées à la main pour rester lisibles
// (aucun calcul runtime).
const centerX = 240
const centerY = 240
const positions: readonly GraphPillarPosition[] = [
  // Concevoir — haut
  { pillarId: "concevoir", cx: 240, cy: 75, labelAnchor: "middle", labelDx: 0, labelDy: -44 },
  // Construire — droite haut
  { pillarId: "construire", cx: 397, cy: 189, labelAnchor: "start", labelDx: 40, labelDy: 6 },
  // Valoriser — droite bas
  { pillarId: "valoriser", cx: 337, cy: 372, labelAnchor: "start", labelDx: 40, labelDy: 6 },
  // Visibilité — gauche bas
  { pillarId: "visibilite", cx: 143, cy: 372, labelAnchor: "end", labelDx: -40, labelDy: 6 },
  // Faire évoluer — gauche haut
  { pillarId: "faire-evoluer", cx: 83, cy: 189, labelAnchor: "end", labelDx: -40, labelDy: 6 },
]

interface GraphNode {
  readonly pillar: ExpertisePillar
  readonly position: GraphPillarPosition
}

const nodes: readonly GraphNode[] = positions.map((position) => {
  const pillar = expertisePillars.find((p) => p.id === position.pillarId)
  if (!pillar) {
    throw new Error(`Pillar introuvable : ${position.pillarId}`)
  }
  return { pillar, position }
})

const titleId = useId()
const descriptionId = useId()
</script>

<template>
  <svg
    class="home-ecosystem-graph"
    viewBox="0 0 480 480"
    role="img"
    :aria-labelledby="`${titleId} ${descriptionId}`"
    xmlns="http://www.w3.org/2000/svg"
  >
    <title :id="titleId">Les cinq pôles de l’écosystème digital Devzair</title>
    <desc :id="descriptionId">
      L’entreprise du client est placée au centre et reliée aux cinq pôles
      d’expertise Devzair&nbsp;: Concevoir, Construire, Valoriser, Développer
      la visibilité et Faire évoluer.
    </desc>

    <!-- Arcs discrets d'arrière-plan, purement décoratifs. -->
    <g aria-hidden="true" class="home-ecosystem-graph__background">
      <circle
        :cx="centerX"
        :cy="centerY"
        r="205"
        fill="none"
        stroke="var(--color-devzair-blue)"
        stroke-width="1"
        stroke-dasharray="2 6"
        opacity="0.35"
      />
      <circle
        :cx="centerX"
        :cy="centerY"
        r="165"
        fill="none"
        stroke="var(--color-devzair-blue)"
        stroke-width="1"
        stroke-dasharray="2 6"
        opacity="0.22"
      />
    </g>

    <!-- Tracés du centre vers chaque pôle. -->
    <g aria-hidden="true" class="home-ecosystem-graph__spokes">
      <line
        v-for="(node, index) in nodes"
        :key="`spoke-${node.pillar.id}`"
        class="home-ecosystem-graph__spoke"
        :style="{ animationDelay: `${index * 80}ms` }"
        :x1="centerX"
        :y1="centerY"
        :x2="node.position.cx"
        :y2="node.position.cy"
        stroke="var(--color-devzair-blue)"
        stroke-width="1.5"
        stroke-linecap="round"
        opacity="0.75"
      />
    </g>

    <!-- Centre : « votre entreprise ». -->
    <g aria-hidden="true" class="home-ecosystem-graph__center">
      <circle :cx="centerX" :cy="centerY" r="56" fill="var(--color-petrol)" />
      <circle
        :cx="centerX"
        :cy="centerY"
        r="56"
        fill="none"
        stroke="var(--color-cream)"
        stroke-width="1"
        opacity="0.15"
      />
      <text
        :x="centerX"
        :y="centerY"
        text-anchor="middle"
        dominant-baseline="middle"
        fill="var(--color-cream)"
        font-family="var(--font-family-heading)"
        font-weight="600"
        font-size="15"
      >
        <tspan :x="centerX" dy="-0.55em">Votre</tspan>
        <tspan :x="centerX" dy="1.15em">entreprise</tspan>
      </text>
    </g>

    <!-- Pôles. -->
    <g aria-hidden="true" class="home-ecosystem-graph__pillars">
      <g
        v-for="(node, index) in nodes"
        :key="node.pillar.id"
        class="home-ecosystem-graph__pillar"
        :data-variant="node.pillar.variant"
        :style="{ animationDelay: `${400 + index * 90}ms` }"
      >
        <circle
          :cx="node.position.cx"
          :cy="node.position.cy"
          r="30"
          :fill="node.pillar.variant === 'primary'
            ? 'var(--color-petrol)'
            : 'var(--color-navy-elevated)'"
        />
        <circle
          :cx="node.position.cx"
          :cy="node.position.cy"
          r="30"
          fill="none"
          stroke="var(--color-cream)"
          stroke-width="1"
          opacity="0.16"
        />
        <text
          :x="node.position.cx + node.position.labelDx"
          :y="node.position.cy + node.position.labelDy"
          :text-anchor="node.position.labelAnchor"
          fill="var(--color-cream)"
          font-family="var(--font-family-heading)"
          font-weight="600"
          font-size="15"
        >{{ node.pillar.shortLabel }}</text>
        <text
          :x="node.position.cx + node.position.labelDx"
          :y="node.position.cy + node.position.labelDy + 16"
          :text-anchor="node.position.labelAnchor"
          fill="var(--color-cream-muted)"
          font-family="var(--font-family-mono)"
          font-weight="700"
          font-size="9"
          letter-spacing="0.06em"
        >{{ node.pillar.description }}</text>
        <text
          :x="node.position.cx"
          :y="node.position.cy + 4"
          text-anchor="middle"
          fill="var(--color-cream)"
          font-family="var(--font-family-mono)"
          font-weight="700"
          font-size="12"
          opacity="0.85"
        >{{ node.pillar.order }}</text>
      </g>
    </g>
  </svg>
</template>

<style scoped>
.home-ecosystem-graph {
  display: block;
  width: 100%;
  height: auto;
  max-width: 100%;
  aspect-ratio: 1 / 1;
}

/* Animation signature — tracés qui se dessinent du centre vers l'extérieur. */
.home-ecosystem-graph__spoke {
  stroke-dasharray: 200;
  stroke-dashoffset: 200;
  animation: home-ecosystem-spoke-draw 700ms var(--ease-out) forwards;
}

@keyframes home-ecosystem-spoke-draw {
  to {
    stroke-dashoffset: 0;
  }
}

/* Pôles — apparition légère après les tracés. */
.home-ecosystem-graph__pillar {
  opacity: 0;
  transform: scale(0.9);
  transform-origin: center;
  transform-box: fill-box;
  animation: home-ecosystem-pillar-in 450ms var(--ease-out) forwards;
}

@keyframes home-ecosystem-pillar-in {
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/*
 * Mouvement réduit — défense en profondeur.
 * La règle globale de animations.css annule déjà les animations, mais nos
 * états initiaux (dashoffset:200, opacity:0) resteraient visibles. On force
 * donc explicitement l'état final ici.
 */
@media (prefers-reduced-motion: reduce) {
  .home-ecosystem-graph__spoke {
    stroke-dashoffset: 0 !important;
    animation: none !important;
  }
  .home-ecosystem-graph__pillar {
    opacity: 1 !important;
    transform: none !important;
    animation: none !important;
  }
}

/*
 * Variante mobile compacte — sur très petits écrans, on gagne un peu de
 * lisibilité en zoomant sur le centre du viewBox via `preserveAspectRatio`.
 * Aucune donnée n'est masquée : le SVG reste identique, seules les marges
 * visuelles se resserrent.
 */
@media (max-width: 359px) {
  .home-ecosystem-graph {
    aspect-ratio: 4 / 5;
  }
}
</style>
