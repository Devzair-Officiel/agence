import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import EstimatorPaymentTerms from "~/components/estimator/EstimatorPaymentTerms.vue"

function mountTerms(estimateMaximumMinor: number) {
  return mount(EstimatorPaymentTerms, { props: { estimateMaximumMinor } })
}

describe("EstimatorPaymentTerms — nombre d'échéances affiché", () => {
  it("90 000 → 2 échéances", () => {
    expect(mountTerms(90_000).text()).toContain("2 échéances")
  })

  it("130 000 → 3 échéances", () => {
    expect(mountTerms(130_000).text()).toContain("3 échéances")
  })

  it("400 000 → 4 échéances", () => {
    expect(mountTerms(400_000).text()).toContain("4 échéances")
  })
})

describe("EstimatorPaymentTerms — contraintes UX", () => {
  it("n'affiche jamais un montant par échéance (pas de division)", () => {
    const wrapper = mountTerms(130_000)
    // 130 000 / 3 = ~433 — on ne veut voir aucun montant calculé
    const text = wrapper.text()
    expect(text).not.toContain("433")
    expect(text).not.toContain("1 300")
    expect(text).not.toContain("1300")
  })

  it("ne contient pas '/ mois'", () => {
    expect(mountTerms(130_000).text()).not.toContain("/ mois")
  })

  it("ne contient pas 'crédit'", () => {
    expect(mountTerms(400_000).text().toLowerCase()).not.toContain("crédit")
  })

  it("ne contient pas 'financement'", () => {
    expect(mountTerms(400_000).text().toLowerCase()).not.toContain("financement")
  })

  it("contient le mot 'échéances'", () => {
    expect(mountTerms(130_000).text()).toContain("échéances")
  })

  it("affiche le label de section sans montant brut", () => {
    const wrapper = mountTerms(90_000)
    expect(wrapper.text()).not.toContain("900")
    expect(wrapper.text()).not.toContain("90000")
  })
})
