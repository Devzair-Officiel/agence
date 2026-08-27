<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * « Prestations » de la page Valoriser — composition « Content Kit /
 * table de studio ».
 *
 * Rend les cinq livrables issus de `expertise-pages.ts`, verbatim, dans
 * une composition asymétrique volontairement non SaaS. Chaque module
 * évoque un format éditorial différent via un motif SVG :
 *   01 Photographie → cadre + crop marks (image en cadrage) ;
 *   02 Textes       → lignes typographiques empilées ;
 *   03 Structuration→ mini grille de mise en page ;
 *   04 Bibliothèque → empilement de cadres ;
 *   05 Guide        → repères / index numéroté.
 *
 * Sémantique : liste ordonnée `<ol>` — l'ordre reflète la séquence de
 * livraison retenue par le brief (image → texte → structure → réutilisation
 * → référence).
 */

interface Props {
  deliverables: readonly string[]
}

defineProps<Props>()

const kinds = [
  "photo",
  "text",
  "structure",
  "library",
  "guide",
] as const

const tags: Record<(typeof kinds)[number], string> = {
  photo: "Image",
  text: "Rédaction",
  structure: "Mise en page",
  library: "Réutilisable",
  guide: "Référence",
}

const generatedId = useId()
const titleId = computed(() => `valoriser-kit-title-${generatedId}`)
</script>

<template>
  <section
    class="valoriser-kit"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="valoriser-kit__container">
      <header class="valoriser-kit__header">
        <BaseEyebrow class="valoriser-kit__eyebrow">
          Prestations
        </BaseEyebrow>
        <h2 :id="titleId" class="valoriser-kit__title">
          Un ensemble de contenus prêt à être utilisé.
        </h2>
      </header>

      <ol class="valoriser-kit__board" role="list">
        <li
          v-for="(deliverable, index) in deliverables"
          :key="deliverable"
          class="valoriser-kit__module"
          :data-position="index + 1"
          :data-kind="kinds[index] ?? 'photo'"
        >
          <div class="valoriser-kit__meta">
            <span class="valoriser-kit__index" aria-hidden="true">
              {{ String(index + 1).padStart(2, "0") }}
            </span>
            <span class="valoriser-kit__tag" aria-hidden="true">
              {{ tags[kinds[index] ?? "photo"] }}
            </span>
          </div>

          <p class="valoriser-kit__label">{{ deliverable }}</p>

          <div class="valoriser-kit__decor" aria-hidden="true">
            <!-- Motif dédié par type de livrable. -->
            <svg
              v-if="(kinds[index] ?? 'photo') === 'photo'"
              viewBox="0 0 120 80"
              class="valoriser-kit__decor-svg"
              role="presentation"
              focusable="false"
              aria-hidden="true"
            >
              <rect x="8" y="8" width="104" height="64" fill="none" stroke="#0c5b57" stroke-width="1" />
              <line x1="8" y1="8" x2="20" y2="8" stroke="#0c5b57" stroke-width="2" />
              <line x1="8" y1="8" x2="8" y2="20" stroke="#0c5b57" stroke-width="2" />
              <line x1="112" y1="8" x2="100" y2="8" stroke="#0c5b57" stroke-width="2" />
              <line x1="112" y1="8" x2="112" y2="20" stroke="#0c5b57" stroke-width="2" />
              <line x1="8" y1="72" x2="20" y2="72" stroke="#0c5b57" stroke-width="2" />
              <line x1="8" y1="72" x2="8" y2="60" stroke="#0c5b57" stroke-width="2" />
              <line x1="112" y1="72" x2="100" y2="72" stroke="#0c5b57" stroke-width="2" />
              <line x1="112" y1="72" x2="112" y2="60" stroke="#0c5b57" stroke-width="2" />
              <circle cx="60" cy="40" r="3" fill="#0c5b57" />
            </svg>

            <svg
              v-else-if="(kinds[index] ?? 'photo') === 'text'"
              viewBox="0 0 120 80"
              class="valoriser-kit__decor-svg"
              role="presentation"
              focusable="false"
              aria-hidden="true"
            >
              <rect x="0" y="10" width="120" height="4" fill="#16191c" opacity="0.85" />
              <rect x="0" y="24" width="96" height="3" fill="#16191c" opacity="0.55" />
              <rect x="0" y="34" width="108" height="3" fill="#16191c" opacity="0.55" />
              <rect x="0" y="44" width="80" height="3" fill="#16191c" opacity="0.55" />
              <rect x="0" y="60" width="120" height="3" fill="#16191c" opacity="0.55" />
              <rect x="0" y="70" width="60" height="3" fill="#16191c" opacity="0.55" />
            </svg>

            <svg
              v-else-if="(kinds[index] ?? 'photo') === 'structure'"
              viewBox="0 0 120 80"
              class="valoriser-kit__decor-svg"
              role="presentation"
              focusable="false"
              aria-hidden="true"
            >
              <rect x="0" y="0" width="52" height="20" fill="none" stroke="#0c5b57" />
              <rect x="60" y="0" width="60" height="20" fill="none" stroke="#0c5b57" />
              <rect x="0" y="28" width="36" height="52" fill="none" stroke="#0c5b57" />
              <rect x="44" y="28" width="76" height="24" fill="none" stroke="#0c5b57" />
              <rect x="44" y="60" width="76" height="20" fill="none" stroke="#0c5b57" />
            </svg>

            <svg
              v-else-if="(kinds[index] ?? 'photo') === 'library'"
              viewBox="0 0 120 80"
              class="valoriser-kit__decor-svg"
              role="presentation"
              focusable="false"
              aria-hidden="true"
            >
              <rect x="8" y="12" width="46" height="56" fill="none" stroke="#0c5b57" />
              <rect x="20" y="22" width="46" height="56" fill="none" stroke="#0c5b57" />
              <rect x="32" y="4" width="46" height="56" fill="none" stroke="#0c5b57" />
              <rect x="60" y="30" width="46" height="46" fill="none" stroke="#0c5b57" />
            </svg>

            <svg
              v-else
              viewBox="0 0 120 80"
              class="valoriser-kit__decor-svg"
              role="presentation"
              focusable="false"
              aria-hidden="true"
            >
              <line x1="0" y1="14" x2="120" y2="14" stroke="#0c5b57" stroke-width="1" />
              <line x1="0" y1="30" x2="120" y2="30" stroke="#0c5b57" stroke-width="1" opacity="0.5" />
              <line x1="0" y1="46" x2="120" y2="46" stroke="#0c5b57" stroke-width="1" opacity="0.5" />
              <line x1="0" y1="62" x2="120" y2="62" stroke="#0c5b57" stroke-width="1" opacity="0.5" />
              <text x="4" y="24" font-family="Space Mono, monospace" font-size="8" fill="#0c5b57" font-weight="700">§01</text>
              <text x="4" y="40" font-family="Space Mono, monospace" font-size="8" fill="#0c5b57" font-weight="700" opacity="0.7">§02</text>
              <text x="4" y="56" font-family="Space Mono, monospace" font-size="8" fill="#0c5b57" font-weight="700" opacity="0.7">§03</text>
              <text x="4" y="72" font-family="Space Mono, monospace" font-size="8" fill="#0c5b57" font-weight="700" opacity="0.7">§04</text>
            </svg>
          </div>
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
.valoriser-kit {
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-14);
}

