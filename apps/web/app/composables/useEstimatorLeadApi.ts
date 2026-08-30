import { ref } from "vue"
import type { EstimateLeadErrorKind, EstimateLeadPayload, EstimateLeadStatus } from "~/types/estimator-lead"

export interface UseEstimatorLeadApiOptions {
  endpoint?: string
  fetcher?: typeof fetch
}

export interface EstimatorLeadApiState {
  leadStatus: Readonly<ReturnType<typeof ref<EstimateLeadStatus>>>
  leadError: Readonly<ReturnType<typeof ref<EstimateLeadErrorKind | null>>>
  submitLead: (payload: EstimateLeadPayload) => Promise<void>
  resetLead: () => void
}

function mapStatusCode(code: number): EstimateLeadErrorKind {
  if (code === 400 || code === 422) return "validation_error"
  if (code === 403) return "forbidden"
  if (code === 413) return "payload_too_large"
  if (code === 429) return "rate_limited"
  return "server_error"
}

export function useEstimatorLeadApi(options: UseEstimatorLeadApiOptions = {}): EstimatorLeadApiState {
  const endpoint = options.endpoint ?? "/api/estimate/lead"
  const fetcher = options.fetcher ?? globalThis.fetch

  const leadStatus = ref<EstimateLeadStatus>("idle")
  const leadError = ref<EstimateLeadErrorKind | null>(null)

  function resetLead(): void {
    leadStatus.value = "idle"
    leadError.value = null
  }

  async function submitLead(payload: EstimateLeadPayload): Promise<void> {
    if (leadStatus.value === "loading") return

    leadStatus.value = "loading"
    leadError.value = null

    let response: Response
    try {
      response = await fetcher(endpoint, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify(payload),
      })
    } catch {
      leadStatus.value = "error"
      leadError.value = "network_error"
      return
    }

    // 201 = success, 202 = honeypot (silencieux côté UX)
    if (response.status === 201 || response.status === 202) {
      leadStatus.value = "success"
      return
    }

    leadStatus.value = "error"
    leadError.value = mapStatusCode(response.status)
  }

  return {
    leadStatus,
    leadError,
    submitLead,
    resetLead,
  }
}
