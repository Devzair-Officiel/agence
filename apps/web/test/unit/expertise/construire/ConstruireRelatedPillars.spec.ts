import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ConstruireRelatedPillars from "~/components/expertise/construire/ConstruireRelatedPillars.vue"
import { expertisePages } from "~/config/expertise-pages"

const globalStubs = {
  NuxtLink: {
    name: "NuxtLink",
    props: ["to"],
    template: `<a :href="to"><slot /></a>`,
  },
}

describe("ConstruireRelatedPillars", () => {
  it("rend deux liens vers les pôles publiés fournis", () => {
    const wrapper = mount(ConstruireRelatedPillars, {
      props: { pillarIds: ["concevoir", "faire-evoluer"] },
      global: { stubs: globalStubs },
    })
    const links = wrapper.findAll("a")
    expect(links).toHaveLength(2)
    expect(links[0]!.attributes("href")).toBe("/expertises/concevoir")
    expect(links[1]!.attributes("href")).toBe("/expertises/faire-evoluer")
  })

  it("rend le shortTitle de chaque pôle latéral en H3", () => {
    const wrapper = mount(ConstruireRelatedPillars, {
      props: { pillarIds: ["concevoir", "faire-evoluer"] },
      global: { stubs: globalStubs },
    })
    const titles = wrapper.findAll("h3").map((h) => h.text())
    // 1er H3 = amont (Concevoir), 2e = centre (Construire non-cliquable),
    // 3e = aval (Faire évoluer).
    expect(titles).toEqual(["Concevoir", "Construire", "Faire évoluer"])
  })

  it("expose le summary du pôle amont dans le corps de la carte", () => {
    const concevoir = expertisePages.find((p) => p.pillarId === "concevoir")!
    const wrapper = mount(ConstruireRelatedPillars, {
      props: { pillarIds: ["concevoir", "faire-evoluer"] },
      global: { stubs: globalStubs },
    })
    expect(wrapper.text()).toContain(concevoir.summary)
  })

  it("porte l'eyebrow validé « Aller plus loin »", () => {
    const wrapper = mount(ConstruireRelatedPillars, {
      props: { pillarIds: ["concevoir", "faire-evoluer"] },
      global: { stubs: globalStubs },
    })
    expect(wrapper.text()).toContain("Aller plus loin")
  })

  it("expose le pôle courant Construire comme nœud central non-cliquable", () => {
    const wrapper = mount(ConstruireRelatedPillars, {
      props: { pillarIds: ["concevoir", "faire-evoluer"] },
      global: { stubs: globalStubs },
    })
    const center = wrapper.get(".construire-related__node--center")
    expect(center.attributes("aria-hidden")).toBe("true")
    expect(center.element.tagName).toBe("DIV")
  })

  it("signale amont/vous êtes ici/aval via des eyebrows", () => {
    const wrapper = mount(ConstruireRelatedPillars, {
      props: { pillarIds: ["concevoir", "faire-evoluer"] },
      global: { stubs: globalStubs },
    })
    const eyebrows = wrapper
      .findAll(".construire-related__node-eyebrow")
      .map((n) => n.text())
    expect(eyebrows).toEqual(["Amont", "Vous êtes ici", "Aval"])
  })
})
