<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from "vue"
import type { CaseStudy } from "~/config/case-studies"

/**
 * Carte projet — visuel de la réalisation dans un cadre 3D CSS pur.
 *
 * Composition :
 *   - `.case-card__frame` porte l'image principale dans un cadre plat
 *     (aucun tilt), surmonté d'un reflet dont l'origine suit le pointeur ;
 *   - un overlay décoratif optionnel (`overlayImageSrc`) est posé devant
 *     la carte, débordant sur le bord droit pour créer un vrai plan avant.
 *     L'overlay est rendu `alt=""` + `aria-hidden` — purement visuel.
 *
 * Mise en scène du pot :
 *   1. Reveal composé — au premier passage dans le viewport, la carte
 *      révèle le pot qui « arrive » depuis le coin bas-droit avec un
 *      léger arc + fade. Un seul déclenchement, puis l'observer se
 *      déconnecte pour ne pas rejouer et rester silencieux au scroll.
 *   2. Flottement ambiant — une fois posé, le pot oscille très
 *      légèrement (translateY + micro-rotation) via une animation CSS
 *      infinie. Pas de tracking pointer, pas de JS pendant le mouvement.
 *
 * Reflet interactif :
 *   - le gradient radial du `.case-card__glare` est positionné via
 *     variables CSS (`--glare-x`, `--glare-y`) mises à jour en JS sur
 *     `pointermove` ; l'opacité passe de 0 à 1 au survol (transition CSS) ;
 *   - le cadre lui-même reste immobile — seul le reflet réagit.
 *
 * Accessibilité :
 *   - `prefers-reduced-motion: reduce` coupe reveal + flottement + tilt.
 *     Le pot reste visible dans son état final, sans mouvement.
 *   - `@media (scripting: none)` : si JS est indisponible, le pot est
 *     visible directement (fallback SSR-safe, l'observer ne tourne pas).
 *   - `@media (hover: none)` : pas de tilt sur touch, mais l'ambiant
 *     continue (non interactif, discret).
 */

const props = defineProps<{
  study: CaseStudy
}>()

const root = ref<HTMLElement | null>(null)
const frame = ref<HTMLElement | null>(null)
const revealed = ref(false)

let observer: IntersectionObserver | null = null

/*
 * Reflet suivant le pointeur — les variables `--glare-x` / `--glare-y`
 * (en % de la surface du cadre) déplacent l'origine du gradient radial
 * du reflet pour donner l'illusion d'une source lumineuse fixe qui
 * balaie la surface au passage du curseur. Aucun tilt : le cadre reste
 * plat, seul le reflet réagit.
 */
function handleFrameMove(event: PointerEvent) {
  const el = frame.value
  if (!el) return
  const rect = el.getBoundingClientRect()
  el.style.setProperty(
    "--glare-x",
    `${((event.clientX - rect.left) / rect.width) * 100}%`,
  )
  el.style.setProperty(
    "--glare-y",
    `${((event.clientY - rect.top) / rect.height) * 100}%`,
  )
}

onMounted(() => {
  if (typeof window === "undefined") return

  const reduced =
    typeof window.matchMedia === "function" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches

  // Reduced-motion ou pas d'observer disponible → pot immédiatement visible
  // dans son état final, on saute complètement la révélation animée.
  if (reduced || typeof IntersectionObserver === "undefined") {
    revealed.value = true
    return
  }
  if (!root.value) return

  observer = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        if (entry.isIntersecting) {
          revealed.value = true
          observer?.disconnect()
          observer = null
          break
        }
      }
    },
    { threshold: 0.25 },
  )
  observer.observe(root.value)
})

onBeforeUnmount(() => {
  observer?.disconnect()
  observer = null
})
</script>

