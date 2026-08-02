import { describe, expect, it } from "vitest"
import { trustPromises } from "~/config/trust-promises"

describe("trustPromises", () => {
  it("exposes exactly five promises", () => {
    expect(trustPromises).toHaveLength(5)
  })

  it("declares stable order values from 1 to 5", () => {
    const orders = trustPromises.map((p) => p.order)
    expect(orders).toEqual([1, 2, 3, 4, 5])
  })

  it("uses unique ids", () => {
    const ids = trustPromises.map((p) => p.id)
    expect(new Set(ids).size).toBe(ids.length)
  })

  it("uses the exact editorial labels (verbatim)", () => {
    const labels = trustPromises.map((p) => p.label)
    expect(labels).toEqual([
      "Approche personnalisée",
      "Vision globale",
      "Qualité technique",
      "Transparence",
      "Accompagnement durable",
    ])
  })

  it("never carries empty label or description", () => {
    for (const promise of trustPromises) {
      expect(promise.label.trim().length).toBeGreaterThan(0)
      expect(promise.description.trim().length).toBeGreaterThan(20)
    }
  })

  it("avoids forbidden superlatives (no « meilleur » or « unique »)", () => {
    for (const promise of trustPromises) {
      expect(promise.description).not.toMatch(/meilleur/i)
      expect(promise.description).not.toMatch(/\bunique\b/i)
    }
  })
})
