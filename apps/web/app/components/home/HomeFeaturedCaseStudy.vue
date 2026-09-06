<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"
import HomeCaseCard from "~/components/home/HomeCaseCard.vue"
import { caseStudies } from "~/config/case-studies"

/**
 * Section « Réalisations » — vitrine home, format carrousel.
 *
 * Un seul projet visible à la fois. Navigation :
 *   - défilement horizontal natif (touch, molette horizontale, clavier
 *     via le focus des boutons) grâce à `scroll-snap-type: x mandatory` ;
 *   - trio prev · pagination · next sous le carrousel (chevrons SVG,
 *     puces cliquables).
 *
 * Les données viennent exclusivement de `app/config/case-studies.ts` —
 * aucun contenu éditorial dupliqué ici. AGENTS.md rule 1 : seuls les
 * projets réellement livrés avec accord client sont publiés.
 *
 * Accessibilité :
 *   - conteneur `aria-roledescription="carousel"` ;
 *   - chaque slide `aria-roledescription="slide"` + `aria-label="i sur N"` ;
 *   - la piste est un `<ul>` sémantique — les slides restent listables
 *     par les lecteurs d'écran quelle que soit la position visuelle ;
 *   - les slides inactives reçoivent `inert` + `aria-hidden` pour éviter
 *     que le focus atterrisse hors du viewport lors d'une tabulation ;
 *   - les boutons prev / next sont désactivés en début / fin (pas de wrap).
 *
 * Stratégie de preload (deux responsabilités distinctes) :
 *   A — observer d'anticipation de la section (rootMargin: "800px 0px") :
 *       déclenche le téléchargement des assets du premier slide avant même
 *       que la section entre dans le viewport ; ne touche pas au reveal.
 *   B — watch sur `current` : précharge discrètement les assets du slide
 *       suivant dès que la slide courante change.
 *   Un Set<string> évite les requêtes dupliquées entre les deux mécanismes.
 *
 * L'ancre `id="realisations"` reste la cible du CTA hero et du lien de
 * navigation « Réalisations ».
 */

const publishedStudies = caseStudies.filter((s) => s.status === "published")

const section = ref<HTMLElement | null>(null)
const track = ref<HTMLElement | null>(null)
const current = ref(0)
const total = publishedStudies.length

let slideObserver: IntersectionObserver | null = null
let sectionObserver: IntersectionObserver | null = null

// URLs déjà demandées — évite les requêtes dupliquées inter-observers.
const preloaded = new Set<string>()

/**
 * Précharge discrètement une ou deux URLs d'image via new Image().
 * fetchPriority "low" pour ne pas concurrencer les ressources critiques.
 */
function preloadStudy(index: number) {
  if (typeof window === "undefined") return
  const study = publishedStudies[index]
  if (!study) return

  for (const url of [study.imageSrc, study.overlayImageSrc]) {
    if (!url || preloaded.has(url)) continue
    preloaded.add(url)
    const img = new window.Image()
    img.decoding = "async"
    img.fetchPriority = "low"
    img.src = url
  }
}

function goTo(index: number) {
  const el = track.value
  if (!el) return
  const clamped = Math.max(0, Math.min(total - 1, index))
  const slide = el.children.item(clamped) as HTMLElement | null
  if (!slide) return
  el.scrollTo({ left: slide.offsetLeft, behavior: "smooth" })
}

function prev() {
  goTo(current.value - 1)
}

function next() {
  goTo(current.value + 1)
}

onMounted(() => {
  if (typeof window === "undefined" || typeof IntersectionObserver === "undefined") return

  // Observer A — anticipation de section.
  // Démarre le téléchargement du premier slide ~800px avant l'entrée dans
  // le viewport. Ne déclenche qu'une seule fois puis se déconnecte.
  const sec = section.value
  if (sec) {
    sectionObserver = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          if (entry.isIntersecting) {
            preloadStudy(0)
            sectionObserver?.disconnect()
            sectionObserver = null
            break
          }
        }
      },
      { rootMargin: "800px 0px", threshold: 0 },
    )
    sectionObserver.observe(sec)
  }

  // Observer B — tracking de la slide courante (> 50 % visible dans la piste).
  const el = track.value
  if (!el) return

  slideObserver = new IntersectionObserver(
    (entries) => {
      for (const entry of entries) {
        if (entry.isIntersecting && entry.intersectionRatio >= 0.5) {
          const index = Number(
            (entry.target as HTMLElement).dataset.slideIndex,
          )
          if (!Number.isNaN(index)) current.value = index
        }
      }
    },
    { root: el, threshold: [0.5, 0.75, 1] },
  )
  for (const child of Array.from(el.children)) {
    slideObserver.observe(child)
  }
})

