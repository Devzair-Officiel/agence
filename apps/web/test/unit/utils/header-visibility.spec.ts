import { describe, expect, it } from "vitest"
import { resolveHeaderVisibility, type HeaderVisibilityState } from "~/utils/header-visibility"

const options = { revealZone: 96, hideTolerance: 12, showTolerance: 8 }

const scrollThrough = (positions: number[], initial: HeaderVisibilityState = { hidden: false, anchorY: 0 }) =>
  positions.reduce((state, y) => resolveHeaderVisibility(state, y, options), initial)

describe("resolveHeaderVisibility", () => {
  it("keeps the header visible inside the reveal zone", () => {
    expect(scrollThrough([40, 80, 96]).hidden).toBe(false)
  })

  it("hides the header once scrolling down beyond the tolerance", () => {
    const visibleAt200 = { hidden: false, anchorY: 200 }
    expect(scrollThrough([210], visibleAt200).hidden).toBe(false)
    expect(scrollThrough([213], visibleAt200).hidden).toBe(true)
  })

  it("ignores small upward jitters while hidden", () => {
    expect(scrollThrough([200, 400, 395]).hidden).toBe(true)
  })

  it("reveals the header once scrolling up beyond the tolerance", () => {
    expect(scrollThrough([200, 400, 391]).hidden).toBe(false)
  })

  it("measures the upward distance from the lowest point reached", () => {
    // 400 → 600 continue la descente : la révélation se mesure depuis 600.
    expect(scrollThrough([200, 400, 600, 595]).hidden).toBe(true)
    expect(scrollThrough([200, 400, 600, 590]).hidden).toBe(false)
  })

  it("measures the downward distance from the highest point reached", () => {
    expect(scrollThrough([200, 400, 300, 310]).hidden).toBe(false)
    expect(scrollThrough([200, 400, 300, 313]).hidden).toBe(true)
  })

  it("reveals the header when returning to the top while hidden", () => {
    expect(scrollThrough([200, 400, 90], { hidden: true, anchorY: 400 }).hidden).toBe(false)
  })
})
