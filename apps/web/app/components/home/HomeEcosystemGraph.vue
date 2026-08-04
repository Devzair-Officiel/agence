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
 *   - Le SVG contient cinq liens `<a>` focusables. On NE lui applique donc
 *     PAS `role="img"` ni `aria-label` global : Axe (règle `nested-interactive`,
 *     WCAG 4.1.2) interdit qu'un élément avec un rôle "widget" (dont img avec
 *     nom accessible) contienne des interactifs. Voir DEC-073.
 *   - Le nom accessible du bloc est porté par un `<nav aria-label>` qui
 *     enveloppe le SVG (landmark de navigation contextualisé).
 *   - Chaque `<a>` porte son propre `aria-label` contextualisé pour rester
 *     utilisable en mode "liste des liens" des lecteurs d'écran.
 *   - Ids générés via `useId()` : sûrs si le composant est rendu plusieurs
 *     fois dans une même page (SSR-safe, pas de collision).
 *   - Groupes purement décoratifs marqués `aria-hidden="true"`. Le groupe
 *     des pôles ne l'est pas : chaque pôle est un lien vers sa page dédiée,
 *     accessible au clavier (focusable natif de `<a href>`).
 *
 * Animation signature :
 *   - CSS pure : intro (dashoffset + apparition des pôles) puis boucles
 *     continues et lentes (halo qui respire, anneau qui tourne, satellite
 *     orbitant). Aucune animation JS, aucune couche de perf non maîtrisée.
 *   - Séquencée par des `animation-delay` explicites côté intro.
 *   - Micro-interaction au survol/focus des liens pôles : révélation de
 *     l'anneau bleu extérieur, colorisation du disque et du libellé —
 *     transitions CSS courtes, sans JS.
 *   - `prefers-reduced-motion` local remet l'état final et coupe les
 *     transitions (défense en profondeur au-dessus de animations.css).
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

// Cercle de 5 points autour du centre (280,260), rayon 165.
// Angles choisis pour placer le premier pôle en haut, puis rotation
// horaire de 72°. Coordonnées calculées à la main pour rester lisibles
// (aucun calcul runtime).
const centerX = 280
const centerY = 260
// Placements calibrés pour :
//   1. les labels ne touchent jamais le cercle du pôle
//      (labelDx > rayon principal 24 + rayon anneau externe 33 = 33) ;
//   2. le bloc label + description reste centré verticalement sur le pôle
//      (labelDy = -6 pour compenser la hauteur de 2 lignes de description).
// Cas particulier Concevoir (haut) : le bloc est empilé au-dessus du cercle,
// on remonte donc suffisamment pour éviter tout chevauchement avec le disque.
const positions: readonly GraphPillarPosition[] = [
  // Concevoir — haut
  { pillarId: "concevoir", cx: 280, cy: 95, labelAnchor: "middle", labelDx: 0, labelDy: -68 },
  // Construire — droite haut
  { pillarId: "construire", cx: 437, cy: 209, labelAnchor: "start", labelDx: 40, labelDy: -6 },
  // Valoriser — droite bas
  { pillarId: "valoriser", cx: 377, cy: 392, labelAnchor: "start", labelDx: 40, labelDy: -6 },
  // Visibilité — gauche bas
  { pillarId: "visibilite", cx: 183, cy: 392, labelAnchor: "end", labelDx: -40, labelDy: -6 },
  // Faire évoluer — gauche haut
  { pillarId: "faire-evoluer", cx: 123, cy: 209, labelAnchor: "end", labelDx: -40, labelDy: -6 },
]

interface GraphNode {
  readonly pillar: ExpertisePillar
  readonly position: GraphPillarPosition
  readonly descriptionLines: readonly string[]
}

function wrapDescription(description: string): readonly string[] {
  const segments = description.split(" · ")

  return segments.reduce<string[]>((lines, segment) => {
    const previousLine = lines.at(-1)
    const nextLine = previousLine ? `${previousLine} · ${segment}` : segment

    if (!previousLine || nextLine.length > 18) {
      lines.push(segment)
    } else {
      lines[lines.length - 1] = nextLine
    }

    return lines
  }, [])
}

