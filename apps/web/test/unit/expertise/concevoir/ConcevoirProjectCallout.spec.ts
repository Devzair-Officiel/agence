import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ConcevoirProjectCallout from "~/components/expertise/concevoir/ConcevoirProjectCallout.vue"

const globalStubs = {
  NuxtLink: {
    name: "NuxtLink",
    props: ["to"],
    template: `<a :href="to"><slot /></a>`,
  },
}

describe("ConcevoirProjectCallout", () => {
  it("rend le titre validé « Vous avez le projet. Construisons d'abord sa direction. »", () => {
    const wrapper = mount(ConcevoirProjectCallout, { global: { stubs: globalStubs } })
    expect(wrapper.get("h2").text()).toBe(
      "Vous avez le projet. Construisons d'abord sa direction.",
    )
  })

  it("expose la description validée du brief éditorial", () => {
    const wrapper = mount(ConcevoirProjectCallout, { global: { stubs: globalStubs } })
    expect(wrapper.text()).toContain(
      "Avant de parler technologie, nous pouvons clarifier les usages",
    )
  })

  it("propose un CTA primaire « Parler de votre projet » vers /contact", () => {
    const wrapper = mount(ConcevoirProjectCallout, { global: { stubs: globalStubs } })
    const link = wrapper.find('a[href="/contact"]')
    expect(link.exists()).toBe(true)
    expect(link.text()).toContain("Parler de votre projet")
  })

  it("propose un CTA secondaire « Découvrir Construire » vers /expertises/construire", () => {
    const wrapper = mount(ConcevoirProjectCallout, { global: { stubs: globalStubs } })
    const link = wrapper.find('a[href="/expertises/construire"]')
    expect(link.exists()).toBe(true)
    expect(link.text()).toContain("Découvrir Construire")
  })

  it("relie la section au H2 par aria-labelledby", () => {
    const wrapper = mount(ConcevoirProjectCallout, { global: { stubs: globalStubs } })
    const h2Id = wrapper.get("h2").attributes("id")
    expect(h2Id).toBeTruthy()
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(h2Id)
  })
})
