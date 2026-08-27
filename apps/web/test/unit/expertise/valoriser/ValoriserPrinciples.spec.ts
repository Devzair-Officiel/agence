import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ValoriserPrinciples from "~/components/expertise/valoriser/ValoriserPrinciples.vue"

const benefits = [
  {
    title: "Une image professionnelle",
    description:
      "Vos photographies reflètent votre activité réelle et se démarquent des banques d'images.",
  },
  {
    title: "Des mots précis",
    description:
      "Vos textes sont adaptés à ce que vous faites et à ceux à qui vous vous adressez.",
  },
  {
    title: "Une offre plus claire",
    description:
      "La structuration facilite la lecture et la décision côté visiteur.",
  },
] as const

describe("ValoriserPrinciples", () => {
  it("expose l'eyebrow « Bénéfices concrets » attendu par le contrat éditorial", () => {
    const wrapper = mount(ValoriserPrinciples, { props: { benefits } })
    expect(wrapper.text()).toContain("Bénéfices concrets")
  })

  it("porte un H2 unique validé", () => {
    const wrapper = mount(ValoriserPrinciples, { props: { benefits } })
    const h2s = wrapper.findAll("h2")
    expect(h2s).toHaveLength(1)
    expect(h2s[0]!.text()).toBe(
      "Une image, une voix, une offre cohérentes.",
    )
  })

  it("rend un H3 par bénéfice avec le titre exact fourni par la config", () => {
    const wrapper = mount(ValoriserPrinciples, { props: { benefits } })
    const titles = wrapper.findAll("h3").map((n) => n.text())
    expect(titles).toEqual(benefits.map((b) => b.title))
  })

  it("rend chaque description associée verbatim", () => {
    const wrapper = mount(ValoriserPrinciples, { props: { benefits } })
    const text = wrapper.text()
    for (const b of benefits) expect(text).toContain(b.description)
  })

  it("mappe les trois labels narratifs Voix / Image / Clarté aux bénéfices", () => {
    const wrapper = mount(ValoriserPrinciples, { props: { benefits } })
    const labels = wrapper
      .findAll(".valoriser-principles__item-label")
      .map((n) => n.text())
    expect(labels).toEqual(["Voix", "Image", "Clarté"])
  })

  it("numérote les principes 01…03", () => {
    const wrapper = mount(ValoriserPrinciples, { props: { benefits } })
    const indexes = wrapper
      .findAll(".valoriser-principles__item-index")
      .map((n) => n.text())
    expect(indexes).toEqual(["01", "02", "03"])
  })
})
