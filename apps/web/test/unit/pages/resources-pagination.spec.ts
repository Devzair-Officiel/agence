import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ResourcePagination from "~/components/resources/ResourcePagination.vue"

/**
 * Pagination : contrat d'URL (page 1 canonique sans query), fenêtre
 * glissante, marquage `aria-current="page"`, et absence de rendu quand
 * il n'y a qu'une seule page.
 */

const buildHref = (page: number) => (page === 1 ? "/ressources" : `/ressources?page=${page}`)

function render(currentPage: number, totalPages: number) {
  return mount(ResourcePagination, {
    props: { currentPage, totalPages, buildHref },
  })
}

describe("ResourcePagination", () => {
  it("ne rend RIEN quand totalPages ≤ 1 (pas de nav superflu)", () => {
    const wrapper = render(1, 1)
    expect(wrapper.find("nav").exists()).toBe(false)
  })

  it("nav a un label français explicite", () => {
    const wrapper = render(1, 3)
    expect(wrapper.find("nav").attributes("aria-label")).toBe("Pagination des ressources")
  })

  it("marque la page courante avec aria-current, sans lien vers elle-même", () => {
    const wrapper = render(2, 3)
    const current = wrapper.find("[aria-current='page']")
    expect(current.exists()).toBe(true)
    expect(current.text()).toBe("2")
    // Doit être un <span>, pas un <a>
    expect(current.element.tagName.toLowerCase()).toBe("span")
  })

  it("la page 1 pointe sur /ressources (sans ?page=1)", () => {
    const wrapper = render(2, 3)
    const links = wrapper.findAll("a").map((a) => a.attributes("href"))
    expect(links).toContain("/ressources")
    expect(links).not.toContain("/ressources?page=1")
  })

  it("désactive le bouton précédent sur la page 1 (aria-disabled)", () => {
    const wrapper = render(1, 5)
    const controls = wrapper.findAll(".resource-pagination__control")
    const prev = controls[0]!
    expect(prev.attributes("aria-disabled")).toBe("true")
    expect(prev.element.tagName.toLowerCase()).toBe("span")
  })

  it("désactive le bouton suivant sur la dernière page", () => {
    const wrapper = render(5, 5)
    const controls = wrapper.findAll(".resource-pagination__control")
    const next = controls[controls.length - 1]!
    expect(next.attributes("aria-disabled")).toBe("true")
  })

  it("insère un ellipsis quand la fenêtre saute plus d'une page", () => {
    const wrapper = render(5, 10)
    expect(wrapper.find(".resource-pagination__ellipsis").exists()).toBe(true)
  })

  it("relaie rel=prev/next sur les contrôles quand ils sont actifs", () => {
    const wrapper = render(3, 5)
    const controls = wrapper.findAll("a.resource-pagination__control")
    const rels = controls.map((c) => c.attributes("rel"))
    expect(rels).toContain("prev")
    expect(rels).toContain("next")
  })
})
