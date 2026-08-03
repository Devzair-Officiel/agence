import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import HomeConnectedApproach from "~/components/home/HomeConnectedApproach.vue"
import { expertisePillars } from "~/config/expertise-pillars"

describe("HomeConnectedApproach", () => {
  it("renders a single H2 with the exact editorial title", () => {
    const wrapper = mount(HomeConnectedApproach)
    const h2s = wrapper.findAll("h2")
    expect(h2s).toHaveLength(1)
    expect(h2s[0]!.text()).toBe(
      "Une seule démarche, où chaque pôle passe le relais au suivant.",
    )
  })

  it("uses a real ordered list of exactly five steps", () => {
    const wrapper = mount(HomeConnectedApproach)
    const list = wrapper.get(".home-approach__journey")
    expect(list.element.tagName).toBe("OL")
    expect(list.findAll("li")).toHaveLength(5)
  })

  it("takes labels and order from expertisePillars (no duplication)", () => {
    const wrapper = mount(HomeConnectedApproach)
    const titles = wrapper.findAll(".home-approach__step-title")
    const expected = [...expertisePillars]
      .sort((a, b) => a.order - b.order)
      .map((p) => p.label)
    expect(titles.map((t) => t.text())).toEqual(expected)
  })

  it("renders the numeric index of each step in Space Mono, aria-hidden", () => {
    const wrapper = mount(HomeConnectedApproach)
    const indexes = wrapper.findAll(".home-approach__step-index")
    expect(indexes.map((i) => i.text().trim())).toEqual([
      "01",
      "02",
      "03",
      "04",
      "05",
    ])
    for (const index of indexes) {
      expect(index.attributes("aria-hidden")).toBe("true")
    }
  })

  it("exposes the long descriptions to screen readers (sr-only spans)", () => {
    const wrapper = mount(HomeConnectedApproach)
    const hidden = wrapper.findAll(".sr-only")
    expect(hidden).toHaveLength(5)
    const expected = [...expertisePillars]
      .sort((a, b) => a.order - b.order)
      .map((p) => p.longDescription.trim())
    expect(hidden.map((h) => h.text().trim())).toEqual(expected)
  })

  it("renders four dedicated decorative connectors between the five cards", () => {
    const wrapper = mount(HomeConnectedApproach)
    const connectors = wrapper.findAll(".home-approach__connector")
    expect(connectors).toHaveLength(4)
    for (const connector of connectors) {
      expect(connector.attributes("aria-hidden")).toBe("true")
      expect(connector.find(".home-approach__connector-glyph--vertical").text()).toBe(
        "↓",
      )
      expect(connector.find(".home-approach__connector-glyph--horizontal").text()).toBe(
        "→",
      )
    }
  })

  it("has no interactive control", () => {
    const wrapper = mount(HomeConnectedApproach)
    expect(wrapper.findAll("button")).toHaveLength(0)
    expect(wrapper.findAll("a")).toHaveLength(0)
    expect(wrapper.findAll("[tabindex]")).toHaveLength(0)
  })
})
