<script setup lang="ts">
import { ref, onMounted, computed } from "vue"
import type { EstimateApiResponse } from "~/types/estimator-api"
import { formatMoneyRangeMinor } from "~/utils/format-money"
import {
  getLineItemLabel,
  getRecurringItemLabel,
  getAssumptionMessage,
  getRecommendedProjectTypeLabel,
  getPeriodLabel,
} from "~/config/estimate-labels"
import EstimatorPaymentTerms from "~/components/estimator/EstimatorPaymentTerms.vue"

const props = defineProps<{
  result: EstimateApiResponse
  additionalFeatureNote: string
  careAnswered: boolean
  careNeeds: string[]
}>()

const emit = defineEmits<{
  "modify-answers": []
}>()

const heading = ref<HTMLHeadingElement | null>(null)

onMounted(() => {
  heading.value?.focus()
})

const isHumanScoping = computed(
  () => props.result.outcome === "human_scoping_required",
)

const visibleAssumptions = computed(() =>
  props.result.assumptions.filter((a) => a !== "unknown_project_requires_human_scoping"),
)

const hasNote = computed(() => props.additionalFeatureNote.trim() !== "")

const showEmptyRecurringNote = computed(() =>
  props.result.recurringItems.length === 0 &&
  props.careAnswered &&
  props.careNeeds.length === 0,
)
</script>

