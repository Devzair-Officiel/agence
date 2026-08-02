<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from "vue"

/**
 * Wrapper Cloudflare Turnstile — chargement conditionnel + événements typés.
 *
 * Stratégie :
 *   - le script `challenges.cloudflare.com/turnstile/v0/api.js` est chargé
 *     une seule fois par onglet, uniquement si `siteKey` est non vide ;
 *   - si `siteKey` est vide (dev par défaut), le composant émet immédiatement
 *     `success("dev-noop")` — l'API Symfony est configurée par
 *     `AlwaysAllowTurnstileVerifier` en dev, donc le token n'est jamais
 *     vérifié. Ce mode se voit visuellement (bandeau `Mode dev`) pour éviter
 *     toute confusion en production ;
 *   - en prod, le widget rend un iframe Cloudflare qui gère seul le challenge,
 *     la localisation et la conformité. On expose ses événements clé
 *     (`success`, `error`, `expired`, `timeout`) au parent.
 *
 * Ce composant est destiné à être rendu **client-only** par le parent :
 * `<ClientOnly><TurnstileWidget /></ClientOnly>`. Aucun rendu SSR n'est
 * attendu — la clé publique reste tout de même côté public (site-key,
 * ce n'est pas un secret).
 */

interface TurnstileRenderOptions {
  sitekey: string
  theme?: "auto" | "light" | "dark"
  size?: "normal" | "compact" | "invisible"
  callback: (token: string) => void
  "error-callback"?: () => void
  "expired-callback"?: () => void
  "timeout-callback"?: () => void
}

interface TurnstileApi {
  render: (container: HTMLElement, options: TurnstileRenderOptions) => string
  reset: (widgetId: string) => void
  remove: (widgetId: string) => void
}

declare global {
  interface Window {
    turnstile?: TurnstileApi
  }
}

interface Props {
  siteKey: string
  /**
   * Interrupteur explicite Turnstile. Doit être aligné avec `TURNSTILE_ENABLED`
   * côté API. Quand `false`, aucun script Cloudflare n'est chargé et le
   * widget émet immédiatement le token `dev-noop`. Défaut : `false`
   * (sûr : rien n'est chargé tant que la prod n'active pas explicitement).
   */
  enabled?: boolean
  theme?: "auto" | "light" | "dark"
}

const props = withDefaults(defineProps<Props>(), {
  enabled: false,
  theme: "auto",
})

const emit = defineEmits<{
  success: [token: string]
  error: []
  expired: []
  timeout: []
}>()

const container = ref<HTMLElement | null>(null)
const widgetId = ref<string | null>(null)
// Le mode "dev-noop" s'active dès que Turnstile est explicitement désactivé
// OU que la site-key est vide. Ceci évite tout appel réseau vers
// challenges.cloudflare.com en dev/test et permet de garantir en E2E qu'aucun
// script tiers n'est chargé quand le flag est off.
const isDevNoop = !props.enabled || !props.siteKey

const SCRIPT_ID = "cf-turnstile-script"
const SCRIPT_SRC = "https://challenges.cloudflare.com/turnstile/v0/api.js"

function loadScript(): Promise<void> {
  if (typeof document === "undefined") return Promise.resolve()
  if (window.turnstile) return Promise.resolve()
  const existing = document.getElementById(SCRIPT_ID) as HTMLScriptElement | null
  if (existing) {
    return new Promise((resolve, reject) => {
      existing.addEventListener("load", () => resolve())
      existing.addEventListener("error", () => reject(new Error("turnstile-load-failed")))
    })
  }
  return new Promise((resolve, reject) => {
    const script = document.createElement("script")
    script.id = SCRIPT_ID
    script.src = SCRIPT_SRC
    script.async = true
    script.defer = true
    script.addEventListener("load", () => resolve())
    script.addEventListener("error", () => reject(new Error("turnstile-load-failed")))
    document.head.appendChild(script)
  })
}

async function renderWidget(): Promise<void> {
  if (!container.value) return
  if (!window.turnstile) return
  widgetId.value = window.turnstile.render(container.value, {
    sitekey: props.siteKey,
    theme: props.theme,
    size: "normal",
    callback: (token: string) => emit("success", token),
    "error-callback": () => emit("error"),
    "expired-callback": () => emit("expired"),
    "timeout-callback": () => emit("timeout"),
  })
}

function reset(): void {
  if (isDevNoop) {
    emit("success", "dev-noop")
    return
  }
  if (widgetId.value && window.turnstile) {
    window.turnstile.reset(widgetId.value)
  }
}

defineExpose({ reset })

onMounted(async () => {
  if (isDevNoop) {
    emit("success", "dev-noop")
    return
  }
  try {
    await loadScript()
    await renderWidget()
  } catch {
    emit("error")
  }
})

onBeforeUnmount(() => {
  if (widgetId.value && window.turnstile) {
    try {
      window.turnstile.remove(widgetId.value)
    } catch {
      /* widget déjà retiré : rien à faire. */
    }
  }
})
</script>

<template>
  <div class="turnstile-widget">
    <div v-if="isDevNoop" class="turnstile-widget__dev-notice" role="note">
      Mode dev — protection anti-bot désactivée (token factice envoyé automatiquement).
    </div>
    <div v-else ref="container" class="turnstile-widget__container" />
  </div>
</template>

<style scoped>
.turnstile-widget {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.turnstile-widget__dev-notice {
  font-family: var(--font-family-mono);
  font-size: 0.75rem;
  color: var(--color-cream-muted);
  padding: var(--space-2) var(--space-3);
  border: 1px dashed var(--border-inverse);
  border-radius: var(--radius-sm);
}

.turnstile-widget__container {
  min-height: 65px;
}
</style>