// Précharge la slide suivante dès que `current` change.
watch(current, (idx) => {
  preloadStudy(idx + 1)
})

onBeforeUnmount(() => {
  slideObserver?.disconnect()
  slideObserver = null
  sectionObserver?.disconnect()
  sectionObserver = null
})
</script>

<template>
  <section
    id="realisations"
    ref="section"
    class="home-case"
    aria-labelledby="home-case-title"
  >
    <BaseContainer width="wide" class="home-case__container">
      <header class="home-case__intro">
        <BaseEyebrow class="home-case__eyebrow">Réalisations</BaseEyebrow>
        <h2 id="home-case-title" class="home-case__title">
          Des solutions concrètes, pas seulement de belles interfaces.
        </h2>
        <p class="home-case__lead">
          Nos réalisations sont conçues pour répondre à un besoin métier réel :
          rendre une entreprise visible, structurer une activité, faciliter le
          quotidien d'une équipe. La forme découle du fond, jamais l'inverse.
        </p>
        <NuxtLink
          to="/realisations"
          class="home-case__hub-link"
        >
          Voir toutes les réalisations
          <span aria-hidden="true">→</span>
        </NuxtLink>
      </header>

      <div
        class="home-case__carousel"
        role="region"
        aria-roledescription="carrousel"
        aria-labelledby="home-case-title"
      >
        <div class="home-case__viewport">
          <ul
            ref="track"
            class="home-case__list"
            aria-label="Réalisations Devzair"
          >
          <li
            v-for="(study, index) in publishedStudies"
            :key="study.id"
            class="home-case__item"
            :data-slide-index="index"
            :aria-hidden="index === current ? undefined : 'true'"
            :inert="index === current ? undefined : true"
            aria-roledescription="diapositive"
            :aria-label="`${index + 1} sur ${total} — ${study.name}`"
          >
          <article
            class="home-case__composition"
            :aria-labelledby="`case-${study.id}-title`"
          >
            <div class="home-case__visual">
              <HomeCaseCard :study="study" />
            </div>

            <div class="home-case__content">
              <p class="home-case__project-eyebrow">
                Projet Devzair · {{ study.name }}
              </p>
              <h3
                :id="`case-${study.id}-title`"
                class="home-case__project-title"
              >
                {{ study.name }}
              </h3>
              <p class="home-case__project-summary">
                {{ study.longDescription }}
              </p>
              <ul class="home-case__tags" aria-label="Interventions Devzair">
                <li
                  v-for="tag in study.tags"
                  :key="tag"
                  class="home-case__tag"
                >
                  {{ tag }}
                </li>
              </ul>
              <dl class="home-case__meta">
                <div class="home-case__meta-row">
                  <dt class="home-case__meta-label">Catégorie</dt>
                  <dd class="home-case__meta-value">{{ study.category }}</dd>
                </div>
                <div v-if="study.year" class="home-case__meta-row">
                  <dt class="home-case__meta-label">Année</dt>
                  <dd class="home-case__meta-value">{{ study.year }}</dd>
                </div>
              </dl>
              <NuxtLink
                v-if="study.status === 'published'"
                :to="study.route"
                class="home-case__cta"
              >
                Voir l'étude de cas
                <span aria-hidden="true" class="home-case__cta-arrow">→</span>
              </NuxtLink>
              <a
                v-else-if="study.href"
                :href="study.href"
                class="home-case__cta"
                target="_blank"
                rel="noopener noreferrer"
              >
                Voir le site
                <span class="sr-only"> (nouvelle fenêtre)</span>
                <span aria-hidden="true" class="home-case__cta-arrow">↗</span>
              </a>
            </div>
          </article>
          </li>
          </ul>
        </div>

        <nav
          v-if="total > 1"
          class="home-case__controls"
          aria-label="Navigation entre les réalisations"
        >
          <button
            type="button"
            class="home-case__nav-btn home-case__nav-btn--prev"
            :disabled="current === 0"
            aria-label="Réalisation précédente"
            @click="prev"
          >
            <svg
              class="home-case__nav-icon"
              viewBox="0 0 24 24"
              width="24"
              height="24"
              aria-hidden="true"
              focusable="false"
            >
              <path
                d="M15 6l-6 6 6 6"
                fill="none"
                stroke="currentColor"
                stroke-width="2.25"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </button>

          <ol class="home-case__pagination">
            <li
              v-for="(study, index) in publishedStudies"
              :key="study.id"
              class="home-case__pagination-item"
            >
              <button
                type="button"
                class="home-case__pagination-btn"
                :aria-current="index === current ? 'true' : undefined"
                :aria-label="`Aller à la réalisation ${study.name}`"
                @click="goTo(index)"
              >
                <span class="home-case__pagination-dot" aria-hidden="true" />
              </button>
            </li>
          </ol>

          <button
            type="button"
            class="home-case__nav-btn home-case__nav-btn--next"
            :disabled="current === total - 1"
            aria-label="Réalisation suivante"
            @click="next"
          >
            <svg
              class="home-case__nav-icon"
              viewBox="0 0 24 24"
              width="24"
              height="24"
              aria-hidden="true"
              focusable="false"
            >
              <path
                d="M9 6l6 6-6 6"
                fill="none"
                stroke="currentColor"
                stroke-width="2.25"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </button>
        </nav>
      </div>
    </BaseContainer>
  </section>
