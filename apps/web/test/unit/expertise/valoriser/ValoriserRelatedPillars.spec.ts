import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ValoriserRelatedPillars from "~/components/expertise/valoriser/ValoriserRelatedPillars.vue"
import { expertisePages } from "~/config/expertise-pages"

const globalStubs = {
  NuxtLink: {
    name: "NuxtLink",
    props: ["to"],
    template: `<a :href="to"><slot /></a>`,
  },
}

describe("ValoriserRelatedPillars", () => {
  it("rend deux liens vers les pôles publiés fournis", () => {
    const wrapper = mount(ValoriserRelatedPillars, {
      props: { pillarIds: ["concevoir", "visibilite"] },
      global: { stubs: globalStubs },
    })
    const links = wrapper.findAll("a")
    expect(links).toHaveLength(2)
    expect(links[0]!.attributes("href")).toBe("/expertises/concevoir")
    expect(links[1]!.attributes("href")).toBe("/expertises/visibilite")
  })

  it("rend le shortTitle de chaque pôle latéral en H3", () => {
    const wrapper = mount(ValoriserRelatedPillars, {
      props: { pillarIds: ["concevoir", "visibilite"] },
      global: { stubs: globalStubs },
    })
    const titles = wrapper.findAll("h3").map((h) => h.text())
    // 1er H3 = amont (Concevoir), 2e = centre (Valoriser non-cliquable),
    // 3e = aval (Visibilité).
    expect(titles).toEqual(["Concevoir", "Valoriser", "Visibilité"])
  })

  it("expose le summary du pôle amont dans le corps de la carte", () => {
    const concevoir = expertisePages.find((p) => p.pillarId === "concevoir")!
    const wrapper = mount(ValoriserRelatedPillars, {
      props: { pillarIds: ["concevoir", "visibilite"] },
      global: { stubs: globalStubs },
    })
    expect(wrapper.text()).toContain(concevoir.summary)
  })

  it("porte l'eyebrow validé « Aller plus loin »", () => {
    const wrapper = mount(ValoriserRelatedPillars, {
      props: { pillarIds: ["concevoir", "visibilite"] },
      global: { stubs: globalStubs },
    })
    expect(wrapper.text()).toContain("Aller plus loin")
  })

  it("expose le pôle courant Valoriser comme nœud central non-cliquable", () => {
    const wrapper = mount(ValoriserRelatedPillars, {
      props: { pillarIds: ["concevoir", "visibilite"] },
      global: { stubs: globalStubs },
    })
    const center = wrapper.get(".valoriser-related__node--center")
    expect(center.attributes("aria-hidden")).toBe("true")
    expect(center.element.tagName).toBe("DIV")
  })

  it("signale Amont / Vous êtes ici / Aval via des eyebrows", () => {
    const wrapper = mount(ValoriserRelatedPillars, {
      props: { pillarIds: ["concevoir", "visibilite"] },
      global: { stubs: globalStubs },
    })
    const eyebrows = wrapper
      .findAll(".valoriser-related__node-eyebrow")
      .map((n) => n.text())
    expect(eyebrows).toEqual(["Amont", "Vous êtes ici", "Aval"])
  })

  it("impose l'ordre narratif Concevoir → Visibilité même si la config déclare l'inverse", () => {
    // La config valoriser fournit `relatedPillarIds: ["visibilite", "concevoir"]`.
    // Le composant doit néanmoins rendre Concevoir en amont et Visibilité en aval.
    const wrapper = mount(ValoriserRelatedPillars, {
      props: { pillarIds: ["visibilite", "concevoir"] },
      global: { stubs: globalStubs },
    })
    const links = wrapper.findAll("a")
    expect(links[0]!.attributes("href")).toBe("/expertises/concevoir")
    expect(links[1]!.attributes("href")).toBe("/expertises/visibilite")
  })
})
