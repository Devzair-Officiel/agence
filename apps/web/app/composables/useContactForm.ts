import { computed, reactive, ref } from "vue"

import {
  emptyContactValues,
  PROJECT_TYPES,
  type ContactErrorCode,
  type ContactFormValues,
  type ContactSubmitResponse,
  type ProjectType,
} from "~/types/contact"

/**
 * Composable du formulaire de contact.
 *
 * Responsabilités :
 *   - héberger l'état réactif (valeurs, statut, erreurs) sans le coupler à un
 *     rendu Vue particulier ;
 *   - valider les entrées côté client de manière minimale (miroir non-strict du
 *     DTO Symfony) — l'autorité reste le serveur, mais on évite les allers-retours
 *     manifestement rejetables ;
 *   - piloter la soumission via `fetch()` (aucun coupleur Nuxt : testable en
 *     isolation avec un `globalThis.fetch` stubé) et mapper la réponse sur les
 *     codes documentés dans `docs/adr/ADR-007-endpoint-contact-securite.md` ;
 *   - protéger contre la double-soumission (`isSubmitting` bloque le bouton et
 *     `submit()` retourne immédiatement si un appel est déjà en vol).
 *
 * Le composable ne parle *ni* de DOM ni d'accessibilité — ces responsabilités
 * appartiennent à ContactForm.vue. Il n'utilise aucun runtime Nuxt (`useFetch`,
 * `useRuntimeConfig`) : l'endpoint et la clé Turnstile sont passés en options,
 * ce qui rend chaque cas d'usage testable sans mock.
 */

export type ContactStatus =
  | "idle"
  | "submitting"
  | "success"
  | "error"

export type FieldErrors = Partial<Record<keyof ContactFormValues, string>>

export interface UseContactFormOptions {
  /** URL absolue ou relative de l'endpoint API contact. */
  endpoint: string
  /** Implémentation de fetch injectée pour les tests. Par défaut `globalThis.fetch`. */
  fetcher?: typeof fetch
}

export interface ContactFormApi {
  values: ContactFormValues
  status: Readonly<{ value: ContactStatus }>
  fieldErrors: Readonly<{ value: FieldErrors }>
  globalError: Readonly<{ value: GlobalError | null }>
  successRequestId: Readonly<{ value: string | null }>
  isSubmitting: Readonly<{ value: boolean }>
  canSubmit: Readonly<{ value: boolean }>
  setTurnstileToken: (token: string | null) => void
  reset: () => void
  submit: () => Promise<ContactSubmitResponse>
}

export interface GlobalError {
  code: ContactErrorCode | "network_error"
  requestId: string | null
  /** Secondes restantes avant de retenter (rate limit uniquement). */
  retryAfter?: number
}

/**
 * Validation client — miroir volontairement minimal du DTO Symfony :
 * on rejette uniquement les cas triviaux (vide, longueur, format email
 * grossier, regex téléphone). Le serveur reste juge de la validité finale.
 */
export function validateContactValues(values: ContactFormValues): FieldErrors {
  const errors: FieldErrors = {}

  const trimmedName = values.name.trim()
  if (trimmedName.length < 2) {
    errors.name = "Le nom est requis (2 caractères minimum)."
  } else if (trimmedName.length > 120) {
    errors.name = "Le nom ne doit pas dépasser 120 caractères."
  }

  const trimmedEmail = values.email.trim()
  if (trimmedEmail.length === 0) {
    errors.email = "L'adresse email est requise."
  } else if (trimmedEmail.length > 254 || !isPlausibleEmail(trimmedEmail)) {
    errors.email = "L'adresse email est invalide."
  }

  if (values.company.length > 160) {
    errors.company = "La société ne doit pas dépasser 160 caractères."
  }

  const trimmedPhone = values.telephone.trim()
  if (trimmedPhone.length > 0 && !/^[0-9 +().-]{4,40}$/u.test(trimmedPhone)) {
    errors.telephone = "Le téléphone contient des caractères non autorisés."
  }

  if (!PROJECT_TYPES.includes(values.projectType as ProjectType)) {
    errors.projectType = "Type de projet non reconnu."
  }

  const trimmedMessage = values.message.trim()
  if (trimmedMessage.length < 20) {
    errors.message = "Le message doit contenir au moins 20 caractères."
  } else if (trimmedMessage.length > 4000) {
    errors.message = "Le message ne doit pas dépasser 4000 caractères."
  }

  if (!values.consent) {
    errors.consent = "Le consentement est requis pour envoyer le message."
  }

  return errors
}

function isPlausibleEmail(value: string): boolean {
  // On reste laxiste : le serveur applique la contrainte finale.
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/u.test(value)
}

interface RawErrorPayload {
  status?: string
  code?: string
  request_id?: string
  errors?: Record<string, unknown>
}

function toResponse(
  httpStatus: number,
  payload: RawErrorPayload,
  headers: Headers,
): ContactSubmitResponse {
  const requestId = typeof payload.request_id === "string" ? payload.request_id : ""

  if (httpStatus === 200 || httpStatus === 202) {
    return { status: "accepted", requestId }
  }

  const code = mapErrorCode(httpStatus, payload.code)
  const errors = code === "validation_failed" ? normaliseErrors(payload.errors) : undefined
  const retryAfterHeader = headers.get("Retry-After")
  const retryAfter = code === "rate_limited" ? parseRetryAfter(retryAfterHeader) : undefined

  return {
    status: "error",
    code,
    requestId,
    errors,
    retryAfter,
  }
}

