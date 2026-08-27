import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import FaireEvoluerCycleVisual from "~/components/expertise/faire-evoluer/FaireEvoluerCycleVisual.vue"

describe("FaireEvoluerCycleVisual", () => {
  it("marque le visuel comme purement décoratif", () => {
    const wrapper = mount(FaireEvoluerCycleVisual)
    const root = wrapper.get(".faire-evoluer-visual")
    expect(root.attributes("aria-hidden")).toBe("true")
    const svg = wrapper.get("svg")
    expect(svg.attributes("aria-hidden")).toBe("true")
    expect(svg.attributes("focusable")).toBe("false")
    expect(svg.attributes("role")).toBe("presentation")
  })

  it("n'introduit aucun texte structuré susceptible d'être annoncé (aucun H1-H6)", () => {
    const wrapper = mount(FaireEvoluerCycleVisual)
    for (const tag of ["h1", "h2", "h3", "h4", "h5", "h6"]) {
      expect(wrapper.findAll(tag)).toHaveLength(0)
    }
  })

  it("n'expose aucune métrique, uptime ou pourcentage", () => {
    const wrapper = mount(FaireEvoluerCycleVisual)
    const html = wrapper.html()
    // Interdits explicites du brief : dashboard, uptime, 99%, SLA, versions.
    expect(html).not.toMatch(/uptime/i)
    expect(html).not.toMatch(/\b99\s*%/)
    expect(html).not.toMatch(/\bSLA\b/i)
    expect(html).not.toMatch(/dashboard/i)
    expect(html).not.toMatch(/v\d+\.\d+\.\d+/)
  })
})
