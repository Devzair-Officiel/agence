import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ExpertiseRelatedPillars from "~/components/expertise/ExpertiseRelatedPillars.vue"
import { expertisePages } from "~/config/expertise-pages"

const globalStubs = {
  NuxtLink: {
    name: "NuxtLink",
    props: ["to"],
    template: `<a :href="to"><slot /></a>`,
  },
}

describe("ExpertiseRelatedPillars", () => {
  it("rend deux liens quand deux pillarIds publiés sont fournis", () => {
    const wrapper = mount(ExpertiseRelatedPillars, {
      props: { pillarIds: ["construire", "valoriser"] },
      global: { stubs: globalStubs },
    })
    const links = wrapper.findAll("a")
    expect(links).toHaveLength(2)
    expect(links[0]!.attributes("href")).toBe("/expertises/construire")
    expect(links[1]!.attributes("href")).toBe("/expertises/valoriser")
  })

  it("chaque carte porte le shortTitle du pôle en H3", () => {
    const wrapper = mount(ExpertiseRelatedPillars, {
      props: { pillarIds: ["construire", "valoriser"] },
      global: { stubs: globalStubs },
    })
    const titles = wrapper.findAll("h3").map((h) => h.text())
    expect(titles).toEqual(["Construire", "Valoriser"])
  })

  it("expose le résumé du pôle sous forme de paragraphe", () => {
    const construire = expertisePages.find((p) => p.pillarId === "construire")!
    const wrapper = mount(ExpertiseRelatedPillars, {
      props: { pillarIds: ["construire"] },
      global: { stubs: globalStubs },
    })
    expect(wrapper.text()).toContain(construire.summary)
  })

  it("ignore les pillarIds inexistants sans planter", () => {
    const wrapper = mount(ExpertiseRelatedPillars, {
      props: { pillarIds: ["construire", "id-inexistant"] },
      global: { stubs: globalStubs },
    })
    const links = wrapper.findAll("a")
    expect(links).toHaveLength(1)
    expect(links[0]!.attributes("href")).toBe("/expertises/construire")
  })

  it("n'émet rien quand aucun pôle valide n'est fourni", () => {
    const wrapper = mount(ExpertiseRelatedPillars, {
      props: { pillarIds: [] },
      global: { stubs: globalStubs },
    })
    expect(wrapper.findAll("ul")).toHaveLength(0)
    expect(wrapper.findAll("a")).toHaveLength(0)
  })
})