<template>
  <div
    ref="root"
    class="case-card"
    :class="{ 'case-card--revealed': revealed }"
    @pointermove="handleFrameMove"
  >
    <div class="case-card__stage">
      <div ref="frame" class="case-card__frame">
        <div class="case-card__glare" aria-hidden="true" />
        <div class="case-card__image-wrap">
          <img
            class="case-card__image"
            :src="props.study.imageSrc"
            :alt="props.study.imageAlt"
            loading="lazy"
            decoding="async"
          >
        </div>
      </div>

      <!--
        Wrapper span pour la révélation, img à l'intérieur pour le
        flottement. Les deux animations vivent sur des éléments
        distincts → aucun conflit de `transform`.
      -->
      <span
        v-if="props.study.overlayImageSrc"
        class="case-card__overlay"
      >
        <img
          class="case-card__overlay-img"
          :src="props.study.overlayImageSrc"
          alt=""
          aria-hidden="true"
          loading="lazy"
          decoding="async"
        >
      </span>
    </div>
  </div>
</template>

<style scoped>
.case-card {
  /*
   * Perspective isolée sur le wrapper : la carte enfant vit en 3D dans ce
   * contexte uniquement, sans polluer le reste de la mise en page. 1400px
   * donne un tilt lisible sans distorsion excessive.
   */
  perspective: 1400px;
  perspective-origin: 50% 45%;
  /*
   * L'overlay pot de miel est un vrai plan avant : il occupe au moins la
   * moitié de la largeur de la carte et déborde franchement en bas-droite,
   * ombre portée `drop-shadow` incluse (~48 px sous l'image).
   *
   * Le carrousel parent (`.home-case__list`) est un conteneur de scroll
   * horizontal (`overflow-x: auto`), ce qui force `overflow-y` en
   * `hidden` via la règle du spec CSS Overflow 3 (les valeurs `visible`
   * et `clip` sont coercées quand l'autre axe scrolle). Impossible donc
   * de compter sur `overflow-clip-margin` pour laisser l'ombre baver :
   * la seule voie fiable est de RÉSERVER assez de padding-bottom ici
   * pour que le pot + son ombre tiennent dans la boîte de la carte,
   * mesurée par la piste. Les valeurs ci-dessous couvrent l'amplitude
   * observée à chaque breakpoint (bottom -10% → -18%).
   */
  padding: var(--space-4) var(--space-16) 6rem var(--space-4);
}

@media (min-width: 640px) {
  .case-card {
    padding-bottom: 9rem;
  }
}

@media (min-width: 1024px) {
  .case-card {
    padding-bottom: 10rem;
  }
}

.case-card__stage {
  position: relative;
  transform-style: preserve-3d;
  aspect-ratio: 3 / 4;
  width: 100%;
  max-height: 60vh;
  max-width: calc(60vh * 3 / 4);
  margin-inline: auto;
}

.case-card__frame {
  position: relative;
  width: 100%;
  height: 100%;
  border-radius: var(--radius-lg);
  background: linear-gradient(
    145deg,
    var(--color-navy-elevated) 0%,
    var(--color-navy-deep) 100%
  );
  border: 1px solid rgba(255, 255, 255, 0.08);
  overflow: hidden;
  transform-style: preserve-3d;
  transition: box-shadow var(--duration-base) var(--ease-out);
  box-shadow:
    0 20px 40px -20px rgba(0, 0, 0, 0.5),
    0 10px 20px -10px rgba(0, 0, 0, 0.35);
}

.case-card:hover .case-card__frame,
.case-card:focus-within .case-card__frame {
  box-shadow:
    0 30px 60px -20px rgba(0, 0, 0, 0.55),
    0 20px 40px -15px rgba(12, 91, 87, 0.35);
}

.case-card__glare {
  position: absolute;
  inset: 0;
  z-index: 2;
  pointer-events: none;
  /*
   * Le reflet suit le pointeur via `--glare-x` / `--glare-y` (en % de la
   * surface), pour renforcer l'illusion d'une source lumineuse fixe qui
   * balaie une carte physique inclinée.
   */
  background: radial-gradient(
    ellipse at var(--glare-x, 30%) var(--glare-y, 15%),
    rgba(255, 255, 255, 0.22),
    transparent 55%
  );
  opacity: 0;
  transform: translateZ(30px);
  transition: opacity var(--duration-base) var(--ease-out);
  mix-blend-mode: screen;
}

.case-card:hover .case-card__glare,
.case-card:focus-within .case-card__glare {
  opacity: 1;
}

