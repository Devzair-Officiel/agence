<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * « Prestations » de la page Visibilité — composition « couches / calques
 * de visibilité ».
 *
 * Rend les cinq livrables issus de `expertise-pages.ts`, verbatim, comme
 * cinq couches empilées d'un plan technique. Chaque couche évoque un
 * type d'intervention différent :
 *   01 Technique — audit SVG grille + coordonnées ;
 *   02 Page      — grille de pages avec repères ;
 *   03 Local     — plan avec point + rayonnement ;
 *   04 Contenu   — timeline abstraite de production ;
 *   05 Suivi     — courbes de mesure abstraites (sans chiffres).
 *
 * Sémantique : liste ordonnée `<ol>`. L'ordre reflète la séquence de
 * livraison retenue (technique → page → local → contenu → suivi).
 */

interface Props {
  deliverables: readonly string[]
}

defineProps<Props>()

const kinds = [
  "technique",
  "page",
  "local",
  "content",
  "tracking",
] as const

const tags: Record<(typeof kinds)[number], string> = {
  technique: "Technique",
  page: "Page",
  local: "Local",
  content: "Contenu",
  tracking: "Suivi",
}

const generatedId = useId()
const titleId = computed(() => `visibilite-layers-title-${generatedId}`)
</script>

<template>
  <section
    class="visibilite-layers"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="visibilite-layers__container">
      <header class="visibilite-layers__header">
        <BaseEyebrow class="visibilite-layers__eyebrow">
          Prestations
        </BaseEyebrow>
        <h2 :id="titleId" class="visibilite-layers__title">
          Cinq couches de visibilité.
        </h2>
      </header>

      <ol class="visibilite-layers__stack" role="list">
        <li
          v-for="(deliverable, index) in deliverables"
          :key="deliverable"
          class="visibilite-layers__layer"
          :data-position="index + 1"
          :data-kind="kinds[index] ?? 'technique'"
        >
          <div class="visibilite-layers__meta">
            <span class="visibilite-layers__index" aria-hidden="true">
              {{ String(index + 1).padStart(2, "0") }}
            </span>
            <span class="visibilite-layers__tag" aria-hidden="true">
              {{ tags[kinds[index] ?? "technique"] }}
            </span>
          </div>

          <p class="visibilite-layers__label">{{ deliverable }}</p>

          <div class="visibilite-layers__decor" aria-hidden="true">
            <svg
              v-if="(kinds[index] ?? 'technique') === 'technique'"
              viewBox="0 0 120 80"
              class="visibilite-layers__decor-svg"
              role="presentation"
              focusable="false"
              aria-hidden="true"
            >
              <line x1="0" y1="20" x2="120" y2="20" stroke="#0c5b57" stroke-width="0.7" stroke-dasharray="3 4" opacity="0.5" />
              <line x1="0" y1="40" x2="120" y2="40" stroke="#0c5b57" stroke-width="0.7" stroke-dasharray="3 4" opacity="0.5" />
              <line x1="0" y1="60" x2="120" y2="60" stroke="#0c5b57" stroke-width="0.7" stroke-dasharray="3 4" opacity="0.5" />
              <line x1="30" y1="0" x2="30" y2="80" stroke="#0c5b57" stroke-width="0.7" stroke-dasharray="3 4" opacity="0.35" />
              <line x1="60" y1="0" x2="60" y2="80" stroke="#0c5b57" stroke-width="0.7" stroke-dasharray="3 4" opacity="0.35" />
              <line x1="90" y1="0" x2="90" y2="80" stroke="#0c5b57" stroke-width="0.7" stroke-dasharray="3 4" opacity="0.35" />
              <circle cx="30" cy="40" r="3" fill="#0c5b57" />
              <circle cx="60" cy="20" r="3" fill="#0c5b57" />
              <circle cx="90" cy="60" r="3" fill="#0c5b57" />
              <path d="M30 40 L60 20 L90 60" stroke="#0c5b57" stroke-width="1" fill="none" />
            </svg>

            <svg
              v-else-if="(kinds[index] ?? 'technique') === 'page'"
              viewBox="0 0 120 80"
              class="visibilite-layers__decor-svg"
              role="presentation"
              focusable="false"
              aria-hidden="true"
            >
              <rect x="0" y="6" width="48" height="6" fill="#0c5b57" opacity="0.85" />
              <rect x="0" y="18" width="72" height="3" fill="#16191c" opacity="0.6" />
              <rect x="0" y="26" width="90" height="3" fill="#16191c" opacity="0.45" />
              <rect x="0" y="34" width="70" height="3" fill="#16191c" opacity="0.45" />
              <rect x="0" y="46" width="80" height="3" fill="#16191c" opacity="0.45" />
              <rect x="0" y="54" width="60" height="3" fill="#16191c" opacity="0.45" />
              <rect x="0" y="66" width="40" height="6" fill="none" stroke="#0c5b57" stroke-width="1" />
            </svg>

            <svg
              v-else-if="(kinds[index] ?? 'technique') === 'local'"
              viewBox="0 0 120 80"
              class="visibilite-layers__decor-svg"
              role="presentation"
              focusable="false"
              aria-hidden="true"
            >
              <circle cx="60" cy="40" r="34" fill="none" stroke="#0c5b57" stroke-width="0.8" opacity="0.35" />
              <circle cx="60" cy="40" r="22" fill="none" stroke="#0c5b57" stroke-width="0.8" opacity="0.55" />
              <circle cx="60" cy="40" r="10" fill="none" stroke="#0c5b57" stroke-width="1" opacity="0.9" />
              <circle cx="60" cy="40" r="3" fill="#0c5b57" />
              <line x1="0" y1="40" x2="120" y2="40" stroke="#0c5b57" stroke-width="0.5" stroke-dasharray="3 4" opacity="0.35" />
              <line x1="60" y1="0" x2="60" y2="80" stroke="#0c5b57" stroke-width="0.5" stroke-dasharray="3 4" opacity="0.35" />
              <text x="94" y="10" font-family="Space Mono, monospace" font-size="7" fill="#0c5b57" opacity="0.7">NAP</text>
            </svg>

            <svg
              v-else-if="(kinds[index] ?? 'technique') === 'content'"
              viewBox="0 0 120 80"
              class="visibilite-layers__decor-svg"
              role="presentation"
              focusable="false"
              aria-hidden="true"
            >
              <line x1="0" y1="40" x2="120" y2="40" stroke="#0c5b57" stroke-width="0.7" opacity="0.4" />
              <line x1="15" y1="30" x2="15" y2="50" stroke="#0c5b57" stroke-width="1.2" />
              <line x1="40" y1="30" x2="40" y2="50" stroke="#0c5b57" stroke-width="1.2" />
              <line x1="65" y1="30" x2="65" y2="50" stroke="#0c5b57" stroke-width="1.2" />
              <line x1="90" y1="30" x2="90" y2="50" stroke="#0c5b57" stroke-width="1.2" />
              <rect x="8" y="15" width="14" height="12" fill="none" stroke="#0c5b57" opacity="0.7" />
              <rect x="33" y="55" width="14" height="12" fill="none" stroke="#0c5b57" opacity="0.7" />
              <rect x="58" y="15" width="14" height="12" fill="none" stroke="#0c5b57" opacity="0.7" />
              <rect x="83" y="55" width="14" height="12" fill="none" stroke="#0c5b57" opacity="0.7" />
              <text x="4" y="76" font-family="Space Mono, monospace" font-size="7" fill="#0c5b57" opacity="0.6">T1 · T2 · T3 · T4</text>
            </svg>

            <svg
              v-else
              viewBox="0 0 120 80"
              class="visibilite-layers__decor-svg"
              role="presentation"
              focusable="false"
              aria-hidden="true"
            >
              <path
                d="M0 60 Q20 55 30 45 T60 30 T90 25 T120 15"
                fill="none"
                stroke="#0c5b57"
                stroke-width="1.4"
              />
              <path
                d="M0 65 Q20 62 30 55 T60 45 T90 40 T120 30"
                fill="none"
                stroke="#0c5b57"
                stroke-width="0.8"
                opacity="0.5"
                stroke-dasharray="3 3"
              />
              <line x1="0" y1="75" x2="120" y2="75" stroke="#16191c" stroke-width="0.5" opacity="0.3" />
              <line x1="0" y1="0" x2="0" y2="80" stroke="#16191c" stroke-width="0.5" opacity="0.3" />
              <circle cx="30" cy="45" r="2" fill="#0c5b57" />
              <circle cx="60" cy="30" r="2" fill="#0c5b57" />
              <circle cx="90" cy="25" r="2" fill="#0c5b57" />
              <text x="90" y="12" font-family="Space Mono, monospace" font-size="7" fill="#0c5b57" opacity="0.7">t · n</text>
            </svg>
          </div>
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
.visibilite-layers {
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-14);
}

