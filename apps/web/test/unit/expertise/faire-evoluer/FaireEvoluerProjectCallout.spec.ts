import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import FaireEvoluerProjectCallout from "~/components/expertise/faire-evoluer/FaireEvoluerProjectCallout.vue"

const globalStubs = {
  NuxtLink: {
    name: "NuxtLink",
    props: ["to"],
    template: `<a :href="to"><slot /></a>`,
  },
}

describe("FaireEvoluerProjectCallout", () => {
  it("rend le titre validé « Votre site est en ligne. Gardons-le utile, fiable et capable d'évoluer. »", () => {
    const wrapper = mount(FaireEvoluerProjectCallout, {
      global: { stubs: globalStubs },
    })
    expect(wrapper.get("h2").text().replace(/\s+/g, " ")).toBe(
      "Votre site est en ligne. Gardons-le utile, fiable et capable d'évoluer.",
    )
  })

  it("expose la description validée du brief éditorial", () => {
    const wrapper = mount(FaireEvoluerProjectCallout, {
      global: { stubs: globalStubs },
    })
    expect(wrapper.text()).toContain("comme un flux, pas comme un chantier annuel")
  })

  it("propose un CTA primaire « Parler de votre suivi » vers /contact", () => {
    const wrapper = mount(FaireEvoluerProjectCallout, {
      global: { stubs: globalStubs },
    })
    const link = wrapper.find('a[href="/contact"]')
    expect(link.exists()).toBe(true)
    expect(link.text()).toContain("Parler de votre suivi")
  })

  it("propose un CTA secondaire « Maintenance et accompagnement » vers /services/maintenance-accompagnement (SEO-COM-5C)", () => {
    const wrapper = mount(FaireEvoluerProjectCallout, {
      global: { stubs: globalStubs },
    })
    const link = wrapper.find('a[href="/services/maintenance-accompagnement"]')
    expect(link.exists()).toBe(true)
    expect(link.text()).toContain("Maintenance et accompagnement")
  })

  it("relie la section au H2 par aria-labelledby", () => {
    const wrapper = mount(FaireEvoluerProjectCallout, {
      global: { stubs: globalStubs },
    })
    const h2Id = wrapper.get("h2").attributes("id")
    expect(h2Id).toBeTruthy()
    expect(wrapper.get("section").attributes("aria-labelledby")).toBe(h2Id)
  })
})
