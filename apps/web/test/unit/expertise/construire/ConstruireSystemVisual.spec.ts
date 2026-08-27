import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ConstruireSystemVisual from "~/components/expertise/construire/ConstruireSystemVisual.vue"

describe("ConstruireSystemVisual", () => {
  it("est marqué décoratif (aria-hidden + role=presentation)", () => {
    const wrapper = mount(ConstruireSystemVisual)
    const root = wrapper.get(".construire-system")
    expect(root.attributes("aria-hidden")).toBe("true")
    expect(root.attributes("role")).toBe("presentation")
  })

  it("rend un SVG focusable=false et aria-hidden pour ne pas capter le focus clavier", () => {
    const wrapper = mount(ConstruireSystemVisual)
    const svg = wrapper.get("svg")
    expect(svg.attributes("focusable")).toBe("false")
    expect(svg.attributes("aria-hidden")).toBe("true")
  })

  it("n'introduit aucun titre H1..H6 dans le visuel décoratif", () => {
    const wrapper = mount(ConstruireSystemVisual)
    for (const tag of ["h1", "h2", "h3", "h4", "h5", "h6"]) {
      expect(wrapper.findAll(tag)).toHaveLength(0)
    }
  })
})