.valoriser-kit__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.valoriser-kit__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 46rem;
}

.valoriser-kit__eyebrow {
  margin: 0;
}

.valoriser-kit__title {
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
 * Mobile : pile pleine largeur. Tablette : 2 colonnes symétriques.
 * Desktop : composition table de studio — modules 1 et 4 dominants,
 * modules 2/3/5 plus resserrés en périphérie, indentations décalées.
 */
.valoriser-kit__board {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-4);
}

.valoriser-kit__module {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-6);
  border: 1px solid var(--border-default);
  background-color: var(--color-cream-elevated);
}

.valoriser-kit__meta {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: var(--space-3);
}

.valoriser-kit__index {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.10em;
  color: var(--color-petrol);
}

.valoriser-kit__tag {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.625rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-petrol);
}

.valoriser-kit__label {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1.0625rem;
  line-height: 1.35;
  color: var(--text-primary);
  margin: 0;
  max-width: 34ch;
}

.valoriser-kit__decor {
  margin-top: auto;
  padding-top: var(--space-3);
  border-top: 1px dotted var(--border-default);
}

.valoriser-kit__decor-svg {
  width: 100%;
  height: 5rem;
  display: block;
  color: var(--color-petrol);
}

/* Tablette : 2 colonnes. */
@media (min-width: 720px) {
  .valoriser-kit__board {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-5);
  }
}

/*
 * Desktop : composition asymétrique éditoriale. On rejette une grille
 * régulière — modules dominants et périphériques alternent.
 *
 * Grille 12 colonnes :
 *   - 01 Photographie : 1 / 7 (dominant gauche)
 *   - 02 Textes       : 7 / 6 (bande médiane)
 *   - 03 Structuration: 2 / 5 (indentation gauche)
 *   - 04 Bibliothèque : 7 / 6 (bande médiane décalée)
 *   - 05 Guide        : 4 / 6 (centrée en pied)
 */
@media (min-width: 1024px) {
  .valoriser-kit {
    padding-block: var(--space-16) var(--space-16);
  }

  .valoriser-kit__board {
    grid-template-columns: repeat(12, minmax(0, 1fr));
    grid-auto-rows: minmax(220px, auto);
    gap: var(--space-5);
  }

  .valoriser-kit__module[data-position="1"] {
    grid-column: 1 / span 6;
    grid-row: 1;
  }
  .valoriser-kit__module[data-position="2"] {
    grid-column: 7 / span 6;
    grid-row: 1;
  }
  .valoriser-kit__module[data-position="3"] {
    grid-column: 2 / span 5;
    grid-row: 2;
  }
  .valoriser-kit__module[data-position="4"] {
    grid-column: 7 / span 6;
    grid-row: 2;
  }
  .valoriser-kit__module[data-position="5"] {
    grid-column: 4 / span 6;
    grid-row: 3;
  }

  .valoriser-kit__module[data-position="1"] .valoriser-kit__label,
  .valoriser-kit__module[data-position="4"] .valoriser-kit__label {
    font-size: 1.1875rem;
    max-width: 28ch;
  }
}
</style>
