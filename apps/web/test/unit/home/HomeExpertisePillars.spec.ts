import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import HomeExpertisePillars from "~/components/home/HomeExpertisePillars.vue"
import { expertisePillars } from "~/config/expertise-pillars"

describe("HomeExpertisePillars", () => {
  it("exposes the section under the #expertises anchor", () => {
    const wrapper = mount(HomeExpertisePillars)
    const section = wrapper.get("section#expertises")
    expect(section.attributes("aria-labelledby")).toBe("home-pillars-title")
  })

  it("renders a single H2 with the exact section title", () => {
    const wrapper = mount(HomeExpertisePillars)
    const h2s = wrapper.findAll("h2")
    expect(h2s).toHaveLength(1)
    expect(h2s[0]!.text()).toBe(
      "Des expertises complémentaires, cinq pôles, une même démarche.",
    )
  })

  it("renders the five pillars in order using expertisePillars as source", () => {
    const wrapper = mount(HomeExpertisePillars)
    const titles = wrapper.findAll(".home-pillars__card-title")
    const expected = [...expertisePillars]
      .sort((a, b) => a.order - b.order)
      .map((p) => p.label)
    expect(titles.map((t) => t.text())).toEqual(expected)
  })

  it("renders long description and exactly three services per pillar", () => {
    const wrapper = mount(HomeExpertisePillars)
    const cards = wrapper.findAll(".home-pillars__card")
    expect(cards).toHaveLength(5)
    const sorted = [...expertisePillars].sort((a, b) => a.order - b.order)
    cards.forEach((card, index) => {
      const pillar = sorted[index]!
      expect(card.get(".home-pillars__card-description").text()).toBe(
        pillar.longDescription,
      )
      const services = card.findAll(".home-pillars__service")
      expect(services).toHaveLength(3)
      expect(services.map((s) => s.text())).toEqual([...pillar.services])
    })
  })

  it("applies the variant data attribute so CSS can style Construire (primary) and Faire évoluer (accent)", () => {
    const wrapper = mount(HomeExpertisePillars)
    const cards = wrapper.findAll(".home-pillars__card")
    const variantsByOrder = cards.reduce<Record<string, string | undefined>>(
      (acc, card) => {
        const order = card.attributes("data-order")
        if (order) {
          acc[order] = card.attributes("data-variant")
        }
        return acc
      },
      {},
    )
    expect(variantsByOrder["2"]).toBe("primary")
    expect(variantsByOrder["5"]).toBe("accent")
  })

  it("uses semantic list markup (ul > li) for pillars and their services", () => {
    const wrapper = mount(HomeExpertisePillars)
    const grid = wrapper.get(".home-pillars__grid")
    expect(grid.element.tagName).toBe("UL")
    expect(grid.findAll(":scope > li")).toHaveLength(5)
    const servicesLists = wrapper.findAll(".home-pillars__services")
    expect(servicesLists).toHaveLength(5)
    for (const list of servicesLists) {
      expect(list.element.tagName).toBe("UL")
    }
  })

  it("keeps the mobile scroll hint aria-hidden and provides an sr-only equivalent", () => {
    const wrapper = mount(HomeExpertisePillars)
    const hint = wrapper.get(".home-pillars__hint")
    expect(hint.attributes("aria-hidden")).toBe("true")
    const srOnly = wrapper.get(".sr-only")
    expect(srOnly.text()).toContain("horizontalement")
  })

  it("does not introduce interactive controls (cards not focusable)", () => {
    const wrapper = mount(HomeExpertisePillars)
    expect(wrapper.findAll("button")).toHaveLength(0)
    expect(wrapper.findAll("a")).toHaveLength(0)
    expect(wrapper.findAll("[tabindex]")).toHaveLength(0)
  })
})