<template>
  <!-- ─── HUMAN SCOPING ─────────────────────────────────────────────────── -->
  <section v-if="isHumanScoping" class="result" aria-labelledby="result-heading">
    <p class="result__eyebrow">Votre projet</p>

    <h2
      id="result-heading"
      ref="heading"
      class="result__heading"
      tabindex="-1"
    >
      Votre projet mérite un cadrage plus précis
    </h2>

    <p class="result__human-scoping-text">
      Vos réponses montrent que plusieurs besoins se croisent.
      Un échange nous permettra de définir la solution la plus adaptée avant
      de vous proposer une estimation suffisamment fiable.
    </p>

    <div
      v-if="result.recommendedProjectType && result.recommendedProjectType !== 'unknown'"
      class="result__recommended-type"
    >
      Orientation identifiée :
      <strong>{{ getRecommendedProjectTypeLabel(result.recommendedProjectType) }}</strong>
    </div>

    <section
      v-if="visibleAssumptions.length > 0"
      class="result__assumptions"
      aria-label="Points à préciser"
    >
      <h3 class="result__assumptions-title">À préciser ensemble</h3>
      <ul class="result__assumption-list">
        <li
          v-for="code in visibleAssumptions"
          :key="code"
          class="result__assumption-item"
        >
          {{ getAssumptionMessage(code) }}
        </li>
      </ul>
    </section>

    <div v-if="hasNote" class="result__note">
      <p class="result__note-label">Besoin complémentaire mentionné</p>
      <p class="result__note-text">{{ additionalFeatureNote }}</p>
      <p class="result__note-disclaimer">
        Ce besoin n'est pas inclus automatiquement dans la fourchette ci-dessus.
        Nous le préciserons avec vous lors du cadrage.
      </p>
    </div>

    <div class="result__actions">
      <button
        type="button"
        class="result__modify"
        @click="emit('modify-answers')"
      >
        Revoir mes réponses
      </button>
    </div>
  </section>

  <!-- ─── ESTIMATED ─────────────────────────────────────────────────────── -->
  <section v-else class="result" aria-labelledby="result-heading">
    <p class="result__eyebrow">Votre estimation</p>

    <h2
      id="result-heading"
      ref="heading"
      class="result__heading"
      tabindex="-1"
    >
      Une première estimation pour votre projet
    </h2>

    <p class="result__recommended-type">
      Type de projet identifié :
      <strong>{{ getRecommendedProjectTypeLabel(result.recommendedProjectType) }}</strong>
    </p>

    <!-- Fourchette principale -->
    <div class="result__range-block" aria-label="Fourchette budgétaire indicative">
      <p class="result__range">
        {{
          formatMoneyRangeMinor(
            result.estimate.minimum,
            result.estimate.maximum,
            result.estimate.currency,
          )
        }}
      </p>
      <p class="result__range-disclaimer">
        Cette estimation est indicative et ne constitue pas un devis.
        Le budget définitif dépendra du périmètre confirmé et des éventuels besoins
        précisés lors du cadrage du projet.
      </p>
    </div>

    <!-- Investissement initial -->
    <section
      v-if="result.oneOffItems.length > 0"
      class="result__breakdown"
      aria-label="Détail de l'investissement initial"
    >
      <h3 class="result__breakdown-title">Investissement initial</h3>
      <ul class="result__items">
        <li
          v-for="item in result.oneOffItems"
          :key="item.code"
          class="result__item"
        >
          <span class="result__item-label">{{ getLineItemLabel(item.code) }}</span>
          <span class="result__item-amount">
            {{ formatMoneyRangeMinor(item.minimum, item.maximum, item.currency) }}
          </span>
        </li>
      </ul>
    </section>

    <!-- Modalités de paiement -->
    <EstimatorPaymentTerms :estimate-maximum-minor="result.estimate.maximum" />

    <!-- Accompagnement récurrent -->
    <section
      v-if="result.recurringItems.length > 0"
      class="result__recurring"
      aria-label="Accompagnement après lancement"
    >
      <h3 class="result__breakdown-title">Accompagnement après lancement</h3>
      <ul class="result__items">
        <li
          v-for="item in result.recurringItems"
          :key="item.code"
          class="result__item"
        >
          <span class="result__item-label">{{ getRecurringItemLabel(item.code) }}</span>
          <span class="result__item-amount">
            {{ formatMoneyRangeMinor(item.minimum, item.maximum, item.currency) }}
            {{ getPeriodLabel(item.period) }}
          </span>
        </li>
      </ul>
    </section>

    <p v-else-if="showEmptyRecurringNote" class="result__no-recurring">
      Vous avez indiqué préférer gérer le suivi en autonomie.
    </p>

    <!-- Hypothèses / À préciser -->
    <section
      v-if="visibleAssumptions.length > 0"
      class="result__assumptions"
      aria-label="Points à préciser lors du cadrage"
    >
      <h3 class="result__assumptions-title">À préciser ensemble</h3>
      <ul class="result__assumption-list">
        <li
          v-for="code in visibleAssumptions"
          :key="code"
          class="result__assumption-item"
        >
          {{ getAssumptionMessage(code) }}
        </li>
      </ul>
    </section>

    <!-- Besoin complémentaire (jamais chiffré) -->
    <div v-if="hasNote" class="result__note">
      <p class="result__note-label">Besoin complémentaire mentionné</p>
      <p class="result__note-text">{{ additionalFeatureNote }}</p>
      <p class="result__note-disclaimer">
        Ce besoin n'est pas inclus automatiquement dans la fourchette ci-dessus.
        Nous le préciserons avec vous lors du cadrage.
      </p>
    </div>

    <div class="result__actions">
      <button
        type="button"
        class="result__modify"
        @click="emit('modify-answers')"
      >
        Modifier mes réponses
      </button>
    </div>
  </section>
</template>

<style scoped>
.result {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.result__eyebrow {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--color-petrol);
  opacity: 0.7;
  margin: 0;
}

.result__heading {
  font-family: var(--font-family-heading);
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--color-ink);
  margin: 0;
  outline: none;
}

.result__human-scoping-text {
  font-size: 1rem;
  color: color-mix(in srgb, var(--color-ink) 80%, transparent);
  line-height: 1.65;
  margin: 0;
}

.result__recommended-type {
  font-size: 0.9375rem;
  color: color-mix(in srgb, var(--color-ink) 75%, transparent);
  margin: 0;
}

