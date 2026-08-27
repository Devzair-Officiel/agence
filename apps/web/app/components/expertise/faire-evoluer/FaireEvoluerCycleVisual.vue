<script setup lang="ts">
/**
 * Visuel décoratif du hero Faire évoluer — direction « Living System /
 * Continuous Care », révision « parcours ouvert ».
 *
 * Micro-passe finale : la boucle presque fermée (silhouette « pomme »)
 * est remplacée par une TRAJECTOIRE OUVERTE ET ASYMÉTRIQUE. Le trait
 * démarre en haut-gauche (OBSERVE), plonge vers le centre-bas (CARE),
 * balaye vers la droite (IMPROVE) puis remonte et s'échappe hors du
 * cadre en haut-droit (NEXT ITER.) via une flèche pointillée très
 * discrète. Aucune fermeture, aucune symétrie, aucun anneau — c'est un
 * PARCOURS et non plus une ORBITE.
 *
 * Accessibilité : `aria-hidden="true"` sur la racine et sur le SVG.
 * `focusable="false"`, `role="presentation"`. Aucun texte structuré
 * annoncé. Aucune métrique / uptime / SLA / version. Aucune animation
 * (donc rien à conditionner sur `prefers-reduced-motion`).
 */
</script>

<template>
  <div class="faire-evoluer-visual" aria-hidden="true">
    <svg
      class="faire-evoluer-visual__diagram"
      viewBox="0 0 480 480"
      role="presentation"
      focusable="false"
      aria-hidden="true"
      preserveAspectRatio="xMidYMid meet"
    >
      <!-- Texture organique : petits points épars, aucune grille -->
      <g fill="#0c5b57" opacity="0.28">
        <circle cx="380" cy="52" r="1.6" />
        <circle cx="42" cy="200" r="1.6" />
        <circle cx="222" cy="86" r="1.6" />
        <circle cx="430" cy="308" r="1.6" />
        <circle cx="176" cy="446" r="1.6" />
        <circle cx="62" cy="384" r="1.6" />
      </g>

      <!-- Trajectoire principale : trois béziers en descente diagonale
           franche, du haut-gauche jusqu'au bas-droit. Aucun retour, aucune
           inflexion ascendante — c'est un PARCOURS. -->
      <path
        d="M 84 96
           C 44 160, 118 200, 174 214
           C 232 232, 258 300, 302 328
           C 342 354, 396 372, 420 382"
        stroke="#0c5b57"
        stroke-width="2.3"
        fill="none"
        stroke-linecap="round"
      />

      <!-- Sortie ouverte vers la droite : trait pointillé + micro-flèche
           poursuivant la direction du parcours. Aucun retour vers OBSERVE. -->
      <path
        d="M 420 382 L 462 388"
        stroke="#0c5b57"
        stroke-width="1.4"
        stroke-dasharray="4 5"
        fill="none"
        opacity="0.55"
        stroke-linecap="round"
      />
      <path
        d="M 456 380 L 464 388 L 456 396"
        stroke="#0c5b57"
        stroke-width="1.4"
        fill="none"
        opacity="0.8"
        stroke-linecap="round"
        stroke-linejoin="round"
      />

      <!-- Checkpoints du parcours -->
      <!-- 01 · OBSERVE (départ, léger halo pour marquer l'entrée) -->
      <g>
        <circle cx="84" cy="96" r="6" fill="#0c5b57" />
        <circle cx="84" cy="96" r="11" fill="none" stroke="#0c5b57" stroke-width="1" opacity="0.35" />
      </g>
      <!-- 02 · CARE (première inflexion) -->
      <circle cx="174" cy="214" r="5" fill="#0c5b57" />
      <!-- 03 · IMPROVE (poursuite descendante) -->
      <circle cx="302" cy="328" r="5" fill="#0c5b57" />
      <!-- 04 · NEXT ITER. (touche navy avant la sortie ouverte) -->
      <circle cx="420" cy="382" r="5" fill="#0e1622" />

      <!-- Micro-repères rythmiques dispersés le long du parcours -->
      <g fill="#0c5b57" opacity="0.35">
        <circle cx="66" cy="146" r="1.5" />
        <circle cx="128" cy="204" r="1.5" />
        <circle cx="228" cy="258" r="1.5" />
        <circle cx="272" cy="308" r="1.5" />
        <circle cx="368" cy="360" r="1.5" />
      </g>

      <!-- Labels Space Mono petrol : suivent la trajectoire, alternent
           gauche/droite pour renforcer l'asymétrie. -->
      <g font-family="Space Mono, monospace" font-size="10" fill="#0c5b57" font-weight="700">
        <text x="104" y="74" letter-spacing="1.4">OBSERVE</text>
        <text x="76" y="240" letter-spacing="1.4">CARE</text>
        <text x="216" y="360" letter-spacing="1.4">IMPROVE</text>
        <text x="312" y="410" letter-spacing="1.4">NEXT ITER.</text>
      </g>
      <g font-family="Space Mono, monospace" font-size="8" fill="#0c5b57" opacity="0.6">
        <text x="104" y="86" letter-spacing="1.4">/ 01</text>
        <text x="76" y="252" letter-spacing="1.4">/ 02</text>
        <text x="216" y="372" letter-spacing="1.4">/ 03</text>
        <text x="312" y="422" letter-spacing="1.4">/ 04</text>
      </g>
    </svg>
  </div>
</template>

<style scoped>
/*
 * Aucun cadre. Le parcours repose directement sur le fond cream du hero
 * et laisse la respiration au H1. En desktop, il peut légèrement empiéter
 * dans le gap entre les colonnes ; la flèche de sortie peut déborder
 * doucement en haut-droit — c'est le signal graphique de « prochaine
 * itération » qui échappe au cadre.
 */
.faire-evoluer-visual {
  position: relative;
  width: 100%;
  max-width: 500px;
  margin-inline: auto;
  aspect-ratio: 1 / 1;
}

.faire-evoluer-visual__diagram {
  width: 100%;
  height: 100%;
  display: block;
  overflow: visible;
}

@media (min-width: 1024px) {
  .faire-evoluer-visual {
    max-width: none;
    margin-inline: 0;
    /* Le parcours mord légèrement dans l'espace inter-colonnes */
    margin-inline-start: calc(-1 * var(--space-6));
  }
}

@media (min-width: 1440px) {
  .faire-evoluer-visual {
    margin-inline-start: calc(-1 * var(--space-8));
  }
}
</style>
