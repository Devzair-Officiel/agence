import { readonly, ref, watch } from "vue"

/**
 * État global de la navigation mobile.
 *
 * Responsabilité unique : ouvrir/fermer le menu mobile et gérer les effets de bord
 * associés (verrou de scroll de la page, restauration du focus).
 *
 * Le raccourci Escape et le piège de focus sont câblés côté composant `MobileNavigation`
 * pour rester au plus près des éléments concernés du DOM.
 *
 * Refs au niveau du module : la nav mobile est un singleton par onglet, il n'est pas
 * utile de passer par `useState` (qui vise le partage entre SSR et client). Ce
 * composable reste consommable côté client — il n'est jamais utilisé pendant le SSR.
 */

const SCROLL_LOCK_CLASS = "is-scroll-locked"

const isClient = typeof document !== "undefined"

const isOpen = ref(false)
const previousFocus = ref<HTMLElement | null>(null)

if (isClient) {
  watch(isOpen, (opened) => {
    document.documentElement.classList.toggle(SCROLL_LOCK_CLASS, opened)
  })
}

export function useMobileNavigation() {
  const open = (trigger?: HTMLElement | null) => {
    if (isClient && trigger) {
      previousFocus.value = trigger
    }
    isOpen.value = true
  }

  const close = () => {
    isOpen.value = false
    if (isClient && previousFocus.value) {
      const target = previousFocus.value
      requestAnimationFrame(() => {
        target.focus()
      })
      previousFocus.value = null
    }
  }

  const toggle = (trigger?: HTMLElement | null) => {
    if (isOpen.value) {
      close()
    } else {
      open(trigger)
    }
  }

  return {
    isOpen: readonly(isOpen),
    open,
    close,
    toggle,
  }
}
