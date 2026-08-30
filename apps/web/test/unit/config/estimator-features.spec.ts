import { describe, expect, it } from "vitest"
import {
  ALL_FEATURES,
  getFeaturesForType,
} from "~/config/estimator-features"

describe("estimator-features", () => {
  describe("ALL_FEATURES", () => {
    it("contains exactly 15 features", () => {
      expect(ALL_FEATURES).toHaveLength(15)
    })

    it("all features have non-empty value, label and description", () => {
      for (const f of ALL_FEATURES) {
        expect(f.value.length).toBeGreaterThan(0)
        expect(f.label.length).toBeGreaterThan(0)
        expect(f.description.length).toBeGreaterThan(0)
      }
    })

    it("feature values are unique", () => {
      const values = ALL_FEATURES.map((f) => f.value)
      expect(new Set(values).size).toBe(values.length)
    })
  })

  describe("getFeaturesForType", () => {
    it("returns 5 features for vitrinesite", () => {
      expect(getFeaturesForType("vitrinesite")).toHaveLength(5)
    })

    it("includes contact_form for vitrinesite", () => {
      const features = getFeaturesForType("vitrinesite")
      expect(features.some((f) => f.value === "contact_form")).toBe(true)
    })

    it("does not include payment for vitrinesite", () => {
      const features = getFeaturesForType("vitrinesite")
      expect(features.some((f) => f.value === "payment")).toBe(false)
    })

    it("returns 8 features for ecommerce", () => {
      expect(getFeaturesForType("ecommerce")).toHaveLength(8)
    })

    it("includes payment and shipping for ecommerce", () => {
      const features = getFeaturesForType("ecommerce")
      expect(features.some((f) => f.value === "payment")).toBe(true)
      expect(features.some((f) => f.value === "shipping")).toBe(true)
    })

    it("returns 8 features for businessapp", () => {
      expect(getFeaturesForType("businessapp")).toHaveLength(8)
    })

    it("includes authentication and roles_and_permissions for businessapp", () => {
      const features = getFeaturesForType("businessapp")
      expect(features.some((f) => f.value === "authentication")).toBe(true)
      expect(features.some((f) => f.value === "roles_and_permissions")).toBe(true)
    })

    it("returns all 15 features for refonte", () => {
      expect(getFeaturesForType("refonte")).toHaveLength(15)
    })

    it("returns all 15 features for other", () => {
      expect(getFeaturesForType("other")).toHaveLength(15)
    })

    it("returns all 15 features for unknown", () => {
      expect(getFeaturesForType("unknown")).toHaveLength(15)
    })
  })
})
