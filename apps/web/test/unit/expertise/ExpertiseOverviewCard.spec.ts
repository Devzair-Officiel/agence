import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ExpertiseOverviewCard from "~/components/expertise/ExpertiseOverviewCard.vue"
import { expertisePillars } from "~/config/expertise-pillars"

describe("ExpertiseOverviewCard", () => {
  const firstPillar = expertisePillars[0]!

  it("renders as an <article> with the pillar variant on data-variant", () => {
    const wrapper = mount(ExpertiseOverviewCard, {
      props: { pillar: firstPillar },
    })
    expect(wrapper.element.tagName).toBe("ARTICLE")
    expect(wrapper.attributes("data-variant")).toBe(firstPillar.variant)
  })

  it("renders exactly one H3 with the pillar label (never a link in Phase 7A)", () => {
    const wrapper = mount(ExpertiseOverviewCard, {
      props: { pillar: firstPillar },
    })
    const headings = wrapper.findAll("h3")
    expect(headings).toHaveLength(1)
    expect(headings[0]!.text()).toBe(firstPillar.label)
    expect(wrapper.findAll("a")).toHaveLength(0)
  })

  it("renders the long description verbatim", () => {
    const wrapper = mount(ExpertiseOverviewCard, {
      props: { pillar: firstPillar },
    })
    expect(wrapper.get(".expertise-overview-card__description").text()).toBe(
      firstPillar.longDescription,
    )
  })

  it("renders the three services as a semantic list", () => {
    const wrapper = mount(ExpertiseOverviewCard, {
      props: { pillar: firstPillar },
    })
    const list = wrapper.get(".expertise-overview-card__services")
    expect(list.element.tagName).toBe("UL")
    const items = list.findAll("li")
    expect(items).toHaveLength(3)
    for (let i = 0; i < items.length; i += 1) {
      expect(items[i]!.text()).toBe(firstPillar.services[i])
    }
  })

  it("displays a zero-padded order badge marked aria-hidden", () => {
    const secondPillar = expertisePillars[1]!
    const wrapper = mount(ExpertiseOverviewCard, {
      props: { pillar: secondPillar },
    })
    const order = wrapper.get(".expertise-overview-card__order")
    expect(order.text()).toBe("02")
    expect(order.attributes("aria-hidden")).toBe("true")
  })

  it("does not carry any focusable element (cards are non-interactive in 7A)", () => {
    const wrapper = mount(ExpertiseOverviewCard, {
      props: { pillar: firstPillar },
    })
    expect(wrapper.findAll("a, button, [tabindex]")).toHaveLength(0)
  })
})
