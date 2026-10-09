/**
 * Décision pure de visibilité du header « masqué au scroll descendant,
 * révélé au scroll montant ».
 *
 * `anchorY` mémorise la position extrême atteinte dans la direction courante :
 * le changement d'état n'intervient qu'après un déplacement inverse supérieur
 * à la tolérance, ce qui évite le clignotement sur les micro-scrolls (trackpad,
 * rebond iOS).
 */

export interface HeaderVisibilityState {
  hidden: boolean
  anchorY: number
}

export interface HeaderVisibilityOptions {
  /** En deçà de cette position, le header reste toujours visible. */
  revealZone: number
  /** Distance de scroll descendant avant masquage. */
  hideTolerance: number
  /** Distance de scroll montant avant révélation. */
  showTolerance: number
}

export const HEADER_VISIBILITY_DEFAULTS: HeaderVisibilityOptions = {
  revealZone: 96,
  hideTolerance: 12,
  showTolerance: 8,
}

export function resolveHeaderVisibility(
  state: HeaderVisibilityState,
  currentY: number,
  options: HeaderVisibilityOptions = HEADER_VISIBILITY_DEFAULTS,
): HeaderVisibilityState {
  if (currentY <= options.revealZone) {
    return { hidden: false, anchorY: currentY }
  }

  if (state.hidden) {
    if (currentY > state.anchorY) return { hidden: true, anchorY: currentY }
    if (state.anchorY - currentY > options.showTolerance) {
      return { hidden: false, anchorY: currentY }
    }
    return state
  }

  if (currentY < state.anchorY) return { hidden: false, anchorY: currentY }
  if (currentY - state.anchorY > options.hideTolerance) {
    return { hidden: true, anchorY: currentY }
  }
  return state
}
