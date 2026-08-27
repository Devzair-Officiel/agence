import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ValoriserHero from "~/components/expertise/valoriser/ValoriserHero.vue"

const props = {
  eyebrow: "Expertise · Valoriser",
  title:
    "Valoriser : donner à votre activité l'image et les mots qu'elle mérite.",
  introduction:
    "Nous produisons ce qui manque : les photographies, les textes et la structuration de votre offre.",
}

describe("ValoriserHero", () => {
  it("rend un H1 unique portant le titre verbatim", () => {
    const wrapper = mount(ValoriserHero, { props })
    const h1 = wrapper.findAll("h1")
    expect(h1).toHaveLength(1)
    expect(h1[0]!.text()).toBe(props.title)
  })

  it("expose un id stable sur le H1 pour aria-labelledby", () => {
    const wrapper = mount(ValoriserHero, { props })
    const h1 = wrapper.get("h1")
    expect(h1.attributes("id")).toBe("expertise-page-hero-title")
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(
      "expertise-page-hero-title",
    )
  })

  it("rend l'eyebrow puis l'introduction", () => {
    const wrapper = mount(ValoriserHero, { props })
    const text = wrapper.text()
    expect(text).toContain(props.eyebrow)
    expect(text).toContain(props.introduction)
  })

  it("n'introduit aucun H2 ni H3 dans le hero", () => {
    const wrapper = mount(ValoriserHero, { props })
    expect(wrapper.findAll("h2")).toHaveLength(0)
    expect(wrapper.findAll("h3")).toHaveLength(0)
  })

  it("intègre le visuel éditorial décoratif marqué aria-hidden", () => {
    const wrapper = mount(ValoriserHero, { props })
    const visual = wrapper.find(".valoriser-visual")
    expect(visual.exists()).toBe(true)
    expect(visual.attributes("aria-hidden")).toBe("true")
  })

  it("expose les trois capacités validées (Photographie / Contenus / Structuration)", () => {
    const wrapper = mount(ValoriserHero, { props })
    const capabilities = wrapper.get(".valoriser-hero__capabilities").text()
    for (const label of ["Photographie", "Contenus", "Structuration"]) {
      expect(capabilities).toContain(label)
    }
  })

  it("expose l'indicateur pôle 03 / 05", () => {
    const wrapper = mount(ValoriserHero, { props })
    expect(wrapper.text()).toContain("Pôle 03 / 05")
  })
})
