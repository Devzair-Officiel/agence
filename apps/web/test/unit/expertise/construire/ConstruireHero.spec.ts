import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ConstruireHero from "~/components/expertise/construire/ConstruireHero.vue"

const props = {
  eyebrow: "Expertise · Construire",
  title: "Construire : sites, e-commerce et applications",
  introduction:
    "Nous développons vos sites, plateformes e-commerce et applications métier sur des bases techniques modernes.",
}

describe("ConstruireHero", () => {
  it("rend un H1 unique portant le titre verbatim", () => {
    const wrapper = mount(ConstruireHero, { props })
    const h1 = wrapper.findAll("h1")
    expect(h1).toHaveLength(1)
    expect(h1[0]!.text()).toBe(props.title)
  })

  it("expose un id stable sur le H1 pour aria-labelledby", () => {
    const wrapper = mount(ConstruireHero, { props })
    const h1 = wrapper.get("h1")
    expect(h1.attributes("id")).toBe("expertise-page-hero-title")
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(
      "expertise-page-hero-title",
    )
  })

  it("rend l'eyebrow puis l'introduction", () => {
    const wrapper = mount(ConstruireHero, { props })
    const text = wrapper.text()
    expect(text).toContain(props.eyebrow)
    expect(text).toContain(props.introduction)
  })

  it("n'introduit aucun H2 ni H3 dans le hero", () => {
    const wrapper = mount(ConstruireHero, { props })
    expect(wrapper.findAll("h2")).toHaveLength(0)
    expect(wrapper.findAll("h3")).toHaveLength(0)
  })

  it("intègre le visuel système décoratif marqué aria-hidden", () => {
    const wrapper = mount(ConstruireHero, { props })
    const visual = wrapper.find(".construire-system")
    expect(visual.exists()).toBe(true)
    expect(visual.attributes("aria-hidden")).toBe("true")
  })

  it("expose les trois capacités validées (Site vitrine / E-commerce / Application métier)", () => {
    const wrapper = mount(ConstruireHero, { props })
    const capabilities = wrapper.get(".construire-hero__capabilities").text()
    for (const label of ["Site vitrine", "E-commerce", "Application métier"]) {
      expect(capabilities).toContain(label)
    }
  })

  it("expose l'indicateur pôle 02 / 05", () => {
    const wrapper = mount(ConstruireHero, { props })
    expect(wrapper.text()).toContain("Pôle 02 / 05")
  })
})
