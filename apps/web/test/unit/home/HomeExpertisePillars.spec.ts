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

  it("renders one accordion toggle per pillar wired to its panel, first one open", () => {
    const wrapper = mount(HomeExpertisePillars)
    const sorted = [...expertisePillars].sort((a, b) => a.order - b.order)
    const cards = wrapper.findAll(".home-pillars__card")

    cards.forEach((card, index) => {
      const pillar = sorted[index]!
      const toggle = card.get("button.home-pillars__card-toggle")
      const panelId = `home-pillar-panel-${pillar.id}`
      expect(toggle.attributes("type")).toBe("button")
      expect(toggle.attributes("aria-label")).toBe(`Détail du pôle ${pillar.label}`)
      expect(toggle.attributes("aria-controls")).toBe(panelId)
      expect(card.get(".home-pillars__card-panel").attributes("id")).toBe(panelId)

      const expanded = index === 0 ? "true" : "false"
      expect(toggle.attributes("aria-expanded")).toBe(expanded)
      expect(card.attributes("data-open")).toBe(expanded)
    })
  })

  it("keeps the toggle outside the H3 so the title carries the label once", () => {
    const wrapper = mount(HomeExpertisePillars)
    for (const title of wrapper.findAll(".home-pillars__card-title")) {
      expect(title.find("button").exists()).toBe(false)
    }
  })

  it("opens and closes each pillar independently on click", async () => {
    const wrapper = mount(HomeExpertisePillars)
    const cards = wrapper.findAll(".home-pillars__card")
    const second = cards[1]!

    await second.get(".home-pillars__card-toggle").trigger("click")
    expect(second.attributes("data-open")).toBe("true")
    expect(second.get(".home-pillars__card-toggle").attributes("aria-expanded")).toBe("true")
    expect(cards[0]!.attributes("data-open")).toBe("true")

    await cards[0]!.get(".home-pillars__card-toggle").trigger("click")
    expect(cards[0]!.attributes("data-open")).toBe("false")
    expect(second.attributes("data-open")).toBe("true")
  })

  it("links each panel to its pillar page with an explicit accessible name", () => {
    const wrapper = mount(HomeExpertisePillars)
    const sorted = [...expertisePillars].sort((a, b) => a.order - b.order)
    const links = wrapper.findAll(".home-pillars__card-more")
    expect(links).toHaveLength(sorted.length)
    links.forEach((link, index) => {
      const pillar = sorted[index]!
      expect(link.attributes("href")).toBe(`/expertises/${pillar.id}`)
      expect(link.get(".sr-only").text()).toBe(pillar.label)
      expect(link.text()).toContain("Découvrir le pôle")
    })
  })

  it("wraps each card title in a link to /expertises/{id} (stretched-link pattern)", () => {
    const wrapper = mount(HomeExpertisePillars)
    // Aucun tabindex custom : la carte reste sémantiquement un `<li>` ; le
    // lien étiré du titre (≥768px) n'englobe aucun autre élément interactif.
    expect(wrapper.findAll("[tabindex]")).toHaveLength(0)
    for (const link of wrapper.findAll(".home-pillars__card-link")) {
      expect(link.find("a, button").exists()).toBe(false)
    }

    const links = wrapper.findAll(".home-pillars__card-link")
    expect(links).toHaveLength(expertisePillars.length)

    const sorted = [...expertisePillars].sort((a, b) => a.order - b.order)
    for (const [index, pillar] of sorted.entries()) {
      const link = links[index]!
      expect(link.attributes("href")).toBe(`/expertises/${pillar.id}`)
      // Le nom accessible du lien reste le libellé complet du pôle — court
      // et unique, contrairement au contenu de toute la carte.
      expect(link.text()).toBe(pillar.label)
    }
  })
})
