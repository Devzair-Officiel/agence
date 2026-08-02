import { describe, expect, it } from "vitest"
import { projectProcess } from "~/config/project-process"

describe("projectProcess", () => {
  it("exposes exactly six steps", () => {
    expect(projectProcess).toHaveLength(6)
  })

  it("declares stable order values from 1 to 6", () => {
    const orders = projectProcess.map((s) => s.order)
    expect(orders).toEqual([1, 2, 3, 4, 5, 6])
  })

  it("uses unique ids", () => {
    const ids = projectProcess.map((s) => s.id)
    expect(new Set(ids).size).toBe(ids.length)
  })

  it("uses the exact editorial labels (verbatim)", () => {
    const labels = projectProcess.map((s) => s.label)
    expect(labels).toEqual([
      "Découverte",
      "Cadrage",
      "Conception",
      "Développement",
      "Lancement",
      "Évolution",
    ])
  })

  it("never carries empty label or description", () => {
    for (const step of projectProcess) {
      expect(step.label.trim().length).toBeGreaterThan(0)
      expect(step.description.trim().length).toBeGreaterThan(20)
    }
  })
})
