<script setup lang="ts">
import { ref } from "vue"
import { useMobileNavigation } from "~/composables/useMobileNavigation"
import { primaryCta, primaryNavigation } from "~/config/navigation"
import { site } from "~/config/site"

const { toggle, isOpen } = useMobileNavigation()

const menuButtonRef = ref<HTMLButtonElement | null>(null)

const onToggleMenu = () => {
  toggle(menuButtonRef.value)
}
</script>

<template>
  <header class="site-header">
    <BaseContainer as="div" class="site-header__inner">
      <NuxtLink to="/" class="site-header__brand" :aria-label="`${site.name} — Accueil`">
        <span class="site-header__logo" aria-hidden="true">D</span>
        <span class="site-header__brand-name">{{ site.name }}</span>
      </NuxtLink>

      <nav class="site-header__nav" aria-label="Navigation principale">
        <ul class="site-header__list" role="list">
          <!-- `external` uniquement quand la cible n'est pas une route Nuxt réelle. -->
          <li v-for="item in primaryNavigation" :key="item.to">
            <NuxtLink
              :to="item.to"
              :external="item.isRoute ? undefined : true"
              class="site-header__link"
            >
              {{ item.label }}
            </NuxtLink>
          </li>
        </ul>
        <BaseButton
          :to="primaryCta.to"
          :external="primaryCta.isRoute ? undefined : true"
          variant="primary"
          class="site-header__cta"
        >
          {{ primaryCta.label }}
        </BaseButton>
      </nav>

      <button
        ref="menuButtonRef"
        type="button"
        class="site-header__menu-button"
        aria-controls="mobile-navigation"
        :aria-expanded="isOpen"
        aria-label="Ouvrir le menu"
        @click="onToggleMenu"
      >
        <span class="site-header__menu-icon" aria-hidden="true">
          <span />
          <span />
          <span />
        </span>
      </button>
    </BaseContainer>
  </header>
</template>

<style scoped>
.site-header {
  position: sticky;
  top: 0;
  z-index: var(--z-header);
  background-color: color-mix(
    in srgb,
    var(--background-primary) 86%,
    transparent
  );
  border-bottom: 1px solid var(--border-default);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

/* Fallback quand backdrop-filter n'est pas disponible ou désactivé. */
@supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
  .site-header {
    background-color: var(--background-primary);
  }
}

.site-header__inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-6);
  padding-block: var(--space-4);
}

.site-header__brand {
  display: inline-flex;
  align-items: center;
  gap: var(--space-3);
  min-height: var(--touch-target-min);
}

.site-header__logo {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 9px;
  background-color: var(--color-navy);
  color: var(--color-cream);
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.125rem;
  line-height: 1;
}

.site-header__brand-name {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.3125rem;
  letter-spacing: -0.03em;
  color: var(--text-primary);
}

.site-header__nav {
  display: none;
  align-items: center;
  gap: var(--space-8);
}

.site-header__list {
  display: flex;
  align-items: center;
  gap: var(--space-8);
  margin: 0;
  padding: 0;
  list-style: none;
}

.site-header__link {
  font-family: var(--font-family-body);
  font-weight: var(--font-weight-body-strong);
  font-size: 0.9375rem;
  color: var(--text-secondary);
  transition: color var(--duration-fast) var(--ease-out);
  padding-block: var(--space-2);
}

.site-header__link:hover,
.site-header__link.router-link-active {
  color: var(--text-primary);
}

.site-header__menu-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: var(--touch-target-min);
  height: var(--touch-target-min);
  border-radius: var(--radius-md);
  background-color: transparent;
  color: var(--text-primary);
}

.site-header__menu-icon {
  display: inline-flex;
  flex-direction: column;
  gap: 5px;
}

.site-header__menu-icon span {
  display: block;
  width: 22px;
  height: 2px;
  background-color: currentColor;
  border-radius: 2px;
}

@media (min-width: 1024px) {
  .site-header__nav {
    display: flex;
  }
  .site-header__menu-button {
    display: none;
  }
}
</style>
