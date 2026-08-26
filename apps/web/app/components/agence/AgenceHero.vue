<script setup lang="ts">
import BaseButton from "~/components/base/BaseButton.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * En-tête dédié à `/agence`.
 *
 * Silhouette desktop = grande forme « feuille » : deux longues courbes
 * bombées vers l'extérieur qui convergent en une pointe basse continue
 * (aucun U ni distorsion au raccord). Le visuel est full-bleed depuis le
 * bord gauche de la fenêtre : ses angles supérieurs sortent volontairement
 * du viewBox de sorte qu'aucun segment horizontal en haut ne soit visible.
 *
 * Deux paths distincts :
 *   - un chemin fermé (avec `Z`) sert de `<clipPath>` pour rogner l'image ;
 *   - le même tracé, mais **ouvert** (sans `Z`), est redessiné par-dessus
 *     en `stroke=petrol` pour dessiner uniquement le contour visible :
 *     courbe gauche + pointe basse + courbe droite. Le bord haut n'est
 *     jamais dessiné → aucune barre horizontale verte.
 *
 * Sur mobile (<768px) la forme disparaît : la même source d'image
 * (`heroImageSrc`) sert de fond rectangulaire pleine largeur avec un
 * dégradé sombre pour la lisibilité du texte clair superposé.
 *
 * Positionnement : la `<section>` suit strictement le flux sous
 * `SiteHeader`. Le SVG est en `position: absolute; top: 0; left: 0` à
 * l'intérieur de cette section — jamais au-dessus du header (aucun
 * `margin-top` ni `top` négatif).
 */

const heroImageSrc = "/brand/agence-devzair.png"
</script>

<template>
  <section class="agence-hero" aria-labelledby="agence-hero-title">
    <!-- Mobile : image rectangulaire pleine largeur en fond. -->
    <img
      class="agence-hero__mobile-image"
      :src="heroImageSrc"
      alt=""
      aria-hidden="true"
    >

    <!--
      Desktop : silhouette SVG.

      viewBox 0 0 1200 590. Le path démarre à (-20, -20) et rejoint
      (1220, -20) : ces deux points sortent du viewBox par le haut, ce qui
      garantit qu'aucun segment horizontal supérieur n'est visible.

      Path (identique pour le clip fermé et le contour ouvert) :
        M 0 0                              angle supérieur gauche du viewBox
        C 60 240 400 570 620 570           courbe bombée gauche
        C 840 570 1140 240 1200 0          courbe bombée droite
        [ Z ]                              uniquement pour le clip

      Endpoints AU bord haut du viewBox (y=0) : la fermeture du clip est
      la ligne y=0 elle-même — aucune bande d'image ne dépasse au-dessus
      du contour. Les deux courbes se rejoignent en (620, 570) avec des
      tangentes horizontales (raccord C¹ lisse, sans U ni pointe cassée).
    -->
    <figure class="agence-hero__visual" aria-hidden="true">
      <svg
        class="agence-hero__frame"
        viewBox="0 0 1214 604"
        preserveAspectRatio="xMidYMid meet"
        focusable="false"
      >
        <defs>
          <clipPath id="agence-hero-clip" clipPathUnits="userSpaceOnUse">
            <path
              d="M 0 0 C 60 240 400 570 620 570 C 840 570 1140 240 1200 0 Z"
            />
          </clipPath>
        </defs>
        <image
          :href="heroImageSrc"
          x="0"
          y="0"
          width="1200"
          height="590"
          preserveAspectRatio="xMidYMid slice"
          clip-path="url(#agence-hero-clip)"
        />
        <path
          d="M 0 0 C 60 240 400 570 620 570 C 840 570 1140 240 1200 0"
          fill="none"
          stroke="var(--color-petrol)"
          stroke-width="28"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
    </figure>

    <div class="agence-hero__content">
      <BaseEyebrow class="agence-hero__eyebrow">L'agence</BaseEyebrow>
      <h1 id="agence-hero-title" class="agence-hero__title">
        Une agence digitale à taille humaine.
      </h1>
      <p class="agence-hero__lead">
        Une équipe réduite, un lien direct, un engagement dans la durée avec
        chaque entreprise accompagnée.
      </p>
      <dl class="agence-hero__pillars">
        <div class="agence-hero__pillar">
          <dt class="agence-hero__pillar-term">Indépendante</dt>
          <dd class="agence-hero__pillar-desc">sans intermédiaire</dd>
        </div>
        <div class="agence-hero__pillar">
          <dt class="agence-hero__pillar-term">Globale</dt>
          <dd class="agence-hero__pillar-desc">tous les métiers reliés</dd>
        </div>
      </dl>
      <div class="agence-hero__cta">
        <BaseButton to="/contact" variant="primary">
          Parler de votre projet
          <template #icon>→</template>
        </BaseButton>
      </div>
    </div>

    <!--
      Décor circuit imprimé bas-droite (inspiré du logo Devzair). Masqué en
      mobile pour éviter la surcharge sur photo.
    -->
    <svg
      class="agence-hero__circuit"
      aria-hidden="true"
      focusable="false"
      viewBox="0 0 360 240"
      preserveAspectRatio="xMaxYMax meet"
    >
      <g class="agence-hero__circuit-traces">
        <path d="M 8 24 L 240 24" />
        <circle cx="8" cy="24" r="4" />
        <circle cx="240" cy="24" r="3" />

        <path d="M 8 64 L 180 64 L 180 96" />
        <circle cx="8" cy="64" r="4" />
        <circle cx="180" cy="96" r="3" />

        <path d="M 8 104 L 320 104" />
        <circle cx="8" cy="104" r="4" />

        <path d="M 60 144 L 280 144 L 280 200" />
        <circle cx="60" cy="144" r="4" />
        <circle cx="280" cy="200" r="3" />

        <path d="M 8 184 L 220 184" />
        <circle cx="8" cy="184" r="4" />
        <circle cx="220" cy="184" r="3" />

        <path d="M 120 224 L 320 224" />
        <circle cx="120" cy="224" r="4" />
      </g>
    </svg>
  </section>
