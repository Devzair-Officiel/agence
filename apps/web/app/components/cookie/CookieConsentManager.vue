<script setup lang="ts">
import { nextTick, onMounted, onUnmounted, ref, watch } from "vue"
import { useCookieConsent } from "~/composables/useCookieConsent"

const {
  isPreferencesOpen,
  pendingPreferences,
  closePreferences,
  acceptAll,
  rejectOptional,
  savePreferences,
} = useCookieConsent()

const dialogRef = ref<HTMLElement | null>(null)
const openerRef = ref<HTMLElement | null>(null)

watch(isPreferencesOpen, async (opened) => {
  if (typeof document === "undefined") return
  if (opened) {
    openerRef.value = document.activeElement as HTMLElement
    await nextTick()
    const first = dialogRef.value?.querySelector<HTMLElement>(
      'button, input:not([type="hidden"]):not(:disabled), [tabindex]:not([tabindex="-1"])',
    )
    first?.focus()
  } else {
    await nextTick()
    openerRef.value?.focus()
    openerRef.value = null
  }
})

function trapFocus(event: KeyboardEvent): void {
  if (!isPreferencesOpen.value || event.key !== "Tab") return
  const focusable = dialogRef.value?.querySelectorAll<HTMLElement>(
    'button:not(:disabled), input:not(:disabled):not([type="hidden"]), [tabindex]:not([tabindex="-1"])',
  )
  if (!focusable || focusable.length === 0) return
  const first = focusable[0] as HTMLElement
  const last = focusable[focusable.length - 1] as HTMLElement
  if (event.shiftKey && document.activeElement === first) {
    last.focus()
    event.preventDefault()
  } else if (!event.shiftKey && document.activeElement === last) {
    first.focus()
    event.preventDefault()
  }
}

function onKeydown(event: KeyboardEvent): void {
  if (event.key === "Escape" && isPreferencesOpen.value) {
    closePreferences()
    return
  }
  trapFocus(event)
}

onMounted(() => {
  if (typeof document !== "undefined") {
    document.addEventListener("keydown", onKeydown)
  }
})

onUnmounted(() => {
  if (typeof document !== "undefined") {
    document.removeEventListener("keydown", onKeydown)
  }
})
</script>

<template>
  <Teleport to="body">
    <div
      v-if="isPreferencesOpen"
      class="consent-overlay"
      aria-hidden="true"
      @click="closePreferences"
    />
    <div
      v-if="isPreferencesOpen"
      ref="dialogRef"
      role="dialog"
      aria-modal="true"
      aria-labelledby="consent-dialog-title"
      class="consent-dialog"
    >
      <!-- En-tête -->
      <div class="consent-dialog__header">
        <h2 id="consent-dialog-title" class="consent-dialog__title">
          Préférences de cookies
        </h2>
        <button
          class="consent-dialog__close"
          aria-label="Fermer les préférences de cookies"
          @click="closePreferences"
        >
          <svg aria-hidden="true" focusable="false" width="20" height="20" viewBox="0 0 24 24" fill="none">
            <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
          </svg>
        </button>
      </div>

      <!-- Corps -->
      <div class="consent-dialog__body">

        <!-- Nécessaires -->
        <div class="consent-category">
          <div class="consent-category__text">
            <h3 class="consent-category__name">Nécessaires</h3>
            <p class="consent-category__desc">
              Nécessaires au fonctionnement et à la sécurité du site.
              Ils ne peuvent pas être désactivés.
            </p>
          </div>
          <label class="consent-toggle consent-toggle--locked" aria-label="Cookies nécessaires (toujours actifs)">
            <input
              type="checkbox"
              class="consent-toggle__input"
              checked
              disabled
              tabindex="-1"
            >
            <span class="consent-toggle__track" aria-hidden="true">
              <span class="consent-toggle__thumb" />
            </span>
          </label>
        </div>

        <!-- Mesure d'audience -->
        <div class="consent-category">
          <div class="consent-category__text">
            <h3 class="consent-category__name">Mesure d'audience</h3>
            <p class="consent-category__desc">
              Permettrait de mesurer l'utilisation du site et d'améliorer
              ses performances lorsque des outils de mesure d'audience sont activés.
            </p>
          </div>
          <label
            class="consent-toggle"
            :class="{ 'consent-toggle--on': pendingPreferences.analytics }"
            :aria-label="pendingPreferences.analytics ? 'Désactiver la mesure d\'audience' : 'Activer la mesure d\'audience'"
          >
            <input
              v-model="pendingPreferences.analytics"
              type="checkbox"
              class="consent-toggle__input"
            >
            <span class="consent-toggle__track" aria-hidden="true">
              <span class="consent-toggle__thumb" />
            </span>
          </label>
        </div>

        <!-- Marketing -->
        <div class="consent-category">
          <div class="consent-category__text">
            <h3 class="consent-category__name">Marketing</h3>
            <p class="consent-category__desc">
              Permettrait d'utiliser des services destinés à mesurer ou
              personnaliser les actions marketing lorsque de tels services sont activés.
            </p>
          </div>
          <label
            class="consent-toggle"
            :class="{ 'consent-toggle--on': pendingPreferences.marketing }"
            :aria-label="pendingPreferences.marketing ? 'Désactiver le marketing' : 'Activer le marketing'"
          >
            <input
              v-model="pendingPreferences.marketing"
              type="checkbox"
              class="consent-toggle__input"
            >
            <span class="consent-toggle__track" aria-hidden="true">
              <span class="consent-toggle__thumb" />
            </span>
          </label>
        </div>

      </div>

      <!-- Actions -->
      <div class="consent-dialog__actions">
        <button class="consent-btn consent-btn--secondary" @click="rejectOptional">
          Tout refuser
        </button>
        <button class="consent-btn consent-btn--secondary" @click="savePreferences">
          Enregistrer mes choix
        </button>
        <button class="consent-btn consent-btn--primary" @click="acceptAll">
          Tout accepter
        </button>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
