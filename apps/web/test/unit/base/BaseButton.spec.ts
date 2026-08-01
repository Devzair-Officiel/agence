import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import BaseButton from "~/components/base/BaseButton.vue"

describe("BaseButton", () => {
  it("renders a <button> with variant primary by default", () => {
    const wrapper = mount(BaseButton, { slots: { default: "Envoyer" } })
    const button = wrapper.get("button")
    expect(button.text()).toContain("Envoyer")
    expect(button.attributes("type")).toBe("button")
    expect(button.attributes("data-variant")).toBe("primary")
  })

  it("renders as secondary when variant='secondary'", () => {
    const wrapper = mount(BaseButton, {
      props: { variant: "secondary" },
      slots: { default: "Annuler" },
    })
    expect(wrapper.get("button").attributes("data-variant")).toBe("secondary")
  })

  it("propagates the disabled attribute", () => {
    const wrapper = mount(BaseButton, {
      props: { disabled: true },
      slots: { default: "Envoyer" },
    })
    expect(wrapper.get("button").attributes("disabled")).toBeDefined()
  })

  it("sets aria-busy and disables the button when loading", () => {
    const wrapper = mount(BaseButton, {
      props: { loading: true },
      slots: { default: "Envoyer" },
    })
    const button = wrapper.get("button")
    expect(button.attributes("aria-busy")).toBe("true")
    expect(button.attributes("disabled")).toBeDefined()
    expect(wrapper.find(".base-button__spinner").exists()).toBe(true)
  })

  it("renders a NuxtLink when to is provided", () => {
    const wrapper = mount(BaseButton, {
      props: { to: "/contact" },
      slots: { default: "Contact" },
    })
    const link = wrapper.get('[data-nuxt-link="true"]')
    expect(link.attributes("href")).toBe("/contact")
    expect(link.element.tagName).toBe("A")
  })

  it("renders an <a> with rel and target for external href", () => {
    const wrapper = mount(BaseButton, {
      props: { href: "https://example.com" },
      slots: { default: "Externe" },
    })
    const anchor = wrapper.find('a:not([data-nuxt-link])')
    expect(anchor.exists()).toBe(true)
    expect(anchor.attributes("href")).toBe("https://example.com")
    expect(anchor.attributes("rel")).toBe("noopener noreferrer")
    expect(anchor.attributes("target")).toBe("_blank")
  })

  it("falls back to a <button> when disabled even if 'to' is provided", () => {
    const wrapper = mount(BaseButton, {
      props: { to: "/contact", disabled: true },
      slots: { default: "Contact" },
    })
    expect(wrapper.find("button").exists()).toBe(true)
    expect(wrapper.find('[data-nuxt-link="true"]').exists()).toBe(false)
  })
})
