import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import VisibiliteLayers from "~/components/expertise/visibilite/VisibiliteLayers.vue"

const deliverables = [
  "Audit SEO technique, sémantique et éditorial",
  "Optimisation des pages existantes",
  "Fiche d'établissement locale et cohérence NAP",
  "Plan éditorial trimestriel ou semestriel",
  "Tableau de suivi des positions et du trafic organique",
] as const

describe("VisibiliteLayers", () => {
  it("expose l'eyebrow « Prestations » attendu par le contrat éditorial", () => {
    const wrapper = mount(VisibiliteLayers, { props: { deliverables } })
    expect(wrapper.text()).toContain("Prestations")
  })

  it("porte un unique H2 nommé « Cinq couches de visibilité. »", () => {
    const wrapper = mount(VisibiliteLayers, { props: { deliverables } })
    expect(wrapper.findAll("h2")).toHaveLength(1)
    expect(wrapper.get("h2").text()).toBe("Cinq couches de visibilité.")
  })

  it("rend un <ol role=list> contenant une couche par livrable", () => {
    const wrapper = mount(VisibiliteLayers, { props: { deliverables } })
    const list = wrapper.get("ol")
    expect(list.attributes("role")).toBe("list")
    expect(list.findAll("li")).toHaveLength(deliverables.length)
  })

  it("rend chaque libellé de livrable verbatim", () => {
    const wrapper = mount(VisibiliteLayers, { props: { deliverables } })
    const rendered = wrapper
      .findAll(".visibilite-layers__label")
      .map((n) => n.text())
    expect(rendered).toEqual([...deliverables])
  })

  it("numérote les couches 01, 02, …", () => {
    const wrapper = mount(VisibiliteLayers, { props: { deliverables } })
    const indexes = wrapper
      .findAll(".visibilite-layers__index")
      .map((n) => n.text())
    expect(indexes).toEqual(["01", "02", "03", "04", "05"])
  })

  it("mappe cinq tags Technique / Page / Local / Contenu / Suivi", () => {
    const wrapper = mount(VisibiliteLayers, { props: { deliverables } })
    const tags = wrapper.findAll(".visibilite-layers__tag").map((n) => n.text())
    expect(tags).toEqual([
      "Technique",
      "Page",
      "Local",
      "Contenu",
      "Suivi",
    ])
  })

  it("marque tous les décors SVG aria-hidden pour ne pas polluer l'AT", () => {
    const wrapper = mount(VisibiliteLayers, { props: { deliverables } })
    const decors = wrapper.findAll(".visibilite-layers__decor")
    expect(decors.length).toBe(deliverables.length)
    for (const decor of decors) {
      expect(decor.attributes("aria-hidden")).toBe("true")
    }
  })
})
