import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ConstruireProjectCallout from "~/components/expertise/construire/ConstruireProjectCallout.vue"

const globalStubs = {
  NuxtLink: {
    name: "NuxtLink",
    props: ["to"],
    template: `<a :href="to"><slot /></a>`,
  },
}

describe("ConstruireProjectCallout", () => {
  it("rend le titre validé « Vous avez le projet. Construisons le produit. »", () => {
    const wrapper = mount(ConstruireProjectCallout, { global: { stubs: globalStubs } })
    expect(wrapper.get("h2").text()).toBe(
      "Vous avez le projet. Construisons le produit.",
    )
  })

  it("expose la description validée du brief éditorial", () => {
    const wrapper = mount(ConstruireProjectCallout, { global: { stubs: globalStubs } })
    expect(wrapper.text()).toContain(
      "nous pouvons transformer un besoin cadré",
    )
  })

  it("propose un CTA primaire « Nous parler du projet » vers /contact", () => {
    const wrapper = mount(ConstruireProjectCallout, { global: { stubs: globalStubs } })
    const link = wrapper.find('a[href="/contact"]')
    expect(link.exists()).toBe(true)
    expect(link.text()).toContain("Nous parler du projet")
  })

  it("propose un CTA secondaire « Découvrir Concevoir » vers /expertises/concevoir", () => {
    const wrapper = mount(ConstruireProjectCallout, { global: { stubs: globalStubs } })
    const link = wrapper.find('a[href="/expertises/concevoir"]')
    expect(link.exists()).toBe(true)
    expect(link.text()).toContain("Découvrir Concevoir")
  })

  it("relie la section au H2 par aria-labelledby", () => {
    const wrapper = mount(ConstruireProjectCallout, { global: { stubs: globalStubs } })
    const h2Id = wrapper.get("h2").attributes("id")
    expect(h2Id).toBeTruthy()
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(h2Id)
  })
})
