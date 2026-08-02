import { beforeEach, describe, expect, it, vi } from "vitest"

import {
  useContactForm,
  validateContactValues,
} from "~/composables/useContactForm"
import { emptyContactValues } from "~/types/contact"

// Contrat unitaire du composable :
//   - `validateContactValues` renvoie un map champ → message pour chaque
//     cas obligatoirement rejeté (miroir minimal du DTO Symfony) ;
//   - `submit()` bloque tant que le token Turnstile est absent (`canSubmit`) ;
//   - `submit()` mappe les statuts HTTP 200/202/400/403/413/429 sur les codes
//     documentés dans ADR-007 ;
//   - une deuxième soumission concurrente est ignorée (protection double-clic) ;
//   - un succès reset les valeurs et le token — évite tout renvoi accidentel.

function validValues() {
  return {
    ...emptyContactValues(),
    name: "Alice",
    email: "alice@example.com",
    company: "Devzair",
    telephone: "+33 6 12 34 56 78",
    projectType: "refonte" as const,
    message:
      "Nous souhaitons refondre notre site vitrine pour clarifier notre offre.",
    consent: true,
    turnstileToken: "cf-token-xyz",
  }
}

describe("validateContactValues", () => {
  it("returns no error on a fully valid payload", () => {
    expect(validateContactValues(validValues())).toEqual({})
  })

  it("flags empty name", () => {
    expect(validateContactValues({ ...validValues(), name: "" }).name).toBeTruthy()
  })

  it("flags too-short name", () => {
    expect(validateContactValues({ ...validValues(), name: "A" }).name).toBeTruthy()
  })

  it("flags too-long name (>120)", () => {
    expect(
      validateContactValues({ ...validValues(), name: "A".repeat(121) }).name,
    ).toBeTruthy()
  })

  it("flags invalid email", () => {
    expect(
      validateContactValues({ ...validValues(), email: "not-an-email" }).email,
    ).toBeTruthy()
  })

  it("flags telephone with invalid characters", () => {
    expect(
      validateContactValues({ ...validValues(), telephone: "06 abc" }).telephone,
    ).toBeTruthy()
  })

  it("accepts an empty telephone (optional field)", () => {
    expect(
      validateContactValues({ ...validValues(), telephone: "" }).telephone,
    ).toBeUndefined()
  })

  it("flags too-short message (<20)", () => {
    expect(
      validateContactValues({ ...validValues(), message: "trop court" }).message,
    ).toBeTruthy()
  })

  it("flags too-long message (>4000)", () => {
    expect(
      validateContactValues({
        ...validValues(),
        message: "a".repeat(4001),
      }).message,
    ).toBeTruthy()
  })

  it("flags missing consent", () => {
    expect(
      validateContactValues({ ...validValues(), consent: false }).consent,
    ).toBeTruthy()
  })

  it("flags unknown projectType", () => {
    expect(
      validateContactValues({
        ...validValues(),
        projectType: "unknown" as never,
      }).projectType,
    ).toBeTruthy()
  })
})

function makeFetchResponse(
  status: number,
  body: Record<string, unknown>,
  headers: Record<string, string> = {},
): Response {
  return new Response(JSON.stringify(body), {
    status,
    headers: {
      "Content-Type": "application/json",
      ...headers,
    },
  })
}

