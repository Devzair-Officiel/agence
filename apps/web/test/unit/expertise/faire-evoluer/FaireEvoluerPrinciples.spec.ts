import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import FaireEvoluerPrinciples from "~/components/expertise/faire-evoluer/FaireEvoluerPrinciples.vue"

const benefits = [
  {
    title: "Un site à jour",
    description:
      "Les mises à jour de sécurité et les correctifs techniques sont appliqués régulièrement, sans attendre un incident.",
  },
  {
    title: "Des décisions informées",
    description:
      "Les évolutions sont priorisées à partir de mesures d'usage réelles, pas à l'intuition.",
  },
  {
    title: "Une réactivité maîtrisée",
    description:
      "Les correctifs et petites évolutions sont livrés au fil de l'eau, sans dépendre d'un chantier annuel.",
  },
] as const

describe("FaireEvoluerPrinciples", () => {
  it("expose l'eyebrow « Bénéfices concrets » attendu par le contrat éditorial", () => {
    const wrapper = mount(FaireEvoluerPrinciples, { props: { benefits } })
    expect(wrapper.text()).toContain("Bénéfices concrets")
  })

  it("porte un H2 unique validé « Stable sans rester immobile. »", () => {
    const wrapper = mount(FaireEvoluerPrinciples, { props: { benefits } })
    const h2s = wrapper.findAll("h2")
    expect(h2s).toHaveLength(1)
    expect(h2s[0]!.text()).toBe("Stable sans rester immobile.")
  })

  it("rend un H3 par bénéfice avec le titre exact fourni par la config", () => {
    const wrapper = mount(FaireEvoluerPrinciples, { props: { benefits } })
    const titles = wrapper.findAll("h3").map((n) => n.text())
    expect(titles).toEqual(benefits.map((b) => b.title))
  })

  it("rend chaque description associée verbatim", () => {
    const wrapper = mount(FaireEvoluerPrinciples, { props: { benefits } })
    const text = wrapper.text()
    for (const b of benefits) expect(text).toContain(b.description)
  })

  it("mappe les trois labels narratifs Santé / Clarté / Rythme aux bénéfices", () => {
    const wrapper = mount(FaireEvoluerPrinciples, { props: { benefits } })
    const labels = wrapper
      .findAll(".faire-evoluer-principles__item-label")
      .map((n) => n.text())
    expect(labels).toEqual(["Santé", "Clarté", "Rythme"])
  })

  it("numérote les principes 01…03", () => {
    const wrapper = mount(FaireEvoluerPrinciples, { props: { benefits } })
    const indexes = wrapper
      .findAll(".faire-evoluer-principles__item-index")
      .map((n) => n.text())
    expect(indexes).toEqual(["01", "02", "03"])
  })
})
