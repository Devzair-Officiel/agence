import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ConcevoirHero from "~/components/expertise/concevoir/ConcevoirHero.vue"

const props = {
  eyebrow: "Expertise · Concevoir",
  title: "Concevoir : identité, expérience et architecture",
  introduction:
    "Avant tout développement, nous posons les fondations visuelles et fonctionnelles de votre projet.",
}

describe("ConcevoirHero", () => {
  it("rend un H1 unique portant le titre verbatim", () => {
    const wrapper = mount(ConcevoirHero, { props })
    const h1 = wrapper.findAll("h1")
    expect(h1).toHaveLength(1)
    expect(h1[0]!.text()).toBe(props.title)
  })

  it("expose un id stable sur le H1 pour aria-labelledby", () => {
    const wrapper = mount(ConcevoirHero, { props })
    const h1 = wrapper.get("h1")
    expect(h1.attributes("id")).toBe("expertise-page-hero-title")
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(
      "expertise-page-hero-title",
    )
  })

  it("rend l'eyebrow puis l'introduction", () => {
    const wrapper = mount(ConcevoirHero, { props })
    const text = wrapper.text()
    expect(text).toContain(props.eyebrow)
    expect(text).toContain(props.introduction)
  })

  it("n'introduit aucun H2 ni H3 dans le hero", () => {
    const wrapper = mount(ConcevoirHero, { props })
    expect(wrapper.findAll("h2")).toHaveLength(0)
    expect(wrapper.findAll("h3")).toHaveLength(0)
  })

  it("intègre le visuel blueprint décoratif marqué aria-hidden", () => {
    const wrapper = mount(ConcevoirHero, { props })
    const blueprint = wrapper.find(".concevoir-blueprint")
    expect(blueprint.exists()).toBe(true)
    expect(blueprint.attributes("aria-hidden")).toBe("true")
  })

  it("liste les cinq étapes validées de la démarche dans le méta hero", () => {
    const wrapper = mount(ConcevoirHero, { props })
    const meta = wrapper.get(".concevoir-hero__meta").text()
    for (const step of ["Objectifs", "Structure", "Parcours", "Interface", "Système"]) {
      expect(meta).toContain(step)
    }
  })
})