.visibilite-layers__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.visibilite-layers__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 46rem;
}

.visibilite-layers__eyebrow {
  margin: 0;
}

.visibilite-layers__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.625rem, 3.4vw, 2.25rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 26ch;
}

/*
 * Mobile : pile pleine largeur. Tablette : 2 colonnes. Desktop :
 * composition asymétrique 12 col — couches dominantes (01/03/05) et
 * couches périphériques (02/04) alternent.
 */
.visibilite-layers__stack {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
}

.visibilite-layers__layer {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-6);
  border: 1px solid var(--border-default);
  background-color: var(--color-cream-elevated);
}

.visibilite-layers__meta {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: var(--space-3);
}

.visibilite-layers__index {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.10em;
  color: var(--color-petrol);
}

.visibilite-layers__tag {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.625rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-petrol);
}

.visibilite-layers__label {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1.0625rem;
  line-height: 1.35;
  color: var(--text-primary);
  margin: 0;
  max-width: 34ch;
}

.visibilite-layers__decor {
  margin-top: auto;
  padding-top: var(--space-3);
  border-top: 1px dotted var(--border-default);
}

.visibilite-layers__decor-svg {
  width: 100%;
  height: 5rem;
  display: block;
  color: var(--color-petrol);
}

@media (min-width: 720px) {
  .visibilite-layers__stack {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-5);
  }
}

