import { ref } from "vue"
import type {
  EstimateApiResponse,
  EstimateErrorKind,
  EstimateStatus,
  EstimateLineItemResponse,
  RecurringEstimateLineItemResponse,
} from "~/types/estimator-api"
import type { EstimatePayload } from "~/types/estimator"

export interface UseEstimatorApiOptions {
  endpoint?: string
  fetcher?: typeof fetch
}

export interface EstimatorApiState {
  estimateResult: Readonly<ReturnType<typeof ref<EstimateApiResponse | null>>>
  estimateStatus: Readonly<ReturnType<typeof ref<EstimateStatus>>>
  estimateError: Readonly<ReturnType<typeof ref<EstimateErrorKind | null>>>
  submitEstimate: (payload: EstimatePayload) => Promise<void>
  clearResult: () => void
}

function mapStatusCode(code: number): EstimateErrorKind {
  if (code === 400 || code === 422) return "validation_error"
  if (code === 403) return "forbidden"
  if (code === 413) return "payload_too_large"
  if (code === 429) return "rate_limited"
  return "server_error"
}

function mapOneOffItem(item: Record<string, unknown>): EstimateLineItemResponse {
  return {
    code: item["code"] as string,
    minimum: item["minimum"] as number,
    maximum: item["maximum"] as number,
    currency: item["currency"] as string,
  }
}

function mapRecurringItem(item: Record<string, unknown>): RecurringEstimateLineItemResponse {
  return {
    code: item["code"] as string,
    minimum: item["minimum"] as number,
    maximum: item["maximum"] as number,
    currency: item["currency"] as string,
    period: item["period"] as string,
  }
}

function mapResponse(raw: Record<string, unknown>): EstimateApiResponse {
  const estimate = raw["estimate"] as Record<string, unknown>
  return {
    status: "ok",
    requestId: raw["request_id"] as string,
    outcome: raw["outcome"] as "estimated" | "human_scoping_required",
    recommendedProjectType: raw["recommended_project_type"] as string,
    estimate: {
      minimum: estimate["minimum"] as number,
      maximum: estimate["maximum"] as number,
      currency: estimate["currency"] as string,
    },
    oneOffItems: (raw["one_off_items"] as Array<Record<string, unknown>>).map(mapOneOffItem),
    recurringItems: (raw["recurring_items"] as Array<Record<string, unknown>>).map(mapRecurringItem),
    assumptions: raw["assumptions"] as string[],
    pricingVersion: raw["pricing_version"] as string,
  }
}

export function useEstimatorApi(options: UseEstimatorApiOptions = {}): EstimatorApiState {
  const endpoint = options.endpoint ?? "/api/estimate"
  const fetcher = options.fetcher ?? globalThis.fetch

  const estimateResult = ref<EstimateApiResponse | null>(null)
  const estimateStatus = ref<EstimateStatus>("idle")
  const estimateError = ref<EstimateErrorKind | null>(null)

  function clearResult(): void {
    estimateResult.value = null
    estimateStatus.value = "idle"
    estimateError.value = null
  }

  async function submitEstimate(payload: EstimatePayload): Promise<void> {
    if (estimateStatus.value === "loading") return

    estimateStatus.value = "loading"
    estimateError.value = null
    estimateResult.value = null

    let response: Response
    try {
      response = await fetcher(endpoint, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        // EstimatePayload is already snake_case — serialize directly
        body: JSON.stringify(payload),
      })
    } catch {
      estimateStatus.value = "error"
      estimateError.value = "network_error"
      return
    }

    if (!response.ok) {
      estimateStatus.value = "error"
      estimateError.value = mapStatusCode(response.status)
      return
    }

    let raw: Record<string, unknown>
    try {
      raw = (await response.json()) as Record<string, unknown>
    } catch {
      estimateStatus.value = "error"
      estimateError.value = "unknown_error"
      return
    }

    estimateResult.value = mapResponse(raw)
    estimateStatus.value = "success"
  }

  return {
    estimateResult,
    estimateStatus,
    estimateError,
    submitEstimate,
    clearResult,
  }
}
