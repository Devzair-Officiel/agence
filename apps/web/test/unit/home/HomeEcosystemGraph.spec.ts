import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import HomeEcosystemGraph from "~/components/home/HomeEcosystemGraph.vue"
import { expertisePillars } from "~/config/expertise-pillars"

describe("HomeEcosystemGraph", () => {
  it("wraps the SVG in a named <nav> landmark and does NOT expose the SVG itself as a named image", () => {
    const wrapper = mount(HomeEcosystemGraph)

    // Le SVG contient cinq liens focusables. Lui donner role="img" +
    // aria-label déclenche `nested-interactive` (Axe / WCAG 4.1.2). Le nom
    // du bloc est donc porté par un <nav aria-label> parent (landmark de
    // navigation), pas par le SVG. Voir DEC-073.
    const nav = wrapper.get("nav.home-ecosystem-navigation")
    expect(nav.attributes("aria-label")).toBe(
      "Explorer les expertises Devzair",
    )

    const svg = wrapper.get("svg.home-ecosystem-graph")
    expect(svg.attributes("role")).toBeUndefined()
    expect(svg.attributes("aria-label")).toBeUndefined()
    expect(svg.attributes("aria-labelledby")).toBeUndefined()
    expect(svg.attributes("aria-describedby")).toBeUndefined()

    // Pas de <title> interne : les navigateurs le rendraient comme un
    // tooltip natif au survol, ce que l'on ne veut pas.
    expect(svg.find("title").exists()).toBe(false)
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

    // Aucun tabindex custom sur le SVG lui-même : on s'appuie sur la
    // focusabilité native des ancres SVG (`<a href>`), qui sont
    // automatiquement dans l'ordre de tabulation.
    expect(svg.attributes("tabindex")).toBeUndefined()
  })

  it("wraps each pillar in a link with a contextualised aria-label (readable in a screen reader link list)", () => {
    const wrapper = mount(HomeEcosystemGraph)
    const links = wrapper.findAll(".home-ecosystem-graph__pillar-link")
    expect(links).toHaveLength(expertisePillars.length)

    for (const [index, pillar] of expertisePillars.entries()) {
      const link = links[index]!
      expect(link.attributes("href")).toBe(`/expertises/${pillar.id}`)
      // Chaque libellé doit rester compréhensible hors contexte du graphe.
      expect(link.attributes("aria-label")).toBe(
        `Découvrir l'expertise ${pillar.label}`,
      )
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

  it("uses an expanded viewBox that accommodates the widest lateral labels", () => {
    const wrapper = mount(HomeEcosystemGraph)
    // Marge latérale gauche portée à 32 unités SVG pour absorber la
    // longueur des libellés des pôles gauches (Visibilité, Faire évoluer)
    // qui débordaient à ≥1 px CSS aux points de rupture 320 / 390 (DEV-045).
    expect(wrapper.get("svg").attributes("viewBox")).toBe("-56 0 664 520")

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
