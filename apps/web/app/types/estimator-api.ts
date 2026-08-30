export type EstimateOutcome = "estimated" | "human_scoping_required"

export type EstimateStatus = "idle" | "loading" | "success" | "error"

export type EstimateErrorKind =
  | "validation_error"
  | "forbidden"
  | "payload_too_large"
  | "rate_limited"
  | "server_error"
  | "network_error"
  | "unknown_error"

export interface EstimateMoneyRange {
  minimum: number
  maximum: number
  currency: string
}

/** One-off line item — investissement initial. Montants en centimes (minor units). */
export interface EstimateLineItemResponse {
  code: string
  minimum: number
  maximum: number
  currency: string
}

/** Ligne récurrente. Montants en centimes. period = "month" en V1. */
export interface RecurringEstimateLineItemResponse {
  code: string
  minimum: number
  maximum: number
  currency: string
  period: string
}

export interface EstimateApiResponse {
  status: "ok"
  requestId: string
  outcome: EstimateOutcome
  recommendedProjectType: string
  estimate: EstimateMoneyRange
  oneOffItems: EstimateLineItemResponse[]
  recurringItems: RecurringEstimateLineItemResponse[]
  assumptions: string[]
  pricingVersion: string
}
