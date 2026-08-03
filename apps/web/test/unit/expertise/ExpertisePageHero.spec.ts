import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ExpertisePageHero from "~/components/expertise/ExpertisePageHero.vue"

describe("ExpertisePageHero", () => {
  const props = {
    eyebrow: "Expertise · Concevoir",
    title: "Concevoir : identité, expérience et architecture",
    introduction:
      "Avant tout développement, nous posons les fondations visuelles et fonctionnelles de votre projet.",
  }

  it("rend un H1 unique portant le titre verbatim", () => {
    const wrapper = mount(ExpertisePageHero, { props })
    const h1 = wrapper.findAll("h1")
    expect(h1).toHaveLength(1)
    expect(h1[0]!.text()).toBe(props.title)
  })

  it("expose un id stable sur le H1 pour aria-labelledby", () => {
    const wrapper = mount(ExpertisePageHero, { props })
    const h1 = wrapper.get("h1")
    expect(h1.attributes("id")).toBe("expertise-page-hero-title")
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(
      "expertise-page-hero-title",
    )
  })

  it("rend l'eyebrow avant le H1", () => {
    const wrapper = mount(ExpertisePageHero, { props })
    expect(wrapper.text()).toContain(props.eyebrow)
  })

  it("rend l'introduction verbatim", () => {
    const wrapper = mount(ExpertisePageHero, { props })
    expect(wrapper.get(".expertise-page-hero__introduction").text()).toBe(
      props.introduction,
    )
  })

  it("n'introduit aucun H2 ni H3 secondaire", () => {
    const wrapper = mount(ExpertisePageHero, { props })
    expect(wrapper.findAll("h2")).toHaveLength(0)
    expect(wrapper.findAll("h3")).toHaveLength(0)
  })
})
