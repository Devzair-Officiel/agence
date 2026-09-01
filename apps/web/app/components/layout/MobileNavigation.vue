<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue"
import { useMobileNavigation } from "~/composables/useMobileNavigation"
import { estimatorCta, primaryCta, primaryNavigation } from "~/config/navigation"
import { site } from "~/config/site"

const { isOpen, close } = useMobileNavigation()

const dialogRef = ref<HTMLElement | null>(null)
const closeButtonRef = ref<HTMLButtonElement | null>(null)

const focusableSelector =
  'a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])'

const getFocusable = (): HTMLElement[] => {
  const root = dialogRef.value
  if (!root) return []
  return Array.from(root.querySelectorAll<HTMLElement>(focusableSelector)).filter(
    (el) => !el.hasAttribute("aria-hidden"),
  )
}

const onKeydown = (event: KeyboardEvent) => {
  if (!isOpen.value) return

  if (event.key === "Escape") {
    event.preventDefault()
    close()
    return
  }

  if (event.key !== "Tab") return

  const focusable = getFocusable()
  if (focusable.length === 0) return

  const first = focusable[0]
  const last = focusable[focusable.length - 1]
  if (!first || !last) return

  const active = document.activeElement as HTMLElement | null

  if (event.shiftKey && active === first) {
    event.preventDefault()
    last.focus()
  } else if (!event.shiftKey && active === last) {
    event.preventDefault()
    first.focus()
  }
}

const onNavigate = () => {
  close()
}

onMounted(() => {
  document.addEventListener("keydown", onKeydown)
})

onBeforeUnmount(() => {
  document.removeEventListener("keydown", onKeydown)
})

watch(isOpen, async (opened) => {
  if (!opened) return
  await nextTick()
  closeButtonRef.value?.focus()
})
</script>

<template>
  <Teleport to="body">
    <Transition name="mobile-nav">
      <div
        v-if="isOpen"
        id="mobile-navigation"
        ref="dialogRef"
        class="mobile-navigation"
        role="dialog"
        aria-modal="true"
        aria-label="Menu principal"
      >
        <div class="mobile-navigation__inner">
          <div class="mobile-navigation__header">
            <div class="mobile-navigation__brand">
              <img
                class="mobile-navigation__logo"
                :alt="site.name"
                src="/brand/logo_devzaire_agency.png"
                width="56"
                height="56"
                decoding="async"
              >
            </div>
            <button
              ref="closeButtonRef"
              type="button"
              class="mobile-navigation__close"
              aria-label="Fermer le menu"
              @click="close"
            >
              <span aria-hidden="true">×</span>
            </button>
          </div>

          <nav aria-label="Navigation principale">
            <ul class="mobile-navigation__list" role="list">
              <li
                v-for="item in primaryNavigation"
                :key="item.to"
                class="mobile-navigation__item"
              >
                <NuxtLink
                  :to="item.to"
                  :external="item.isRoute ? undefined : true"
                  class="mobile-navigation__link"
                  @click="onNavigate"
                >
                  {{ item.label }}
                  <span class="mobile-navigation__arrow" aria-hidden="true">→</span>
                </NuxtLink>
              </li>
            </ul>
          </nav>

          <div class="mobile-navigation__estimator">
            <NuxtLink
              :to="estimatorCta.to"
              class="mobile-navigation__estimator-link"
              @click="onNavigate"
            >
              {{ estimatorCta.label }}
              <span class="mobile-navigation__arrow" aria-hidden="true">→</span>
            </NuxtLink>
          </div>

          <BaseButton
            :to="primaryCta.to"
            :external="primaryCta.isRoute ? undefined : true"
            variant="primary"
            class="mobile-navigation__cta"
            @click="onNavigate"
          >
            {{ primaryCta.label }}
          </BaseButton>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.mobile-navigation {
  position: fixed;
  inset: 0;
  z-index: var(--z-mobile-nav);
  background-color: var(--background-inverse);
  color: var(--text-inverse);
  overflow-y: auto;
}

.mobile-navigation__inner {
  display: flex;
  flex-direction: column;
  min-height: 100dvh;
  padding: var(--space-4) var(--container-gutter-mobile) var(--space-8);
}

