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

  it("uses a <footer> semantic element", () => {
    expect(mountFooter().element.tagName).toBe("FOOTER")
  })

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

  it("renders the /ressources link in the Ressources column", () => {
    const wrapper = mountFooter()
    const resourcesGroup = footerNavigation.find((g) => g.title === "Ressources")
    expect(resourcesGroup).toBeDefined()
    const link = wrapper.find('a[href="/ressources"]')
    expect(link.exists()).toBe(true)
  })

  it("renders the nav landmark for footer navigation", () => {
    const wrapper = mountFooter()
    const nav = wrapper.find('nav[aria-label="Navigation du pied de page"]')
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

  it("renders legal links when legalNavigation is non-empty", () => {
    const wrapper = mountFooter()
    if (legalNavigation.length === 0) {
      expect(wrapper.find(".site-footer__legal-list").exists()).toBe(false)
    } else {
      for (const item of legalNavigation) {
        expect(wrapper.find(`a[href="${item.to}"]`).exists()).toBe(true)
      }
    }
  })

  it("renders the wordmark as aria-hidden and not focusable", () => {
    const wrapper = mountFooter()
    const wordmark = wrapper.find(".site-footer__wordmark")
    expect(wordmark.exists()).toBe(true)
    expect(wordmark.attributes("aria-hidden")).toBe("true")
    expect(wordmark.attributes("tabindex")).toBeUndefined()
  })

  it("renders dynamic copyright year", () => {
    const wrapper = mountFooter()
    const copyright = wrapper.find(".site-footer__copyright")
    expect(copyright.exists()).toBe(true)
    expect(copyright.text()).toContain(String(new Date().getFullYear()))
  })

  it("renders the back-to-top link with href #top", () => {
    const wrapper = mountFooter()
    const link = wrapper.find('a[href="#top"]')
    expect(link.exists()).toBe(true)
    expect(link.classes()).toContain("site-footer__back-to-top")
  })

  it("does not render mailto or tel links when contact fields are null", () => {
    const wrapper = mountFooter()
    expect(wrapper.find('a[href^="mailto:"]').exists()).toBe(false)
    expect(wrapper.find('a[href^="tel:"]').exists()).toBe(false)
  })

  it("has no link with a bare href=\"#\" (no dead anchors)", () => {
    const wrapper = mountFooter()
    const bareHash = wrapper.findAll('a[href="#"]')
    expect(bareHash.length).toBe(0)
  })

  it("renders the logo link to / with aria-label", () => {
    const wrapper = mountFooter()
    const logoLink = wrapper.find('a[href="/"]')
    expect(logoLink.exists()).toBe(true)
    expect(logoLink.attributes("aria-label")).toBeTruthy()
  })
})
