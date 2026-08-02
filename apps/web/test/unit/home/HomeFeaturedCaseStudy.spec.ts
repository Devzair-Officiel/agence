import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import HomeFeaturedCaseStudy from "~/components/home/HomeFeaturedCaseStudy.vue"

describe("HomeFeaturedCaseStudy", () => {
  it("exposes the #realisations anchor on the section root", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    const section = wrapper.get("section")
    expect(section.attributes("id")).toBe("realisations")
  })

  it("renders a single H2 with the exact editorial title", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    const h2s = wrapper.findAll("h2")
    expect(h2s).toHaveLength(1)
    expect(h2s[0]!.text()).toBe(
      "Des solutions concrètes, pas seulement de belles interfaces.",
    )
  })

  it("publishes the honest-state placeholder title verbatim", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    const h3s = wrapper.findAll("h3")
    expect(h3s).toHaveLength(1)
    expect(h3s[0]!.text()).toBe(
      "Nos réalisations détaillées seront bientôt disponibles.",
    )
  })

  it("carries the « Études de cas en préparation » eyebrow", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    expect(wrapper.text()).toContain("Études de cas en préparation")
  })

  it("has no interactive control (no button, no link, no tabindex)", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    expect(wrapper.findAll("button")).toHaveLength(0)
    expect(wrapper.findAll("a")).toHaveLength(0)
    expect(wrapper.findAll("[tabindex]")).toHaveLength(0)
  })

  it("carries no fictional client name or numeric proof", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    const text = wrapper.text()
    expect(text).not.toMatch(/\d+\s?%/)
    expect(text).not.toMatch(/témoignage/i)
    expect(text).not.toMatch(/étude de cas .+\balias/i)
  })

  it("does not render an image placeholder or skeleton loader", () => {
    const wrapper = mount(HomeFeaturedCaseStudy)
    expect(wrapper.findAll("img")).toHaveLength(0)
    expect(wrapper.findAll(".skeleton")).toHaveLength(0)
  })
})
