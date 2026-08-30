import type { EstimatePayload } from "~/types/estimator"

/**
 * Nature de la proposition de partenariat.
 * Ces codes sont des catégories de qualification — ils ne signifient pas
 * que Devzair accepte ces modèles.
 */
export type PartnershipTypeCode =
  | "revenue_share"
  | "equity_stake"
  | "commercial_collaboration"
  | "service_exchange"
  | "other"

/**
 * État d'avancement du projet au moment de la proposition.
 */
export type ProjectStageCode =
  | "idea"
  | "in_creation"
  | "launched"
  | "with_clients"
  | "established"

/**
 * Indicateur de revenus (qualitatif uniquement — aucun chiffre demandé en V1).
 */
export type GeneratesRevenueCode = "yes" | "not_yet" | "prefer_to_discuss"

/**
 * Payload envoyé à POST /api/estimate/partnership.
 *
 * Contient : questionnaire + champs contact + qualification partenariat.
 * Aucun prix frontend : le backend recalcule systématiquement l'estimation.
 * Aucun champ percentage, equity, commission, valuation — interdit en V1.
 */
export interface EstimatePartnershipPayload extends EstimatePayload {
  name: string
  email: string
  phone?: string
  company?: string
  lead_request_id?: string
  partnership_type: PartnershipTypeCode
  project_stage: ProjectStageCode
  generates_revenue?: GeneratesRevenueCode
  proposal_text: string
  relevance_text?: string
  website?: string
}

export type EstimatePartnershipStatus = "idle" | "loading" | "success" | "error"

export type EstimatePartnershipErrorKind =
  | "validation_error"
  | "rate_limited"
  | "forbidden"
  | "payload_too_large"
  | "network_error"
  | "server_error"
  | "unknown_error"

/** Champs de contact pré-remplis depuis un lead EST-6 déjà soumis. */
export interface PartnershipInitialContact {
  name: string
  email: string
  phone?: string
  company?: string
  leadRequestId?: string
}
