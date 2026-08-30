import { describe, expect, it } from "vitest"
import { formatMoneyMinor, formatMoneyRangeMinor } from "~/utils/format-money"

describe("formatMoneyMinor", () => {
  it("converts minor units (centimes) to formatted euros", () => {
    const result = formatMoneyMinor(90_000, "EUR")
    expect(result).toContain("900")
    expect(result).toContain("€")
  })

  it("rounds display without decimals", () => {
    const result = formatMoneyMinor(100_050, "EUR")
    expect(result).not.toMatch(/,\d{2}/)
  })

  it("formats 0 without decimals", () => {
    const result = formatMoneyMinor(0, "EUR")
    expect(result).toContain("0")
    expect(result).toContain("€")
  })

  it("does not re-round — 4900 cents = 49 €", () => {
    const result = formatMoneyMinor(4_900, "EUR")
    expect(result).toContain("49")
  })
})

describe("formatMoneyRangeMinor", () => {
  it("formats a range with dash separator", () => {
    const result = formatMoneyRangeMinor(90_000, 130_000, "EUR")
    expect(result).toContain("900")
    expect(result).toContain("1")
    expect(result).toContain("–")
  })

  it("both bounds are present", () => {
    const result = formatMoneyRangeMinor(170_000, 250_000, "EUR")
    expect(result).toContain("1")
    expect(result).toContain("2")
    expect(result).toContain("–")
  })
})