function mapErrorCode(httpStatus: number, code: string | undefined): ContactErrorCode {
  const knownCodes: readonly ContactErrorCode[] = [
    "validation_failed",
    "origin_not_allowed",
    "turnstile_rejected",
    "payload_too_large",
    "rate_limited",
    "invalid_json",
  ]
  if (typeof code === "string" && (knownCodes as readonly string[]).includes(code)) {
    return code as ContactErrorCode
  }
  // Fallback contrat par statut HTTP.
  if (httpStatus === 413) return "payload_too_large"
  if (httpStatus === 429) return "rate_limited"
  if (httpStatus === 403) return "origin_not_allowed"
  return "validation_failed"
}

function normaliseErrors(
  raw: Record<string, unknown> | undefined,
): Partial<Record<keyof ContactFormValues, readonly string[]>> {
  if (!raw) return {}
  const result: Partial<Record<keyof ContactFormValues, readonly string[]>> = {}
  const knownFields: readonly (keyof ContactFormValues)[] = [
    "name",
    "email",
    "company",
    "telephone",
    "projectType",
    "message",
    "consent",
    "website",
    "turnstileToken",
  ]
  for (const field of knownFields) {
    const value = raw[field]
    if (Array.isArray(value)) {
      const messages = value.filter((entry): entry is string => typeof entry === "string")
      if (messages.length > 0) result[field] = messages
    } else if (typeof value === "string") {
      result[field] = [value]
    }
  }
  return result
}

function parseRetryAfter(header: string | null): number | undefined {
  if (!header) return undefined
  const asNumber = Number.parseInt(header, 10)
  if (Number.isFinite(asNumber) && asNumber >= 0) return asNumber
  return undefined
}

export function useContactForm(options: UseContactFormOptions): ContactFormApi {
  const values = reactive<ContactFormValues>(emptyContactValues())
  const status = ref<ContactStatus>("idle")
  const fieldErrors = ref<FieldErrors>({})
  const globalError = ref<GlobalError | null>(null)
  const successRequestId = ref<string | null>(null)
  const isSubmitting = computed(() => status.value === "submitting")
  const canSubmit = computed(
    () => !isSubmitting.value && Boolean(values.turnstileToken),
  )

  const fetcher = options.fetcher ?? globalThis.fetch

  function setTurnstileToken(token: string | null): void {
    values.turnstileToken = token
  }

  function reset(): void {
    Object.assign(values, emptyContactValues())
    status.value = "idle"
    fieldErrors.value = {}
    globalError.value = null
    successRequestId.value = null
  }

  async function submit(): Promise<ContactSubmitResponse> {
    if (isSubmitting.value) {
      return { status: "network_error", requestId: null }
    }

    const localErrors = validateContactValues(values)
    if (Object.keys(localErrors).length > 0) {
      fieldErrors.value = localErrors
      status.value = "error"
      globalError.value = null
      return { status: "error", code: "validation_failed", requestId: "" }
    }

    fieldErrors.value = {}
    globalError.value = null
    status.value = "submitting"

    let response: Response
    try {
      response = await fetcher(options.endpoint, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          name: values.name.trim(),
          email: values.email.trim(),
          company: values.company.trim() || null,
          telephone: values.telephone.trim() || null,
          projectType: values.projectType,
          message: values.message.trim(),
          consent: values.consent,
          website: values.website,
          turnstileToken: values.turnstileToken,
        }),
      })
    } catch {
      status.value = "error"
      globalError.value = { code: "network_error", requestId: null }
      return { status: "network_error", requestId: null }
    }

    let payload: RawErrorPayload
    try {
      payload = (await response.json()) as RawErrorPayload
    } catch {
      payload = {}
    }

    const normalised = toResponse(response.status, payload, response.headers)

    if (normalised.status === "accepted") {
      status.value = "success"
      successRequestId.value = normalised.requestId
      // On efface les valeurs pour éviter un re-envoi accidentel ; le token
      // Turnstile est également remis à null (widget à re-résoudre).
      Object.assign(values, emptyContactValues())
      return normalised
    }

    status.value = "error"
    if (normalised.status === "error" && normalised.errors) {
      const mapped: FieldErrors = {}
      for (const [field, messages] of Object.entries(normalised.errors)) {
        if (Array.isArray(messages) && messages.length > 0) {
          mapped[field as keyof ContactFormValues] = messages[0]!
        }
      }
      fieldErrors.value = mapped
    }
    if (normalised.status === "error") {
      globalError.value = {
        code: normalised.code,
        requestId: normalised.requestId,
        retryAfter: normalised.retryAfter,
      }
    }
    return normalised
  }

  return {
    values,
    status,
    fieldErrors,
    globalError,
    successRequestId,
    isSubmitting,
    canSubmit,
    setTurnstileToken,
    reset,
    submit,
  }
}
