import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import HomeProcess from "~/components/home/HomeProcess.vue"
import { projectProcess } from "~/config/project-process"

describe("HomeProcess", () => {
  it("renders a single H2 with the exact editorial title", () => {
    const wrapper = mount(HomeProcess)
    const h2s = wrapper.findAll("h2")
    expect(h2s).toHaveLength(1)
    expect(h2s[0]!.text()).toBe(
      "Un cadre clair, du premier échange au suivi.",
    )
  })

  it("uses a real ordered list of exactly six steps", () => {
    const wrapper = mount(HomeProcess)
    const list = wrapper.get(".home-process__list")
    expect(list.element.tagName).toBe("OL")
    expect(list.findAll("li")).toHaveLength(6)
  })

  it("takes labels and order from projectProcess (no duplication)", () => {
    const wrapper = mount(HomeProcess)
    const titles = wrapper.findAll(".home-process__step-title")
    const expected = [...projectProcess]
      .sort((a, b) => a.order - b.order)
      .map((s) => s.label)
    expect(titles.map((t) => t.text())).toEqual(expected)
  })

  it("closes with the « Évolution » step (order 6)", () => {
    const wrapper = mount(HomeProcess)
    const titles = wrapper.findAll(".home-process__step-title")
    expect(titles[titles.length - 1]!.text()).toBe("Évolution")
  })

  it("uses padded numbering 01..06 marked aria-hidden", () => {
    const wrapper = mount(HomeProcess)
    const indexes = wrapper.findAll(".home-process__step-index")
    expect(indexes.map((i) => i.text().trim())).toEqual([
      "01",
      "02",
      "03",
      "04",
      "05",
      "06",
    ])
    for (const index of indexes) {
      expect(index.attributes("aria-hidden")).toBe("true")
    }
  })

  it("has no interactive control (no accordion, no tab, no button, no link)", () => {
    const wrapper = mount(HomeProcess)
    expect(wrapper.findAll("button")).toHaveLength(0)
    expect(wrapper.findAll("a")).toHaveLength(0)
    expect(wrapper.findAll("[tabindex]")).toHaveLength(0)
    expect(wrapper.findAll("[role='tab']")).toHaveLength(0)
  })
})
