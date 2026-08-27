import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import FaireEvoluerHero from "~/components/expertise/faire-evoluer/FaireEvoluerHero.vue"

const props = {
  eyebrow: "Expertise · Faire évoluer",
  title: "Faire évoluer : maintenance, mesure et évolutions",
  introduction:
    "Nous assurons le suivi dans le temps de votre site : mesures, mises à jour, sécurité, évolutions fonctionnelles. Un site vivant reste utile ; un site figé se dégrade en quelques mois.",
}

describe("FaireEvoluerHero", () => {
  it("rend un H1 unique portant le titre verbatim", () => {
    const wrapper = mount(FaireEvoluerHero, { props })
    const h1 = wrapper.findAll("h1")
    expect(h1).toHaveLength(1)
    expect(h1[0]!.text()).toBe(props.title)
  })

  it("expose un id stable sur le H1 pour aria-labelledby", () => {
    const wrapper = mount(FaireEvoluerHero, { props })
    const h1 = wrapper.get("h1")
    expect(h1.attributes("id")).toBe("expertise-page-hero-title")
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(
      "expertise-page-hero-title",
    )
  })

  it("rend l'eyebrow puis l'introduction verbatim", () => {
    const wrapper = mount(FaireEvoluerHero, { props })
    const text = wrapper.text()
    expect(text).toContain(props.eyebrow)
    expect(text).toContain(props.introduction)
  })

  it("n'introduit ni H2 ni H3 dans le hero", () => {
    const wrapper = mount(FaireEvoluerHero, { props })
    expect(wrapper.findAll("h2")).toHaveLength(0)
    expect(wrapper.findAll("h3")).toHaveLength(0)
  })

  it("intègre le visuel cyclique décoratif marqué aria-hidden", () => {
    const wrapper = mount(FaireEvoluerHero, { props })
    const visual = wrapper.find(".faire-evoluer-visual")
    expect(visual.exists()).toBe(true)
    expect(visual.attributes("aria-hidden")).toBe("true")
  })

  it("expose les trois capacités validées", () => {
    const wrapper = mount(FaireEvoluerHero, { props })
    const capabilities = wrapper.get(".faire-evoluer-hero__capabilities").text()
    for (const label of ["Maintenance", "sécurité", "Mesure", "amélioration", "Évolutions"]) {
      expect(capabilities).toContain(label)
    }
  })

  it("expose l'indicateur pôle 05 / 05", () => {
    const wrapper = mount(FaireEvoluerHero, { props })
    expect(wrapper.text()).toContain("Pôle 05 / 05")
  })
})
