<script setup lang="ts">
import { computed } from "vue"

/**
 * Bandeau d'état — succès ou erreur globale du formulaire.
 *
 * Le `requestId` complet est conservé pour la corrélation côté logs/API,
 * mais seule la référence courte (#XXXXXXXX, 8 premiers caractères en
 * majuscules) est affichée à l'utilisateur — l'UUID brut est technique
 * et n'apporte rien de lisible au prospect.
 *
 * Deux régions ARIA distinctes :
 *   - succès : `role="status" aria-live="polite"` (annonce non-intrusive) ;
 *   - erreur : `role="alert" aria-live="assertive"` (interrompt le lecteur).
 *
 * Le composant ne fait *aucune* décision : il rend ce qui lui est passé et
 * l'annonce accessible. Aucun timer, aucun auto-dismiss — l'utilisateur choisit
 * quand relire ou changer le formulaire.
 */

type Variant = "success" | "error"

interface Props {
  variant: Variant
  title: string
  message: string
  requestId?: string | null
}

const props = withDefaults(defineProps<Props>(), {
  requestId: null,
})

const role = computed(() => (props.variant === "success" ? "status" : "alert"))
const ariaLive = computed(() =>
  props.variant === "success" ? "polite" : "assertive",
)

const shortId = computed(() =>
  props.requestId ? `#${props.requestId.substring(0, 8).toUpperCase()}` : null,
)
</script>

<template>
  <div
    class="contact-status"
    :data-variant="variant"
    :role="role"
    :aria-live="ariaLive"
    tabindex="-1"
  >
    <div class="contact-status__header">
      <span class="contact-status__icon" aria-hidden="true">
        <svg
          v-if="variant === 'success'"
          width="22"
          height="22"
          viewBox="0 0 22 22"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <circle cx="11" cy="11" r="10" stroke="currentColor" stroke-width="1.5" fill="rgba(42,110,74,0.1)" />
          <path d="M6 11l3.5 3.5 6.5-7" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <svg
          v-else
          width="22"
          height="22"
          viewBox="0 0 22 22"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <circle cx="11" cy="11" r="10" stroke="currentColor" stroke-width="1.5" fill="rgba(178,58,46,0.1)" />
          <path d="M11 6.5v6.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" />
          <circle cx="11" cy="15.5" r="1" fill="currentColor" />
        </svg>
      </span>
      <p class="contact-status__title">{{ title }}</p>
    </div>

    <p class="contact-status__message">{{ message }}</p>

    <p v-if="variant === 'success'" class="contact-status__note">
      En général, nous répondons sous 24 à 48h ouvrées.
    </p>

    <div v-if="shortId" class="contact-status__ref">
      <span class="contact-status__ref-label">Réf. support</span>
      <code class="contact-status__ref-code">{{ shortId }}</code>
    </div>
  </div>
</template>

<style scoped>
.contact-status {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-5);
  border-radius: var(--radius-lg);
  border: 1px solid transparent;
  animation: status-enter var(--duration-base) var(--ease-out) both;
}

@keyframes status-enter {
  from {
    opacity: 0;
    transform: translateY(-6px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (prefers-reduced-motion: reduce) {
  .contact-status {
    animation: none;
  }
}

.contact-status:focus-visible {
  outline: var(--focus-ring-width) solid var(--focus-ring);
  outline-offset: var(--focus-ring-gap);
}

.contact-status[data-variant="success"] {
  background-color: rgba(42, 110, 74, 0.06);
  border-color: rgba(42, 110, 74, 0.22);
  color: var(--text-primary);
}

.contact-status[data-variant="error"] {
  background-color: rgba(178, 58, 46, 0.05);
  border-color: rgba(178, 58, 46, 0.22);
  color: var(--text-primary);
}

/* ── Header (icon + title) ── */

.contact-status__header {
  display: flex;
  align-items: center;
  gap: var(--space-3);
}

.contact-status__icon {
  flex: none;
  display: flex;
  align-items: center;
  justify-content: center;
}

.contact-status[data-variant="success"] .contact-status__icon {
  color: var(--color-status-success);
}

.contact-status[data-variant="error"] .contact-status__icon {
  color: var(--color-status-error);
}

.contact-status__title {
  margin: 0;
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1.0625rem;
  line-height: 1.3;
  letter-spacing: -0.01em;
  color: var(--text-primary);
}

/* ── Body ── */

.contact-status__message {
  margin: 0;
  font-size: 0.9375rem;
  line-height: 1.55;
  color: var(--text-secondary);
}

.contact-status__note {
  margin: 0;
  font-size: 0.875rem;
  line-height: 1.5;
  color: var(--text-muted);
}

/* ── Reference badge ── */

.contact-status__ref {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  flex-wrap: wrap;
  align-self: flex-start;
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-sm);
}

.contact-status[data-variant="success"] .contact-status__ref {
  background: rgba(42, 110, 74, 0.06);
  border: 1px solid rgba(42, 110, 74, 0.18);
}

.contact-status[data-variant="error"] .contact-status__ref {
  background: rgba(178, 58, 46, 0.05);
  border: 1px solid rgba(178, 58, 46, 0.18);
}

.contact-status__ref-label {
  font-family: var(--font-family-body);
  font-size: 0.6875rem;
  font-weight: var(--font-weight-body-strong);
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--text-muted);
}

.contact-status__ref-code {
  font-family: var(--font-family-mono);
  font-size: 0.8125rem;
  letter-spacing: 0.02em;
  color: var(--text-primary);
}
</style>
