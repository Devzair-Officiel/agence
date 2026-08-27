import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import FaireEvoluerRelatedPillars from "~/components/expertise/faire-evoluer/FaireEvoluerRelatedPillars.vue"
import { expertisePages } from "~/config/expertise-pages"

const globalStubs = {
  NuxtLink: {
    name: "NuxtLink",
    props: ["to"],
    template: `<a :href="to"><slot /></a>`,
  },
}

describe("FaireEvoluerRelatedPillars", () => {
  it("rend deux liens vers les pôles publiés fournis (Construire + Visibilité)", () => {
    const wrapper = mount(FaireEvoluerRelatedPillars, {
      props: { pillarIds: ["construire", "visibilite"] },
      global: { stubs: globalStubs },
    })
    const links = wrapper.findAll("a")
    expect(links).toHaveLength(2)
    expect(links[0]!.attributes("href")).toBe("/expertises/construire")
    expect(links[1]!.attributes("href")).toBe("/expertises/visibilite")
  })

  it("rend le shortTitle en H3 pour chaque pôle latéral + centre Faire évoluer", () => {
    const wrapper = mount(FaireEvoluerRelatedPillars, {
      props: { pillarIds: ["construire", "visibilite"] },
      global: { stubs: globalStubs },
    })
    const titles = wrapper.findAll("h3").map((h) => h.text())
    expect(titles).toEqual(["Construire", "Faire évoluer", "Visibilité"])
  })

  it("expose le summary du pôle amont Construire dans le corps de la carte", () => {
    const construire = expertisePages.find((p) => p.pillarId === "construire")!
    const wrapper = mount(FaireEvoluerRelatedPillars, {
      props: { pillarIds: ["construire", "visibilite"] },
      global: { stubs: globalStubs },
    })
    expect(wrapper.text()).toContain(construire.summary)
  })

  it("porte l'eyebrow validé « Aller plus loin »", () => {
    const wrapper = mount(FaireEvoluerRelatedPillars, {
      props: { pillarIds: ["construire", "visibilite"] },
      global: { stubs: globalStubs },
    })
    expect(wrapper.text()).toContain("Aller plus loin")
  })

  it("expose le pôle courant Faire évoluer comme nœud central non-cliquable", () => {
    const wrapper = mount(FaireEvoluerRelatedPillars, {
      props: { pillarIds: ["construire", "visibilite"] },
      global: { stubs: globalStubs },
    })
    const center = wrapper.get(".faire-evoluer-related__node--center")
    expect(center.attributes("aria-hidden")).toBe("true")
    expect(center.element.tagName).toBe("DIV")
  })

  it("signale Amont / Vous êtes ici / Amont via les eyebrows (deux amonts convergents)", () => {
    const wrapper = mount(FaireEvoluerRelatedPillars, {
      props: { pillarIds: ["construire", "visibilite"] },
      global: { stubs: globalStubs },
    })
    const eyebrows = wrapper
      .findAll(".faire-evoluer-related__node-eyebrow")
      .map((n) => n.text())
    expect(eyebrows).toEqual(["Amont", "Vous êtes ici", "Amont"])
  })

  it("impose l'ordre narratif Construire → Visibilité même si la config déclare l'inverse", () => {
    const wrapper = mount(FaireEvoluerRelatedPillars, {
      props: { pillarIds: ["visibilite", "construire"] },
      global: { stubs: globalStubs },
    })
    const links = wrapper.findAll("a")
    expect(links[0]!.attributes("href")).toBe("/expertises/construire")
    expect(links[1]!.attributes("href")).toBe("/expertises/visibilite")
  })
})
