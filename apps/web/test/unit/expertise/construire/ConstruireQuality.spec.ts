import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ConstruireQuality from "~/components/expertise/construire/ConstruireQuality.vue"

const benefits = [
  {
    title: "Un socle technique moderne",
    description: "Les frameworks retenus sont maintenus.",
  },
  {
    title: "Un produit testé",
    description: "Les fonctionnalités sont couvertes par des tests.",
  },
  {
    title: "Une maintenance sereine",
    description: "La structure du code permet de reprendre le projet.",
  },
] as const

describe("ConstruireQuality", () => {
  it("expose l'eyebrow « Bénéfices concrets » attendu par le contrat éditorial", () => {
    const wrapper = mount(ConstruireQuality, { props: { benefits } })
    expect(wrapper.text()).toContain("Bénéfices concrets")
  })

  it("porte un H2 unique validé", () => {
    const wrapper = mount(ConstruireQuality, { props: { benefits } })
    const h2s = wrapper.findAll("h2")
    expect(h2s).toHaveLength(1)
    expect(h2s[0]!.text()).toBe(
      "Construit pour fonctionner aujourd'hui. Et demain.",
    )
  })

  it("rend un H3 par bénéfice avec le titre exact fourni", () => {
    const wrapper = mount(ConstruireQuality, { props: { benefits } })
    const titles = wrapper.findAll("h3").map((n) => n.text())
    expect(titles).toEqual(benefits.map((b) => b.title))
  })

  it("rend chaque description associée sous forme de paragraphe", () => {
    const wrapper = mount(ConstruireQuality, { props: { benefits } })
    const text = wrapper.text()
    for (const b of benefits) expect(text).toContain(b.description)
  })

  it("mappe les trois contrôles Architecture / Tests / Maintenabilité aux bénéfices", () => {
    const wrapper = mount(ConstruireQuality, { props: { benefits } })
    const controlLabels = wrapper
      .findAll(".construire-quality__control-label")
      .map((n) => n.text())
    expect(controlLabels).toEqual(["Architecture", "Tests", "Maintenabilité"])
  })
})
