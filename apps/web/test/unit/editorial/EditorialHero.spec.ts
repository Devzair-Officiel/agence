import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import EditorialHero from "~/components/editorial/EditorialHero.vue"

describe("EditorialHero", () => {
  const props = {
    eyebrow: "L'agence",
    title: "Une agence digitale à taille humaine.",
    lead: "Nous accompagnons les entreprises dans leur globalité.",
  }

  it("renders exactly one H1 with the provided title", () => {
    const wrapper = mount(EditorialHero, { props })
    const headings = wrapper.findAll("h1")
    expect(headings).toHaveLength(1)
    expect(headings[0]!.text()).toBe(props.title)
  })

  it("renders the eyebrow as a paragraph before the H1 (not as a heading)", () => {
    const wrapper = mount(EditorialHero, { props })
    expect(wrapper.findAll("h2")).toHaveLength(0)
    const eyebrow = wrapper.get(".editorial-hero__eyebrow")
    expect(eyebrow.text()).toBe(props.eyebrow)
    expect(eyebrow.element.tagName).toBe("P")
  })

  it("renders the lead paragraph verbatim", () => {
    const wrapper = mount(EditorialHero, { props })
    expect(wrapper.get(".editorial-hero__lead").text()).toBe(props.lead)
  })

  it("uses a <section> with aria-labelledby pointing at the H1", () => {
    const wrapper = mount(EditorialHero, { props })
    const section = wrapper.get("section")
    const titleId = wrapper.get("h1").attributes("id")
    expect(section.attributes("aria-labelledby")).toBe(titleId)
    expect(titleId).toBeTruthy()
  })

  it("renders an actions slot when provided", () => {
    const wrapper = mount(EditorialHero, {
      props,
      slots: {
        actions: '<a href="/#contact" data-test="cta">CTA</a>',
      },
    })
    expect(wrapper.find('[data-test="cta"]').exists()).toBe(true)
  })

  it("omits the actions container when no slot is provided", () => {
    const wrapper = mount(EditorialHero, { props })
    expect(wrapper.find(".editorial-hero__actions").exists()).toBe(false)
  })
})