</template>

<style scoped>
.agence-hero {
  position: relative;
  overflow: hidden;
  isolation: isolate;
  background-color: var(--background-primary);
  color: var(--text-primary);
}

/* -------------------------------------------------------------------------
   MOBILE (par défaut, <768px)
   ------------------------------------------------------------------------- */

.agence-hero__visual {
  display: none;
}

.agence-hero__mobile-image {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  z-index: 0;
}

.agence-hero::before {
  content: "";
  position: absolute;
  inset: 0;
  z-index: 1;
  background: linear-gradient(
    to top,
    rgba(11, 22, 27, 0.86) 0%,
    rgba(11, 22, 27, 0.62) 45%,
    rgba(11, 22, 27, 0.32) 100%
  );
  pointer-events: none;
}

.agence-hero__content {
  position: relative;
  z-index: 2;
  min-height: clamp(430px, 70svh, 620px);
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  justify-content: flex-end;
  padding: var(--space-10) var(--container-gutter-mobile);
  color: #ffffff;
  max-width: 40rem;
}

.agence-hero__eyebrow {
  margin-bottom: var(--space-2);
  color: #ffffff;
}

.agence-hero__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2rem, 4.6vw, 3.25rem);
  line-height: 1.1;
  letter-spacing: -0.01em;
  color: #ffffff;
  margin: 0;
  max-width: 18ch;
  text-wrap: balance;
}

.agence-hero__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.5vw, 1.125rem);
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.9);
  margin: 0;
  max-width: 44ch;
}

.agence-hero__pillars {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-6);
  margin: var(--space-2) 0 0;
}

.agence-hero__pillar {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
  padding-right: var(--space-6);
  min-width: 12rem;
}

.agence-hero__pillar + .agence-hero__pillar {
  padding-left: var(--space-6);
  border-left: 1px solid rgba(255, 255, 255, 0.35);
}

.agence-hero__pillar-term {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.0625rem;
  color: #ffffff;
  margin: 0;
}

.agence-hero__pillar-desc {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  color: rgba(255, 255, 255, 0.85);
  margin: 0;
}

.agence-hero__cta {
  margin-top: var(--space-4);
}

.agence-hero__circuit {
  display: none;
}

@media (min-width: 480px) {
  .agence-hero__content {
    padding-inline: var(--container-gutter-tablet);
  }
}

/* -------------------------------------------------------------------------
   TABLETTE & DESKTOP (≥768px)
   ------------------------------------------------------------------------- */

@media (min-width: 768px) {
  .agence-hero::before {
    content: none;
  }

  .agence-hero__mobile-image {
    display: none;
  }

  /*
   * Le visuel est en absolute — il ne pousse pas la hauteur de la section.
   * min-height couvre la hauteur du viewport visible (moins la hauteur du
   * SiteHeader sticky ≈ 5.5rem) pour que le hero occupe tout l'écran au
   * chargement, avec un plancher garantissant la lisibilité en paysage.
   */
  .agence-hero {
    min-height: max(calc(100svh - 5.5rem), 520px);
  }

  .agence-hero__visual {
    display: block;
    position: absolute;
    top: 0;
    left: 0;
    width: min(56vw, 1080px);
    margin: 0;
    pointer-events: none;
    filter: drop-shadow(0 32px 60px rgba(12, 91, 87, 0.22));
  }

  .agence-hero__frame {
    display: block;
    width: 100%;
    height: auto;
  }

  /*
   * Le contenu se glisse à droite du visuel. `margin-left` en unités de
   * viewport permet au texte de commencer là où la diagonale droite du
   * visuel a déjà libéré la place — pas de grille fixe qui pousserait le
   * texte hors du triangle.
   */
  .agence-hero__content {
    justify-content: center;
    min-height: 0;
    color: var(--text-primary);
    margin-left: min(58vw, 1120px);
    padding: var(--space-10) var(--container-gutter-tablet) var(--space-14) 0;
    max-width: 40rem;
  }

  .agence-hero__eyebrow {
    color: inherit;
  }

  .agence-hero__title {
    color: var(--text-primary);
  }

  .agence-hero__lead {
    color: var(--text-secondary);
  }

  .agence-hero__pillar + .agence-hero__pillar {
    border-left: 1px solid var(--border-default);
  }

  .agence-hero__pillar-term {
    color: var(--text-primary);
  }

  .agence-hero__pillar-desc {
    color: var(--text-secondary);
  }

  .agence-hero__circuit {
    display: block;
    position: absolute;
    right: 0;
    bottom: 0;
    width: 320px;
    height: 200px;
    pointer-events: none;
    opacity: 0.25;
    z-index: 0;
  }

  .agence-hero__circuit-traces path {
    fill: none;
    stroke: var(--color-devzair-blue);
    stroke-width: 1.5;
    stroke-linecap: round;
    stroke-linejoin: round;
  }

  .agence-hero__circuit-traces circle {
    fill: var(--color-devzair-blue);
    stroke: none;
  }
}

@media (min-width: 1024px) {
  .agence-hero__content {
    padding-inline: var(--container-gutter-desktop) 0;
    padding-block: var(--space-12) var(--space-16);
  }

  .agence-hero__circuit {
    width: 460px;
    height: 280px;
    opacity: 0.3;
  }
}

@media (prefers-reduced-motion: reduce) {
  .agence-hero,
  .agence-hero * {
    animation: none !important;
    transition: none !important;
  }
}
</style>