describe("useContactForm.submit", () => {
  beforeEach(() => {
    vi.restoreAllMocks()
  })

  it("cannot submit while the turnstile token is null", () => {
    const form = useContactForm({
      endpoint: "/api/contact",
      fetcher: vi.fn(),
    })
    expect(form.canSubmit.value).toBe(false)
    form.setTurnstileToken("cf-abc")
    Object.assign(form.values, validValues())
    expect(form.canSubmit.value).toBe(true)
  })

  it("returns a validation error and never calls fetch on invalid values", async () => {
    const fetcher = vi.fn()
    const form = useContactForm({ endpoint: "/api/contact", fetcher })
    form.setTurnstileToken("cf-abc")
    // valeurs restent vides → validation client échoue.
    const result = await form.submit()
    expect(result.status).toBe("error")
    expect(fetcher).not.toHaveBeenCalled()
    expect(form.status.value).toBe("error")
    expect(form.fieldErrors.value.name).toBeTruthy()
  })

  it("maps HTTP 200 → accepted and resets the values + token", async () => {
    const fetcher = vi.fn().mockResolvedValue(
      makeFetchResponse(200, { status: "accepted", request_id: "req-1" }),
    )
    const form = useContactForm({ endpoint: "/api/contact", fetcher })
    Object.assign(form.values, validValues())
    const result = await form.submit()
    expect(result).toEqual({ status: "accepted", requestId: "req-1" })
    expect(form.status.value).toBe("success")
    expect(form.successRequestId.value).toBe("req-1")
    expect(form.values.email).toBe("")
    expect(form.values.turnstileToken).toBeNull()
    expect(fetcher).toHaveBeenCalledOnce()
    const call = fetcher.mock.calls[0]
    expect(call![0]).toBe("/api/contact")
    const body = JSON.parse((call![1] as RequestInit).body as string) as Record<
      string,
      unknown
    >
    expect(body.email).toBe("alice@example.com")
    expect(body.company).toBe("Devzair")
    expect(body.website).toBe("") // honeypot doit toujours être vide
  })

  it("maps HTTP 400 validation_failed → per-field errors", async () => {
    const fetcher = vi.fn().mockResolvedValue(
      makeFetchResponse(400, {
        status: "error",
        code: "validation_failed",
        request_id: "req-2",
        errors: { email: ["L'adresse email est invalide."] },
      }),
    )
    const form = useContactForm({ endpoint: "/api/contact", fetcher })
    Object.assign(form.values, validValues())
    const result = await form.submit()
    expect(result.status).toBe("error")
    if (result.status === "error") {
      expect(result.code).toBe("validation_failed")
      expect(result.errors?.email?.[0]).toContain("email")
    }
    expect(form.fieldErrors.value.email).toContain("email")
  })

  it("maps HTTP 429 → rate_limited and parses Retry-After", async () => {
    const fetcher = vi.fn().mockResolvedValue(
      makeFetchResponse(
        429,
        { status: "error", code: "rate_limited", request_id: "req-3" },
        { "Retry-After": "42" },
      ),
    )
    const form = useContactForm({ endpoint: "/api/contact", fetcher })
    Object.assign(form.values, validValues())
    const result = await form.submit()
    expect(result.status).toBe("error")
    if (result.status === "error") {
      expect(result.code).toBe("rate_limited")
      expect(result.retryAfter).toBe(42)
    }
    expect(form.globalError.value?.retryAfter).toBe(42)
  })

  it("maps HTTP 403 turnstile_rejected → dedicated code", async () => {
    const fetcher = vi.fn().mockResolvedValue(
      makeFetchResponse(403, {
        status: "error",
        code: "turnstile_rejected",
        request_id: "req-4",
      }),
    )
    const form = useContactForm({ endpoint: "/api/contact", fetcher })
    Object.assign(form.values, validValues())
    const result = await form.submit()
    expect(result.status).toBe("error")
    if (result.status === "error") expect(result.code).toBe("turnstile_rejected")
    expect(form.globalError.value?.code).toBe("turnstile_rejected")
  })

  it("maps HTTP 413 → payload_too_large", async () => {
    const fetcher = vi.fn().mockResolvedValue(
      makeFetchResponse(413, {
        status: "error",
        code: "payload_too_large",
        request_id: "req-5",
      }),
    )
    const form = useContactForm({ endpoint: "/api/contact", fetcher })
    Object.assign(form.values, validValues())
    const result = await form.submit()
    if (result.status === "error") expect(result.code).toBe("payload_too_large")
  })

  it("maps HTTP 503 temporary_error → dedicated code and preserves user values", async () => {
    const fetcher = vi.fn().mockResolvedValue(
      makeFetchResponse(503, {
        status: "error",
        code: "temporary_error",
        request_id: "req-503",
      }),
    )
    const form = useContactForm({ endpoint: "/api/contact", fetcher })
    Object.assign(form.values, validValues())
    const result = await form.submit()

    expect(result.status).toBe("error")
    if (result.status === "error") {
      expect(result.code).toBe("temporary_error")
      expect(result.requestId).toBe("req-503")
    }
    expect(form.globalError.value?.code).toBe("temporary_error")
    // Contrat ADR-008 §7 : les valeurs saisies ne sont *jamais* purgées sur
    // une erreur temporaire — l'utilisateur doit pouvoir retenter directement.
    expect(form.values.email).toBe("alice@example.com")
    expect(form.values.message).toContain("refondre notre site vitrine")
    expect(form.values.turnstileToken).toBe("cf-token-xyz")
  })

  it("maps a network exception → network_error", async () => {
    const fetcher = vi.fn().mockRejectedValue(new Error("connection refused"))
    const form = useContactForm({ endpoint: "/api/contact", fetcher })
    Object.assign(form.values, validValues())
    const result = await form.submit()
    expect(result.status).toBe("network_error")
    expect(form.globalError.value?.code).toBe("network_error")
  })

  it("ignores a concurrent submit call (double-click protection)", async () => {
    let resolveFetch: ((value: Response) => void) | null = null
    const fetcher = vi.fn(
      () =>
        new Promise<Response>((resolve) => {
          resolveFetch = resolve
        }),
    )
    const form = useContactForm({ endpoint: "/api/contact", fetcher })
    Object.assign(form.values, validValues())
    const first = form.submit()
    const second = await form.submit()
    expect(second.status).toBe("network_error")
    expect(fetcher).toHaveBeenCalledOnce()
    resolveFetch?.(
      makeFetchResponse(200, { status: "accepted", request_id: "req-x" }),
    )
    await first
  })
})
