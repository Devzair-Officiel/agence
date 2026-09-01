/**
 * Couche événements estimateur — no-op en V1.
 *
 * Contrat de l'interface : les événements et leurs propriétés sont typés
 * pour garantir qu'aucune donnée PII n'est transmise par inadvertance lors
 * de l'intégration d'un provider futur.
 *
 * Règles PII :
 * - Aucune propriété ne doit contenir : name, email, phone, company,
 *   additionalFeatureNote, proposalText, relevanceText, lead_id.
 * - Seules des valeurs non-identifiantes sont acceptées (codes enum, version).
 *
 * Pour connecter un provider (Plausible, Fathom, Matomo…) :
 *   1. Remplacer les corps des fonctions `track*` par les appels du provider.
 *   2. Vérifier que le consentement est obtenu si le provider utilise des cookies.
 *   3. Ne jamais passer les propriétés PII listées ci-dessus.
 */

// ─── Types d'événements autorisés ───────────────────────────────────────────

export type EstimatorEventName =
  | 'estimator_started'
  | 'estimator_step_completed'
  | 'estimator_estimate_requested'
  | 'estimator_estimate_completed'
  | 'estimator_answers_modified'
  | 'estimator_lead_form_opened'
  | 'estimator_lead_submitted'
  | 'estimator_partnership_form_opened'
  | 'estimator_partnership_submitted'

/** Propriétés autorisées — jamais de PII. */
export interface EstimatorEventProps {
  step_key?: string
  project_type?: string
  outcome?: string
  pricing_version?: string
  total_steps?: number
}

export function useEstimatorAnalytics() {
  /**
   * Enregistre un événement estimateur.
   * No-op en V1 : aucun provider connecté, aucun cookie créé.
   */
  function track(_event: EstimatorEventName, _props?: EstimatorEventProps): void {
    // No-op — connect a privacy-first provider here when approved.
    // Example: plausible(_event, { props: _props })
  }

  return { track }
}