.case-card__image-wrap {
  position: relative;
  width: 100%;
  height: 100%;
  overflow: hidden;
  background-color: var(--color-navy);
  transform: translateZ(20px);
}

.case-card__image {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center center;
}

/*
 * Overlay pot de miel — wrapper span.
 *
 * Positionnement, reveal et empilement Z-index vivent ici. Le wrapper
 * ne porte AUCUN filter ni animation infinie : ces deux propriétés
 * (drop-shadow + oscillation) descendent sur l'img à l'intérieur pour
 * éviter de reformater l'ombre à chaque frame et pour empêcher un
 * conflit `transform` entre reveal (wrapper) et flottement (img).
 *
 * Reveal composé :
 *   - état par défaut → opacity 0, translation depuis le bas-droit +
 *     léger arc (rotate 6deg) ;
 *   - passage dans le viewport → classe `.case-card--revealed` posée
 *     par l'IntersectionObserver → retour à opacity 1, transform none.
 *   - transition longue (`--duration-signature`, 900ms) pour un effet
 *     éditorial, unique.
 */
.case-card__overlay {
  position: absolute;
  bottom: -10%;
  right: -30%;
  width: 75%;
  max-width: 300px;
  z-index: 3;
  pointer-events: none;
  opacity: 0;
  transform: translate(40px, 30px) rotate(6deg);
  transform-origin: 20% 20%;
  transition:
    opacity var(--duration-signature) var(--ease-out),
    transform var(--duration-signature) var(--ease-out);
}

.case-card--revealed .case-card__overlay {
  opacity: 1;
  transform: none;
}

/*
 * Image pot — flottement ambiant.
 *
 * Micro-oscillation infinie sur `transform` (compositor-only, 60 fps).
 * `alternate` évite la coupure sèche du cycle. Amplitude volontairement
 * discrète : le pot respire, il ne danse pas.
 * `will-change: transform` pré-alloue une couche compositor pour éviter
 * les repaints de l'ombre à chaque cycle.
 */
.case-card__overlay-img {
  display: block;
  width: 100%;
  height: auto;
  filter: drop-shadow(0 22px 26px rgba(0, 0, 0, 0.35));
  transform-origin: 55% 30%;
  /*
   * Flottement ambiant discret : présent mais pas envahissant.
   *   - amplitude : ~10px vertical + 2° de balancement + micro-drift
   *     horizontal, pour un mouvement organique mais posé ;
   *   - cadence : 4.5s ease-in-out infinite alternate → cycle complet
   *     9s, respiration lente.
   * Compositor-only, `will-change: transform` pré-alloue une couche
   * GPU pour éviter que l'ombre re-rendue à chaque frame ne coûte cher.
   */
  animation: case-card-float 4.5s ease-in-out infinite alternate;
  will-change: transform;
}

@keyframes case-card-float {
  from {
    transform: translate3d(0, 0, 0) rotate(-0.5deg);
  }
  to {
    transform: translate3d(-2px, -10px, 0) rotate(1.5deg);
  }
}

@media (min-width: 640px) {
  .case-card__overlay {
    width: 78%;
    max-width: 400px;
    bottom: -14%;
    right: -38%;
  }
}

@media (min-width: 1024px) {
  .case-card__overlay {
    width: 80%;
    max-width: 480px;
    bottom: -18%;
    right: -45%;
  }
}

/*
 * Touch (hover: none) : pas d'événement hover fiable, on masque le
 * reflet. Le flottement ambiant du pot est conservé — mouvement passif,
 * non interactif, qui donne vie à la composition sans exiger d'action.
 */
@media (hover: none) {
  .case-card__glare {
    display: none;
  }
}

/*
 * Reduced-motion + scripting désactivé : le reflet et l'apparition
 * animée sont neutralisés, le pot est visible dans son état final.
 * Fallback SSR-safe : `scripting: none` couvre le cas où
 * l'IntersectionObserver ne tournerait jamais.
 */
@media (prefers-reduced-motion: reduce), (scripting: none) {
  .case-card__glare {
    display: none;
  }
  .case-card__overlay {
    opacity: 1;
    transform: none;
    transition: none;
  }
  .case-card__overlay-img {
    animation: none;
    transform: none;
  }
}
</style>
