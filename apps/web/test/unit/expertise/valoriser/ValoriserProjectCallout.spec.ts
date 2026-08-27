import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ValoriserProjectCallout from "~/components/expertise/valoriser/ValoriserProjectCallout.vue"

const globalStubs = {
  NuxtLink: {
    name: "NuxtLink",
    props: ["to"],
    template: `<a :href="to"><slot /></a>`,
  },
}

describe("ValoriserProjectCallout", () => {
  it("rend le titre validé « Votre activité a de la valeur. Donnons-lui la forme qu'elle mérite. »", () => {
    const wrapper = mount(ValoriserProjectCallout, { global: { stubs: globalStubs } })
    expect(wrapper.get("h2").text()).toBe(
      "Votre activité a de la valeur. Donnons-lui la forme qu'elle mérite.",
    )
  })

  it("expose la description validée du brief éditorial", () => {
    const wrapper = mount(ValoriserProjectCallout, { global: { stubs: globalStubs } })
    expect(wrapper.text()).toContain(
      "construire un ensemble de contenus cohérent",
    )
  })

  it("propose un CTA primaire « Parler de vos contenus » vers /contact", () => {
    const wrapper = mount(ValoriserProjectCallout, { global: { stubs: globalStubs } })
    const link = wrapper.find('a[href="/contact"]')
    expect(link.exists()).toBe(true)
    expect(link.text()).toContain("Parler de vos contenus")
  })

  it("propose un CTA secondaire « Découvrir Visibilité » vers /expertises/visibilite", () => {
    const wrapper = mount(ValoriserProjectCallout, { global: { stubs: globalStubs } })
    const link = wrapper.find('a[href="/expertises/visibilite"]')
    expect(link.exists()).toBe(true)
    expect(link.text()).toContain("Découvrir Visibilité")
  })

  it("relie la section au H2 par aria-labelledby", () => {
    const wrapper = mount(ValoriserProjectCallout, { global: { stubs: globalStubs } })
    const h2Id = wrapper.get("h2").attributes("id")
    expect(h2Id).toBeTruthy()
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(h2Id)
  })
})
