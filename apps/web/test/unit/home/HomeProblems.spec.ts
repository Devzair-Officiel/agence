import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import HomeProblems from "~/components/home/HomeProblems.vue"

describe("HomeProblems", () => {
  it("renders a single H2 with the exact section title", () => {
    const wrapper = mount(HomeProblems)
    const headings = wrapper.findAll("h2")
    expect(headings).toHaveLength(1)
    expect(headings[0]!.text()).toBe(
      "Beaucoup d’entreprises méritent mieux que leur présence en ligne actuelle.",
    )
  })

  it("uses a real ordered list of exactly five items", () => {
    const wrapper = mount(HomeProblems)
    const list = wrapper.get(".home-problems__list")
    expect(list.element.tagName).toBe("OL")
    expect(list.findAll("li")).toHaveLength(5)
  })

  it("renders one H3 per problem, none empty", () => {
    const wrapper = mount(HomeProblems)
    const titles = wrapper.findAll(".home-problems__item-title")
    expect(titles).toHaveLength(5)
    for (const title of titles) {
      expect(title.element.tagName).toBe("H3")
      expect(title.text().trim().length).toBeGreaterThan(10)
    }
  })

  it("uses Space Mono numbering marked aria-hidden (padded to two digits)", () => {
    const wrapper = mount(HomeProblems)
    const numbers = wrapper.findAll(".home-problems__number")
    expect(numbers).toHaveLength(5)
    const labels = numbers.map((n) => n.text().trim())
    expect(labels).toEqual(["01", "02", "03", "04", "05"])
    for (const number of numbers) {
      expect(number.attributes("aria-hidden")).toBe("true")
    }
  })

  it("has no interactive control (no button, no link, no tabindex)", () => {
    const wrapper = mount(HomeProblems)
    expect(wrapper.findAll("button")).toHaveLength(0)
    expect(wrapper.findAll("a")).toHaveLength(0)
    expect(wrapper.findAll("[tabindex]")).toHaveLength(0)
  })

  it("does not carry any fictional numeric proof (no percentage, no client name)", () => {
    const wrapper = mount(HomeProblems)
    const text = wrapper.text()
    expect(text).not.toMatch(/\d+\s?%/)
    expect(text).not.toMatch(/témoignage/i)
  })
})
