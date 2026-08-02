import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import HomeTrust from "~/components/home/HomeTrust.vue"
import { trustPromises } from "~/config/trust-promises"

describe("HomeTrust", () => {
  it("renders a single H2 with the exact editorial title", () => {
    const wrapper = mount(HomeTrust)
    const h2s = wrapper.findAll("h2")
    expect(h2s).toHaveLength(1)
    expect(h2s[0]!.text()).toBe(
      "Ce qui fait la différence, concrètement.",
    )
  })

  it("uses an unordered list of exactly five promises", () => {
    const wrapper = mount(HomeTrust)
    const list = wrapper.get(".home-trust__list")
    expect(list.element.tagName).toBe("UL")
    expect(list.findAll("li")).toHaveLength(5)
  })

  it("takes labels and order from trustPromises (no duplication)", () => {
    const wrapper = mount(HomeTrust)
    const titles = wrapper.findAll(".home-trust__item-title")
    const expected = [...trustPromises]
      .sort((a, b) => a.order - b.order)
      .map((p) => p.label)
    expect(titles.map((t) => t.text())).toEqual(expected)
  })

  it("renders each promise with a non-empty H3 and description", () => {
    const wrapper = mount(HomeTrust)
    const titles = wrapper.findAll(".home-trust__item-title")
    const descriptions = wrapper.findAll(".home-trust__item-description")
    expect(titles).toHaveLength(5)
    expect(descriptions).toHaveLength(5)
    for (const title of titles) {
      expect(title.element.tagName).toBe("H3")
    }
    for (const description of descriptions) {
      expect(description.text().trim().length).toBeGreaterThan(20)
    }
  })

  it("has no interactive control (cards are not links or buttons)", () => {
    const wrapper = mount(HomeTrust)
    expect(wrapper.findAll("button")).toHaveLength(0)
    expect(wrapper.findAll("a")).toHaveLength(0)
    expect(wrapper.findAll("[tabindex]")).toHaveLength(0)
  })

  it("avoids forbidden absolute phrasing (« meilleur », « unique »)", () => {
    const wrapper = mount(HomeTrust)
    const text = wrapper.text()
    expect(text).not.toMatch(/meilleur/i)
    expect(text).not.toMatch(/\bunique\b/i)
  })
})
