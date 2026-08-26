import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ConcevoirProcessMap from "~/components/expertise/concevoir/ConcevoirProcessMap.vue"

describe("ConcevoirProcessMap", () => {
  it("rend un <ol> sémantique avec exactement cinq étapes", () => {
    const wrapper = mount(ConcevoirProcessMap)
    const list = wrapper.find("ol")
    expect(list.exists()).toBe(true)
    expect(list.findAll("li")).toHaveLength(5)
  })

  it("expose les cinq libellés d'étapes validés dans l'ordre exact", () => {
    const wrapper = mount(ConcevoirProcessMap)
    const titles = wrapper.findAll("h3").map((h) => h.text())
    expect(titles).toEqual([
      "Objectifs",
      "Structure",
      "Parcours",
      "Interface",
      "Système",
    ])
  })

  it("numérote chaque étape 01…05", () => {
    const wrapper = mount(ConcevoirProcessMap)
    const indexes = wrapper
      .findAll(".concevoir-process__marker-index")
      .map((n) => n.text())
    expect(indexes).toEqual(["01", "02", "03", "04", "05"])
  })

  it("porte le H2 « Du flou au système. »", () => {
    const wrapper = mount(ConcevoirProcessMap)
    const h2 = wrapper.get("h2")
    expect(h2.text()).toBe("Du flou au système.")
  })

  it("lie la section au H2 via aria-labelledby", () => {
    const wrapper = mount(ConcevoirProcessMap)
    const section = wrapper.get("section")
    const h2Id = wrapper.get("h2").attributes("id")
    expect(h2Id).toBeTruthy()
    expect(section.attributes("aria-labelledby")).toBe(h2Id)
  })
})
