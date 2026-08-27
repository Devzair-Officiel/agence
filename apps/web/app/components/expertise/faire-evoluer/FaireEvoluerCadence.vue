<script setup lang="ts">
import { computed, useId } from "vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * Section « Prestations » de la page Faire évoluer — cinq livrables rendus
 * comme cinq cadences différentes plutôt que comme cinq cartes SaaS.
 *
 * Direction éditoriale :
 *   - fond cream (bloc clair après la bande sombre `ContinuousLoop`) ;
 *   - chaque livrable est associé à un rythme court (CONTINU, MESURÉ,
 *     CYCLE COURT, PRIORISÉ, POINT DE SITUATION) et à une signature SVG
 *     décorative — jamais des chiffres, jamais de dates concrètes ;
 *   - le mot « trimestriel » apparaît uniquement au sein du livrable
 *     exact (« Point trimestriel sur l'état du site »), pas comme label
 *     autonome ;
 *   - pas de fake dashboard, pas de fausse timeline, pas de tickets.
 *
 * Contenu :
 *   - eyebrow verbatim : « Prestations » ;
 *   - H2 validé : « Un rythme de maintenance, pas un grand chantier
 *     annuel. » ;
 *   - cinq `deliverables` verbatim depuis `expertise-pages.ts`.
 *
 * Accessibilité : les motifs de cadence sont `aria-hidden`. Le rythme est
 * verbalisé par le label court en amont de chaque livrable.
 */

interface Props {
  deliverables: readonly string[]
}

defineProps<Props>()

interface CadenceEntry {
  readonly key: string
  readonly label: string
  readonly pattern: "continu" | "mesure" | "cycle-court" | "priorise" | "situation"
}

const CADENCE_ENTRIES: readonly CadenceEntry[] = [
  { key: "01", label: "Continu", pattern: "continu" },
  { key: "02", label: "Mesuré", pattern: "mesure" },
  { key: "03", label: "Cycle court", pattern: "cycle-court" },
  { key: "04", label: "Priorisé", pattern: "priorise" },
  { key: "05", label: "Point de situation", pattern: "situation" },
]

const generatedId = useId()
const titleId = computed(() => `faire-evoluer-cadence-title-${generatedId}`)
</script>

<template>
  <section
    class="faire-evoluer-cadence"
    :aria-labelledby="titleId"
  >
    <BaseContainer width="wide" class="faire-evoluer-cadence__container">
      <header class="faire-evoluer-cadence__header">
        <BaseEyebrow class="faire-evoluer-cadence__eyebrow">
          Prestations
        </BaseEyebrow>
        <h2 :id="titleId" class="faire-evoluer-cadence__title">
          Un rythme de maintenance, pas un grand chantier annuel.
        </h2>
        <p class="faire-evoluer-cadence__lead">
          Cinq livrables articulés autour de rythmes distincts. Chacun
          adopte la cadence la plus utile — flux continu, mesure régulière,
          cycle court, priorisation partagée ou point périodique.
        </p>
      </header>

      <ol class="faire-evoluer-cadence__list" role="list">
        <li
          v-for="(deliverable, index) in deliverables"
          :key="deliverable"
          class="faire-evoluer-cadence__item"
          :data-position="index + 1"
        >
          <div class="faire-evoluer-cadence__meta">
            <p class="faire-evoluer-cadence__item-index" aria-hidden="true">
              {{ CADENCE_ENTRIES[index]?.key ?? "" }}
            </p>
            <p class="faire-evoluer-cadence__item-label" aria-hidden="true">
              {{ CADENCE_ENTRIES[index]?.label ?? "" }}
            </p>
          </div>

          <!--
            Signature SVG décorative propre à chaque cadence. Aucune donnée
            métrique, aucune graduation temporelle réelle : uniquement des
            primitives (trait, points, arcs) qui *évoquent* le rythme.
          -->
          <svg
            class="faire-evoluer-cadence__pattern"
            viewBox="0 0 200 40"
            role="presentation"
            focusable="false"
            aria-hidden="true"
            preserveAspectRatio="none"
          >
            <template v-if="CADENCE_ENTRIES[index]?.pattern === 'continu'">
              <line x1="4" y1="20" x2="196" y2="20" stroke="#0c5b57" stroke-width="1.6" stroke-linecap="round" />
              <circle cx="4" cy="20" r="2.5" fill="#0c5b57" />
              <circle cx="196" cy="20" r="2.5" fill="#0c5b57" />
            </template>
            <template v-else-if="CADENCE_ENTRIES[index]?.pattern === 'mesure'">
              <line x1="4" y1="20" x2="196" y2="20" stroke="#0c5b57" stroke-width="1" opacity="0.35" />
              <g fill="#0c5b57">
                <circle cx="14" cy="20" r="2.5" />
                <circle cx="44" cy="20" r="2.5" />
                <circle cx="74" cy="20" r="2.5" />
                <circle cx="104" cy="20" r="2.5" />
                <circle cx="134" cy="20" r="2.5" />
                <circle cx="164" cy="20" r="2.5" />
                <circle cx="194" cy="20" r="2.5" />
              </g>
            </template>
            <template v-else-if="CADENCE_ENTRIES[index]?.pattern === 'cycle-court'">
              <g fill="none" stroke="#0c5b57" stroke-width="1.5" stroke-linecap="round">
                <path d="M 8 20 L 34 20" />
                <path d="M 44 20 L 70 20" />
                <path d="M 80 20 L 106 20" />
                <path d="M 116 20 L 142 20" />
                <path d="M 152 20 L 178 20" />
              </g>
              <g fill="#0c5b57" opacity="0.6">
                <circle cx="39" cy="20" r="1.6" />
                <circle cx="75" cy="20" r="1.6" />
                <circle cx="111" cy="20" r="1.6" />
                <circle cx="147" cy="20" r="1.6" />
              </g>
            </template>
            <template v-else-if="CADENCE_ENTRIES[index]?.pattern === 'priorise'">
              <line x1="4" y1="20" x2="196" y2="20" stroke="#0c5b57" stroke-width="1" opacity="0.3" />
              <g fill="#0c5b57">
                <circle cx="24" cy="20" r="5" />
                <circle cx="80" cy="20" r="3.5" opacity="0.75" />
                <circle cx="128" cy="20" r="2.5" opacity="0.55" />
                <circle cx="172" cy="20" r="1.8" opacity="0.35" />
              </g>
            </template>
            <template v-else-if="CADENCE_ENTRIES[index]?.pattern === 'situation'">
              <line x1="4" y1="20" x2="196" y2="20" stroke="#0c5b57" stroke-width="1" opacity="0.35" />
              <g fill="none" stroke="#0c5b57" stroke-width="1.4" stroke-linecap="round">
                <path d="M 24 26 L 24 14" />
                <path d="M 100 26 L 100 14" />
                <path d="M 176 26 L 176 14" />
              </g>
              <g fill="#0c5b57">
                <circle cx="24" cy="20" r="2.5" />
                <circle cx="100" cy="20" r="2.5" />
                <circle cx="176" cy="20" r="2.5" />
              </g>
            </template>
          </svg>

          <p class="faire-evoluer-cadence__item-text">
            {{ deliverable }}
          </p>
        </li>
      </ol>
    </BaseContainer>
  </section>
</template>

<style scoped>
.faire-evoluer-cadence {
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-14) var(--space-14);
}