const nodes: readonly GraphNode[] = positions.map((position) => {
  const pillar = expertisePillars.find((p) => p.id === position.pillarId)
  if (!pillar) {
    throw new Error(`Pillar introuvable : ${position.pillarId}`)
  }
  return {
    pillar,
    position,
    descriptionLines: wrapDescription(pillar.description),
  }
})

const centerHaloId = useId()
const pillarHaloId = useId()
const centerGlowId = useId()
const centerGradientId = useId()

/**
 * Navigation vers la page détaillée du pôle survolé, en respectant les
 * modificateurs standards (ctrl/cmd/shift/alt/clic-milieu) pour préserver
 * l'ouverture dans un nouvel onglet.
 */
function onPillarClick(event: MouseEvent, pillarId: string) {
  if (
    event.metaKey ||
    event.ctrlKey ||
    event.shiftKey ||
    event.altKey ||
    event.button !== 0
  ) {
    return
  }
  event.preventDefault()
  void navigateTo(`/expertises/${pillarId}`)
}
</script>

<template>
  <nav
    class="home-ecosystem-navigation"
    aria-label="Explorer les expertises Devzair"
  >
    <svg
      class="home-ecosystem-graph"
      viewBox="-56 0 664 520"
      xmlns="http://www.w3.org/2000/svg"
    >
    <defs>
      <radialGradient :id="centerHaloId" cx="50%" cy="50%" r="50%">
        <stop offset="0%" stop-color="var(--color-petrol)" stop-opacity="0.55" />
        <stop offset="55%" stop-color="var(--color-petrol)" stop-opacity="0.16" />
        <stop offset="100%" stop-color="var(--color-petrol)" stop-opacity="0" />
      </radialGradient>
      <radialGradient :id="pillarHaloId" cx="50%" cy="50%" r="50%">
        <stop offset="0%" stop-color="var(--color-devzair-blue)" stop-opacity="0.35" />
        <stop offset="100%" stop-color="var(--color-devzair-blue)" stop-opacity="0" />
      </radialGradient>
      <!-- Dégradé subtil du noeud central : haut plus clair, bas plus profond. -->
      <radialGradient :id="centerGradientId" cx="50%" cy="35%" r="65%">
        <stop offset="0%" stop-color="#0f6b66" />
        <stop offset="65%" stop-color="var(--color-petrol)" />
        <stop offset="100%" stop-color="#08403d" />
      </radialGradient>
      <!-- Flou gaussien réutilisé pour la lueur diffuse. -->
      <filter :id="centerGlowId" x="-50%" y="-50%" width="200%" height="200%">
        <feGaussianBlur stdDeviation="6" />
      </filter>
    </defs>

    <!-- Arcs discrets d'arrière-plan, purement décoratifs. Rotation lente continue. -->
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

    <!-- Satellite orbitant sur le cercle extérieur (indépendant du groupe
         d'arrière-plan pour éviter les transformations composées). -->
    <g aria-hidden="true" class="home-ecosystem-graph__satellite">
      <circle
        :cx="centerX"
        :cy="centerY - 205"
        r="7"
        fill="var(--color-devzair-blue)"
        opacity="0.25"
      />
      <circle
        :cx="centerX"
        :cy="centerY - 205"
        r="3.5"
        fill="var(--color-devzair-blue)"
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
      <!-- Halo diffus (radial gradient) qui respire. -->
      <circle
        class="home-ecosystem-graph__center-halo"
        :cx="centerX"
        :cy="centerY"
        r="115"
        :fill="`url(#${centerHaloId})`"
      />
      <!-- Anneau externe fin, décoratif. -->
      <circle
        class="home-ecosystem-graph__center-ring"
        :cx="centerX"
        :cy="centerY"
        r="72"
        fill="none"
        stroke="var(--color-devzair-blue)"
        stroke-width="1"
        stroke-dasharray="1 5"
        opacity="0.5"
      />
      <!-- Lueur floue derrière le disque (blur filter). -->
      <circle
        :cx="centerX"
        :cy="centerY"
        r="58"
        fill="var(--color-petrol)"
        opacity="0.65"
        :filter="`url(#${centerGlowId})`"
      />
      <!-- Disque principal avec dégradé radial. -->
      <circle
        :cx="centerX"
        :cy="centerY"
        r="58"
        :fill="`url(#${centerGradientId})`"
      />
      <!-- Anneau interne fin (highlight sommet). -->
      <circle
        :cx="centerX"
        :cy="centerY"
        r="58"
        fill="none"
        stroke="var(--color-cream)"
        stroke-width="1"
        opacity="0.22"
      />
      <circle
        :cx="centerX"
        :cy="centerY"
        r="49"
        fill="none"
        stroke="var(--color-cream)"
        stroke-width="0.75"
        opacity="0.12"
      />
      <!-- Étiquette centrale. -->
      <text
        :x="centerX"
        :y="centerY - 4"
        text-anchor="middle"
        dominant-baseline="middle"
        fill="var(--color-cream)"
        font-family="var(--font-family-heading)"
        font-weight="600"
        font-size="15"
        letter-spacing="0"
      >
        <tspan :x="centerX" dy="-0.55em">Votre</tspan>
        <tspan :x="centerX" dy="1.2em">entreprise</tspan>
      </text>
      <!-- Micro-accent : petit point sous le libellé. -->
      <circle
        :cx="centerX"
        :cy="centerY + 26"
        r="1.8"
        fill="var(--color-devzair-blue)"
        opacity="0.9"
      />
    </g>

    <!-- Pôles — chaque pôle est un lien vers sa page dédiée. -->
    <g class="home-ecosystem-graph__pillars">
      <a
        v-for="(node, index) in nodes"
        :key="node.pillar.id"
        class="home-ecosystem-graph__pillar-link"
        :href="`/expertises/${node.pillar.id}`"
        :aria-label="`Découvrir l'expertise ${node.pillar.label}`"
        @click="onPillarClick($event, node.pillar.id)"
      >
        <g
          class="home-ecosystem-graph__pillar"
          :data-variant="node.pillar.variant"
          :style="{ animationDelay: `${400 + index * 90}ms` }"
        >
          <!-- Halo bleu diffus derrière chaque pôle. -->
          <circle
            class="home-ecosystem-graph__pillar-halo"
            :cx="node.position.cx"
            :cy="node.position.cy"
            r="48"
            :fill="`url(#${pillarHaloId})`"
          />
          <!-- Anneau externe fin, en dashed — décoration très discrète. -->
          <circle
            :cx="node.position.cx"
            :cy="node.position.cy"
            r="33"
            fill="none"
            stroke="var(--color-devzair-blue)"
            stroke-width="1"
            stroke-dasharray="1 4"
            opacity="0.22"
          />
          <!-- Anneau d'interaction — invisible au repos, se révèle au
               survol / focus pour indiquer la cliquabilité. -->
          <circle
            class="home-ecosystem-graph__pillar-outline"
            :cx="node.position.cx"
            :cy="node.position.cy"
            r="38"
            fill="none"
            stroke="var(--color-devzair-blue)"
            stroke-width="1.5"
            opacity="0"
          />
          <!-- Disque principal (rayon réduit — laisse respirer l'anneau externe). -->
          <circle
            class="home-ecosystem-graph__pillar-disc"
            :cx="node.position.cx"
            :cy="node.position.cy"
            r="24"
            fill="var(--color-navy-elevated)"
          />
          <!-- Highlight interne. -->
          <circle
            :cx="node.position.cx"
            :cy="node.position.cy"
            r="24"
            fill="none"
            stroke="var(--color-cream)"
            stroke-width="0.75"
            opacity="0.22"
          />
          <!-- Numéro d'ordre. -->
          <text
            class="home-ecosystem-graph__pillar-index"
            :x="node.position.cx"
            :y="node.position.cy + 4.5"
            text-anchor="middle"
            fill="var(--color-cream)"
            font-family="var(--font-family-mono)"
            font-weight="700"
            font-size="13"
            opacity="0.95"
          >{{ node.pillar.order }}</text>
          <text
            class="home-ecosystem-graph__pillar-label"
            :x="node.position.cx + node.position.labelDx"
            :y="node.position.cy + node.position.labelDy"
            :text-anchor="node.position.labelAnchor"
            fill="var(--color-cream)"
            font-family="var(--font-family-heading)"
            font-weight="600"
            font-size="17"
            letter-spacing="-0.005em"
          >{{ node.pillar.shortLabel }}</text>
          <text
            class="home-ecosystem-graph__pillar-description"
            :x="node.position.cx + node.position.labelDx"
            :y="node.position.cy + node.position.labelDy + 20"
            :text-anchor="node.position.labelAnchor"
            fill="var(--color-cream-muted)"
            font-family="var(--font-family-mono)"
            font-weight="700"
            font-size="9.5"
            letter-spacing="0.08em"
          >
            <tspan
              v-for="(line, lineIndex) in node.descriptionLines"
              :key="`${node.pillar.id}-description-${lineIndex}`"
              :x="node.position.cx + node.position.labelDx"
              :dy="lineIndex === 0 ? 0 : 13"
            >{{ line }}</tspan>
          </text>
        </g>
      </a>
    </g>
    </svg>
  </nav>
</template>

<style scoped>
.home-ecosystem-navigation {
  display: block;
  width: 100%;
  max-width: 100%;
}

.home-ecosystem-graph {
  display: block;
  width: 100%;
  height: auto;
  max-width: 100%;
}

/* Animation signature — tracés qui se dessinent du centre vers l'extérieur,
 * puis pulsent doucement en continu pour suggérer un flux entre le centre
 * et chaque pôle. */
.home-ecosystem-graph__spoke {
  stroke-dasharray: 200;
  stroke-dashoffset: 200;
  animation:
    home-ecosystem-spoke-draw 700ms var(--ease-out) forwards,
    home-ecosystem-spoke-pulse 3.2s var(--ease-in-out) 900ms infinite;
}

@keyframes home-ecosystem-spoke-draw {
  to {
    stroke-dashoffset: 0;
  }
}

@keyframes home-ecosystem-spoke-pulse {
  0%,
  100% {
    opacity: 0.55;
  }
  50% {
    opacity: 1;
  }
}

/* Halo autour du noeud central — respiration lente. */
.home-ecosystem-graph__center-halo {
  transform-origin: 280px 260px;
  animation: home-ecosystem-center-breathe 4.8s var(--ease-in-out) infinite;
}

@keyframes home-ecosystem-center-breathe {
  0%,
  100% {
    opacity: 0.75;
    transform: scale(0.94);
  }
  50% {
    opacity: 1;
    transform: scale(1.06);
  }
}

/* Anneau dashed autour du noeud central — rotation lente inverse au fond. */
.home-ecosystem-graph__center-ring {
  transform-origin: 280px 260px;
  animation: home-ecosystem-center-ring-rotate 45s linear infinite reverse;
}

@keyframes home-ecosystem-center-ring-rotate {
  to {
    transform: rotate(360deg);
  }
}

/* Halo derrière chaque pôle — respiration décalée d'un pôle à l'autre. */
.home-ecosystem-graph__pillar-halo {
  transform-origin: center;
  transform-box: fill-box;
  animation: home-ecosystem-pillar-halo 4.4s var(--ease-in-out) infinite;
}

.home-ecosystem-graph__pillar:nth-child(1) .home-ecosystem-graph__pillar-halo {
  animation-delay: 0s;
}
.home-ecosystem-graph__pillar:nth-child(2) .home-ecosystem-graph__pillar-halo {
  animation-delay: 0.6s;
}
.home-ecosystem-graph__pillar:nth-child(3) .home-ecosystem-graph__pillar-halo {
  animation-delay: 1.2s;
}
.home-ecosystem-graph__pillar:nth-child(4) .home-ecosystem-graph__pillar-halo {
  animation-delay: 1.8s;
}
.home-ecosystem-graph__pillar:nth-child(5) .home-ecosystem-graph__pillar-halo {
  animation-delay: 2.4s;
}

@keyframes home-ecosystem-pillar-halo {
  0%,
  100% {
    opacity: 0.35;
    transform: scale(0.85);
  }
  50% {
    opacity: 1;
    transform: scale(1.1);
  }
}

/* Cercles dashés d'arrière-plan — rotation lente. */
.home-ecosystem-graph__background {
  transform-origin: 280px 260px;
  animation: home-ecosystem-background-rotate 60s linear infinite;
}

@keyframes home-ecosystem-background-rotate {
  to {
    transform: rotate(360deg);
  }
}

/* Satellite orbitant sur le cercle extérieur (sens horaire, plus rapide
 * que la rotation d'arrière-plan pour offrir un contrepoint visuel). */
.home-ecosystem-graph__satellite {
  transform-origin: 280px 260px;
  animation: home-ecosystem-satellite-orbit 24s linear infinite;
}

@keyframes home-ecosystem-satellite-orbit {
  to {
    transform: rotate(360deg);
  }
}

/* Pôles — apparition légère après les tracés. */
.home-ecosystem-graph__pillar {
  opacity: 0;
  transform: scale(0.9);
  transform-origin: center;
  transform-box: fill-box;
  animation: home-ecosystem-pillar-in 450ms var(--ease-out) forwards;
  /*
   * Toute la boîte englobante du pôle (cercle + libellé + description +
   * espace entre les deux) devient cliquable. Par défaut, l'ancre SVG ne
   * capte le pointeur que sur les formes réellement peintes, ce qui laisse
   * une "zone morte" entre le libellé et la description.
   * Supporté sur navigateurs modernes (Chrome/Edge 118+, Firefox 116+,
   * Safari 15.4+) — cible du projet.
   */
  pointer-events: bounding-box;
}

@keyframes home-ecosystem-pillar-in {
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Lien pôle → page dédiée. Le focus visible est porté par l'anneau bleu
 * dédié (`.home-ecosystem-graph__pillar-outline`) ; on désactive donc le
 * focus ring natif du navigateur qui rendrait mal sur un élément SVG. */
.home-ecosystem-graph__pillar-link {
  cursor: pointer;
  outline: none;
  -webkit-tap-highlight-color: transparent;
}

.home-ecosystem-graph__pillar-outline {
  transition:
    opacity var(--duration-base) var(--ease-out);
  transform-origin: center;
  transform-box: fill-box;
}

.home-ecosystem-graph__pillar-disc,
.home-ecosystem-graph__pillar-label,
.home-ecosystem-graph__pillar-index {
  transition:
    fill var(--duration-base) var(--ease-out);
}

.home-ecosystem-graph__pillar-link:hover .home-ecosystem-graph__pillar-outline,
.home-ecosystem-graph__pillar-link:focus-visible .home-ecosystem-graph__pillar-outline {
  opacity: 0.9;
}

.home-ecosystem-graph__pillar-link:hover .home-ecosystem-graph__pillar-disc,
.home-ecosystem-graph__pillar-link:focus-visible .home-ecosystem-graph__pillar-disc {
  fill: var(--color-devzair-blue);
}

.home-ecosystem-graph__pillar-link:hover .home-ecosystem-graph__pillar-label,
.home-ecosystem-graph__pillar-link:focus-visible .home-ecosystem-graph__pillar-label {
  fill: var(--color-devzair-blue);
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
    opacity: 0.75 !important;
  }
  .home-ecosystem-graph__pillar {
    opacity: 1 !important;
    transform: none !important;
    animation: none !important;
  }
  .home-ecosystem-graph__pillar-halo,
  .home-ecosystem-graph__center-halo,
  .home-ecosystem-graph__center-ring,
  .home-ecosystem-graph__background,
  .home-ecosystem-graph__satellite {
    animation: none !important;
    transform: none !important;
  }
  .home-ecosystem-graph__pillar-halo {
    opacity: 0.55 !important;
  }
  .home-ecosystem-graph__center-halo {
    opacity: 0.9 !important;
  }
  .home-ecosystem-graph__pillar-outline,
  .home-ecosystem-graph__pillar-disc,
  .home-ecosystem-graph__pillar-label,
  .home-ecosystem-graph__pillar-index {
    transition: none !important;
  }
}

</style>
