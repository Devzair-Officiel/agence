import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import VisibiliteRelatedPillars from "~/components/expertise/visibilite/VisibiliteRelatedPillars.vue"
import { expertisePages } from "~/config/expertise-pages"

const globalStubs = {
  NuxtLink: {
    name: "NuxtLink",
    props: ["to"],
    template: `<a :href="to"><slot /></a>`,
  },
}

describe("VisibiliteRelatedPillars", () => {
  it("rend deux liens vers les pôles publiés fournis", () => {
    const wrapper = mount(VisibiliteRelatedPillars, {
      props: { pillarIds: ["valoriser", "faire-evoluer"] },
      global: { stubs: globalStubs },
    })
    const links = wrapper.findAll("a")
    expect(links).toHaveLength(2)
    expect(links[0]!.attributes("href")).toBe("/expertises/valoriser")
    expect(links[1]!.attributes("href")).toBe("/expertises/faire-evoluer")
  })

  it("rend le shortTitle de chaque pôle latéral en H3", () => {
    const wrapper = mount(VisibiliteRelatedPillars, {
      props: { pillarIds: ["valoriser", "faire-evoluer"] },
      global: { stubs: globalStubs },
    })
    const titles = wrapper.findAll("h3").map((h) => h.text())
    // 1er H3 = amont (Valoriser), 2e = centre (Visibilité non-cliquable),
    // 3e = aval (Faire évoluer).
    expect(titles).toEqual(["Valoriser", "Visibilité", "Faire évoluer"])
  })

  it("expose le summary du pôle amont dans le corps de la carte", () => {
    const valoriser = expertisePages.find((p) => p.pillarId === "valoriser")!
    const wrapper = mount(VisibiliteRelatedPillars, {
      props: { pillarIds: ["valoriser", "faire-evoluer"] },
      global: { stubs: globalStubs },
    })
    expect(wrapper.text()).toContain(valoriser.summary)
  })

  it("porte l'eyebrow validé « Aller plus loin »", () => {
    const wrapper = mount(VisibiliteRelatedPillars, {
      props: { pillarIds: ["valoriser", "faire-evoluer"] },
      global: { stubs: globalStubs },
    })
    expect(wrapper.text()).toContain("Aller plus loin")
  })

  it("expose le pôle courant Visibilité comme nœud central non-cliquable", () => {
    const wrapper = mount(VisibiliteRelatedPillars, {
      props: { pillarIds: ["valoriser", "faire-evoluer"] },
      global: { stubs: globalStubs },
    })
    const center = wrapper.get(".visibilite-related__node--center")
    expect(center.attributes("aria-hidden")).toBe("true")
    expect(center.element.tagName).toBe("DIV")
  })

  it("signale Amont / Vous êtes ici / Aval via des eyebrows", () => {
    const wrapper = mount(VisibiliteRelatedPillars, {
      props: { pillarIds: ["valoriser", "faire-evoluer"] },
      global: { stubs: globalStubs },
    })
    const eyebrows = wrapper
      .findAll(".visibilite-related__node-eyebrow")
      .map((n) => n.text())
    expect(eyebrows).toEqual(["Amont", "Vous êtes ici", "Aval"])
  })

  it("impose l'ordre narratif Valoriser → Faire évoluer même si la config déclare l'inverse", () => {
    const wrapper = mount(VisibiliteRelatedPillars, {
      props: { pillarIds: ["faire-evoluer", "valoriser"] },
      global: { stubs: globalStubs },
    })
    const links = wrapper.findAll("a")
    expect(links[0]!.attributes("href")).toBe("/expertises/valoriser")
    expect(links[1]!.attributes("href")).toBe("/expertises/faire-evoluer")
  })
})
