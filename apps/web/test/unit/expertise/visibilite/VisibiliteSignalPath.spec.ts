import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import VisibiliteSignalPath from "~/components/expertise/visibilite/VisibiliteSignalPath.vue"

const props = {
  approachDescription:
    "Nous analysons les intentions de recherche réellement pertinentes pour votre activité, corrigeons les blocages techniques du site, produisons ou révisons les contenus stratégiques, et suivons les résultats dans le temps. Nous privilégions les gains durables aux quick wins fragiles.",
}

describe("VisibiliteSignalPath", () => {
  it("rend un <ol> sémantique avec exactement cinq étapes", () => {
    const wrapper = mount(VisibiliteSignalPath, { props })
    const list = wrapper.find("ol")
    expect(list.exists()).toBe(true)
    expect(list.findAll("li")).toHaveLength(5)
  })

  it("expose les cinq libellés d'étapes validés dans l'ordre exact", () => {
    const wrapper = mount(VisibiliteSignalPath, { props })
    const titles = wrapper.findAll("h3").map((h) => h.text())
    expect(titles).toEqual([
      "Intentions",
      "Fondations",
      "Pages",
      "Présence",
      "Mesure",
    ])
  })

  it("numérote chaque étape 01…05", () => {
    const wrapper = mount(VisibiliteSignalPath, { props })
    const indexes = wrapper
      .findAll(".visibilite-path__item-index")
      .map((n) => n.text())
    expect(indexes).toEqual(["01", "02", "03", "04", "05"])
  })

  it("porte le H2 validé « Le parcours d'un signal, de l'intention à la mesure. »", () => {
    const wrapper = mount(VisibiliteSignalPath, { props })
    const h2 = wrapper.get("h2")
    expect(h2.text()).toBe(
      "Le parcours d'un signal, de l'intention à la mesure.",
    )
  })

  it("expose l'eyebrow validé « Notre approche »", () => {
    const wrapper = mount(VisibiliteSignalPath, { props })
    expect(wrapper.text()).toContain("Notre approche")
  })

  it("rend approachDescription verbatim en tête de section", () => {
    const wrapper = mount(VisibiliteSignalPath, { props })
    expect(wrapper.text()).toContain(props.approachDescription)
  })

  it("expose les notes narratives validées en légende de chaque étape", () => {
    const wrapper = mount(VisibiliteSignalPath, { props })
    const notes = wrapper.findAll(".visibilite-path__item-note").map((n) => n.text())
    expect(notes).toEqual([
      "Ce que vos publics cherchent réellement.",
      "Blocages techniques et structure du site.",
      "Rédaction et optimisation stratégique.",
      "Fiche locale, citations, cohérence.",
      "Positions et trafic organique dans le temps.",
    ])
  })

  it("lie la section au H2 via aria-labelledby", () => {
    const wrapper = mount(VisibiliteSignalPath, { props })
    const section = wrapper.get("section")
    const h2Id = wrapper.get("h2").attributes("id")
    expect(h2Id).toBeTruthy()
    expect(section.attributes("aria-labelledby")).toBe(h2Id)
  })
})
