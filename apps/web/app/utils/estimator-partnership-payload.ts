import type { EstimatePayload } from "~/types/estimator"
import type {
  EstimatePartnershipPayload,
  GeneratesRevenueCode,
  PartnershipInitialContact,
  PartnershipTypeCode,
  ProjectStageCode,
} from "~/types/estimator-partnership"

export interface PartnershipFormFields {
  partnershipType: PartnershipTypeCode
  projectStage: ProjectStageCode
  generatesRevenue?: GeneratesRevenueCode
  proposalText: string
  relevanceText?: string
}

/**
 * Construit le payload POST /api/estimate/partnership.
 *
 * Aucun prix frontend inclus. Le backend recalcule l'estimation.
 * Aucun champ percentage, equity, commission, valuation — interdit en V1.
 */
export function toPartnershipPayload(
  questionnaire: EstimatePayload,
  contact: PartnershipInitialContact & { name: string; email: string },
  partnership: PartnershipFormFields,
): EstimatePartnershipPayload {
  const payload: EstimatePartnershipPayload = {
    ...questionnaire,
    name: contact.name.trim(),
    email: contact.email.trim(),
    partnership_type: partnership.partnershipType,
    project_stage: partnership.projectStage,
    proposal_text: partnership.proposalText.trim(),
  }

  if (contact.phone && contact.phone.trim() !== "") {
    payload.phone = contact.phone.trim()
  }

  if (contact.company && contact.company.trim() !== "") {
    payload.company = contact.company.trim()
  }

  if (contact.leadRequestId && contact.leadRequestId.trim() !== "") {
    payload.lead_request_id = contact.leadRequestId.trim()
  }

  if (partnership.generatesRevenue) {
    payload.generates_revenue = partnership.generatesRevenue
  }

  if (partnership.relevanceText && partnership.relevanceText.trim() !== "") {
    payload.relevance_text = partnership.relevanceText.trim()
  }

  return payload
}
