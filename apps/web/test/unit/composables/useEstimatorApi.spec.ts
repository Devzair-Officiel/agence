import { describe, expect, it, vi } from "vitest"
import { useEstimatorApi } from "~/composables/useEstimatorApi"
import type { EstimatePayload } from "~/types/estimator"

const BASE_PAYLOAD: EstimatePayload = {
  project_type: "vitrinesite",
  objectives: [],
  current_situation: "none",
  scale: "small",
  features: [],
  content_needs: [],
  visibility_needs: [],
  care_needs: [],
}

const ESTIMATE_SUCCESS_BODY = {
  status: "ok",
  request_id: "req-123",
  outcome: "estimated",
  recommended_project_type: "vitrinesite",
  estimate: { minimum: 90_000, maximum: 130_000, currency: "EUR" },
  one_off_items: [
    { code: "BASE", minimum: 90_000, maximum: 130_000, currency: "EUR" },
  ],
  recurring_items: [
    {
      code: "RECURRING_ESSENTIAL_MAINTENANCE",
      minimum: 4_900,
      maximum: 6_900,
      currency: "EUR",
      period: "month",
    },
  ],
  assumptions: ["scope_to_confirm"],
  pricing_version: "2026-v1",
}

function makeFetcher(status: number, body: unknown) {
  return vi.fn().mockResolvedValue({
    ok: status >= 200 && status < 300,
    status,
    json: () => Promise.resolve(body),
  })
}

describe("useEstimatorApi — état initial", () => {
  it("idle au départ", () => {
    const { estimateStatus, estimateResult, estimateError } = useEstimatorApi()
    expect(estimateStatus.value).toBe("idle")
    expect(estimateResult.value).toBeNull()
    expect(estimateError.value).toBeNull()
  })
})

describe("useEstimatorApi — POST /api/estimate", () => {
  it("envoie bien une requête POST vers /api/estimate", async () => {
    const fetcher = makeFetcher(200, ESTIMATE_SUCCESS_BODY)
    const { submitEstimate } = useEstimatorApi({ fetcher })
    await submitEstimate(BASE_PAYLOAD)
    expect(fetcher).toHaveBeenCalledOnce()
    const [url, init] = fetcher.mock.calls[0] as [string, RequestInit]
    expect(url).toBe("/api/estimate")
    expect(init.method).toBe("POST")
    expect(init.headers).toMatchObject({ "Content-Type": "application/json" })
  })

  it("le payload sérialisé contient project_type en snake_case — sans additionalFeatureNote", async () => {
    const fetcher = makeFetcher(200, ESTIMATE_SUCCESS_BODY)
    const { submitEstimate } = useEstimatorApi({ fetcher })
    await submitEstimate(BASE_PAYLOAD)
    const [, init] = fetcher.mock.calls[0] as [string, RequestInit]
    const body = JSON.parse(init.body as string) as Record<string, unknown>
    expect(body["project_type"]).toBe("vitrinesite")
    expect(body["additionalFeatureNote"]).toBeUndefined()
    expect(body["additional_feature_note"]).toBeUndefined()
  })

  it("status success après 200", async () => {
    const { submitEstimate, estimateStatus } = useEstimatorApi({
      fetcher: makeFetcher(200, ESTIMATE_SUCCESS_BODY),
    })
    await submitEstimate(BASE_PAYLOAD)
    expect(estimateStatus.value).toBe("success")
  })

  it("le résultat est mappé correctement", async () => {
    const { submitEstimate, estimateResult } = useEstimatorApi({
      fetcher: makeFetcher(200, ESTIMATE_SUCCESS_BODY),
    })
    await submitEstimate(BASE_PAYLOAD)
    const r = estimateResult.value!
    expect(r.requestId).toBe("req-123")
    expect(r.outcome).toBe("estimated")
    expect(r.recommendedProjectType).toBe("vitrinesite")
    expect(r.estimate.minimum).toBe(90_000)
    expect(r.estimate.maximum).toBe(130_000)
    expect(r.estimate.currency).toBe("EUR")
    expect(r.oneOffItems[0].code).toBe("BASE")
    expect(r.oneOffItems[0].minimum).toBe(90_000)
    expect(r.oneOffItems[0].maximum).toBe(130_000)
    expect(r.recurringItems[0].code).toBe("RECURRING_ESSENTIAL_MAINTENANCE")
    expect(r.recurringItems[0].minimum).toBe(4_900)
    expect(r.recurringItems[0].maximum).toBe(6_900)
    expect(r.recurringItems[0].period).toBe("month")
    expect(r.assumptions).toEqual(["scope_to_confirm"])
    expect(r.pricingVersion).toBe("2026-v1")
  })

  it("human_scoping_required est correctement mappé", async () => {
    const humanBody = {
      ...ESTIMATE_SUCCESS_BODY,
      outcome: "human_scoping_required",
      recommended_project_type: "unknown",
      assumptions: ["unknown_project_requires_human_scoping"],
    }
    const { submitEstimate, estimateResult } = useEstimatorApi({
      fetcher: makeFetcher(200, humanBody),
    })
    await submitEstimate(BASE_PAYLOAD)
    expect(estimateResult.value?.outcome).toBe("human_scoping_required")
    expect(estimateResult.value?.assumptions).toContain(
      "unknown_project_requires_human_scoping",
    )
  })
})

