<script setup lang="ts">
defineProps<{
  currentStep: number
  canGoNext: boolean
  isLastStep?: boolean
  isSubmitting?: boolean
}>()

const emit = defineEmits<{
  prev: []
  next: []
}>()
</script>

<template>
  <div class="estimator-nav">
    <BaseButton
      v-if="currentStep > 1"
      variant="secondary"
      type="button"
      class="estimator-nav__back"
      :disabled="isSubmitting"
      @click="emit('prev')"
    >
      ← Retour
    </BaseButton>
    <span v-else class="estimator-nav__spacer" aria-hidden="true" />
    <BaseButton
      variant="primary"
      type="button"
      class="estimator-nav__next"
      :disabled="!canGoNext || isSubmitting"
      :loading="isSubmitting"
      @click="emit('next')"
    >
      {{ isLastStep ? 'Voir mon estimation' : 'Continuer →' }}
    </BaseButton>
  </div>
</template>

<style scoped>
.estimator-nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-4);
  padding-top: var(--space-6);
  border-top: 1px solid color-mix(in srgb, var(--color-ink) 10%, transparent);
}

.estimator-nav__spacer {
  display: block;
  min-width: 1px;
}

.estimator-nav__next {
  margin-left: auto;
}
</style>
