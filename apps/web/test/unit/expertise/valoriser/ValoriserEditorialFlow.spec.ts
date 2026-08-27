import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ValoriserEditorialFlow from "~/components/expertise/valoriser/ValoriserEditorialFlow.vue"

const props = {
  approachDescription:
    "Nous partons de votre activité réelle : nous observons, nous préparons, puis nous produisons ce qui manque.",
}

describe("ValoriserEditorialFlow", () => {
  it("rend un <ol> sémantique avec exactement cinq étapes", () => {
    const wrapper = mount(ValoriserEditorialFlow, { props })
    const list = wrapper.find("ol")
    expect(list.exists()).toBe(true)
    expect(list.findAll("li")).toHaveLength(5)
  })

  it("expose les cinq libellés d'étapes validés dans l'ordre exact", () => {
    const wrapper = mount(ValoriserEditorialFlow, { props })
    const titles = wrapper.findAll("h3").map((h) => h.text())
    expect(titles).toEqual([
      "Observer",
      "Préparer",
      "Produire",
      "Structurer",
      "Décliner",
    ])
  })

  it("numérote chaque étape 01…05", () => {
    const wrapper = mount(ValoriserEditorialFlow, { props })
    const indexes = wrapper
      .findAll(".valoriser-flow__item-index")
      .map((n) => n.text())
    expect(indexes).toEqual(["01", "02", "03", "04", "05"])
  })

  it("porte le H2 validé « De la matière au message. »", () => {
    const wrapper = mount(ValoriserEditorialFlow, { props })
    const h2 = wrapper.get("h2")
    expect(h2.text()).toBe("De la matière au message.")
  })

  it("expose l'eyebrow validé « Notre approche »", () => {
    const wrapper = mount(ValoriserEditorialFlow, { props })
    expect(wrapper.text()).toContain("Notre approche")
  })

  it("rend approachDescription verbatim en tête de section", () => {
    const wrapper = mount(ValoriserEditorialFlow, { props })
    expect(wrapper.text()).toContain(props.approachDescription)
  })

  it("expose les notes narratives validées en légende de chaque étape", () => {
    const wrapper = mount(ValoriserEditorialFlow, { props })
    const notes = wrapper.findAll(".valoriser-flow__item-note").map((n) => n.text())
    expect(notes).toEqual([
      "Activité, offre, publics.",
      "Angles, prises de vue, messages.",
      "Images et textes.",
      "Pages, offres, hiérarchie.",
      "Site et supports.",
    ])
  })

  it("lie la section au H2 via aria-labelledby", () => {
    const wrapper = mount(ValoriserEditorialFlow, { props })
    const section = wrapper.get("section")
    const h2Id = wrapper.get("h2").attributes("id")
    expect(h2Id).toBeTruthy()
    expect(section.attributes("aria-labelledby")).toBe(h2Id)
  })
})
