<script setup lang="ts">
import { computed } from "vue"
import {
  footerNavigation,
  legalNavigation,
  primaryCta,
} from "~/config/navigation"
import { site } from "~/config/site"

// Coordonnée non publiée tant que non validée par le client — cf. site.contact.
// Aucune valeur fictive n'est rendue dans le footer.
const hasContact = computed(() =>
  Boolean(site.contact.email || site.contact.phone),
)
</script>

<template>
  <footer class="site-footer">
    <BaseContainer as="div" class="site-footer__inner">
      <div class="site-footer__brand">
        <div class="site-footer__identity">
          <span class="site-footer__logo" aria-hidden="true">D</span>
          <span class="site-footer__brand-name">{{ site.name }}</span>
        </div>
        <p class="site-footer__description">{{ site.description }}</p>
      </div>

      <div
        v-for="group in footerNavigation"
        :key="group.title"
        class="site-footer__column"
      >
        <p class="site-footer__column-title">{{ group.title }}</p>
        <ul class="site-footer__list" role="list">
          <!-- external : routes pas encore livrées, à retirer au fil de la Phase 5. -->
          <li v-for="item in group.items" :key="item.to">
            <NuxtLink :to="item.to" external class="site-footer__link">
              {{ item.label }}
            </NuxtLink>
          </li>
        </ul>
      </div>

      <div class="site-footer__column">
        <p class="site-footer__column-title">Contact</p>
        <ul class="site-footer__list" role="list">
          <li>
            <NuxtLink :to="primaryCta.to" external class="site-footer__link">
              {{ primaryCta.label }}
            </NuxtLink>
          </li>
          <li v-if="hasContact && site.contact.email">
            <a
              :href="`mailto:${site.contact.email}`"
              class="site-footer__link"
            >
              {{ site.contact.email }}
            </a>
          </li>
          <li v-if="hasContact && site.contact.phone">
            <a
              :href="`tel:${site.contact.phone.replace(/\s+/g, '')}`"
              class="site-footer__link"
            >
              {{ site.contact.phone }}
            </a>
          </li>
        </ul>
      </div>
    </BaseContainer>

    <BaseContainer as="div" class="site-footer__legal">
      <span class="site-footer__copyright"
        >© {{ new Date().getFullYear() }} {{ site.name }}</span
      >
      <ul class="site-footer__legal-list" role="list">
        <li v-for="item in legalNavigation" :key="item.to">
          <NuxtLink :to="item.to" external class="site-footer__link">
            {{ item.label }}
          </NuxtLink>
        </li>
      </ul>
    </BaseContainer>
  </footer>
</template>

<style scoped>
.site-footer {
  background-color: var(--background-inverse-deep);
  color: var(--text-inverse-muted);
  padding-top: var(--space-16);
  margin-top: auto;
}

.site-footer__inner {
  display: grid;
  grid-template-columns: 1fr;
  gap: var(--space-10);
  padding-bottom: var(--space-10);
}

.site-footer__identity {
  display: inline-flex;
  align-items: center;
  gap: var(--space-3);
}

.site-footer__logo {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background-color: var(--color-navy);
  color: var(--color-cream);
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
}

.site-footer__brand-name {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.1875rem;
  letter-spacing: -0.03em;
  color: var(--text-inverse);
}

.site-footer__description {
  margin-top: var(--space-4);
  max-width: 34ch;
  color: var(--color-cream-subtle);
  font-size: 0.875rem;
}

.site-footer__column-title {
  margin: 0 0 var(--space-4);
  font-family: var(--font-family-mono);
  font-weight: var(--font-weight-mono);
  font-size: 0.6875rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  /* AA WCAG 4.5:1 sur --background-inverse-deep (contrast ≈ 6.0:1). */
  color: var(--color-cream-subtle);
}

.site-footer__list {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  margin: 0;
  padding: 0;
  list-style: none;
}

.site-footer__link {
  display: inline-flex;
  min-height: var(--touch-target-min);
  align-items: center;
  font-family: var(--font-family-body);
  font-size: 0.875rem;
  color: var(--color-cream-muted);
  transition: color var(--duration-fast) var(--ease-out);
}

.site-footer__link:hover {
  color: var(--text-inverse);
}

.site-footer__legal {
  border-top: 1px solid var(--border-inverse);
  padding-block: var(--space-5);
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-4);
  align-items: center;
  justify-content: space-between;
  font-family: var(--font-family-body);
  font-size: 0.78125rem;
  color: var(--color-cream-subtle);
}

.site-footer__legal-list {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-6);
  margin: 0;
  padding: 0;
  list-style: none;
}

.site-footer__copyright {
  color: var(--color-cream-subtle);
}

@media (min-width: 768px) {
  .site-footer__inner {
    grid-template-columns: 1.4fr 1fr 1fr 1fr;
  }
}

@media (prefers-reduced-motion: reduce) {
  .site-footer__link {
    transition: none;
  }
}
</style>
