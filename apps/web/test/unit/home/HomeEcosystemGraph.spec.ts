import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import HomeEcosystemGraph from "~/components/home/HomeEcosystemGraph.vue"
import { expertisePillars } from "~/config/expertise-pillars"

describe("HomeEcosystemGraph", () => {
  it("renders an accessible SVG with role='img', aria-label and a linked desc (no <title> tooltip)", () => {
    const wrapper = mount(HomeEcosystemGraph)
    const svg = wrapper.get("svg.home-ecosystem-graph")
    expect(svg.attributes("role")).toBe("img")

    // Pas de <title> interne : les navigateurs le rendraient comme un
    // tooltip natif au survol, ce que l'on ne veut pas.
    expect(svg.find("title").exists()).toBe(false)

    expect(svg.attributes("aria-label")).toContain("cinq pôles")

    const desc = svg.get("desc")
    expect(desc.text()).toContain("Concevoir")
    expect(desc.text()).toContain("Faire évoluer")

    const describedBy = svg.attributes("aria-describedby")
    expect(describedBy).toBeTruthy()
    expect(desc.attributes("id")).toBe(describedBy)

    expect(svg.attributes("aria-labelledby")).toBeUndefined()
  })

  it("keeps only the decorative top-level groups aria-hidden, exposing the interactive pillars group", () => {
    const wrapper = mount(HomeEcosystemGraph)
    const svg = wrapper.get("svg.home-ecosystem-graph")

    // Les groupes racines sont toujours au nombre de 5 (background, satellite,
    // spokes, center, pillars). Seuls les 4 premiers sont décoratifs ; le
    // groupe pillars héberge les liens et doit rester dans l'arbre a11y.
    const topLevelGroups = Array.from(svg.element.children).filter(
      (node) => node.tagName.toLowerCase() === "g",
    )
    expect(topLevelGroups).toHaveLength(5)

    const hiddenGroups = topLevelGroups.filter(
      (group) => group.getAttribute("aria-hidden") === "true",
    )
    expect(hiddenGroups).toHaveLength(4)

    const pillarsGroup = topLevelGroups.find((group) =>
      group.classList.contains("home-ecosystem-graph__pillars"),
    )
    expect(pillarsGroup).toBeDefined()
    expect(pillarsGroup!.getAttribute("aria-hidden")).toBeNull()

    // Aucun tabindex custom : on s'appuie sur la focusabilité native des
    // ancres SVG (`<a href>`), qui sont automatiquement dans l'ordre de
    // tabulation.
    expect(svg.find("[tabindex]").exists()).toBe(false)
  })

  it("wraps each pillar in a link to its expertise page with an accessible label", () => {
    const wrapper = mount(HomeEcosystemGraph)
    const links = wrapper.findAll(".home-ecosystem-graph__pillar-link")
    expect(links).toHaveLength(expertisePillars.length)

    for (const [index, pillar] of expertisePillars.entries()) {
      const link = links[index]!
      expect(link.attributes("href")).toBe(`/expertises/${pillar.id}`)
      expect(link.attributes("aria-label")).toContain(pillar.label)
    }
  })

  it("renders one graphical node per pillar with every configured label and description segment", () => {
    const wrapper = mount(HomeEcosystemGraph)
    const pillars = wrapper.findAll(".home-ecosystem-graph__pillar")
    expect(pillars).toHaveLength(expertisePillars.length)

    for (const [index, pillar] of expertisePillars.entries()) {
      const node = pillars[index]!
      expect(node.get(".home-ecosystem-graph__pillar-label").text()).toBe(
        pillar.shortLabel,
      )

      const renderedDescription = node
        .findAll(".home-ecosystem-graph__pillar-description tspan")
        .map((line) => line.text())
        .join(" · ")
      expect(renderedDescription).toBe(pillar.description)
    }
  })

  it("uses an expanded viewBox and wraps long descriptions instead of clipping them", () => {
    const wrapper = mount(HomeEcosystemGraph)
    expect(wrapper.get("svg").attributes("viewBox")).toBe("-24 0 620 520")

    const descriptionLines = wrapper.findAll(
      ".home-ecosystem-graph__pillar-description tspan",
    )
    expect(descriptionLines.length).toBeGreaterThan(expertisePillars.length)
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
