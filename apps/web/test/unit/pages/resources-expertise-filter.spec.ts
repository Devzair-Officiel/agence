import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ResourceExpertiseFilter from "~/components/resources/ResourceExpertiseFilter.vue"
import { expertisePages } from "~/config/expertise-pages"

/**
 * Phase 10A2 — barre de filtre par expertise sur `/ressources`.
 *
 * Vérifie que chaque expertise est un LIEN (SSR + partageable), que
 * l'option active est annoncée `aria-current="page"`, et que la barre
 * expose un libellé de navigation accessible.
 */

describe("ResourceExpertiseFilter", () => {
  it("expose une nav étiquetée « Filtrer les ressources par expertise »", () => {
    const wrapper = mount(ResourceExpertiseFilter, {
      props: { activeExpertise: null },
    })
    const nav = wrapper.find("nav")
    expect(nav.exists()).toBe(true)
    expect(nav.attributes("aria-label")).toContain("Filtrer")
  })

  it("rend une option « Toutes » + une option par expertise publiée", () => {
    const wrapper = mount(ResourceExpertiseFilter, {
      props: { activeExpertise: null },
    })
    const links = wrapper.findAll("a")
    expect(links).toHaveLength(1 + expertisePages.length)
    expect(links[0]?.text()).toBe("Toutes")
    expect(links[0]?.attributes("href")).toBe("/ressources")
  })

  it("construit des href `/ressources?expertise=<id>` pour chaque expertise", () => {
    const wrapper = mount(ResourceExpertiseFilter, {
      props: { activeExpertise: null },
    })
    for (const page of expertisePages) {
      const link = wrapper.find(`a[href="/ressources?expertise=${page.id}"]`)
      expect(link.exists()).toBe(true)
      expect(link.text()).toBe(page.shortTitle)
    }
  })

  it("marque « Toutes » comme actif quand activeExpertise est null", () => {
    const wrapper = mount(ResourceExpertiseFilter, {
      props: { activeExpertise: null },
    })
    const allLink = wrapper.find('a[href="/ressources"]')
    expect(allLink.attributes("aria-current")).toBe("page")
    for (const page of expertisePages) {
      const link = wrapper.find(`a[href="/ressources?expertise=${page.id}"]`)
      expect(link.attributes("aria-current")).toBeUndefined()
    }
  })

  it("marque uniquement l'expertise active", () => {
    const wrapper = mount(ResourceExpertiseFilter, {
      props: { activeExpertise: "concevoir" },
    })
    const active = wrapper.find('a[href="/ressources?expertise=concevoir"]')
    expect(active.attributes("aria-current")).toBe("page")
    expect(wrapper.find('a[href="/ressources"]').attributes("aria-current")).toBeUndefined()
    expect(
      wrapper.find('a[href="/ressources?expertise=construire"]').attributes("aria-current"),
    ).toBeUndefined()
  })

  it("ne contient aucun <button> (les filtres sont des liens SSR)", () => {
    const wrapper = mount(ResourceExpertiseFilter, {
      props: { activeExpertise: null },
    })
    expect(wrapper.findAll("button")).toHaveLength(0)
  })
})
