import { describe, expect, it } from "vitest"
import { toLeadPayload } from "~/utils/estimator-lead-payload"
import type { EstimatePayload } from "~/types/estimator"

const BASE_QUESTIONNAIRE: EstimatePayload = {
  project_type: "vitrinesite",
  objectives: [],
  current_situation: "none",
  scale: "small",
  features: ["contact_form"],
  content_needs: [],
  visibility_needs: ["seo_basic"],
  care_needs: [],
}

describe("toLeadPayload", () => {
  it("inclut les champs questionnaire dans le payload", () => {
    const payload = toLeadPayload(BASE_QUESTIONNAIRE, {
      name: "Jean Dupont",
      email: "jean@example.com",
    })

    expect(payload.project_type).toBe("vitrinesite")
    expect(payload.features).toEqual(["contact_form"])
    expect(payload.visibility_needs).toEqual(["seo_basic"])
  })

  it("inclut name et email", () => {
    const payload = toLeadPayload(BASE_QUESTIONNAIRE, {
      name: "  Marie Martin  ",
      email: "marie@example.com",
    })

    expect(payload.name).toBe("Marie Martin")
    expect(payload.email).toBe("marie@example.com")
  })

  it("trim les espaces sur name et email", () => {
    const payload = toLeadPayload(BASE_QUESTIONNAIRE, {
      name: "  Jean  ",
      email: "  jean@example.com  ",
    })

    expect(payload.name).toBe("Jean")
    expect(payload.email).toBe("jean@example.com")
  })

  it("inclut phone si non vide", () => {
    const payload = toLeadPayload(BASE_QUESTIONNAIRE, {
      name: "Jean",
      email: "jean@example.com",
      phone: "+33 6 12 34 56 78",
    })

    expect(payload.phone).toBe("+33 6 12 34 56 78")
  })

  it("n'inclut pas phone si vide ou absent", () => {
    const payloadAbsent = toLeadPayload(BASE_QUESTIONNAIRE, {
      name: "Jean",
      email: "jean@example.com",
    })
    const payloadVide = toLeadPayload(BASE_QUESTIONNAIRE, {
      name: "Jean",
      email: "jean@example.com",
      phone: "  ",
    })

    expect(payloadAbsent).not.toHaveProperty("phone")
    expect(payloadVide).not.toHaveProperty("phone")
  })

  it("inclut additional_feature_note si non vide", () => {
    const payload = toLeadPayload(BASE_QUESTIONNAIRE, {
      name: "Jean",
      email: "jean@example.com",
      additionalFeatureNote: "Connexion ERP",
    })

    expect(payload.additional_feature_note).toBe("Connexion ERP")
  })

  it("n'inclut pas additional_feature_note si vide ou absent", () => {
    const payloadAbsent = toLeadPayload(BASE_QUESTIONNAIRE, {
      name: "Jean",
      email: "jean@example.com",
    })

    expect(payloadAbsent).not.toHaveProperty("additional_feature_note")
  })

  it("ne contient aucun champ de prix (estimate, one_off_items, pricing_version)", () => {
    const payload = toLeadPayload(BASE_QUESTIONNAIRE, {
      name: "Jean",
      email: "jean@example.com",
    }) as Record<string, unknown>

    expect(payload).not.toHaveProperty("estimate")
    expect(payload).not.toHaveProperty("one_off_items")
    expect(payload).not.toHaveProperty("recurring_items")
    expect(payload).not.toHaveProperty("pricing_version")
  })
})
