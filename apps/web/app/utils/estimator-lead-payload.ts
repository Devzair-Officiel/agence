import type { EstimatePayload } from "~/types/estimator"
import type { EstimateLeadPayload } from "~/types/estimator-lead"

export interface LeadContactFields {
  name: string
  email: string
  phone?: string
  company?: string
  additionalFeatureNote?: string
}

/**
 * Construit le payload POST /api/estimate/lead à partir du questionnaire
 * et des champs contact. Aucun champ de prix n'est inclus.
 *
 * `additionalFeatureNote` est incluse dans le lead (stockée, non chiffrée) ;
 * elle n'était pas transmise à POST /api/estimate.
 */
export function toLeadPayload(
  questionnaire: EstimatePayload,
  contact: LeadContactFields,
): EstimateLeadPayload {
  const payload: EstimateLeadPayload = {
    ...questionnaire,
    name: contact.name.trim(),
    email: contact.email.trim(),
  }

  if (contact.phone && contact.phone.trim() !== "") {
    payload.phone = contact.phone.trim()
  }

  if (contact.company && contact.company.trim() !== "") {
    payload.company = contact.company.trim()
  }

  if (contact.additionalFeatureNote && contact.additionalFeatureNote.trim() !== "") {
    payload.additional_feature_note = contact.additionalFeatureNote.trim()
  }

  return payload
}