</template>

<style scoped>
.home-case {
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-16);
  /*
   * Clip horizontal uniquement : borne l'overlay décoratif du pot
   * (voir `.case-card__overlay`) qui déborde vers la droite pour créer
   * son plan avant. `overflow-x: clip` évite la scrollbar horizontale
   * qu'un `overflow-x: hidden` provoquerait, tout en laissant l'axe
   * vertical libre pour l'ombre portée du pot.
   */
  overflow-x: clip;
  overflow-y: visible;
}

.home-case__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-12);
}

.home-case__intro {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 44rem;
}

.home-case__eyebrow {
  margin-bottom: var(--space-2);
}

.home-case__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.75rem, 4vw, 2.5rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 24ch;
}

.home-case__lead {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.0625rem);
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

.home-case__hub-link {
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

.home-case__hub-link:hover {
  color: var(--text-primary);
}

.home-case__hub-link:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
  border-radius: 2px;
}

.home-case__carousel {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
  min-width: 0;
}

/*
 * Viewport = simple wrapper de la piste. Les flèches prev/next vivent
 * désormais dans `.home-case__controls` sous le carrousel — plus rien
 * n'est ancré ici en absolu.
 */
.home-case__viewport {
  min-width: 0;
}

/*
 * Piste horizontale : chaque enfant occupe 100 % de la largeur visible et
 * s'aligne en `scroll-snap-align: start`. Le défilement natif (touch,
 * molette horizontale, focus clavier des boutons prev/next) fait tout
 * le travail — aucun listener JS de scroll n'est nécessaire. Une
 * IntersectionObserver locale à la piste met à jour l'index courant
 * pour synchroniser la pagination.
 */
.home-case__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: row;
  gap: 0;
  /*
   * `position: relative` fait de la piste l'offsetParent de chaque
   * slide → `slide.offsetLeft` retourne alors la position correcte à
   * passer à `scrollTo()` dans `goTo()`. Sans ça, l'offset remonte
   * jusqu'à un ancêtre positionné plus haut (voire le body) et la
   * navigation par chevrons / pagination scrolle à la mauvaise position.
   */
  position: relative;
  /*
   * Piste = conteneur de scroll horizontal. `overflow-y: hidden` évite
   * la scrollbar verticale que le spec CSS Overflow 3 impose dès que
   * `overflow-x: auto` cohabite avec un `overflow-y: visible` (coerção
   * en `auto`). Note : `overflow-y: clip` serait également coercé en
   * `hidden`, donc `overflow-clip-margin` n'est pas exploitable ici —
   * la respiration verticale du pot est gérée par le padding-bottom
   * de `.case-card` (voir HomeCaseCard.vue), pas par l'overflow.
   */
  overflow-x: auto;
  overflow-y: hidden;
  scroll-snap-type: x mandatory;
  scroll-behavior: smooth;
  scrollbar-width: none;
  overscroll-behavior-x: contain;
}

.home-case__list::-webkit-scrollbar {
  display: none;
}

.home-case__item {
  flex: 0 0 100%;
  min-width: 0;
  scroll-snap-align: start;
  scroll-snap-stop: always;
}

.home-case__composition {
  display: flex;
  flex-direction: column;
  gap: var(--space-8);
}

.home-case__visual {
  min-width: 0;
}

.home-case__content {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  min-width: 0;
}

.home-case__project-eyebrow {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text-accent);
  margin: 0;
}

.home-case__project-title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.5rem, 3vw, 2rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
}

.home-case__project-summary {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 52ch;
}

.home-case__tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
  list-style: none;
  padding: 0;
  margin: 0;
}

