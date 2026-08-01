import { describe, expect, it, beforeEach } from "vitest"
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
})