/* Fourchette principale */
.result__range-block {
  background: color-mix(in srgb, var(--color-petrol) 6%, var(--color-cream-elevated));
  border: 1px solid color-mix(in srgb, var(--color-petrol) 16%, transparent);
  border-radius: var(--radius-lg);
  padding: var(--space-5) var(--space-6);
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.result__range {
  font-family: var(--font-family-heading);
  font-size: 2rem;
  font-weight: 700;
  color: var(--color-petrol);
  margin: 0;
  line-height: 1.1;
}

.result__range-disclaimer {
  font-size: 0.8125rem;
  color: color-mix(in srgb, var(--color-ink) 60%, transparent);
  margin: 0;
  font-style: italic;
  line-height: 1.5;
}

/* Titres de section */
.result__breakdown-title {
  font-family: var(--font-family-heading);
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-ink);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0 0 var(--space-3) 0;
}

/* Line items */
.result__items {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.result__item {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: var(--space-4);
  font-size: 0.9375rem;
  color: var(--color-ink);
}

@media (max-width: 480px) {
  .result__item {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--space-1);
  }
}

.result__item-label {
  flex: 1;
}

.result__item-amount {
  font-weight: 600;
  white-space: nowrap;
  color: var(--color-petrol);
}

/* Récurrent séparé visuellement */
.result__recurring {
  border-top: 1px solid color-mix(in srgb, var(--color-ink) 10%, transparent);
  padding-top: var(--space-4);
}

.result__no-recurring {
  font-size: 0.875rem;
  color: color-mix(in srgb, var(--color-ink) 60%, transparent);
  font-style: italic;
  margin: 0;
}

/* Hypothèses */
.result__assumptions {
  border-top: 1px solid color-mix(in srgb, var(--color-ink) 10%, transparent);
  padding-top: var(--space-4);
}

.result__assumptions-title {
  font-size: 0.8125rem;
  font-weight: 600;
  color: color-mix(in srgb, var(--color-ink) 68%, transparent);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0 0 var(--space-3) 0;
}

.result__assumption-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.result__assumption-item {
  font-size: 0.875rem;
  color: color-mix(in srgb, var(--color-ink) 80%, transparent);
  padding-left: var(--space-3);
  position: relative;
  line-height: 1.5;
}

.result__assumption-item::before {
  content: "·";
  position: absolute;
  left: 0;
  color: var(--color-petrol);
  font-weight: 700;
}

/* Note complémentaire */
.result__note {
  background: color-mix(in srgb, var(--color-ink) 4%, var(--color-cream-elevated));
  border: 1px solid color-mix(in srgb, var(--color-ink) 10%, transparent);
  border-radius: var(--radius-md);
  padding: var(--space-4) var(--space-5);
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.result__note-label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: color-mix(in srgb, var(--color-ink) 68%, transparent);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin: 0;
}

.result__note-text {
  font-size: 0.9375rem;
  color: var(--color-ink);
  margin: 0;
  line-height: 1.55;
}

.result__note-disclaimer {
  font-size: 0.8125rem;
  color: color-mix(in srgb, var(--color-ink) 60%, transparent);
  font-style: italic;
  margin: 0;
  line-height: 1.5;
}

/* Actions */
.result__actions {
  display: flex;
  gap: var(--space-3);
  flex-wrap: wrap;
}

.result__modify {
  align-self: flex-start;
  background: none;
  border: 1px solid color-mix(in srgb, var(--color-petrol) 40%, transparent);
  border-radius: var(--radius-md);
  padding: var(--space-2) var(--space-4);
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  font-weight: 500;
  color: var(--color-petrol);
  cursor: pointer;
  transition: background var(--duration-base) var(--ease-out),
    border-color var(--duration-base) var(--ease-out);
}

.result__modify:hover {
  background: color-mix(in srgb, var(--color-petrol) 8%, transparent);
  border-color: var(--color-petrol);
}

.result__modify:focus-visible {
  outline: 2px solid var(--focus-ring);
  outline-offset: 2px;
}

@media (prefers-reduced-motion: reduce) {
  .result__modify {
    transition: none;
  }
}
</style>