.faire-evoluer-cadence__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-10);
}

.faire-evoluer-cadence__header {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  max-width: 52rem;
}

.faire-evoluer-cadence__eyebrow {
  margin: 0;
}

.faire-evoluer-cadence__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.625rem, 3.4vw, 2.25rem);
  line-height: 1.15;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 26ch;
}

.faire-evoluer-cadence__lead {
  font-family: var(--font-family-body);
  font-size: 1rem;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
  max-width: 60ch;
}

.faire-evoluer-cadence__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0;
  border-top: 1px solid var(--border-default);
}

.faire-evoluer-cadence__item {
  display: grid;
  grid-template-columns: minmax(0, 1fr);
  gap: var(--space-3);
  padding-block: var(--space-6);
  border-bottom: 1px solid var(--border-default);
}

.faire-evoluer-cadence__meta {
  display: flex;
  align-items: baseline;
  gap: var(--space-3);
}

.faire-evoluer-cadence__item-index {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.75rem;
  letter-spacing: 0.10em;
  color: var(--color-petrol);
  margin: 0;
}

.faire-evoluer-cadence__item-label {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.8125rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--color-petrol);
  margin: 0;
}

.faire-evoluer-cadence__pattern {
  width: 100%;
  max-width: 260px;
  height: 24px;
  display: block;
}

.faire-evoluer-cadence__item-text {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: clamp(1.0625rem, 1.6vw, 1.25rem);
  line-height: 1.35;
  color: var(--text-primary);
  margin: 0;
  max-width: 44ch;
}

@media (min-width: 768px) {
  .faire-evoluer-cadence {
    padding-block: var(--space-16) var(--space-16);
  }
}

/*
 * Desktop : chaque livrable devient une bande horizontale — meta à gauche,
 * signature de cadence au centre, texte à droite. Le rythme se lit en une
 * ligne, comme une partition.
 */
@media (min-width: 1024px) {
  .faire-evoluer-cadence__item {
    grid-template-columns: minmax(0, 220px) minmax(0, 260px) minmax(0, 1fr);
    align-items: center;
    gap: var(--space-6);
    padding-block: var(--space-8);
  }

  .faire-evoluer-cadence__meta {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--space-1);
  }

  .faire-evoluer-cadence__pattern {
    max-width: 260px;
  }
}
</style>
