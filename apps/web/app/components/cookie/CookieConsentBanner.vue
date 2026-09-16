<script setup lang="ts">
import { useCookieConsent } from "~/composables/useCookieConsent"

const { showBanner, acceptAll, rejectOptional, openPreferences } = useCookieConsent()
</script>

<template>
  <div
    v-if="showBanner"
    role="region"
    aria-label="Préférences de cookies"
    aria-live="polite"
    class="consent-banner"
  >
    <div class="consent-banner__content">
      <p class="consent-banner__text">
        Ce site utilise des cookies. Certains sont nécessaires à son
        fonctionnement&nbsp;; d'autres nécessitent votre accord préalable.
      </p>
      <div class="consent-banner__actions">
        <button class="consent-banner__btn consent-banner__btn--secondary" @click="openPreferences">
          Personnaliser
        </button>
        <button class="consent-banner__btn consent-banner__btn--secondary" @click="rejectOptional">
          Tout refuser
        </button>
        <button class="consent-banner__btn consent-banner__btn--primary" @click="acceptAll">
          Tout accepter
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.consent-banner {
  position: fixed;
  bottom: 0;
  inset-inline: 0;
  background: var(--background-primary);
  border-top: 1px solid var(--border-default);
  box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.08);
  z-index: 800;
  padding: var(--space-4) var(--space-5);
}

.consent-banner__content {
  max-width: 900px;
  margin-inline: auto;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: var(--space-4);
}

.consent-banner__text {
  flex: 1;
  min-width: min(100%, 320px);
  font-size: 0.9375rem;
  color: var(--color-ink-muted);
  line-height: 1.55;
  margin: 0;
}

.consent-banner__actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
}

.consent-banner__btn {
  padding: var(--space-2) var(--space-4);
  min-height: 2.5rem;
  font-size: 0.875rem;
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-body-strong);
  border-radius: var(--radius-sm);
  cursor: pointer;
  white-space: nowrap;
  transition: background 150ms var(--ease-out), color 150ms var(--ease-out), border-color 150ms var(--ease-out);
}

.consent-banner__btn--secondary {
  background: transparent;
  border: 1px solid var(--border-default);
  color: var(--color-ink-muted);
}

.consent-banner__btn--secondary:hover {
  border-color: var(--color-ink-muted);
  color: var(--color-ink);
}

.consent-banner__btn--primary {
  background: var(--color-petrol);
  border: 1px solid var(--color-petrol);
  color: #fff;
}

.consent-banner__btn--primary:hover {
  background: var(--color-petrol-hover, #3a6e6a);
  border-color: var(--color-petrol-hover, #3a6e6a);
}

.consent-banner__btn:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
}

@media (prefers-reduced-motion: reduce) {
  .consent-banner__btn { transition: none; }
}
</style>
