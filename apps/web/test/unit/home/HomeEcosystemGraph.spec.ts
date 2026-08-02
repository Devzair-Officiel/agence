import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import HomeEcosystemGraph from "~/components/home/HomeEcosystemGraph.vue"
import { expertisePillars } from "~/config/expertise-pillars"

describe("HomeEcosystemGraph", () => {
  it("renders an accessible SVG with role='img' and a title/desc pair", () => {
    const wrapper = mount(HomeEcosystemGraph)
    const svg = wrapper.get("svg.home-ecosystem-graph")
    expect(svg.attributes("role")).toBe("img")

    const title = svg.get("title")
    const desc = svg.get("desc")
    expect(title.text()).toContain("cinq pôles")
    expect(desc.text()).toContain("Concevoir")
    expect(desc.text()).toContain("Faire évoluer")

    const labelledBy = svg.attributes("aria-labelledby")
    expect(labelledBy).toBeTruthy()
    const [titleRef, descRef] = labelledBy!.split(" ")
    expect(title.attributes("id")).toBe(titleRef)
    expect(desc.attributes("id")).toBe(descRef)
    expect(titleRef).not.toBe(descRef)
  })

  it("hides every top-level decorative group from assistive tech and stays outside the tab order", () => {
    const wrapper = mount(HomeEcosystemGraph)
    const svg = wrapper.get("svg.home-ecosystem-graph")

    // Seuls les groupes racines (background, spokes, center, pillars) portent
    // `aria-hidden`. Les <g> imbriqués héritent, on ne les décore pas deux fois.
    const topLevelGroups = Array.from(svg.element.children).filter(
      (node) => node.tagName.toLowerCase() === "g",
    )
    expect(topLevelGroups).toHaveLength(4)
    for (const group of topLevelGroups) {
      expect(group.getAttribute("aria-hidden")).toBe("true")
    }

    expect(svg.find("[tabindex]").exists()).toBe(false)
  })

  it("renders one graphical node per pillar with the config's short label", () => {
    const wrapper = mount(HomeEcosystemGraph)
    const pillars = wrapper.findAll(".home-ecosystem-graph__pillar")
    expect(pillars).toHaveLength(expertisePillars.length)

    const svgHtml = wrapper.html()
    for (const pillar of expertisePillars) {
      expect(svgHtml).toContain(pillar.shortLabel)
      expect(svgHtml).toContain(pillar.description)
    }
  })

  it("distinguishes the primary pillar via data-variant", () => {
    const wrapper = mount(HomeEcosystemGraph)
    const primary = wrapper.findAll(
      '.home-ecosystem-graph__pillar[data-variant="primary"]',
    )
    expect(primary).toHaveLength(1)
  })

  it("staggers the spoke animations via inline animation-delay", () => {
    const wrapper = mount(HomeEcosystemGraph)
    const spokes = wrapper.findAll(".home-ecosystem-graph__spoke")
    expect(spokes).toHaveLength(5)
    // Values below stay in sync with the 80ms cascade defined in the component.
    expect(spokes[0]!.attributes("style")).toContain("animation-delay: 0ms")
    expect(spokes[4]!.attributes("style")).toContain("animation-delay: 320ms")
  })
})
