export function formatMoneyMinor(amountMinor: number, currency: string): string {
  return new Intl.NumberFormat("fr-FR", {
    style: "currency",
    currency,
    maximumFractionDigits: 0,
    minimumFractionDigits: 0,
  }).format(amountMinor / 100)
}

export function formatMoneyRangeMinor(
  minimumMinor: number,
  maximumMinor: number,
  currency: string,
): string {
  const fmt = new Intl.NumberFormat("fr-FR", {
    style: "currency",
    currency,
    maximumFractionDigits: 0,
    minimumFractionDigits: 0,
  })
  return `${fmt.format(minimumMinor / 100)} – ${fmt.format(maximumMinor / 100)}`
}
