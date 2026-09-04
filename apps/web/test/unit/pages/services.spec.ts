import { beforeEach, describe, expect, it, vi } from "vitest"
import { mount } from "@vue/test-utils"
import ServicesPage from "~/pages/services/index.vue"
import { servicePages } from "~/config/service-pages"

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

describe("/services page", () => {
  beforeEach(() => {
    seoCalls.length = 0
  })

  it("renders exactly one H1 with the editorial title verbatim", () => {
    const wrapper = mount(ServicesPage)
    const headings = wrapper.findAll("h1")
    expect(headings).toHaveLength(1)
    expect(headings[0]!.text()).toBe(
      "Créer, rendre visible et faire évoluer votre présence en ligne.",
    )
  })

  it("carries the « Nos services » eyebrow", () => {
    const wrapper = mount(ServicesPage)
    expect(wrapper.text()).toContain("Nos services")
  })

  it("publishes the exact SEO title and description through usePageSeo", () => {
    mount(ServicesPage)
    expect(seoCalls).toHaveLength(1)
    expect(seoCalls[0]).toMatchObject({
      title: "Nos services web : création, visibilité et accompagnement",
      description:
        "Création de site internet, e-commerce, application web, SEO, design, contenu et maintenance. Devzair couvre l'ensemble du cycle numérique selon le besoin réel de votre projet.",
      path: "/services",
      type: "website",
    })
  })

  it("renders all eight service titles in the grid", () => {
    const wrapper = mount(ServicesPage)
    for (const service of servicePages) {
      expect(wrapper.text()).toContain(service.title)
    }
  })

  it("renders all eight service summaries in the grid", () => {
    const wrapper = mount(ServicesPage)
    for (const service of servicePages) {
      expect(wrapper.text()).toContain(service.summary)
    }
  })

  it("renders zero NuxtLink elements for planned services", () => {
    const wrapper = mount(ServicesPage)
    const nuxtLinks = wrapper.findAll("[data-nuxt-link]")
    // Only hero CTAs (/estimer-mon-projet and /contact) + EditorialCallout primary/secondary.
    // No service cards should produce NuxtLink when all are planned.
    for (const link of nuxtLinks) {
      const href = link.attributes("href") ?? ""
      // None should point to a /services/{slug} route.
      expect(href).not.toMatch(/^\/services\/[a-z]/)
    }
  })

  it("renders the three orientation blocks with their titles", () => {
    const wrapper = mount(ServicesPage)
    expect(wrapper.text()).toContain(
      "Vous n'avez pas encore de site — ou votre site actuel ne vous représente plus.",
    )
    expect(wrapper.text()).toContain(
      "Vous avez un site, mais peu de visiteurs qualifiés.",
    )
    expect(wrapper.text()).toContain(
      "Vous avez un site en ligne et avez besoin d'un suivi dans le temps.",
    )
  })

  it("renders the estimateur CTA link", () => {
    const wrapper = mount(ServicesPage)
    const links = wrapper.findAll("[data-nuxt-link]")
    const hrefs = links.map((l) => l.attributes("href"))
    expect(hrefs).toContain("/estimer-mon-projet")
  })

  it("renders the contact CTA link", () => {
    const wrapper = mount(ServicesPage)
    const links = wrapper.findAll("[data-nuxt-link]")
    const hrefs = links.map((l) => l.attributes("href"))
    expect(hrefs).toContain("/contact")
  })
})
