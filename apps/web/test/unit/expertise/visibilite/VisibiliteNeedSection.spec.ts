import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import VisibiliteNeedSection from "~/components/expertise/visibilite/VisibiliteNeedSection.vue"

const props = {
  needDescription:
    "Aux entreprises qui ont un site mais peu de trafic qualifié, à celles qui veulent apparaître sur les recherches locales de leur zone, ou qui souhaitent structurer une production de contenus dans la durée sans dépendre exclusivement de la publicité payante.",
}

describe("VisibiliteNeedSection", () => {
  it("porte le H2 validé « Trois territoires de visibilité. »", () => {
    const wrapper = mount(VisibiliteNeedSection, { props })
    expect(wrapper.get("h2").text()).toBe("Trois territoires de visibilité.")
  })

  it("expose l'eyebrow validé « À qui cela s'adresse »", () => {
    const wrapper = mount(VisibiliteNeedSection, { props })
    expect(wrapper.text()).toContain("À qui cela s'adresse")
  })

  it("rend needDescription verbatim en tête de section", () => {
    const wrapper = mount(VisibiliteNeedSection, { props })
    expect(wrapper.text()).toContain(props.needDescription)
  })

  it("rend une vraie <ol role=list> avec trois zones", () => {
    const wrapper = mount(VisibiliteNeedSection, { props })
    const list = wrapper.get("ol")
    expect(list.attributes("role")).toBe("list")
    expect(list.findAll("li")).toHaveLength(3)
  })

  it("rend les trois zones validées (Être trouvé / Être présent localement / Rester visible dans la durée)", () => {
    const wrapper = mount(VisibiliteNeedSection, { props })
    const titles = wrapper
      .findAll(".visibilite-need__zone-title")
      .map((n) => n.text())
    expect(titles).toEqual([
      "Être trouvé",
      "Être présent localement",
      "Rester visible dans la durée",
    ])
  })

  it("étiquette chaque zone Zone 01/02/03", () => {
    const wrapper = mount(VisibiliteNeedSection, { props })
    const rings = wrapper
      .findAll(".visibilite-need__zone-ring")
      .map((n) => n.text())
    expect(rings).toEqual(["Zone 01", "Zone 02", "Zone 03"])
  })

  it("associe chaque zone à sa portée narrative (SEO / Local / Éditorial)", () => {
    const wrapper = mount(VisibiliteNeedSection, { props })
    const scopes = wrapper
      .findAll(".visibilite-need__zone-scope-value")
      .map((n) => n.text())
    expect(scopes).toEqual([
      "SEO · organique",
      "Local · NAP",
      "Éditorial · durée",
    ])
  })

  it("relie la section au H2 par aria-labelledby", () => {
    const wrapper = mount(VisibiliteNeedSection, { props })
    const h2Id = wrapper.get("h2").attributes("id")
    expect(h2Id).toBeTruthy()
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(h2Id)
  })
})
