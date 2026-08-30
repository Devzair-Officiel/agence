import { describe, expect, it } from "vitest"
import {
  getLineItemLabel,
  getRecurringItemLabel,
  getAssumptionMessage,
  getRecommendedProjectTypeLabel,
  getPeriodLabel,
  LINE_ITEM_LABELS,
  RECURRING_ITEM_LABELS,
  ASSUMPTION_MESSAGES,
  RECOMMENDED_PROJECT_TYPE_LABELS,
} from "~/config/estimate-labels"

// Codes V1 réellement produits par ProjectEstimationEngine + DevzairPricingCatalogV1
const ALL_ONE_OFF_CODES = [
  "BASE",
  "FEATURE_CONTACT_FORM",
  "FEATURE_BOOKING_FORM",
  "FEATURE_MULTILINGUAL",
  "FEATURE_PAYMENT",
  "FEATURE_SHIPPING",
  "FEATURE_CUSTOMER_ACCOUNTS",
  "FEATURE_AUTHENTICATION",
  "FEATURE_ROLES_AND_PERMISSIONS",
  "FEATURE_CONTENT_MANAGEMENT",
  "FEATURE_DOCUMENT_GENERATION",
  "FEATURE_NOTIFICATIONS",
  "FEATURE_IMPORT_EXPORT",
  "FEATURE_API_INTEGRATION",
  "FEATURE_STOCK_MANAGEMENT",
  "FEATURE_PROMOTIONS",
  "CONTENT_VISUAL_IDENTITY_NEEDED",
  "CONTENT_VISUAL_IDENTITY_PARTIAL",
  "CONTENT_CONTENT_TO_WRITE",
  "CONTENT_CONTENT_WITH_HELP",
  "CONTENT_PHOTOGRAPHY_NEEDED",
  "VISIBILITY_SEO_ADVANCED",
  "VISIBILITY_LOCAL_VISIBILITY",
  "VISIBILITY_EDITORIAL_STRATEGY",
]

const ALL_RECURRING_CODES = [
  "RECURRING_ESSENTIAL_MAINTENANCE",
  "RECURRING_MAINTENANCE_FOLLOWUP",
  "RECURRING_ACCOMPANIMENT",
  "RECURRING_REGULAR_EVOLUTION",
  "RECURRING_CONTINUOUS_SEO",
]

// Codes d'assumption réellement produits par ProjectEstimationEngine
const ALL_ASSUMPTION_CODES = [
  "scope_to_confirm",
  "external_integration_to_confirm",
  "advanced_scope_requires_confirmation",
  "redesign_existing_system_to_audit",
  "unknown_project_requires_human_scoping",
]

const ALL_PROJECT_TYPE_CODES = [
  "vitrinesite",
  "ecommerce",
  "businessapp",
  "refonte",
  "other",
  "unknown",
]

describe("LINE_ITEM_LABELS — couverture complète V1", () => {
  it.each(ALL_ONE_OFF_CODES)("le code %s a un label français", (code) => {
    expect(LINE_ITEM_LABELS[code]).toBeDefined()
    expect(LINE_ITEM_LABELS[code]).not.toBe("")
  })
})

describe("RECURRING_ITEM_LABELS — couverture complète V1", () => {
  it.each(ALL_RECURRING_CODES)("le code récurrent %s a un label français", (code) => {
    expect(RECURRING_ITEM_LABELS[code]).toBeDefined()
    expect(RECURRING_ITEM_LABELS[code]).not.toBe("")
  })
})

describe("ASSUMPTION_MESSAGES — couverture complète V1", () => {
  it.each(ALL_ASSUMPTION_CODES)("l'assumption %s a un message éditorial", (code) => {
    expect(ASSUMPTION_MESSAGES[code]).toBeDefined()
    expect(ASSUMPTION_MESSAGES[code]).not.toBe("")
  })
})

describe("RECOMMENDED_PROJECT_TYPE_LABELS — couverture complète V1", () => {
  it.each(ALL_PROJECT_TYPE_CODES)("le type %s a un label français", (code) => {
    expect(RECOMMENDED_PROJECT_TYPE_LABELS[code]).toBeDefined()
  })

  it("businessapp est mappé (pas webapp)", () => {
    expect(RECOMMENDED_PROJECT_TYPE_LABELS["businessapp"]).toBeDefined()
    expect(RECOMMENDED_PROJECT_TYPE_LABELS["webapp"]).toBeUndefined()
  })
})

describe("getLineItemLabel", () => {
  it("retourne le label pour un code connu", () => {
    expect(getLineItemLabel("BASE")).toBe("Conception et développement")
  })

  it("retourne un fallback générique pour un code inconnu", () => {
    const fallback = getLineItemLabel("UNKNOWN_FUTURE_CODE")
    expect(fallback).toBeTruthy()
    expect(fallback).not.toBe("UNKNOWN_FUTURE_CODE")
  })
})

describe("getRecurringItemLabel", () => {
  it("retourne le label pour un code connu", () => {
    expect(getRecurringItemLabel("RECURRING_CONTINUOUS_SEO")).toBe("SEO continu")
  })

  it("fallback pour code inconnu — jamais le code brut", () => {
    const fallback = getRecurringItemLabel("RECURRING_UNKNOWN")
    expect(fallback).not.toBe("RECURRING_UNKNOWN")
    expect(fallback.length).toBeGreaterThan(0)
  })
})

describe("getAssumptionMessage", () => {
  it("retourne un texte humain pour scope_to_confirm", () => {
    const msg = getAssumptionMessage("scope_to_confirm")
    expect(msg).toBeTruthy()
    expect(msg).not.toBe("scope_to_confirm")
  })

  it("fallback générique pour code inconnu — pas le code brut", () => {
    const msg = getAssumptionMessage("future_unknown_assumption")
    expect(msg).not.toBe("future_unknown_assumption")
    expect(msg.length).toBeGreaterThan(0)
  })
})

describe("getRecommendedProjectTypeLabel", () => {
  it("vitrinesite → label français", () => {
    expect(getRecommendedProjectTypeLabel("vitrinesite")).toBeTruthy()
    expect(getRecommendedProjectTypeLabel("vitrinesite")).not.toBe("vitrinesite")
  })

  it("businessapp → label français", () => {
    expect(getRecommendedProjectTypeLabel("businessapp")).toBeTruthy()
    expect(getRecommendedProjectTypeLabel("businessapp")).not.toBe("businessapp")
  })

  it("fallback pour code inconnu", () => {
    const label = getRecommendedProjectTypeLabel("unknown_future_type")
    expect(label.length).toBeGreaterThan(0)
    expect(label).not.toBe("unknown_future_type")
  })
})

describe("getPeriodLabel", () => {
  it("month → '/ mois'", () => {
    expect(getPeriodLabel("month")).toBe("/ mois")
  })

  it("fallback pour une période inconnue", () => {
    expect(getPeriodLabel("week")).toBe("/ week")
  })
})
