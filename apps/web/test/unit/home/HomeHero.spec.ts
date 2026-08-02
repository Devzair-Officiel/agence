import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import HomeHero from "~/components/home/HomeHero.vue"

describe("HomeHero", () => {
  it("renders a single H1 with the full accessible sentence", () => {
    const wrapper = mount(HomeHero)
    const headings = wrapper.findAll("h1")
    expect(headings).toHaveLength(1)
    const title = headings[0]!.text()
    expect(title).toContain("Des solutions digitales complètes")
    expect(title).toContain("visible, crédible et efficace")
    expect(title).toContain("en ligne.")
  })

  it("keeps the visual emphasis inline inside the H1 (no split heading)", () => {
    const wrapper = mount(HomeHero)
    const h1 = wrapper.get("h1")
    const emphasis = h1.find(".home-hero__title-emphasis")
    expect(emphasis.exists()).toBe(true)
    expect(emphasis.text()).toBe("visible, crédible et efficace")
    expect(emphasis.element.tagName).toBe("SPAN")
  })

  it("renders the introduction paragraph verbatim", () => {
    const wrapper = mount(HomeHero)
    const lead = wrapper.get(".home-hero__lead").text()
    expect(lead).toContain("Sites internet, applications métier, identité visuelle")
    expect(lead).toContain("référencement")
    expect(lead).toContain("évolutive.")
  })

  it("exposes the two CTAs as real links (not fake buttons)", () => {
    const wrapper = mount(HomeHero)
    const ctas = wrapper.get(".home-hero__ctas")
    const links = ctas.findAll("a")
    expect(links).toHaveLength(2)

    const primary = links[0]!
    expect(primary.text()).toContain("Parler de votre projet")
    expect(primary.attributes("href")).toBe("#contact")
    expect(primary.attributes("data-variant")).toBe("primary")

    const secondary = links[1]!
    expect(secondary.text()).toContain("Découvrir nos réalisations")
    expect(secondary.attributes("href")).toBe("#realisations")
    expect(secondary.attributes("data-variant")).toBe("secondary")
  })

  it("renders exactly two reassurance items as a semantic list", () => {
    const wrapper = mount(HomeHero)
    const list = wrapper.get(".home-hero__reassurance")
    expect(list.element.tagName).toBe("UL")
    const items = list.findAll("li")
    expect(items).toHaveLength(2)

    const titles = items.map((li) =>
      li.get(".home-hero__reassurance-title").text(),
    )
    const details = items.map((li) =>
      li.get(".home-hero__reassurance-detail").text(),
    )
    expect(titles).toEqual([
      "Un pilotage clair du projet",
      "Plusieurs expertises",
    ])
    expect(details).toEqual(["Du brief à l’évolution", "Réunies en cinq pôles"])
  })

  it("embeds the ecosystem graph exactly once", () => {
    const wrapper = mount(HomeHero)
    const svgs = wrapper.findAll("svg.home-ecosystem-graph")
    expect(svgs).toHaveLength(1)
  })
})
