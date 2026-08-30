export interface PaymentInstallmentRule {
  maxAmountMinor: number | null
  maxInstallments: number
}

export const INSTALLMENT_RULES: PaymentInstallmentRule[] = [
  { maxAmountMinor: 99_999, maxInstallments: 2 },
  { maxAmountMinor: 250_000, maxInstallments: 3 },
  { maxAmountMinor: null, maxInstallments: 4 },
]

export function getMaxInstallments(estimateMaximumMinor: number): number {
  for (const rule of INSTALLMENT_RULES) {
    if (rule.maxAmountMinor === null || estimateMaximumMinor <= rule.maxAmountMinor) {
      return rule.maxInstallments
    }
  }
  return 4
}