/* ── Overlay ────────────────────────────────────────────────────────────────── */

.consent-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 900;
}

/* ── Boîte de dialogue ──────────────────────────────────────────────────────── */

.consent-dialog {
  position: fixed;
  inset-inline: var(--space-4);
  bottom: var(--space-4);
  max-width: 540px;
  margin-inline: auto;
  background: var(--background-primary);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-lg, 12px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.16);
  z-index: 901;
  display: flex;
  flex-direction: column;
  max-height: calc(100dvh - var(--space-8));
  overflow: hidden;
}

@media (min-width: 640px) {
  .consent-dialog {
    inset-inline: auto;
    right: var(--space-6);
    bottom: var(--space-6);
    left: auto;
    width: 540px;
  }
}

/* ── En-tête ────────────────────────────────────────────────────────────────── */

.consent-dialog__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-5) var(--space-5) var(--space-4);
  border-bottom: 1px solid var(--border-default);
  flex-shrink: 0;
}

.consent-dialog__title {
  font-family: var(--font-family-heading);
  font-size: 1.0625rem;
  font-weight: var(--font-weight-body-strong);
  color: var(--color-ink);
  margin: 0;
}

.consent-dialog__close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  border: none;
  background: none;
  color: var(--color-ink-muted);
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: color 150ms var(--ease-out), background 150ms var(--ease-out);
  flex-shrink: 0;
}

.consent-dialog__close:hover {
  color: var(--color-ink);
  background: var(--background-subtle, #f3f0e8);
}

.consent-dialog__close:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
}

/* ── Corps ──────────────────────────────────────────────────────────────────── */

.consent-dialog__body {
  display: flex;
  flex-direction: column;
  gap: 0;
  overflow-y: auto;
  flex: 1;
}

/* ── Catégorie ──────────────────────────────────────────────────────────────── */

.consent-category {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-4);
  padding: var(--space-4) var(--space-5);
  border-bottom: 1px solid var(--border-default);
}

.consent-category:last-child {
  border-bottom: none;
}

.consent-category__text {
  flex: 1;
  min-width: 0;
}

.consent-category__name {
  font-family: var(--font-family-heading);
  font-size: 0.9375rem;
  font-weight: var(--font-weight-body-strong);
  color: var(--color-ink);
  margin: 0 0 var(--space-1);
}

.consent-category__desc {
  font-size: 0.8125rem;
  color: var(--color-ink-muted);
  line-height: 1.55;
  margin: 0;
}

/* ── Toggle ─────────────────────────────────────────────────────────────────── */

.consent-toggle {
  position: relative;
  display: inline-flex;
  align-items: center;
  width: 2.75rem;
  height: 1.5rem;
  cursor: pointer;
  flex-shrink: 0;
}

.consent-toggle--locked {
  cursor: not-allowed;
}

.consent-toggle__input {
  position: absolute;
  inset: 0;
  opacity: 0;
  width: 100%;
  height: 100%;
  margin: 0;
  cursor: inherit;
  z-index: 1;
}

.consent-toggle__track {
  position: relative;
  width: 100%;
  height: 100%;
  background: var(--border-default);
  border-radius: 9999px;
  transition: background 150ms var(--ease-out);
  pointer-events: none;
}

.consent-toggle--on .consent-toggle__track,
.consent-toggle--locked .consent-toggle__track {
  background: var(--color-petrol);
}

.consent-toggle--locked .consent-toggle__track {
  opacity: 0.7;
}

.consent-toggle__thumb {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 1.25rem;
  height: 1.25rem;
  background: #fff;
  border-radius: 50%;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  transition: transform 150ms var(--ease-out);
}

.consent-toggle--on .consent-toggle__thumb {
  transform: translateX(1.25rem);
}

.consent-toggle__input:focus-visible + .consent-toggle__track {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
}

/* ── Actions ────────────────────────────────────────────────────────────────── */

.consent-dialog__actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
  padding: var(--space-4) var(--space-5);
  border-top: 1px solid var(--border-default);
  flex-shrink: 0;
}

.consent-btn {
  flex: 1;
  min-width: 0;
  padding: var(--space-2) var(--space-3);
  min-height: 2.5rem;
  font-size: 0.875rem;
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-body-strong);
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: background 150ms var(--ease-out), color 150ms var(--ease-out), border-color 150ms var(--ease-out);
  white-space: nowrap;
}

.consent-btn--secondary {
  background: transparent;
  border: 1px solid var(--border-default);
  color: var(--color-ink-muted);
}

.consent-btn--secondary:hover {
  border-color: var(--color-ink-muted);
  color: var(--color-ink);
}

.consent-btn--primary {
  background: var(--color-petrol);
  border: 1px solid var(--color-petrol);
  color: #fff;
}

.consent-btn--primary:hover {
  background: var(--color-petrol-hover, #3a6e6a);
  border-color: var(--color-petrol-hover, #3a6e6a);
}

.consent-btn:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
}

@media (prefers-reduced-motion: reduce) {
  .consent-toggle__track,
  .consent-toggle__thumb,
  .consent-btn,
  .consent-dialog__close { transition: none; }
}
</style>
