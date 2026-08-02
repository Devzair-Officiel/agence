import { describe, expect, it } from "vitest"
import { expertisePillars } from "~/config/expertise-pillars"

describe("expertisePillars", () => {
  it("exposes exactly five pillars", () => {
    expect(expertisePillars).toHaveLength(5)
  })

  it("declares stable order values from 1 to 5", () => {
    const orders = expertisePillars.map((p) => p.order)
    expect(orders).toEqual([1, 2, 3, 4, 5])
  })

  it("uses unique ids", () => {
    const ids = expertisePillars.map((p) => p.id)
    expect(new Set(ids).size).toBe(ids.length)
  })

  it("keeps the accessible full label for the visibility pillar", () => {
    const visibility = expertisePillars.find((p) => p.id === "visibilite")
    expect(visibility).toBeDefined()
    expect(visibility?.label).toBe("Développer la visibilité")
    expect(visibility?.shortLabel).toBe("Visibilité")
  })

  it("never carries empty label, description, shortLabel or longDescription", () => {
    for (const pillar of expertisePillars) {
      expect(pillar.label.trim().length).toBeGreaterThan(0)
      expect(pillar.shortLabel.trim().length).toBeGreaterThan(0)
      expect(pillar.description.trim().length).toBeGreaterThan(0)
      expect(pillar.longDescription.trim().length).toBeGreaterThan(20)
    }
  })

  it("exposes exactly three non-empty services per pillar", () => {
    for (const pillar of expertisePillars) {
      expect(pillar.services).toHaveLength(3)
      for (const service of pillar.services) {
        expect(service.trim().length).toBeGreaterThan(0)
      }
    }
  })

  it("uses one primary variant (Construire), one accent (Faire évoluer), three defaults", () => {
    const primaries = expertisePillars.filter((p) => p.variant === "primary")
    const accents = expertisePillars.filter((p) => p.variant === "accent")
    const defaults = expertisePillars.filter((p) => p.variant === "default")
    expect(primaries).toHaveLength(1)
    expect(primaries[0]?.id).toBe("construire")
    expect(accents).toHaveLength(1)
    expect(accents[0]?.id).toBe("faire-evoluer")
    expect(defaults).toHaveLength(3)
  })
})
