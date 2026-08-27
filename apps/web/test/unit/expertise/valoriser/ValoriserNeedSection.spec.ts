import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ValoriserNeedSection from "~/components/expertise/valoriser/ValoriserNeedSection.vue"

const props = {
  needDescription:
    "Aux entreprises dont l'activité gagne à être mieux montrée : photos, textes, structuration de l'offre.",
}

describe("ValoriserNeedSection", () => {
  it("porte le H2 validé « Quand votre savoir-faire mérite d'être mieux montré. »", () => {
    const wrapper = mount(ValoriserNeedSection, { props })
    expect(wrapper.get("h2").text()).toBe(
      "Quand votre savoir-faire mérite d'être mieux montré.",
    )
  })

  it("expose l'eyebrow validé « À qui cela s'adresse »", () => {
    const wrapper = mount(ValoriserNeedSection, { props })
    expect(wrapper.text()).toContain("À qui cela s'adresse")
  })

  it("rend needDescription verbatim en tête de section", () => {
    const wrapper = mount(ValoriserNeedSection, { props })
    expect(wrapper.text()).toContain(props.needDescription)
  })

  it("rend une vraie <ol role=list> avec trois situations", () => {
    const wrapper = mount(ValoriserNeedSection, { props })
    const list = wrapper.get("ol")
    expect(list.attributes("role")).toBe("list")
    expect(list.findAll("li")).toHaveLength(3)
  })

  it("rend les trois situations validées (Montrer / Expliquer / Structurer)", () => {
    const wrapper = mount(ValoriserNeedSection, { props })
    const verbs = wrapper.findAll(".valoriser-need__panel-verb").map((n) => n.text())
    expect(verbs).toEqual(["Montrer", "Expliquer", "Structurer"])
  })

  it("associe chaque situation à un format éditorial cohérent", () => {
    const wrapper = mount(ValoriserNeedSection, { props })
    const formats = wrapper
      .findAll(".valoriser-need__panel-format-value")
      .map((n) => n.text())
    expect(formats).toEqual([
      "Photographie professionnelle",
      "Contenus rédactionnels",
      "Structuration de l'offre",
    ])
  })

  it("relie la section au H2 par aria-labelledby", () => {
    const wrapper = mount(ValoriserNeedSection, { props })
    const h2Id = wrapper.get("h2").attributes("id")
    expect(h2Id).toBeTruthy()
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(h2Id)
  })
})
