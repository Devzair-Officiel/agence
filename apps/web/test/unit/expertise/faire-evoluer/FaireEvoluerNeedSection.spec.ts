import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import FaireEvoluerNeedSection from "~/components/expertise/faire-evoluer/FaireEvoluerNeedSection.vue"

const props = {
  needDescription:
    "Aux entreprises dont le site est en production et qui veulent le garder à jour, mesurer son usage réel, corriger les défauts et ajouter les fonctionnalités que l'activité rend nécessaires. Le pôle Faire évoluer prend le relais une fois le site en ligne.",
}

describe("FaireEvoluerNeedSection", () => {
  it("porte le H2 validé « Quand la mise en ligne n'est que le début. »", () => {
    const wrapper = mount(FaireEvoluerNeedSection, { props })
    expect(wrapper.get("h2").text()).toBe(
      "Quand la mise en ligne n'est que le début.",
    )
  })

  it("expose l'eyebrow validé « À qui cela s'adresse »", () => {
    const wrapper = mount(FaireEvoluerNeedSection, { props })
    expect(wrapper.text()).toContain("À qui cela s'adresse")
  })

  it("rend needDescription verbatim en tête de section", () => {
    const wrapper = mount(FaireEvoluerNeedSection, { props })
    expect(wrapper.text()).toContain(props.needDescription)
  })

  it("rend une vraie <ol role=list> avec trois phases", () => {
    const wrapper = mount(FaireEvoluerNeedSection, { props })
    const list = wrapper.get("ol")
    expect(list.attributes("role")).toBe("list")
    expect(list.findAll("li")).toHaveLength(3)
  })

  it("étiquette les trois phases MAINTENIR / COMPRENDRE / ADAPTER", () => {
    const wrapper = mount(FaireEvoluerNeedSection, { props })
    const labels = wrapper
      .findAll(".faire-evoluer-need__phase-label")
      .map((n) => n.text())
    expect(labels).toEqual(["Maintenir", "Comprendre", "Adapter"])
  })

  it("associe chaque phase à sa prestation config (services)", () => {
    const wrapper = mount(FaireEvoluerNeedSection, { props })
    const links = wrapper
      .findAll(".faire-evoluer-need__phase-link-value")
      .map((n) => n.text())
    expect(links).toEqual([
      "Maintenance et sécurité",
      "Mesure et amélioration continue",
      "Évolutions fonctionnelles",
    ])
  })

  it("numérote les phases 01/02/03", () => {
    const wrapper = mount(FaireEvoluerNeedSection, { props })
    const indexes = wrapper
      .findAll(".faire-evoluer-need__phase-index")
      .map((n) => n.text())
    expect(indexes).toEqual(["01", "02", "03"])
  })

  it("relie la section au H2 par aria-labelledby", () => {
    const wrapper = mount(FaireEvoluerNeedSection, { props })
    const h2Id = wrapper.get("h2").attributes("id")
    expect(h2Id).toBeTruthy()
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(h2Id)
  })
})
