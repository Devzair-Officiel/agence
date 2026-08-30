<script setup lang="ts">
import { computed } from "vue"
import { getMaxInstallments } from "~/config/estimator-payment-options"

const props = defineProps<{
  estimateMaximumMinor: number
}>()

const maxInstallments = computed(() => getMaxInstallments(props.estimateMaximumMinor))
</script>

<template>
  <div class="payment-terms" aria-label="Modalités de paiement indicatives">
    <p class="payment-terms__label">Modalités de règlement</p>
    <p class="payment-terms__text">
      Jusqu'à <strong>{{ maxInstallments }} échéances</strong> possibles selon le périmètre définitif.
    </p>
    <p class="payment-terms__note">
      Le calendrier précis est défini lors du cadrage du projet.
    </p>
  </div>
</template>

<style scoped>
.payment-terms {
  border: 1px solid color-mix(in srgb, var(--color-petrol) 20%, transparent);
  border-radius: var(--radius-md);
  padding: var(--space-4) var(--space-5);
  background: color-mix(in srgb, var(--color-petrol) 4%, var(--color-cream-elevated));
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.payment-terms__label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: color-mix(in srgb, var(--color-ink) 68%, transparent);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin: 0;
}

.payment-terms__text {
  font-size: 0.9375rem;
  color: var(--color-ink);
  margin: 0;
  line-height: 1.5;
}

.payment-terms__note {
  font-size: 0.8125rem;
  color: color-mix(in srgb, var(--color-ink) 60%, transparent);
  font-style: italic;
  margin: 0;
}
</style>
