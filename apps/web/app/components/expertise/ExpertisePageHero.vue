<script setup lang="ts">
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseEyebrow from "~/components/base/BaseEyebrow.vue"

/**
 * En-tête spécifique aux pages `/expertises/{slug}`.
 *
 * Distinct de `EditorialHero` par deux points :
 *   - il rend une **introduction** narrative sous le H1 (2-3 phrases),
 *     là où EditorialHero attend un `lead` d'une phrase ;
 *   - il n'expose aucun slot `actions` : les CTA sont portés par le
 *     `EditorialCallout` final, pas par le hero.
 *
 * Accessibilité :
 *   - un H1 unique par page (garanti par la route dynamique) ;
 *   - l'`id` du H1 est stable (`expertise-page-hero-title`), ciblé par
 *     `aria-labelledby` sur la `<section>` parente ;
 *   - fond `cream`, pas d'animation.
 */

interface Props {
  eyebrow: string
  title: string
  introduction: string
}

defineProps<Props>()
</script>

<template>
  <section
    class="expertise-page-hero"
    aria-labelledby="expertise-page-hero-title"
  >
    <BaseContainer class="expertise-page-hero__container">
      <BaseEyebrow class="expertise-page-hero__eyebrow">
        {{ eyebrow }}
      </BaseEyebrow>
      <h1
        id="expertise-page-hero-title"
        class="expertise-page-hero__title"
      >
        {{ title }}
      </h1>
      <p class="expertise-page-hero__introduction">
        {{ introduction }}
      </p>
    </BaseContainer>
  </section>
</template>

<style scoped>
.expertise-page-hero {
  background-color: var(--background-primary);
  color: var(--text-primary);
  padding-block: var(--space-12) var(--space-10);
}

.expertise-page-hero__container {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
  max-width: 52rem;
}

.expertise-page-hero__eyebrow {
  margin: 0;
}

.expertise-page-hero__title {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(2rem, 4.4vw, 3rem);
  line-height: 1.12;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
  max-width: 22ch;
}

.expertise-page-hero__introduction {
  font-family: var(--font-family-body);
  font-size: clamp(1rem, 1.4vw, 1.125rem);
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  max-width: 62ch;
}

@media (min-width: 768px) {
  .expertise-page-hero {
    padding-block: var(--space-16) var(--space-12);
  }
}
</style>
