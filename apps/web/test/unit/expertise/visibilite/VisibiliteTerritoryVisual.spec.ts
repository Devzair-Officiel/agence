import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import VisibiliteTerritoryVisual from "~/components/expertise/visibilite/VisibiliteTerritoryVisual.vue"

describe("VisibiliteTerritoryVisual", () => {
  it("est marqué décoratif (aria-hidden sur la racine)", () => {
    const wrapper = mount(VisibiliteTerritoryVisual)
    const root = wrapper.get(".visibilite-visual")
    expect(root.attributes("aria-hidden")).toBe("true")
  })

  it("rend un SVG focusable=false et aria-hidden pour ne pas capter le focus clavier", () => {
    const wrapper = mount(VisibiliteTerritoryVisual)
    const svg = wrapper.get("svg")
    expect(svg.attributes("focusable")).toBe("false")
    expect(svg.attributes("aria-hidden")).toBe("true")
  })

  it("n'introduit aucun titre H1..H6 dans le visuel décoratif", () => {
    const wrapper = mount(VisibiliteTerritoryVisual)
    for (const tag of ["h1", "h2", "h3", "h4", "h5", "h6"]) {
      expect(wrapper.findAll(tag)).toHaveLength(0)
    }
  })

  it("porte les annotations territoire et pôle en Space Mono", () => {
    const wrapper = mount(VisibiliteTerritoryVisual)
    const text = wrapper.text()
    expect(text).toContain("Territoire")
    expect(text).toContain("Signaux")
    expect(text).toContain("Pôle · 04 / 05")
  })
})
