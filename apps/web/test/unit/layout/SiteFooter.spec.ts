import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import SiteFooter from "~/components/layout/SiteFooter.vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import { footerNavigation, legalNavigation } from "~/config/navigation"
import { expertisePages } from "~/config/expertise-pages"

describe("SiteFooter", () => {
  function mountFooter() {
    return mount(SiteFooter, {
      global: { components: { BaseContainer } },
    })
  }

  // ── Sémantique ─────────────────────────────────────────────────────

  it("uses a <footer> semantic element", () => {
    expect(mountFooter().element.tagName).toBe("FOOTER")
  })

  it("renders the nav landmark for footer navigation", () => {
    const nav = mountFooter().find('nav[aria-label="Navigation du pied de page"]')
    expect(nav.exists()).toBe(true)
  })

  it("renders column titles as h3 elements", () => {
    const wrapper = mountFooter()
    const h3s = wrapper.findAll("h3.site-footer__column-title")
    expect(h3s.length).toBe(footerNavigation.length)
    const titles = h3s.map((el) => el.text())
    for (const group of footerNavigation) {
      expect(titles).toContain(group.title)
    }
  })

  // ── Colonne Marque ─────────────────────────────────────────────────

  it("renders the logo link to / with aria-label", () => {
    const wrapper = mountFooter()
    const link = wrapper.find('a.site-footer__logo-link[href="/"]')
    expect(link.exists()).toBe(true)
    expect(link.attributes("aria-label")).toBeTruthy()
  })

  it("renders the eyebrow containing 'Agence digitale'", () => {
    const wrapper = mountFooter()
    const eyebrow = wrapper.find(".site-footer__eyebrow")
    expect(eyebrow.exists()).toBe(true)
    expect(eyebrow.text().toLowerCase()).toContain("agence digitale")
  })

  it("renders the tagline with brand positioning text", () => {
    const wrapper = mountFooter()
    const tagline = wrapper.find(".site-footer__tagline")
    expect(tagline.exists()).toBe(true)
    expect(tagline.text()).toContain("Sites")
    expect(tagline.text()).toContain("Visibilité")
  })

  // ── Expertises ─────────────────────────────────────────────────────

  it("renders all navigation links declared in footerNavigation", () => {
    const wrapper = mountFooter()
    for (const group of footerNavigation) {
      for (const item of group.items) {
        const link = wrapper.find(`a[href="${item.to}"]`)
        expect(link.exists(), `link to ${item.to} should exist`).toBe(true)
        expect(link.text()).toContain(item.label)
      }
    }
  })

  it("renders exactly 5 expertise links pointing to /expertises/* routes", () => {
    const wrapper = mountFooter()
    const published = expertisePages.filter((p) => p.status === "published")
    expect(published).toHaveLength(5)
    for (const page of published) {
      const link = wrapper.find(`a[href="${page.route}"]`)
      expect(link.exists(), `expertise link ${page.route} should exist`).toBe(true)
      expect(link.text()).toContain(page.shortTitle)
    }
  })

  // ── Découvrir (y compris Ressources) ───────────────────────────────

  it("renders /ressources inside the Découvrir group (not a separate group)", () => {
    const wrapper = mountFooter()

    // Le lien /ressources est bien présent
    const link = wrapper.find('a[href="/ressources"]')
    expect(link.exists()).toBe(true)

    // Il n'existe pas de groupe "Ressources" séparé
    const discoverGroup = footerNavigation.find((g) => g.title === "Découvrir")
    expect(discoverGroup).toBeDefined()
    const resourcesItem = discoverGroup!.items.find((i) => i.to === "/ressources")
    expect(resourcesItem).toBeDefined()

    const resourcesGroup = footerNavigation.find((g) => g.title === "Ressources")
    expect(resourcesGroup).toBeUndefined()
  })

  it("has exactly 2 nav groups (Expertises + Découvrir)", () => {
    expect(footerNavigation).toHaveLength(2)
    expect(footerNavigation[0].title).toBe("Expertises")
    expect(footerNavigation[1].title).toBe("Découvrir")
  })

  // ── Éléments supprimés ─────────────────────────────────────────────

  it("does NOT render the DEVZAIR wordmark", () => {
    expect(mountFooter().find(".site-footer__wordmark").exists()).toBe(false)
  })

  it("does NOT render decorative link arrows (.site-footer__link-arrow)", () => {
    expect(mountFooter().find(".site-footer__link-arrow").exists()).toBe(false)
  })

  // ── Barre légale ───────────────────────────────────────────────────

  it("renders dynamic copyright year", () => {
    const wrapper = mountFooter()
    const copyright = wrapper.find(".site-footer__copyright")
    expect(copyright.exists()).toBe(true)
    expect(copyright.text()).toContain(String(new Date().getFullYear()))
  })

  it("renders the back-to-top link with href #top", () => {
    const wrapper = mountFooter()
    const link = wrapper.find('a[href="#top"].site-footer__back-to-top')
    expect(link.exists()).toBe(true)
  })

  it("does not render legal links when legalNavigation is empty", () => {
    const wrapper = mountFooter()
    if (legalNavigation.length === 0) {
      expect(wrapper.find(".site-footer__legal-list").exists()).toBe(false)
    } else {
      for (const item of legalNavigation) {
        expect(wrapper.find(`a[href="${item.to}"]`).exists()).toBe(true)
      }
    }
  })

  // ── Sécurité ───────────────────────────────────────────────────────

  it("does not render mailto or tel links (no fake contact info)", () => {
    const wrapper = mountFooter()
    expect(wrapper.find('a[href^="mailto:"]').exists()).toBe(false)
    expect(wrapper.find('a[href^="tel:"]').exists()).toBe(false)
  })

  it("has no bare href=\"#\" dead anchors", () => {
    expect(mountFooter().findAll('a[href="#"]')).toHaveLength(0)
  })
})
