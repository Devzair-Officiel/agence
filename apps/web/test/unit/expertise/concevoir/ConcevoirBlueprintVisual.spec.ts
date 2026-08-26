import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ConcevoirBlueprintVisual from "~/components/expertise/concevoir/ConcevoirBlueprintVisual.vue"

describe("ConcevoirBlueprintVisual", () => {
  it("est marqué décoratif (aria-hidden + role=presentation)", () => {
    const wrapper = mount(ConcevoirBlueprintVisual)
    const root = wrapper.get(".concevoir-blueprint")
    expect(root.attributes("aria-hidden")).toBe("true")
    expect(root.attributes("role")).toBe("presentation")
  })

  it("rend un SVG focusable=false et aria-hidden pour ne pas capter le focus clavier", () => {
    const wrapper = mount(ConcevoirBlueprintVisual)
    const svg = wrapper.get("svg")
    expect(svg.attributes("focusable")).toBe("false")
    expect(svg.attributes("aria-hidden")).toBe("true")
  })

  it("n'introduit aucun titre H1..H6 dans le visuel décoratif", () => {
    const wrapper = mount(ConcevoirBlueprintVisual)
    for (const tag of ["h1", "h2", "h3", "h4", "h5", "h6"]) {
      expect(wrapper.findAll(tag)).toHaveLength(0)
    }
  })
})
