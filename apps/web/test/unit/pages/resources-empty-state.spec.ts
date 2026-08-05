import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ResourceEmptyState from "~/components/resources/ResourceEmptyState.vue"

/**
 * L'état vide doit être honnête : dire qu'aucune ressource n'est publiée,
 * ne pas inventer de contenu de placeholder, offrir une bascule utile.
 */

describe("ResourceEmptyState", () => {
  it("annonce explicitement l'absence de ressource publiée", () => {
    const wrapper = mount(ResourceEmptyState)
    const text = wrapper.text()
    expect(text).toContain("Aucune ressource")
    expect(text).toContain("bibliothèque de ressources")
  })

  it("ne contient aucun contenu de placeholder (pas de « bientôt », pas de date fictive)", () => {
    const wrapper = mount(ResourceEmptyState)
    const text = wrapper.text().toLowerCase()
    expect(text).not.toMatch(/bientôt disponible/)
    expect(text).not.toMatch(/lorem ipsum/)
    expect(text).not.toMatch(/\d{4}-\d{2}-\d{2}/) // pas de date en dur
  })

  it("expose exactement deux CTA (Agence + Contact) — pas d'auto-référence vers /ressources", () => {
    const wrapper = mount(ResourceEmptyState)
    const links = wrapper.findAll("a[href]")
    const hrefs = links.map((l) => l.attributes("href"))
    expect(hrefs).toContain("/agence")
    expect(hrefs).toContain("/contact")
    expect(hrefs).not.toContain("/ressources")
  })

  it("est structuré autour d'un H2 étiqueté (aria-labelledby)", () => {
    const wrapper = mount(ResourceEmptyState)
    const section = wrapper.find("section")
    expect(section.attributes("aria-labelledby")).toBe("resource-empty-title")
    expect(wrapper.find("#resource-empty-title").element.tagName.toLowerCase()).toBe("h2")
  })
})
