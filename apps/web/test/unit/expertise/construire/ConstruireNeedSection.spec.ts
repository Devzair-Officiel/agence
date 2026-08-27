import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ConstruireNeedSection from "~/components/expertise/construire/ConstruireNeedSection.vue"

const props = {
  needDescription:
    "Aux entreprises qui ont besoin d'un site vitrine sur mesure, d'une boutique en ligne fiable ou d'une application métier taillée pour leurs processus.",
}

describe("ConstruireNeedSection", () => {
  it("porte le H2 validé « Quand le projet doit devenir un outil. »", () => {
    const wrapper = mount(ConstruireNeedSection, { props })
    expect(wrapper.get("h2").text()).toBe(
      "Quand le projet doit devenir un outil.",
    )
  })

  it("expose l'eyebrow validé « À qui cela s'adresse »", () => {
    const wrapper = mount(ConstruireNeedSection, { props })
    expect(wrapper.text()).toContain("À qui cela s'adresse")
  })

  it("rend needDescription verbatim en tête de section", () => {
    const wrapper = mount(ConstruireNeedSection, { props })
    expect(wrapper.text()).toContain(props.needDescription)
  })

  it("rend les trois situations validées (Présenter / Vendre / Organiser)", () => {
    const wrapper = mount(ConstruireNeedSection, { props })
    const situations = wrapper.findAll(".construire-need__situation-verb").map((n) => n.text())
    expect(situations).toEqual(["Présenter", "Vendre", "Organiser"])
  })

  it("associe chaque situation à un format éditorial cohérent", () => {
    const wrapper = mount(ConstruireNeedSection, { props })
    const formats = wrapper.findAll(".construire-need__situation-format-value").map((n) => n.text())
    expect(formats).toEqual([
      "Site vitrine sur mesure",
      "Plateforme e-commerce",
      "Application métier",
    ])
  })

  it("relie la section au H2 par aria-labelledby", () => {
    const wrapper = mount(ConstruireNeedSection, { props })
    const h2Id = wrapper.get("h2").attributes("id")
    expect(h2Id).toBeTruthy()
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(h2Id)
  })
})
