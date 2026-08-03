import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import SiteBreadcrumb from "~/components/layout/SiteBreadcrumb.vue"

const globalStubs = {
  NuxtLink: {
    name: "NuxtLink",
    props: ["to"],
    template: `<a :href="to"><slot /></a>`,
  },
}

describe("SiteBreadcrumb", () => {
  it("est un <nav> étiquetté « Fil d'Ariane »", () => {
    const wrapper = mount(SiteBreadcrumb, {
      props: { items: [{ label: "Accueil", to: "/" }, { label: "Page" }] },
      global: { stubs: globalStubs },
    })
    const nav = wrapper.get("nav")
    expect(nav.attributes("aria-label")).toBe("Fil d'Ariane")
  })

  it("rend une <ol> ordonnée avec autant d'items que d'entrées", () => {
    const wrapper = mount(SiteBreadcrumb, {
      props: {
        items: [
          { label: "Accueil", to: "/" },
          { label: "Expertises", to: "/expertises" },
          { label: "Concevoir" },
        ],
      },
      global: { stubs: globalStubs },
    })
    const list = wrapper.get("ol")
    expect(list.findAll("li")).toHaveLength(3)
  })

  it("rend chaque entrée intermédiaire comme un lien", () => {
    const wrapper = mount(SiteBreadcrumb, {
      props: {
        items: [
          { label: "Accueil", to: "/" },
          { label: "Expertises", to: "/expertises" },
          { label: "Concevoir" },
        ],
      },
      global: { stubs: globalStubs },
    })
    const links = wrapper.findAll("a")
    expect(links).toHaveLength(2)
    expect(links[0]!.attributes("href")).toBe("/")
    expect(links[1]!.attributes("href")).toBe("/expertises")
  })

  it("marque la dernière entrée avec aria-current=\"page\" et sans lien", () => {
    const wrapper = mount(SiteBreadcrumb, {
      props: {
        items: [
          { label: "Accueil", to: "/" },
          { label: "Expertises", to: "/expertises" },
          { label: "Concevoir" },
        ],
      },
      global: { stubs: globalStubs },
    })
    const current = wrapper.get('[aria-current="page"]')
    expect(current.text()).toBe("Concevoir")
    expect(current.element.tagName).not.toBe("A")
  })

  it("n'ajoute pas de séparateur après la dernière entrée", () => {
    const wrapper = mount(SiteBreadcrumb, {
      props: {
        items: [{ label: "Accueil", to: "/" }, { label: "Page" }],
      },
      global: { stubs: globalStubs },
    })
    const separators = wrapper.findAll(".site-breadcrumb__separator")
    expect(separators).toHaveLength(1)
    for (const sep of separators) {
      expect(sep.attributes("aria-hidden")).toBe("true")
    }
  })
})
