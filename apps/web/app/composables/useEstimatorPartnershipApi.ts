import { ref } from "vue"
import type {
  EstimatePartnershipErrorKind,
  EstimatePartnershipPayload,
  EstimatePartnershipStatus,
} from "~/types/estimator-partnership"

export interface UseEstimatorPartnershipApiOptions {
  endpoint?: string
  fetcher?: typeof fetch
}

export interface EstimatorPartnershipApiState {
  partnershipStatus: Readonly<ReturnType<typeof ref<EstimatePartnershipStatus>>>
  partnershipError: Readonly<ReturnType<typeof ref<EstimatePartnershipErrorKind | null>>>
  partnershipProposalId: Readonly<ReturnType<typeof ref<string | null>>>
  submitPartnership: (payload: EstimatePartnershipPayload) => Promise<void>
  resetPartnership: () => void
}

function mapStatusCode(code: number): EstimatePartnershipErrorKind {
  if (code === 400 || code === 422) return "validation_error"
  if (code === 403) return "forbidden"
  if (code === 413) return "payload_too_large"
  if (code === 429) return "rate_limited"
  return "server_error"
}

export function useEstimatorPartnershipApi(
  options: UseEstimatorPartnershipApiOptions = {},
): EstimatorPartnershipApiState {
  const endpoint = options.endpoint ?? "/api/estimate/partnership"
  const fetcher = options.fetcher ?? globalThis.fetch

  const partnershipStatus = ref<EstimatePartnershipStatus>("idle")
  const partnershipError = ref<EstimatePartnershipErrorKind | null>(null)
  const partnershipProposalId = ref<string | null>(null)

  function resetPartnership(): void {
    partnershipStatus.value = "idle"
    partnershipError.value = null
    partnershipProposalId.value = null
  }

  async function submitPartnership(payload: EstimatePartnershipPayload): Promise<void> {
    if (partnershipStatus.value === "loading") return

    partnershipStatus.value = "loading"
    partnershipError.value = null
    partnershipProposalId.value = null

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
      partnershipStatus.value = "error"
      partnershipError.value = "network_error"
      return
    }

    // 201 = success, 202 = honeypot (silencieux côté UX)
    if (response.status === 201 || response.status === 202) {
      try {
        const data = await response.json()
        if (data.proposal_id) {
          partnershipProposalId.value = data.proposal_id
        }
      } catch {
        // JSON optionnel — pas bloquant
      }
      partnershipStatus.value = "success"
      return
    }

    partnershipStatus.value = "error"
    partnershipError.value = mapStatusCode(response.status)
  }

  return {
    partnershipStatus,
    partnershipError,
    partnershipProposalId,
    submitPartnership,
    resetPartnership,
  }
}
