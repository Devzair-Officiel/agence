import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import FaireEvoluerContinuousLoop from "~/components/expertise/faire-evoluer/FaireEvoluerContinuousLoop.vue"

const props = {
  approachDescription:
    "Nous mettons en place un suivi mesuré (usages, performances, erreurs), planifions les mises à jour techniques régulières, priorisons les évolutions avec vous et livrons les correctifs sans attendre un chantier annuel. La maintenance est un flux, pas un événement.",
}

describe("FaireEvoluerContinuousLoop", () => {
  it("porte le H2 validé « Observer. Prioriser. Améliorer. Recommencer. »", () => {
    const wrapper = mount(FaireEvoluerContinuousLoop, { props })
    expect(wrapper.get("h2").text()).toBe(
      "Observer. Prioriser. Améliorer. Recommencer.",
    )
  })

  it("expose l'eyebrow validé « Notre approche »", () => {
    const wrapper = mount(FaireEvoluerContinuousLoop, { props })
    expect(wrapper.text()).toContain("Notre approche")
  })

  it("rend approachDescription verbatim en tête de section", () => {
    const wrapper = mount(FaireEvoluerContinuousLoop, { props })
    expect(wrapper.text()).toContain(props.approachDescription)
  })

  it("rend une vraie <ol role=list> avec cinq étapes ordonnées", () => {
    const wrapper = mount(FaireEvoluerContinuousLoop, { props })
    const list = wrapper.get("ol")
    expect(list.attributes("role")).toBe("list")
    const items = list.findAll("li")
    expect(items).toHaveLength(5)
  })

  it("étiquette les étapes OBSERVER / MAINTENIR / PRIORISER / LIVRER / RÉÉVALUER", () => {
    const wrapper = mount(FaireEvoluerContinuousLoop, { props })
    const labels = wrapper
      .findAll(".faire-evoluer-loop__step-label")
      .map((n) => n.text())
    expect(labels).toEqual([
      "Observer",
      "Maintenir",
      "Prioriser",
      "Livrer",
      "Réévaluer",
    ])
  })

  it("numérote les étapes 01…05", () => {
    const wrapper = mount(FaireEvoluerContinuousLoop, { props })
    const indexes = wrapper
      .findAll(".faire-evoluer-loop__step-index")
      .map((n) => n.text())
    expect(indexes).toEqual(["01", "02", "03", "04", "05"])
  })

  it("porte visiblement le retour cyclique « Réévaluer → recommencer »", () => {
    const wrapper = mount(FaireEvoluerContinuousLoop, { props })
    const returnLine = wrapper.get(".faire-evoluer-loop__return")
    expect(returnLine.text()).toContain("Réévaluer → recommencer")
    expect(returnLine.attributes("aria-hidden")).toBe("true")
  })

  it("marque le décor SVG comme purement décoratif (aria-hidden, focusable=false)", () => {
    const wrapper = mount(FaireEvoluerContinuousLoop, { props })
    const svg = wrapper.find("svg.faire-evoluer-loop__decor")
    expect(svg.exists()).toBe(true)
    expect(svg.attributes("aria-hidden")).toBe("true")
    expect(svg.attributes("focusable")).toBe("false")
  })

  it("relie la section au H2 par aria-labelledby", () => {
    const wrapper = mount(FaireEvoluerContinuousLoop, { props })
    const h2Id = wrapper.get("h2").attributes("id")
    expect(h2Id).toBeTruthy()
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(h2Id)
  })
})
