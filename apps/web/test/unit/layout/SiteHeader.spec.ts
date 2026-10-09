import { afterEach, describe, expect, it, beforeEach, vi } from "vitest"
import { mount } from "@vue/test-utils"
import SiteHeader from "~/components/layout/SiteHeader.vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import BaseButton from "~/components/base/BaseButton.vue"
import { useMobileNavigation } from "~/composables/useMobileNavigation"

describe("SiteHeader", () => {
  beforeEach(() => {
    document.documentElement.className = ""
    const { isOpen, close } = useMobileNavigation()
    if (isOpen.value) close()
  })

  it("exposes the mobile menu button with aria-expanded=false when closed", () => {
    const wrapper = mount(SiteHeader, {
      global: { components: { BaseContainer, BaseButton } },
    })
    const button = wrapper.get(".site-header__menu-button")
    expect(button.attributes("aria-expanded")).toBe("false")
    expect(button.attributes("aria-controls")).toBe("mobile-navigation")
    expect(button.attributes("aria-label")).toBe("Ouvrir le menu")
  })

  it("updates aria-expanded to true after clicking the menu button", async () => {
    const wrapper = mount(SiteHeader, {
      global: { components: { BaseContainer, BaseButton } },
    })
    const button = wrapper.get(".site-header__menu-button")
    await button.trigger("click")
    expect(button.attributes("aria-expanded")).toBe("true")
  })

  it("uses a semantic <header> with a <nav aria-label>", () => {
    const wrapper = mount(SiteHeader, {
      global: { components: { BaseContainer, BaseButton } },
    })
    expect(wrapper.element.tagName).toBe("HEADER")
    const nav = wrapper.find("nav")
    expect(nav.attributes("aria-label")).toBe("Navigation principale")
  })

  describe("hide on scroll", () => {
    let frames: FrameRequestCallback[] = []

    const scrollTo = (y: number) => {
      Object.defineProperty(window, "scrollY", { configurable: true, value: y })
      window.dispatchEvent(new Event("scroll"))
      const pending = frames
      frames = []
      pending.forEach((callback) => callback(0))
    }

    beforeEach(() => {
      frames = []
      vi.spyOn(window, "requestAnimationFrame").mockImplementation((callback) => {
        frames.push(callback)
        return frames.length
      })
      Object.defineProperty(document.documentElement, "scrollHeight", { configurable: true, value: 5000 })
    })

    afterEach(() => {
      vi.restoreAllMocks()
      Object.defineProperty(window, "scrollY", { configurable: true, value: 0 })
    })

    const mountHeader = () =>
      mount(SiteHeader, {
        attachTo: document.body,
        global: { components: { BaseContainer, BaseButton } },
      })

    it("hides when scrolling down and reveals when scrolling up", async () => {
      const wrapper = mountHeader()
      expect(wrapper.classes()).not.toContain("site-header--hidden")

      scrollTo(300)
      scrollTo(600)
      await wrapper.vm.$nextTick()
      expect(wrapper.classes()).toContain("site-header--hidden")

      scrollTo(550)
      await wrapper.vm.$nextTick()
      expect(wrapper.classes()).not.toContain("site-header--hidden")
      wrapper.unmount()
    })

    it("stays visible while the mobile menu is open", async () => {
      const wrapper = mountHeader()
      await wrapper.get(".site-header__menu-button").trigger("click")

      scrollTo(300)
      scrollTo(600)
      await wrapper.vm.$nextTick()
      expect(wrapper.classes()).not.toContain("site-header--hidden")
      wrapper.unmount()
    })
  })
})
