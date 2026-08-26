import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ConcevoirRelatedPillars from "~/components/expertise/concevoir/ConcevoirRelatedPillars.vue"
import { expertisePages } from "~/config/expertise-pages"

const globalStubs = {
  NuxtLink: {
    name: "NuxtLink",
    props: ["to"],
    template: `<a :href="to"><slot /></a>`,
  },
}

describe("ConcevoirRelatedPillars", () => {
  it("rend deux liens vers les pôles publiés fournis", () => {
    const wrapper = mount(ConcevoirRelatedPillars, {
      props: { pillarIds: ["construire", "valoriser"] },
      global: { stubs: globalStubs },
    })
    const links = wrapper.findAll("a")
    expect(links).toHaveLength(2)
    expect(links[0]!.attributes("href")).toBe("/expertises/construire")
    expect(links[1]!.attributes("href")).toBe("/expertises/valoriser")
  })

  it("rend le shortTitle de chaque pôle en H3", () => {
    const wrapper = mount(ConcevoirRelatedPillars, {
      props: { pillarIds: ["construire", "valoriser"] },
      global: { stubs: globalStubs },
    })
    const titles = wrapper.findAll("h3").map((h) => h.text())
    expect(titles).toEqual(["Construire", "Valoriser"])
  })

  it("expose le summary du pôle dans le corps de la carte", () => {
    const construire = expertisePages.find((p) => p.pillarId === "construire")!
    const wrapper = mount(ConcevoirRelatedPillars, {
      props: { pillarIds: ["construire"] },
      global: { stubs: globalStubs },
    })
    expect(wrapper.text()).toContain(construire.summary)
  })

  it("porte l'eyebrow validé « Aller plus loin »", () => {
    const wrapper = mount(ConcevoirRelatedPillars, {
      props: { pillarIds: ["construire", "valoriser"] },
      global: { stubs: globalStubs },
    })
    expect(wrapper.text()).toContain("Aller plus loin")
  })
})
