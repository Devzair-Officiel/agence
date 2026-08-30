import { describe, expect, it, vi } from "vitest"
import { useEstimatorLeadApi } from "~/composables/useEstimatorLeadApi"
import type { EstimateLeadPayload } from "~/types/estimator-lead"

const BASE_PAYLOAD: EstimateLeadPayload = {
  name: "Jean Dupont",
  email: "jean@example.com",
  project_type: "vitrinesite",
  objectives: [],
  current_situation: "none",
  scale: "small",
  features: [],
  content_needs: [],
  visibility_needs: [],
  care_needs: [],
}

function makeFetcher(status: number, body: unknown = { status: "ok" }) {
  return vi.fn().mockResolvedValue({
    ok: status >= 200 && status < 300,
    status,
    json: () => Promise.resolve(body),
  })
}

function makeNetworkError() {
  return vi.fn().mockRejectedValue(new TypeError("Failed to fetch"))
}

// ─── État initial ─────────────────────────────────────────────────────────

describe("useEstimatorLeadApi — état initial", () => {
  it("idle au départ", () => {
    const { leadStatus, leadError } = useEstimatorLeadApi()
    expect(leadStatus.value).toBe("idle")
    expect(leadError.value).toBeNull()
  })
})

// ─── POST réussi ──────────────────────────────────────────────────────────

describe("useEstimatorLeadApi — POST /api/estimate/lead", () => {
  it("envoie une requête POST vers /api/estimate/lead", async () => {
    const fetcher = makeFetcher(201)
    const { submitLead } = useEstimatorLeadApi({ fetcher })
    await submitLead(BASE_PAYLOAD)
    expect(fetcher).toHaveBeenCalledOnce()
    const [url, init] = fetcher.mock.calls[0]
    expect(url).toBe("/api/estimate/lead")
    expect(init.method).toBe("POST")
  })

  it("status 'success' après 201", async () => {
    const { leadStatus, submitLead } = useEstimatorLeadApi({ fetcher: makeFetcher(201) })
    await submitLead(BASE_PAYLOAD)
    expect(leadStatus.value).toBe("success")
  })

  it("status 'success' après 202 (honeypot silencieux)", async () => {
    const { leadStatus, submitLead } = useEstimatorLeadApi({ fetcher: makeFetcher(202) })
    await submitLead(BASE_PAYLOAD)
    expect(leadStatus.value).toBe("success")
  })

  it("corps POST est du JSON valide contenant name + email", async () => {
    const fetcher = makeFetcher(201)
    const { submitLead } = useEstimatorLeadApi({ fetcher })
    await submitLead(BASE_PAYLOAD)
    const body = JSON.parse(fetcher.mock.calls[0][1].body)
    expect(body.name).toBe("Jean Dupont")
    expect(body.email).toBe("jean@example.com")
  })

  it("corps POST ne contient aucun champ de prix", async () => {
    const fetcher = makeFetcher(201)
    const { submitLead } = useEstimatorLeadApi({ fetcher })
    await submitLead(BASE_PAYLOAD)
    const body = JSON.parse(fetcher.mock.calls[0][1].body)
    expect(body).not.toHaveProperty("estimate")
    expect(body).not.toHaveProperty("one_off_items")
    expect(body).not.toHaveProperty("pricing_version")
  })

  it("protection double-submit : une seule requête si appelé deux fois simultanément", async () => {
    const fetcher = makeFetcher(201)
    const { submitLead } = useEstimatorLeadApi({ fetcher })

    // Les deux appels sont lancés sans attendre le premier
    const p1 = submitLead(BASE_PAYLOAD)
    const p2 = submitLead(BASE_PAYLOAD)
    await Promise.all([p1, p2])

    expect(fetcher).toHaveBeenCalledOnce()
  })
})

// ─── Gestion d'erreurs ────────────────────────────────────────────────────

describe("useEstimatorLeadApi — erreurs", () => {
  it("erreur réseau → error + network_error", async () => {
    const { leadStatus, leadError, submitLead } = useEstimatorLeadApi({ fetcher: makeNetworkError() })
    await submitLead(BASE_PAYLOAD)
    expect(leadStatus.value).toBe("error")
    expect(leadError.value).toBe("network_error")
  })

  it("429 → error + rate_limited", async () => {
    const { leadStatus, leadError, submitLead } = useEstimatorLeadApi({ fetcher: makeFetcher(429) })
    await submitLead(BASE_PAYLOAD)
    expect(leadStatus.value).toBe("error")
    expect(leadError.value).toBe("rate_limited")
  })

  it("400 → error + validation_error", async () => {
    const { leadStatus, leadError, submitLead } = useEstimatorLeadApi({ fetcher: makeFetcher(400) })
    await submitLead(BASE_PAYLOAD)
    expect(leadStatus.value).toBe("error")
    expect(leadError.value).toBe("validation_error")
  })

  it("500 → error + server_error", async () => {
    const { leadStatus, leadError, submitLead } = useEstimatorLeadApi({ fetcher: makeFetcher(500) })
    await submitLead(BASE_PAYLOAD)
    expect(leadStatus.value).toBe("error")
    expect(leadError.value).toBe("server_error")
  })
})

// ─── resetLead ────────────────────────────────────────────────────────────

describe("useEstimatorLeadApi — resetLead", () => {
  it("remet à zéro status et error", async () => {
    const { leadStatus, leadError, submitLead, resetLead } = useEstimatorLeadApi({ fetcher: makeFetcher(429) })
    await submitLead(BASE_PAYLOAD)
    expect(leadStatus.value).toBe("error")

    resetLead()
    expect(leadStatus.value).toBe("idle")
    expect(leadError.value).toBeNull()
  })
})
