<script setup lang="ts">
import { computed } from "vue"

/**
 * Bandeau d'état — succès ou erreur globale du formulaire.
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
</script>

<template>
  <div
    class="contact-status"
    :data-variant="variant"
    :role="role"
    :aria-live="ariaLive"
    tabindex="-1"
  >
    <p class="contact-status__title">{{ title }}</p>
    <p class="contact-status__message">{{ message }}</p>
    <p v-if="requestId" class="contact-status__meta">
      Référence support :
      <code class="contact-status__code">{{ requestId }}</code>
    </p>
  </div>
</template>

<style scoped>
.contact-status {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding: var(--space-4);
  border-radius: var(--radius-md);
  border-left: 4px solid transparent;
}

.contact-status:focus-visible {
  outline: 2px solid var(--color-devzair-blue);
  outline-offset: 2px;
}

.contact-status[data-variant="success"] {
  background-color: var(--color-cream);
  color: var(--text-primary);
  border-left-color: var(--color-status-success);
}

.contact-status[data-variant="error"] {
  background-color: var(--color-cream);
  color: var(--text-primary);
  border-left-color: var(--color-status-error);
}

.contact-status__title {
  margin: 0;
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-heading);
  font-size: 1rem;
}

.contact-status__message {
  margin: 0;
  font-size: 0.9375rem;
  line-height: 1.5;
}

.contact-status__meta {
  margin: 0;
  font-size: 0.8125rem;
  color: var(--text-muted);
}

.contact-status__code {
  font-family: var(--font-family-mono);
  font-size: 0.8125rem;
  background-color: rgba(0, 0, 0, 0.05);
  padding: 0.125rem 0.375rem;
  border-radius: var(--radius-sm);
}
</style>
