import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ConcevoirBenefits from "~/components/expertise/concevoir/ConcevoirBenefits.vue"

const benefits = [
  { title: "Décisions prises en amont", description: "Choix arbitrés avant dev." },
  { title: "Identité cohérente", description: "Le système visuel s'applique partout." },
  { title: "Base durable", description: "Les composants sont pensés pour évoluer." },
] as const

describe("ConcevoirBenefits", () => {
  it("expose l'eyebrow « Bénéfices concrets » attendu par le contrat éditorial", () => {
    const wrapper = mount(ConcevoirBenefits, { props: { benefits } })
    expect(wrapper.text()).toContain("Bénéfices concrets")
  })

  it("porte un H2 unique et propre à Concevoir", () => {
    const wrapper = mount(ConcevoirBenefits, { props: { benefits } })
    const h2s = wrapper.findAll("h2")
    expect(h2s).toHaveLength(1)
    expect(h2s[0]!.text()).toBe(
      "Ce que vous obtenez à l'issue de la conception.",
    )
  })

  it("rend un H3 par bénéfice avec le titre exact fourni", () => {
    const wrapper = mount(ConcevoirBenefits, { props: { benefits } })
    const titles = wrapper.findAll("h3").map((n) => n.text())
    expect(titles).toEqual(benefits.map((b) => b.title))
  })

  it("rend chaque description associée sous forme de paragraphe", () => {
    const wrapper = mount(ConcevoirBenefits, { props: { benefits } })
    const text = wrapper.text()
    for (const b of benefits) expect(text).toContain(b.description)
  })
})
