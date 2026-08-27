import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ValoriserEditorialVisual from "~/components/expertise/valoriser/ValoriserEditorialVisual.vue"

describe("ValoriserEditorialVisual", () => {
  it("est marqué décoratif (aria-hidden sur la racine)", () => {
    const wrapper = mount(ValoriserEditorialVisual)
    const root = wrapper.get(".valoriser-visual")
    expect(root.attributes("aria-hidden")).toBe("true")
  })

  it("rend un SVG focusable=false et aria-hidden pour ne pas capter le focus clavier", () => {
    const wrapper = mount(ValoriserEditorialVisual)
    const svg = wrapper.get("svg")
    expect(svg.attributes("focusable")).toBe("false")
    expect(svg.attributes("aria-hidden")).toBe("true")
  })

  it("n'introduit aucun titre H1..H6 dans le visuel décoratif", () => {
    const wrapper = mount(ValoriserEditorialVisual)
    for (const tag of ["h1", "h2", "h3", "h4", "h5", "h6"]) {
      expect(wrapper.findAll(tag)).toHaveLength(0)
    }
  })

  it("annonce les trois zones éditoriales IMAGE / MESSAGE / STRUCTURE", () => {
    const wrapper = mount(ValoriserEditorialVisual)
    const captions = wrapper
      .findAll(".valoriser-visual__caption")
      .map((n) => n.text())
    expect(captions).toEqual(["IMAGE / 01", "MESSAGE / 02", "STRUCTURE / 03"])
  })
})
