import { beforeEach, describe, expect, it, vi } from "vitest"
import { mount } from "@vue/test-utils"
import ExpertisesPage from "~/pages/expertises/index.vue"
import { expertisePages } from "~/config/expertise-pages"
import { expertisePillars } from "~/config/expertise-pillars"

interface SeoCall {
  title: string
  description: string
  path: string
  type?: string
}

const seoCalls: SeoCall[] = []

;(globalThis as unknown as { usePageSeo: (input: SeoCall) => void }).usePageSeo =
  vi.fn((input: SeoCall) => {
    seoCalls.push(input)
  })

describe("/expertises page", () => {
  beforeEach(() => {
    seoCalls.length = 0
  })

  it("renders exactly one H1 with the editorial title verbatim", () => {
    const wrapper = mount(ExpertisesPage)
    const headings = wrapper.findAll("h1")
    expect(headings).toHaveLength(1)
    expect(headings[0]!.text()).toBe(
      "Cinq pôles complémentaires pour construire une présence digitale cohérente.",
    )
  })

  it("carries the « Nos expertises » eyebrow", () => {
    const wrapper = mount(ExpertisesPage)
    expect(wrapper.text()).toContain("Nos expertises")
  })

  it("publishes the introduction verbatim", () => {
    const wrapper = mount(ExpertisesPage)
    expect(wrapper.text()).toContain(
      "Chaque entreprise possède des besoins différents. Nous réunissons les expertises adaptées pour concevoir, construire, valoriser, rendre visible et faire évoluer votre projet.",
    )
  })

  it("publishes the exact SEO title and description through usePageSeo", () => {
    mount(ExpertisesPage)
    expect(seoCalls).toHaveLength(1)
    expect(seoCalls[0]).toMatchObject({
      title: "Expertises web, design, contenu et SEO",
      description:
        "Découvrez les cinq pôles d'expertise Devzair : stratégie et design, développement web, contenus, visibilité SEO, maintenance et évolution.",
      path: "/expertises",
      type: "website",
    })
  })

  it("renders exactly five ExpertiseOverviewCard entries with each pillar label", () => {
    const wrapper = mount(ExpertisesPage)
    const cards = wrapper.findAll(".expertise-overview-card")
    expect(cards).toHaveLength(5)
    const labels = cards.map((c) => c.get("h3").text())
    expect(labels).toEqual(expertisePillars.map((p) => p.label))
  })

  it("links to `/agence` and to the `/#contact` anchor", () => {
    const wrapper = mount(ExpertisesPage)
    const hrefs = wrapper.findAll("a").map((a) => a.attributes("href"))
    expect(hrefs).toContain("/agence")
    expect(hrefs).toContain("/#contact")
  })

  it("links every published expertise card to its own `/expertises/{slug}` route", () => {
    const wrapper = mount(ExpertisesPage)
    const hrefs = wrapper.findAll("a").map((a) => a.attributes("href") ?? "")
    const publishedRoutes = expertisePages
      .filter((p) => p.status === "published")
      .map((p) => p.route)
    for (const route of publishedRoutes) {
      expect(hrefs).toContain(route)
    }
  })

  it("never links to a route absent from expertise-pages.ts", () => {
    const wrapper = mount(ExpertisesPage)
    const hrefs = wrapper.findAll("a").map((a) => a.attributes("href") ?? "")
    const knownRoutes = new Set(expertisePages.map((p) => p.route))
    for (const href of hrefs) {
      if (href.startsWith("/expertises/")) {
        expect(knownRoutes.has(href)).toBe(true)
      }
    }
  })

  it("never publishes fictional data or invented numbers", () => {
    const wrapper = mount(ExpertisesPage)
    const text = wrapper.text().toLowerCase()
    expect(text).not.toMatch(/lorem ipsum/)
    expect(text).not.toMatch(/john doe/)
    expect(text).not.toMatch(/@example\./)
    expect(text).not.toMatch(/plus de \d+ (clients|projets|années)/)
  })
})
