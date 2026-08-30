import { describe, expect, it } from "vitest"

import {
  ESTIMATOR_PROJECT_TYPES,
  type ProjectTypeCode,
} from "~/config/estimator-project-types"

const BACKEND_CODES: readonly ProjectTypeCode[] = [
  "vitrinesite",
  "ecommerce",
  "businessapp",
  "refonte",
  "other",
  "unknown",
]

describe("ESTIMATOR_PROJECT_TYPES", () => {
  it("contains exactly 6 entries", () => {
    expect(ESTIMATOR_PROJECT_TYPES).toHaveLength(6)
  })

  it("all values match backend ProjectType enum codes", () => {
    const values = ESTIMATOR_PROJECT_TYPES.map((t) => t.value)
    expect(values).toEqual(expect.arrayContaining(BACKEND_CODES))
    expect(values).toHaveLength(BACKEND_CODES.length)
  })

  it("each entry has a non-empty label and description", () => {
    for (const type of ESTIMATOR_PROJECT_TYPES) {
      expect(type.label.length).toBeGreaterThan(0)
      expect(type.description.length).toBeGreaterThan(0)
    }
  })

  it("unknown entry exists and has a reassuring description", () => {
    const unknown = ESTIMATOR_PROJECT_TYPES.find((t) => t.value === "unknown")
    expect(unknown).toBeDefined()
    expect(unknown!.description.length).toBeGreaterThan(20)
  })

  it("values are unique", () => {
    const values = ESTIMATOR_PROJECT_TYPES.map((t) => t.value)
    expect(new Set(values).size).toBe(values.length)
  })
})
