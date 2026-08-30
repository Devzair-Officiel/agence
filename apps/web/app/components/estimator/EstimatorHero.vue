<script setup lang="ts">
// Compact intro hero — not a full marketing hero.
// Appears immediately in viewport on load.
</script>

<template>
  <section class="estimator-hero" aria-labelledby="estimator-hero-title">
    <BaseContainer width="wide">
      <BaseEyebrow>Configurateur de projet</BaseEyebrow>
      <h1 id="estimator-hero-title" class="estimator-hero__title">
        Estimez votre projet digital
      </h1>
      <p class="estimator-hero__subtitle">
        Répondez à quelques questions et recevez une première fourchette
        budgétaire. Sans engagement, en moins de 3 minutes.
      </p>
    </BaseContainer>
  </section>
</template>

<style scoped>
.estimator-hero {
  background: var(--color-navy);
  padding-block: var(--space-10) var(--space-8);
}

.estimator-hero :deep(.base-eyebrow) {
  color: color-mix(in srgb, var(--color-sand) 70%, transparent);
  margin-bottom: var(--space-4);
}

.estimator-hero__title {
  font-family: var(--font-family-heading);
  font-size: clamp(1.75rem, 4vw, 2.5rem);
  font-weight: 700;
  color: #fff;
  line-height: 1.15;
  margin: 0 0 var(--space-4);
  max-width: 32ch;
}

.estimator-hero__subtitle {
  font-size: clamp(0.9375rem, 1.5vw, 1.0625rem);
  color: color-mix(in srgb, #fff 75%, transparent);
  line-height: 1.6;
  margin: 0;
  max-width: 52ch;
}
</style>
