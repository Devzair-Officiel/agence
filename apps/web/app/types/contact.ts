/**
 * Types partagés du formulaire de contact.
 *
 * Ces contrats miroitent le DTO Symfony `App\Contact\Dto\ContactRequest` et la
 * table des réponses HTTP documentée dans `docs/adr/ADR-007-endpoint-contact-securite.md`.
 * Toute divergence côté client (règles plus laxistes) est explicitement acceptée :
 * l'autorité reste le serveur. Ces types ne sont *jamais* utilisés pour bloquer
 * une soumission — ils typent les valeurs manipulées par le composable et les
 * composants de rendu.
 */

export const PROJECT_TYPES = [
  "refonte",
  "creation",
  "seo",
  "audit",
  "autre",
] as const

export type ProjectType = (typeof PROJECT_TYPES)[number]

/** Valeurs du formulaire, dans l'état où elles vivent dans le composable. */
export interface ContactFormValues {
  name: string
  email: string
  company: string
  telephone: string
  projectType: ProjectType
  message: string
  consent: boolean
  /** Honeypot — doit rester vide, jamais lié à un champ utilisateur visible. */
  website: string
  /** Token Cloudflare Turnstile injecté par le widget ; null tant qu'absent. */
  turnstileToken: string | null
}

/** Codes retournés par l'API (cf. ADR-007). */
export type ContactErrorCode =
  | "validation_failed"
  | "origin_not_allowed"
  | "turnstile_rejected"
  | "payload_too_large"
  | "rate_limited"
  | "invalid_json"

/** Réponse JSON typée normalisée par le composable. */
export type ContactSubmitResponse =
  | {
      status: "accepted"
      requestId: string
    }
  | {
      status: "error"
      code: ContactErrorCode
      requestId: string
      /** Erreurs de validation champ par champ (uniquement si code === "validation_failed"). */
      errors?: Partial<Record<keyof ContactFormValues, readonly string[]>>
      /** Nombre de secondes à attendre (uniquement si code === "rate_limited"). */
      retryAfter?: number
    }
  | {
      status: "network_error"
      requestId: null
    }

export function emptyContactValues(): ContactFormValues {
  return {
    name: "",
    email: "",
    company: "",
    telephone: "",
    projectType: "autre",
    message: "",
    consent: false,
    website: "",
    turnstileToken: null,
  }
}
