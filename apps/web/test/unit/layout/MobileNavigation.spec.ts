import { describe, expect, it, beforeEach } from "vitest"
import { mount, flushPromises } from "@vue/test-utils"
import MobileNavigation from "~/components/layout/MobileNavigation.vue"
import BaseButton from "~/components/base/BaseButton.vue"
import { useMobileNavigation } from "~/composables/useMobileNavigation"

describe("MobileNavigation", () => {
  beforeEach(() => {
    document.body.innerHTML = ""
    document.documentElement.className = ""
    const { isOpen, close } = useMobileNavigation()
    if (isOpen.value) close()
  })

  it("does not render its dialog when the menu is closed", () => {
    const wrapper = mount(MobileNavigation, {
      global: { components: { BaseButton } },
    })
    expect(wrapper.find('[role="dialog"]').exists()).toBe(false)
  })

  it("renders as a modal dialog with the expected ARIA attributes when open", async () => {
    const { open } = useMobileNavigation()
    const wrapper = mount(MobileNavigation, {
      attachTo: document.body,
      global: { components: { BaseButton } },
    })
    open()
    await flushPromises()

    const dialog = wrapper.find('[role="dialog"]')
    expect(dialog.exists()).toBe(true)
    expect(dialog.attributes("aria-modal")).toBe("true")
    expect(dialog.attributes("aria-label")).toBe("Menu principal")
    expect(dialog.attributes("id")).toBe("mobile-navigation")
  })

  it("closes when Escape is pressed", async () => {
    const { open, isOpen } = useMobileNavigation()
    mount(MobileNavigation, {
      global: { components: { BaseButton } },
    })
    open()
    await flushPromises()
    expect(isOpen.value).toBe(true)

    document.dispatchEvent(new KeyboardEvent("keydown", { key: "Escape" }))
    await flushPromises()
    expect(isOpen.value).toBe(false)
  })

  it("closes when a navigation link is clicked", async () => {
    const { open, isOpen } = useMobileNavigation()
    const wrapper = mount(MobileNavigation, {
      attachTo: document.body,
      global: { components: { BaseButton } },
    })
    open()
    await flushPromises()

    const firstLink = wrapper.find(".mobile-navigation__link")
    expect(firstLink.exists()).toBe(true)
    await firstLink.trigger("click")
    await flushPromises()
    expect(isOpen.value).toBe(false)
  })
})
