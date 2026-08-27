import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import VisibiliteHero from "~/components/expertise/visibilite/VisibiliteHero.vue"

const props = {
  eyebrow: "Expertise · Visibilité",
  title: "Développer la visibilité : SEO, local et éditorial",
  introduction:
    "Nous travaillons la visibilité durable de votre entreprise : référencement naturel, présence locale et stratégie éditoriale. La visibilité ne se décrète pas — elle se construit, page par page, requête par requête.",
}

describe("VisibiliteHero", () => {
  it("rend un H1 unique portant le titre verbatim", () => {
    const wrapper = mount(VisibiliteHero, { props })
    const h1 = wrapper.findAll("h1")
    expect(h1).toHaveLength(1)
    expect(h1[0]!.text()).toBe(props.title)
  })

  it("expose un id stable sur le H1 pour aria-labelledby", () => {
    const wrapper = mount(VisibiliteHero, { props })
    const h1 = wrapper.get("h1")
    expect(h1.attributes("id")).toBe("expertise-page-hero-title")
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(
      "expertise-page-hero-title",
    )
  })

  it("rend l'eyebrow puis l'introduction", () => {
    const wrapper = mount(VisibiliteHero, { props })
    const text = wrapper.text()
    expect(text).toContain(props.eyebrow)
    expect(text).toContain(props.introduction)
  })

  it("n'introduit aucun H2 ni H3 dans le hero", () => {
    const wrapper = mount(VisibiliteHero, { props })
    expect(wrapper.findAll("h2")).toHaveLength(0)
    expect(wrapper.findAll("h3")).toHaveLength(0)
  })

  it("intègre le visuel territoire décoratif marqué aria-hidden", () => {
    const wrapper = mount(VisibiliteHero, { props })
    const visual = wrapper.find(".visibilite-visual")
    expect(visual.exists()).toBe(true)
    expect(visual.attributes("aria-hidden")).toBe("true")
  })

  it("expose les trois capacités validées (SEO / Visibilité locale / Stratégie éditoriale)", () => {
    const wrapper = mount(VisibiliteHero, { props })
    const capabilities = wrapper.get(".visibilite-hero__capabilities").text()
    for (const label of ["SEO", "Visibilité locale", "Stratégie éditoriale"]) {
      expect(capabilities).toContain(label)
    }
  })

  it("expose l'indicateur pôle 04 / 05", () => {
    const wrapper = mount(VisibiliteHero, { props })
    expect(wrapper.text()).toContain("Pôle 04 / 05")
  })
})
