import { onBeforeUnmount, onMounted, readonly, ref } from "vue"
import { resolveHeaderVisibility, type HeaderVisibilityState } from "~/utils/header-visibility"

/**
 * Indique si le header doit être masqué selon la direction du scroll.
 *
 * Client uniquement : pendant le SSR et avant le montage, `isHidden` vaut
 * `false`, donc le HTML serveur expose toujours le header visible.
 * L'écoute est passive et regroupée par `requestAnimationFrame` pour ne
 * calculer qu'une fois par frame.
 */
export function useHideOnScroll() {
  const isHidden = ref(false)
  let state: HeaderVisibilityState = { hidden: false, anchorY: 0 }
  let frame: number | null = null

  // Borne la position pour neutraliser le rebond élastique (iOS) en haut
  // comme en bas de page, qui produirait de faux scrolls montants.
  const readScrollY = () => {
    const maxY = document.documentElement.scrollHeight - window.innerHeight
    return Math.min(Math.max(window.scrollY, 0), Math.max(maxY, 0))
  }

  const update = () => {
    frame = null
    state = resolveHeaderVisibility(state, readScrollY())
    isHidden.value = state.hidden
  }

  const onScroll = () => {
    if (frame === null) frame = requestAnimationFrame(update)
  }

  onMounted(() => {
    state = { hidden: false, anchorY: readScrollY() }
    window.addEventListener("scroll", onScroll, { passive: true })
  })

  onBeforeUnmount(() => {
    window.removeEventListener("scroll", onScroll)
    if (frame !== null) cancelAnimationFrame(frame)
  })

  return { isHidden: readonly(isHidden) }
}