.home-case__tag {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--text-accent);
  padding: 4px 10px;
  border: 1px solid rgba(12, 91, 87, 0.35);
  border-radius: var(--radius-pill);
  background-color: rgba(12, 91, 87, 0.06);
}

.home-case__meta {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-6);
  margin: var(--space-2) 0 0;
  padding: 0;
}

.home-case__meta-row {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.home-case__meta-label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text-muted);
  margin: 0;
}

.home-case__meta-value {
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 0.9375rem;
  color: var(--text-primary);
  margin: 0;
}

.home-case__cta {
  align-self: flex-start;
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  margin-top: var(--space-2);
  padding: var(--space-3) var(--space-5);
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 0.9375rem;
  color: var(--action-on-primary);
  background-color: var(--action-primary);
  border-radius: var(--radius-pill);
  text-decoration: none;
  transition:
    background-color var(--duration-base) var(--ease-out),
    transform var(--duration-base) var(--ease-out);
  min-height: 44px;
}

.home-case__cta:hover {
  background-color: var(--action-primary-hover);
  transform: translateY(-1px);
}

.home-case__cta:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
}

.home-case__cta-arrow {
  transition: transform var(--duration-base) var(--ease-out);
}

.home-case__cta:hover .home-case__cta-arrow {
  transform: translateX(3px);
}

/* Réservé au lecteur d'écran — même règle que le skip link. */
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

/*
 * Contrôles placés SOUS la piste : trio [prev · pagination · next].
 * Le `<nav>` reste l'ancre sémantique pour les lecteurs d'écran ; le
 * groupe est centré horizontalement pour se caler avec l'axe de la
 * composition, quel que soit le breakpoint.
 */
.home-case__controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-4);
  padding-top: var(--space-4);
}

/*
 * Flèches prev / next — chevrons SVG dans un pastille pleine couleur
 * action. Choix appuyé (fond plein, ombre discrète, taille > touch min)
 * pour affirmer la présence de la commande de navigation sous le
 * carrousel.
 *   - désactivées en bord de piste (pas de wrap) → contraste réduit ;
 *   - `currentColor` sur le tracé SVG permet aux états hover / disabled
 *     de piloter la couleur du chevron via `color`.
 */
.home-case__nav-btn {
  appearance: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 3rem;
  height: 3rem;
  padding: 0;
  border: none;
  border-radius: 50%;
  background-color: var(--action-primary);
  color: var(--action-on-primary);
  cursor: pointer;
  box-shadow: var(--shadow-sm);
  transition:
    background-color var(--duration-base) var(--ease-out),
    color var(--duration-base) var(--ease-out),
    transform var(--duration-base) var(--ease-out),
    opacity var(--duration-base) var(--ease-out);
}

.home-case__nav-icon {
  display: block;
  width: 1.375rem;
  height: 1.375rem;
  pointer-events: none;
}

.home-case__nav-btn:hover:not(:disabled) {
  background-color: var(--action-primary-hover);
  transform: translateY(-1px);
}

.home-case__nav-btn:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
}

.home-case__nav-btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.home-case__pagination {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  list-style: none;
  margin: 0;
  padding: 0;
}

.home-case__pagination-btn {
  appearance: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  /*
   * Cible cliquable ≥ 44 px conservée (WCAG 2.2), la puce visuelle est
   * plus petite et centrée grâce au padding transparent du bouton.
   */
  width: var(--touch-target-min);
  height: var(--touch-target-min);
  padding: 0;
  border: none;
  background: transparent;
  cursor: pointer;
}

.home-case__pagination-btn:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
  border-radius: 50%;
}

.home-case__pagination-dot {
  display: block;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background-color: var(--border-strong);
  transition:
    background-color var(--duration-base) var(--ease-out),
    transform var(--duration-base) var(--ease-out);
}

.home-case__pagination-btn:hover .home-case__pagination-dot {
  background-color: var(--action-primary-hover);
}

.home-case__pagination-btn[aria-current="true"] .home-case__pagination-dot {
  background-color: var(--action-primary);
  transform: scale(1.35);
}

@media (min-width: 768px) {
  .home-case {
    padding-block: var(--space-20);
  }
}

@media (min-width: 1024px) {
  .home-case {
    padding-block: var(--space-24);
  }

  .home-case__composition {
    display: grid;
    grid-template-columns: minmax(0, 55fr) minmax(0, 45fr);
    align-items: center;
    gap: var(--space-12);
  }

  .home-case__content {
    gap: var(--space-5);
  }
}

@media (min-width: 1440px) {
  .home-case__composition {
    gap: var(--space-16);
  }
}
</style>
