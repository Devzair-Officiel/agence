import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import SiteFooter from "~/components/layout/SiteFooter.vue"
import BaseContainer from "~/components/base/BaseContainer.vue"
import {
  footerNavigation,
  legalNavigation,
} from "~/config/navigation"

describe("SiteFooter", () => {
  it("renders every link declared in the navigation config", () => {
    const wrapper = mount(SiteFooter, {
      global: {
        components: { BaseContainer },
      },
    })

    for (const group of footerNavigation) {
      for (const item of group.items) {
        const link = wrapper.find(`a[href="${item.to}"]`)
        expect(link.exists(), `link to ${item.to} should be rendered`).toBe(true)
        expect(link.text()).toBe(item.label)
      }
    }

    for (const item of legalNavigation) {
      const link = wrapper.find(`a[href="${item.to}"]`)
      expect(link.exists(), `legal link to ${item.to} should be rendered`).toBe(true)
    }
  })

  it("does not render fake contact info when contact fields are null", () => {
    const wrapper = mount(SiteFooter, {
      global: {
        components: { BaseContainer },
      },
    })
    // No mailto: or tel: links should appear when contact info is not validated
    expect(wrapper.find('a[href^="mailto:"]').exists()).toBe(false)
    expect(wrapper.find('a[href^="tel:"]').exists()).toBe(false)
  })

  it("uses a <footer> semantic element", () => {
    const wrapper = mount(SiteFooter, {
      global: {
        components: { BaseContainer },
      },
    })
    expect(wrapper.element.tagName).toBe("FOOTER")
  })
})