describe("useEstimatorApi — erreurs HTTP", () => {
  it.each([
    [400, "validation_error"],
    [422, "validation_error"],
    [403, "forbidden"],
    [413, "payload_too_large"],
    [429, "rate_limited"],
    [500, "server_error"],
    [503, "server_error"],
  ])("HTTP %i → estimateError = %s", async (status, expectedError) => {
    const { submitEstimate, estimateStatus, estimateError } = useEstimatorApi({
      fetcher: makeFetcher(status, {}),
    })
    await submitEstimate(BASE_PAYLOAD)
    expect(estimateStatus.value).toBe("error")
    expect(estimateError.value).toBe(expectedError)
    expect(estimateStatus.value).not.toBe("loading")
  })

  it("network error → estimateError = network_error", async () => {
    const fetcher = vi.fn().mockRejectedValue(new TypeError("Failed to fetch"))
    const { submitEstimate, estimateError, estimateStatus } = useEstimatorApi({ fetcher })
    await submitEstimate(BASE_PAYLOAD)
    expect(estimateStatus.value).toBe("error")
    expect(estimateError.value).toBe("network_error")
  })
})

describe("useEstimatorApi — double submit", () => {
  it("un second appel pendant loading est ignoré", async () => {
    let resolve!: (v: unknown) => void
    const fetcher = vi.fn().mockReturnValue(
      new Promise((r) => {
        resolve = r
      }),
    )
    const { submitEstimate, estimateStatus } = useEstimatorApi({ fetcher })

    const p1 = submitEstimate(BASE_PAYLOAD)
    expect(estimateStatus.value).toBe("loading")

    // Second appel pendant le loading → ignoré
    await submitEstimate(BASE_PAYLOAD)
    expect(fetcher).toHaveBeenCalledOnce()

    resolve({ ok: true, status: 200, json: () => Promise.resolve(ESTIMATE_SUCCESS_BODY) })
    await p1
  })
})

describe("useEstimatorApi — clearResult", () => {
  it("remet l'état à idle après success", async () => {
    const { submitEstimate, estimateStatus, estimateResult, clearResult } = useEstimatorApi({
      fetcher: makeFetcher(200, ESTIMATE_SUCCESS_BODY),
    })
    await submitEstimate(BASE_PAYLOAD)
    expect(estimateStatus.value).toBe("success")

    clearResult()
    expect(estimateStatus.value).toBe("idle")
    expect(estimateResult.value).toBeNull()
  })

  it("remet l'état à idle après error", async () => {
    const { submitEstimate, estimateStatus, estimateError, clearResult } = useEstimatorApi({
      fetcher: makeFetcher(500, {}),
    })
    await submitEstimate(BASE_PAYLOAD)
    expect(estimateStatus.value).toBe("error")

    clearResult()
    expect(estimateStatus.value).toBe("idle")
    expect(estimateError.value).toBeNull()
  })
})
