import { describe, it, expect, vi } from "vitest"
import { useEstimatorPartnershipApi } from "~/composables/useEstimatorPartnershipApi"
import type { EstimatePartnershipPayload } from "~/types/estimator-partnership"

const BASE_PAYLOAD: EstimatePartnershipPayload = {
  project_type: "vitrinesite",
  objectives: [],
  current_situation: "none",
  scale: "small",
  features: [],
  content_needs: [],
  visibility_needs: [],
  care_needs: [],
  name: "Jean Test",
  email: "jean@test.com",
  partnership_type: "revenue_share",
  project_stage: "launched",
  proposal_text: "Ma proposition.",
}

function makeApi(status: number, body: unknown = {}) {
  const fetcher = vi.fn().mockResolvedValue({
    status,
    json: () => Promise.resolve(body),
  } as Response)
  return { api: useEstimatorPartnershipApi({ fetcher }), fetcher }
}

describe("useEstimatorPartnershipApi", () => {
  it("démarre en idle", () => {
    const { api } = makeApi(201)
    expect(api.partnershipStatus.value).toBe("idle")
    expect(api.partnershipError.value).toBeNull()
    expect(api.partnershipProposalId.value).toBeNull()
  })

  it("passe en loading puis success sur 201", async () => {
    const { api } = makeApi(201, { proposal_id: "prop-uuid-123", status: "submitted" })
    await api.submitPartnership(BASE_PAYLOAD)
    expect(api.partnershipStatus.value).toBe("success")
    expect(api.partnershipProposalId.value).toBe("prop-uuid-123")
  })

  it("passe en success sur 202 (honeypot silencieux)", async () => {
    const { api } = makeApi(202)
    await api.submitPartnership(BASE_PAYLOAD)
    expect(api.partnershipStatus.value).toBe("success")
  })

  it("double submit guard : n'envoie pas deux fois", async () => {
    const fetcher = vi.fn().mockReturnValue(new Promise(() => {}))
    const api = useEstimatorPartnershipApi({ fetcher })

    void api.submitPartnership(BASE_PAYLOAD)
    void api.submitPartnership(BASE_PAYLOAD)

    expect(fetcher).toHaveBeenCalledTimes(1)
  })

  it("passe en error network si fetch échoue", async () => {
    const fetcher = vi.fn().mockRejectedValue(new Error("Network"))
    const api = useEstimatorPartnershipApi({ fetcher })
    await api.submitPartnership(BASE_PAYLOAD)
    expect(api.partnershipStatus.value).toBe("error")
    expect(api.partnershipError.value).toBe("network_error")
  })

  it("mappe 400 → validation_error", async () => {
    const { api } = makeApi(400)
    await api.submitPartnership(BASE_PAYLOAD)
    expect(api.partnershipError.value).toBe("validation_error")
  })

  it("mappe 429 → rate_limited", async () => {
    const { api } = makeApi(429)
    await api.submitPartnership(BASE_PAYLOAD)
    expect(api.partnershipError.value).toBe("rate_limited")
  })

  it("mappe 403 → forbidden", async () => {
    const { api } = makeApi(403)
    await api.submitPartnership(BASE_PAYLOAD)
    expect(api.partnershipError.value).toBe("forbidden")
  })

  it("mappe 500 → server_error", async () => {
    const { api } = makeApi(500)
    await api.submitPartnership(BASE_PAYLOAD)
    expect(api.partnershipError.value).toBe("server_error")
  })

  it("resetPartnership remet idle", async () => {
    const { api } = makeApi(400)
    await api.submitPartnership(BASE_PAYLOAD)
    expect(api.partnershipStatus.value).toBe("error")
    api.resetPartnership()
    expect(api.partnershipStatus.value).toBe("idle")
    expect(api.partnershipError.value).toBeNull()
    expect(api.partnershipProposalId.value).toBeNull()
  })

  it("envoie un POST vers /api/estimate/partnership", async () => {
    const { api, fetcher } = makeApi(201, { proposal_id: "x" })
    await api.submitPartnership(BASE_PAYLOAD)
    expect(fetcher).toHaveBeenCalledWith(
      "/api/estimate/partnership",
      expect.objectContaining({ method: "POST" }),
    )
  })

  it("le payload ne contient jamais de prix frontend", async () => {
    const { api, fetcher } = makeApi(201, { proposal_id: "x" })
    await api.submitPartnership(BASE_PAYLOAD)
    const body = JSON.parse((fetcher.mock.calls[0][1] as RequestInit).body as string)
    expect(body.estimate).toBeUndefined()
    expect(body.pricing_version).toBeUndefined()
    expect(body.one_off_items).toBeUndefined()
  })
})
