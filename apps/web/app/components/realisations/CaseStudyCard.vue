<script setup lang="ts">
import type { CaseStudy } from "~/config/case-studies"

interface Props {
  study: CaseStudy
}

defineProps<Props>()
</script>

<template>
  <article class="cs-card">
    <NuxtLink
      :to="study.route"
      class="cs-card__link"
      :aria-label="`Voir l'étude de cas ${study.name}`"
    >
      <div class="cs-card__visual">
        <img
          :src="study.imageSrc"
          :alt="study.imageAlt"
          class="cs-card__img"
          loading="lazy"
          decoding="async"
          width="800"
          height="500"
        >
      </div>
      <div class="cs-card__body">
        <p class="cs-card__category">{{ study.category }}</p>
        <h2 class="cs-card__name">{{ study.name }}</h2>
        <p class="cs-card__description">{{ study.description }}</p>
        <ul class="cs-card__tags" aria-label="Interventions">
          <li
            v-for="tag in study.tags"
            :key="tag"
            class="cs-card__tag"
          >
            {{ tag }}
          </li>
        </ul>
        <span class="cs-card__cta" aria-hidden="true">
          Voir l'étude de cas →
        </span>
      </div>
    </NuxtLink>
  </article>
</template>

<style scoped>
.cs-card {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.cs-card__link {
  display: flex;
  flex-direction: column;
  height: 100%;
  background-color: var(--background-secondary);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg);
  overflow: hidden;
  text-decoration: none;
  color: inherit;
  transition:
    border-color var(--duration-base) var(--ease-out),
    box-shadow var(--duration-base) var(--ease-out),
    transform var(--duration-base) var(--ease-out);
}

.cs-card__link:hover {
  border-color: var(--color-petrol);
  box-shadow: 0 4px 20px rgba(12, 91, 87, 0.1);
  transform: translateY(-2px);
}

.cs-card__link:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
}

.cs-card__visual {
  overflow: hidden;
  aspect-ratio: 16 / 9;
  background-color: var(--background-inverse);
}

.cs-card__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform var(--duration-slow) var(--ease-out);
}

.cs-card__link:hover .cs-card__img {
  transform: scale(1.04);
}

.cs-card__body {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-5);
  flex: 1;
}

.cs-card__category {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--text-accent);
  margin: 0;
}

.cs-card__name {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: clamp(1.125rem, 2vw, 1.375rem);
  line-height: 1.2;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0;
}

.cs-card__description {
  font-family: var(--font-family-body);
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 0;
  flex: 1;
}

.cs-card__tags {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
  list-style: none;
  padding: 0;
  margin: 0;
}

.cs-card__tag {
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.625rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--text-accent);
  padding: 3px 8px;
  border: 1px solid rgba(12, 91, 87, 0.3);
  border-radius: var(--radius-pill);
  background-color: rgba(12, 91, 87, 0.05);
}

.cs-card__cta {
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 0.875rem;
  color: var(--text-accent);
  margin-top: auto;
  padding-top: var(--space-2);
}

@media (prefers-reduced-motion: reduce) {
  .cs-card__link,
  .cs-card__img {
    transition: none;
  }

  .cs-card__link:hover {
    transform: none;
  }

  .cs-card__link:hover .cs-card__img {
    transform: none;
  }
}
</style>