.mobile-navigation__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-block: var(--space-3);
  border-bottom: 1px solid var(--border-inverse);
}

.mobile-navigation__brand {
  display: inline-flex;
  align-items: center;
  gap: var(--space-3);
}

.mobile-navigation__logo {
  display: block;
  width: 56px;
  height: 56px;
  object-fit: contain;
}

.mobile-navigation__close {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: var(--touch-target-min);
  height: var(--touch-target-min);
  color: var(--text-inverse);
  font-family: var(--font-family-mono);
  font-size: 1.5rem;
  line-height: 1;
}

.mobile-navigation__list {
  list-style: none;
  margin: 0;
  padding: var(--space-2) 0;
  display: flex;
  flex-direction: column;
}

.mobile-navigation__item {
  border-bottom: 1px solid var(--border-inverse);
}

.mobile-navigation__link {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-block: var(--space-5);
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1.375rem;
  letter-spacing: -0.02em;
  color: var(--text-inverse-muted);
  min-height: var(--touch-target-min);
  transition: color var(--duration-fast) var(--ease-out);
}

.mobile-navigation__link:hover {
  color: var(--text-inverse);
}

.mobile-navigation__link.router-link-active {
  color: var(--color-devzair-blue);
}

.mobile-navigation__arrow {
  font-family: var(--font-family-mono);
  font-size: 0.9375rem;
  color: var(--color-devzair-blue);
  opacity: 0.45;
  transition:
    transform var(--duration-base) var(--ease-out),
    opacity var(--duration-fast) var(--ease-out);
}

.mobile-navigation__link:hover .mobile-navigation__arrow,
.mobile-navigation__link.router-link-active .mobile-navigation__arrow {
  transform: translateX(8px);
  opacity: 1;
}

.mobile-navigation__cta {
  margin-top: var(--space-6);
  width: 100%;
}

.mobile-navigation__estimator {
  border-top: 1px solid var(--border-inverse);
  padding-top: var(--space-2);
}

.mobile-navigation__estimator-link {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-block: var(--space-5);
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading-medium);
  font-size: 1.125rem;
  letter-spacing: -0.02em;
  color: var(--color-devzair-blue);
  min-height: var(--touch-target-min);
  transition: opacity var(--duration-fast) var(--ease-out);
}

.mobile-navigation__estimator-link:hover {
  opacity: 0.8;
}

@media (prefers-reduced-motion: reduce) {
  .mobile-navigation__estimator-link { transition: none; }
}

.mobile-nav-enter-active,
.mobile-nav-leave-active {
  transition:
    opacity var(--duration-base) var(--ease-out),
    transform var(--duration-base) var(--ease-out);
}

.mobile-nav-enter-from,
.mobile-nav-leave-to {
  opacity: 0;
  transform: translateY(-12px);
}

.mobile-nav-enter-active .mobile-navigation__item {
  animation: mobileItemIn var(--duration-slow) var(--ease-out) both;
}

.mobile-nav-enter-active .mobile-navigation__item:nth-child(1) { animation-delay: 80ms; }
.mobile-nav-enter-active .mobile-navigation__item:nth-child(2) { animation-delay: 130ms; }
.mobile-nav-enter-active .mobile-navigation__item:nth-child(3) { animation-delay: 180ms; }
.mobile-nav-enter-active .mobile-navigation__item:nth-child(4) { animation-delay: 230ms; }
.mobile-nav-enter-active .mobile-navigation__item:nth-child(5) { animation-delay: 280ms; }

.mobile-nav-enter-active .mobile-navigation__cta {
  animation: mobileItemIn var(--duration-slow) var(--ease-out) 320ms both;
}

@keyframes mobileItemIn {
  from {
    opacity: 0;
    transform: translateY(18px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (min-width: 1024px) {
  .mobile-navigation {
    display: none;
  }
}

@media (prefers-reduced-motion: reduce) {
  .mobile-nav-enter-active,
  .mobile-nav-leave-active {
    transition: none;
  }

  .mobile-nav-enter-active .mobile-navigation__item,
  .mobile-nav-enter-active .mobile-navigation__cta {
    animation: none;
  }

  .mobile-navigation__link,
  .mobile-navigation__arrow {
    transition: none;
  }
}
</style>
