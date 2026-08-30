import type { EstimatePayload } from "~/types/estimator"

/**
 * Payload envoyé à POST /api/estimate/lead.
 *
 * Contient les champs contact + questionnaire (reprise de EstimatePayload).
 * Aucun prix : le backend recalcule l'estimation à partir du questionnaire.
 */
export interface EstimateLeadPayload extends EstimatePayload {
  name: string
  email: string
  phone?: string
  company?: string
  additional_feature_note?: string
  website?: string
}

export type EstimateLeadStatus = "idle" | "loading" | "success" | "error"

export type EstimateLeadErrorKind =
  | "validation_error"
  | "rate_limited"
  | "forbidden"
  | "payload_too_large"
  | "network_error"
  | "server_error"
  | "unknown_error"
