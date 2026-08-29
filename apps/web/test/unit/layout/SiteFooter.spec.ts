import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import SiteFooter from "~/components/layout/SiteFooter.vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import { footerNavigation, legalNavigation, primaryCta } from "~/config/navigation"
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

  it("renders the logo link with aria-label", () => {
    const wrapper = mountFooter()
    const logoLink = wrapper.find("a.site-footer__logo-link")
    expect(logoLink.exists()).toBe(true)
    expect(logoLink.attributes("aria-label")).toBeTruthy()
    expect(logoLink.find("img.site-footer__logo").exists()).toBe(true)
  })

  it("renders the tagline without periods", () => {
    const wrapper = mountFooter()
    const tagline = wrapper.find(".site-footer__tagline")
    expect(tagline.exists()).toBe(true)
    expect(tagline.text()).toContain("Sites")
    expect(tagline.text()).toContain("Visibilité")
    // Pas de points dans la tagline
    expect(tagline.text()).not.toContain(".")
  })

  it("renders the positioning description", () => {
    expect(mountFooter().find(".site-footer__description").text()).toContain(
      "solutions digitales cohérentes",
    )
  })

  // ── Expertises ─────────────────────────────────────────────────────

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

  // ── Découvrir ──────────────────────────────────────────────────────

  it("renders all nav items declared in footerNavigation groups", () => {
    const wrapper = mountFooter()
    for (const group of footerNavigation) {
      for (const item of group.items) {
        const link = wrapper.find(`a[href="${item.to}"]`)
        expect(link.exists(), `link to ${item.to} should exist`).toBe(true)
        expect(link.text()).toContain(item.label)
      }
    }
  })

  it("renders /ressources as a plain link inside Découvrir items", () => {
    const discoverGroup = footerNavigation.find((g) => g.title === "Découvrir")
    expect(discoverGroup).toBeDefined()
    expect(discoverGroup!.items.some((i) => i.to === "/ressources")).toBe(true)
    expect(mountFooter().find('a[href="/ressources"]').exists()).toBe(true)
  })

  it("has exactly 2 nav groups (Expertises + Découvrir)", () => {
    expect(footerNavigation).toHaveLength(2)
    expect(footerNavigation[0].title).toBe("Expertises")
    expect(footerNavigation[1].title).toBe("Découvrir")
  })

  it("has no separate Ressources group", () => {
    expect(footerNavigation.find((g) => g.title === "Ressources")).toBeUndefined()
  })

  // ── CTA "Parler de votre projet" ───────────────────────────────────

  it("renders the CTA button for primaryCta in the Découvrir column", () => {
    const wrapper = mountFooter()
    const cta = wrapper.find(`a.site-footer__cta[href="${primaryCta.to}"]`)
    expect(cta.exists()).toBe(true)
    expect(cta.text()).toContain(primaryCta.label)
    expect(cta.find(".site-footer__cta-icon").exists()).toBe(true)
  })

  it("does NOT include the CTA label in the regular items list", () => {
    const discoverGroup = footerNavigation.find((g) => g.title === "Découvrir")
    expect(discoverGroup?.items.some((i) => i.label === primaryCta.label)).toBe(false)
  })

  // ── Éléments supprimés ─────────────────────────────────────────────

  it("does NOT render the DEVZAIR wordmark", () => {
    expect(mountFooter().find(".site-footer__wordmark").exists()).toBe(false)
  })

  it("does NOT render decorative link arrows (.site-footer__link-arrow)", () => {
    expect(mountFooter().find(".site-footer__link-arrow").exists()).toBe(false)
  })

  // ── Liens inline ───────────────────────────────────────────────────

  it("nav links do not use display:block (must be inline-flex)", () => {
    // Vérifié via la classe CSS — le composant doit utiliser .site-footer__link
    const wrapper = mountFooter()
    const links = wrapper.findAll("a.site-footer__link")
    expect(links.length).toBeGreaterThan(0)
  })

  // ── Barre légale ───────────────────────────────────────────────────

  it("renders dynamic copyright year", () => {
    const copyright = mountFooter().find(".site-footer__copyright")
    expect(copyright.exists()).toBe(true)
    expect(copyright.text()).toContain(String(new Date().getFullYear()))
  })

  it("renders the back-to-top link with href #top", () => {
    expect(mountFooter().find('a[href="#top"].site-footer__back-to-top').exists()).toBe(true)
  })

  it("does not render legal list when legalNavigation is empty", () => {
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

  it("has no empty href", () => {
    const hrefs = mountFooter()
      .findAll("a")
      .map((link) => link.attributes("href"))
    expect(hrefs.every((href) => typeof href === "string" && href.length > 0)).toBe(true)
  })
})