/*
 * Desktop 12 col :
 *   - 01 Technique : 1 / 7  (dominant gauche)
 *   - 02 Page      : 7 / 6  (bande médiane)
 *   - 03 Local     : 2 / 5  (indenté gauche)
 *   - 04 Contenu   : 7 / 6  (bande médiane décalée)
 *   - 05 Suivi     : 4 / 6  (centré en pied)
 */
@media (min-width: 1024px) {
  .visibilite-layers {
    padding-block: var(--space-16) var(--space-16);
  }

  .visibilite-layers__stack {
    grid-template-columns: repeat(12, minmax(0, 1fr));
    grid-auto-rows: minmax(220px, auto);
    gap: var(--space-5);
  }

  .visibilite-layers__layer[data-position="1"] {
    grid-column: 1 / span 6;
    grid-row: 1;
  }
  .visibilite-layers__layer[data-position="2"] {
    grid-column: 7 / span 6;
    grid-row: 1;
  }
  .visibilite-layers__layer[data-position="3"] {
    grid-column: 2 / span 5;
    grid-row: 2;
  }
  .visibilite-layers__layer[data-position="4"] {
    grid-column: 7 / span 6;
    grid-row: 2;
  }
  .visibilite-layers__layer[data-position="5"] {
    grid-column: 4 / span 6;
    grid-row: 3;
  }

  .visibilite-layers__layer[data-position="1"] .visibilite-layers__label,
  .visibilite-layers__layer[data-position="4"] .visibilite-layers__label {
    font-size: 1.1875rem;
    max-width: 28ch;
  }
}
</style>
