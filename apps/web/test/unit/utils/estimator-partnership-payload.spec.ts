import { describe, it, expect } from "vitest"
import { toPartnershipPayload } from "~/utils/estimator-partnership-payload"
import type { EstimatePayload } from "~/types/estimator"

const BASE_QUESTIONNAIRE: EstimatePayload = {
  project_type: "vitrinesite",
  objectives: [],
  current_situation: "none",
  scale: "small",
  features: [],
  content_needs: [],
  visibility_needs: [],
  care_needs: [],
}

describe("toPartnershipPayload", () => {
  it("includes all questionnaire fields", () => {
    const payload = toPartnershipPayload(
      BASE_QUESTIONNAIRE,
      { name: "Jean", email: "jean@test.com" },
      { partnershipType: "revenue_share", projectStage: "launched", proposalText: "Ma proposition." },
    )

    expect(payload.project_type).toBe("vitrinesite")
    expect(payload.scale).toBe("small")
    expect(payload.features).toEqual([])
  })

  it("includes required contact fields", () => {
    const payload = toPartnershipPayload(
      BASE_QUESTIONNAIRE,
      { name: "  Jean  ", email: "  jean@test.com  " },
      { partnershipType: "equity_stake", projectStage: "idea", proposalText: "Proposition." },
    )

    expect(payload.name).toBe("Jean")
    expect(payload.email).toBe("jean@test.com")
  })

  it("includes optional contact fields when provided", () => {
    const payload = toPartnershipPayload(
      BASE_QUESTIONNAIRE,
      { name: "Jean", email: "jean@test.com", phone: "+33 6 00 00 00 00", company: "Startup SAS" },
      { partnershipType: "other", projectStage: "with_clients", proposalText: "Proposition." },
    )

    expect(payload.phone).toBe("+33 6 00 00 00 00")
    expect(payload.company).toBe("Startup SAS")
  })

  it("omits empty optional contact fields", () => {
    const payload = toPartnershipPayload(
      BASE_QUESTIONNAIRE,
      { name: "Jean", email: "jean@test.com", phone: "", company: "" },
      { partnershipType: "other", projectStage: "idea", proposalText: "Proposition." },
    )

    expect(payload.phone).toBeUndefined()
    expect(payload.company).toBeUndefined()
  })

  it("includes leadRequestId when provided", () => {
    const payload = toPartnershipPayload(
      BASE_QUESTIONNAIRE,
      { name: "Jean", email: "jean@test.com", leadRequestId: "req-abc-123" },
      { partnershipType: "revenue_share", projectStage: "established", proposalText: "Proposition." },
    )

    expect(payload.lead_request_id).toBe("req-abc-123")
  })

  it("omits leadRequestId when not provided", () => {
    const payload = toPartnershipPayload(
      BASE_QUESTIONNAIRE,
      { name: "Jean", email: "jean@test.com" },
      { partnershipType: "service_exchange", projectStage: "idea", proposalText: "Proposition." },
    )

    expect(payload.lead_request_id).toBeUndefined()
  })

  it("includes generatesRevenue when provided", () => {
    const payload = toPartnershipPayload(
      BASE_QUESTIONNAIRE,
      { name: "Jean", email: "jean@test.com" },
      { partnershipType: "commercial_collaboration", projectStage: "with_clients", proposalText: "Proposition.", generatesRevenue: "yes" },
    )

    expect(payload.generates_revenue).toBe("yes")
  })

  it("includes relevanceText when provided", () => {
    const payload = toPartnershipPayload(
      BASE_QUESTIONNAIRE,
      { name: "Jean", email: "jean@test.com" },
      { partnershipType: "equity_stake", projectStage: "launched", proposalText: "Proposition.", relevanceText: "Pertinent car croissance forte." },
    )

    expect(payload.relevance_text).toBe("Pertinent car croissance forte.")
  })

  it("trims proposalText", () => {
    const payload = toPartnershipPayload(
      BASE_QUESTIONNAIRE,
      { name: "Jean", email: "jean@test.com" },
      { partnershipType: "other", projectStage: "idea", proposalText: "  Ma proposition  " },
    )

    expect(payload.proposal_text).toBe("Ma proposition")
  })

  it("NEVER includes frontend estimate prices", () => {
    const payload = toPartnershipPayload(
      BASE_QUESTIONNAIRE,
      { name: "Jean", email: "jean@test.com" },
      { partnershipType: "revenue_share", projectStage: "launched", proposalText: "Proposition." },
    )

    expect((payload as Record<string, unknown>).estimate).toBeUndefined()
    expect((payload as Record<string, unknown>).pricing_version).toBeUndefined()
    expect((payload as Record<string, unknown>).one_off_items).toBeUndefined()
    expect((payload as Record<string, unknown>).recurring_items).toBeUndefined()
  })

  it("NEVER includes percentage or equity fields", () => {
    const payload = toPartnershipPayload(
      BASE_QUESTIONNAIRE,
      { name: "Jean", email: "jean@test.com" },
      { partnershipType: "equity_stake", projectStage: "idea", proposalText: "Proposition." },
    )

    expect((payload as Record<string, unknown>).percentage).toBeUndefined()
    expect((payload as Record<string, unknown>).equity).toBeUndefined()
    expect((payload as Record<string, unknown>).commissionRate).toBeUndefined()
    expect((payload as Record<string, unknown>).revenue_share_rate).toBeUndefined()
    expect((payload as Record<string, unknown>).valuation).toBeUndefined()
  })
})
