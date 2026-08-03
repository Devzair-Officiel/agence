import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ExpertiseBenefits from "~/components/expertise/ExpertiseBenefits.vue"

describe("ExpertiseBenefits", () => {
  const benefits = [
    {
      title: "Un socle technique moderne",
      description:
        "Les frameworks retenus sont maintenus et adoptés par une large communauté.",
    },
    {
      title: "Un produit testé",
      description:
        "Les fonctionnalités importantes sont couvertes par des tests automatisés.",
    },
    {
      title: "Une maintenance sereine",
      description:
        "La documentation et la couverture de tests permettent de reprendre le projet.",
    },
  ]

  it("rend une <ul role=\"list\"> avec un item par bénéfice", () => {
    const wrapper = mount(ExpertiseBenefits, { props: { benefits } })
    const list = wrapper.get("ul")
    expect(list.attributes("role")).toBe("list")
    expect(wrapper.findAll("li")).toHaveLength(benefits.length)
  })

  it("rend un H3 par bénéfice avec le titre verbatim", () => {
    const wrapper = mount(ExpertiseBenefits, { props: { benefits } })
    const headings = wrapper.findAll("h3")
    expect(headings).toHaveLength(benefits.length)
    for (let i = 0; i < benefits.length; i += 1) {
      expect(headings[i]!.text()).toBe(benefits[i]!.title)
    }
  })

  it("rend la description de chaque bénéfice verbatim", () => {
    const wrapper = mount(ExpertiseBenefits, { props: { benefits } })
    const descriptions = wrapper.findAll(".expertise-benefits__description")
    expect(descriptions).toHaveLength(benefits.length)
    for (let i = 0; i < benefits.length; i += 1) {
      expect(descriptions[i]!.text()).toBe(benefits[i]!.description)
    }
  })
})
