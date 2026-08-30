import { describe, expect, it } from "vitest"
import { getMaxInstallments, INSTALLMENT_RULES } from "~/config/estimator-payment-options"

// ─── Boundaries (minor units = centimes) ─────────────────────────────────────
// < 100 000  → 2 échéances
// 100 000–250 000 inclus → 3
// > 250 000  → 4

describe("getMaxInstallments — limites", () => {
  it("99 999 → 2", () => expect(getMaxInstallments(99_999)).toBe(2))
  it("100 000 → 3", () => expect(getMaxInstallments(100_000)).toBe(3))
  it("250 000 → 3", () => expect(getMaxInstallments(250_000)).toBe(3))
  it("250 001 → 4", () => expect(getMaxInstallments(250_001)).toBe(4))
})

describe("getMaxInstallments — cas réels", () => {
  it("90 000 (900 €) → 2", () => expect(getMaxInstallments(90_000)).toBe(2))
  it("130 000 (1 300 €) → 3", () => expect(getMaxInstallments(130_000)).toBe(3))
  it("400 000 (4 000 €) → 4", () => expect(getMaxInstallments(400_000)).toBe(4))
})

describe("getMaxInstallments — valeurs extrêmes", () => {
  it("1 → 2", () => expect(getMaxInstallments(1)).toBe(2))
  it("1 000 000 → 4", () => expect(getMaxInstallments(1_000_000)).toBe(4))
})

describe("INSTALLMENT_RULES — structure", () => {
  it("contient exactement 3 règles", () => expect(INSTALLMENT_RULES).toHaveLength(3))

  it("la dernière règle a maxAmountMinor null (catch-all)", () => {
    const last = INSTALLMENT_RULES[INSTALLMENT_RULES.length - 1]
    expect(last.maxAmountMinor).toBeNull()
  })

  it("les règles sont triées par maxAmountMinor croissant", () => {
    const nonNull = INSTALLMENT_RULES.filter((r) => r.maxAmountMinor !== null)
    for (let i = 1; i < nonNull.length; i++) {
      expect(nonNull[i].maxAmountMinor!).toBeGreaterThan(nonNull[i - 1].maxAmountMinor!)
    }
  })
})
