import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import FaireEvoluerCadence from "~/components/expertise/faire-evoluer/FaireEvoluerCadence.vue"

const deliverables = [
  "Mises à jour techniques et de sécurité régulières",
  "Suivi des mesures d'usage et des performances",
  "Correctifs livrés sur cycle court",
  "Évolutions fonctionnelles priorisées avec vous",
  "Point trimestriel sur l'état du site",
] as const

describe("FaireEvoluerCadence", () => {
  it("porte le H2 validé « Un rythme de maintenance, pas un grand chantier annuel. »", () => {
    const wrapper = mount(FaireEvoluerCadence, { props: { deliverables } })
    expect(wrapper.get("h2").text()).toBe(
      "Un rythme de maintenance, pas un grand chantier annuel.",
    )
  })

  it("expose l'eyebrow validé « Prestations »", () => {
    const wrapper = mount(FaireEvoluerCadence, { props: { deliverables } })
    expect(wrapper.text()).toContain("Prestations")
  })

  it("rend chaque livrable verbatim depuis la config", () => {
    const wrapper = mount(FaireEvoluerCadence, { props: { deliverables } })
    const text = wrapper.text()
    for (const item of deliverables) expect(text).toContain(item)
  })

  it("rend une vraie <ol role=list> avec cinq entrées", () => {
    const wrapper = mount(FaireEvoluerCadence, { props: { deliverables } })
    const list = wrapper.get("ol")
    expect(list.attributes("role")).toBe("list")
    expect(list.findAll("li")).toHaveLength(5)
  })

  it("associe chaque livrable à sa cadence narrative", () => {
    const wrapper = mount(FaireEvoluerCadence, { props: { deliverables } })
    const labels = wrapper
      .findAll(".faire-evoluer-cadence__item-label")
      .map((n) => n.text())
    expect(labels).toEqual([
      "Continu",
      "Mesuré",
      "Cycle court",
      "Priorisé",
      "Point de situation",
    ])
  })

  it("marque chaque motif de cadence comme purement décoratif", () => {
    const wrapper = mount(FaireEvoluerCadence, { props: { deliverables } })
    const patterns = wrapper.findAll("svg.faire-evoluer-cadence__pattern")
    expect(patterns).toHaveLength(5)
    for (const svg of patterns) {
      expect(svg.attributes("aria-hidden")).toBe("true")
      expect(svg.attributes("focusable")).toBe("false")
    }
  })

  it("n'introduit aucune date, mois ni pourcentage inventé", () => {
    const wrapper = mount(FaireEvoluerCadence, { props: { deliverables } })
    const html = wrapper.html()
    expect(html).not.toMatch(/\b(janvier|février|mars|avril|mai|juin|juillet|août|septembre|octobre|novembre|décembre)\b/i)
    expect(html).not.toMatch(/\b20\d{2}\b/)
    expect(html).not.toMatch(/\b\d{1,3}\s*%\B/)
  })

  it("relie la section au H2 par aria-labelledby", () => {
    const wrapper = mount(FaireEvoluerCadence, { props: { deliverables } })
    const h2Id = wrapper.get("h2").attributes("id")
    expect(h2Id).toBeTruthy()
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(h2Id)
  })
})
