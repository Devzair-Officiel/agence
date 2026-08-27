import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ConstruireBuildFlow from "~/components/expertise/construire/ConstruireBuildFlow.vue"

const props = {
  approachDescription:
    "Nous travaillons par itérations, en livrant des incréments testables plutôt qu'un gros projet en fin de parcours.",
}

describe("ConstruireBuildFlow", () => {
  it("rend un <ol> sémantique avec exactement cinq étapes", () => {
    const wrapper = mount(ConstruireBuildFlow, { props })
    const list = wrapper.find("ol")
    expect(list.exists()).toBe(true)
    expect(list.findAll("li")).toHaveLength(5)
  })

  it("expose les cinq libellés d'étapes validés dans l'ordre exact", () => {
    const wrapper = mount(ConstruireBuildFlow, { props })
    const titles = wrapper.findAll("h3").map((h) => h.text())
    expect(titles).toEqual([
      "Socle",
      "Interfaces",
      "Logique métier",
      "Connexions",
      "Validation",
    ])
  })

  it("numérote chaque étape 01…05", () => {
    const wrapper = mount(ConstruireBuildFlow, { props })
    const indexes = wrapper
      .findAll(".construire-flow__marker-index")
      .map((n) => n.text())
    expect(indexes).toEqual(["01", "02", "03", "04", "05"])
  })

  it("porte le H2 validé « Construire par couches. Valider à chaque étape. »", () => {
    const wrapper = mount(ConstruireBuildFlow, { props })
    const h2 = wrapper.get("h2")
    expect(h2.text()).toBe("Construire par couches. Valider à chaque étape.")
  })

  it("expose l'eyebrow validé « Notre approche »", () => {
    const wrapper = mount(ConstruireBuildFlow, { props })
    expect(wrapper.text()).toContain("Notre approche")
  })

  it("rend approachDescription verbatim en tête de section", () => {
    const wrapper = mount(ConstruireBuildFlow, { props })
    expect(wrapper.text()).toContain(props.approachDescription)
  })

  it("expose les libellés courts validés en légende de chaque étape", () => {
    const wrapper = mount(ConstruireBuildFlow, { props })
    const captions = wrapper.findAll(".construire-flow__step-caption").map((n) => n.text())
    expect(captions).toEqual([
      "Architecture et environnement.",
      "Écrans et parcours.",
      "Fonctionnalités et règles.",
      "Données et outils existants.",
      "Tests, documentation, livraison.",
    ])
  })

  it("lie la section au H2 via aria-labelledby", () => {
    const wrapper = mount(ConstruireBuildFlow, { props })
    const section = wrapper.get("section")
    const h2Id = wrapper.get("h2").attributes("id")
    expect(h2Id).toBeTruthy()
    expect(section.attributes("aria-labelledby")).toBe(h2Id)
  })
})
