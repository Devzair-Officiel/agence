import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import VisibiliteProjectCallout from "~/components/expertise/visibilite/VisibiliteProjectCallout.vue"

const globalStubs = {
  NuxtLink: {
    name: "NuxtLink",
    props: ["to"],
    template: `<a :href="to"><slot /></a>`,
  },
}

describe("VisibiliteProjectCallout", () => {
  it("rend le titre validé « Développer la visibilité de votre entreprise, sans dépendre de la publicité payante. »", () => {
    const wrapper = mount(VisibiliteProjectCallout, { global: { stubs: globalStubs } })
    expect(wrapper.get("h2").text()).toBe(
      "Développer la visibilité de votre entreprise, sans dépendre de la publicité payante.",
    )
  })

  it("expose la description validée du brief éditorial", () => {
    const wrapper = mount(VisibiliteProjectCallout, { global: { stubs: globalStubs } })
    expect(wrapper.text()).toContain(
      "construire une visibilité qui s'ancre dans la durée",
    )
  })

  it("propose un CTA primaire « Parler de votre visibilité » vers /contact", () => {
    const wrapper = mount(VisibiliteProjectCallout, { global: { stubs: globalStubs } })
    const link = wrapper.find('a[href="/contact"]')
    expect(link.exists()).toBe(true)
    expect(link.text()).toContain("Parler de votre visibilité")
  })

  it("propose un CTA secondaire « Découvrir Faire évoluer » vers /expertises/faire-evoluer", () => {
    const wrapper = mount(VisibiliteProjectCallout, { global: { stubs: globalStubs } })
    const link = wrapper.find('a[href="/expertises/faire-evoluer"]')
    expect(link.exists()).toBe(true)
    expect(link.text()).toContain("Découvrir Faire évoluer")
  })

  it("relie la section au H2 par aria-labelledby", () => {
    const wrapper = mount(VisibiliteProjectCallout, { global: { stubs: globalStubs } })
    const h2Id = wrapper.get("h2").attributes("id")
    expect(h2Id).toBeTruthy()
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(h2Id)
  })
})
