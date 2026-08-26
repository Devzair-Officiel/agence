import { describe, expect, it } from "vitest"
import { formatEditorialDate } from "~/utils/editorial-date"

// La suite est délibérément indépendante du TZ du runner. Les cas
// critiques sont ceux où l'heure UTC est proche des bornes locales
// « Europe/Paris » (UTC+1 en hiver, UTC+2 en été).
describe("formatEditorialDate", () => {
  it("rend l'ISO en date française « j mois yyyy »", () => {
    // 15 janvier 2026 10:00 UTC → 11:00 Europe/Paris (CET) → 15 janvier 2026.
    expect(formatEditorialDate("2026-01-15T10:00:00Z")).toBe("15 janvier 2026")
  })

  it("bascule au jour local suivant pour un ISO à cheval sur minuit UTC (heure d'été)", () => {
    // Cas de bug réel Devzair (hydration mismatch résolu par ce refactor) :
    // 2026-08-09T22:19:20+00:00 = 2026-08-10T00:19:20+02:00 (CEST) →
    // « 10 août 2026 » en Europe/Paris. Un formatteur sans `timeZone`
    // laisse `Intl.DateTimeFormat` utiliser le TZ système (UTC en Docker,
    // Europe/Paris en navigateur) et produit deux chaînes différentes.
    expect(formatEditorialDate("2026-08-09T22:19:20+00:00")).toBe(
      "10 août 2026",
    )
  })

  it("bascule au jour local suivant pour un ISO à cheval sur minuit UTC (heure d'hiver)", () => {
    // 2026-01-15T23:30:00Z = 2026-01-16T00:30:00+01:00 (CET) → 16 janvier.
    expect(formatEditorialDate("2026-01-15T23:30:00Z")).toBe("16 janvier 2026")
  })

  it("est stable même si l'ISO est déjà exprimé dans un autre fuseau", () => {
    // 2026-08-10T00:19:20+02:00 pointe le même instant absolu que
    // 2026-08-09T22:19:20Z — le résultat doit être identique.
    expect(formatEditorialDate("2026-08-10T00:19:20+02:00")).toBe(
      "10 août 2026",
    )
  })

  it("retourne la chaîne d'origine si l'ISO est invalide (ne casse pas le rendu)", () => {
    expect(formatEditorialDate("pas-une-date")).toBe("pas-une-date")
    expect(formatEditorialDate("")).toBe("")
  })
})
