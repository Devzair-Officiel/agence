import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ResourceMeta from "~/components/resources/ResourceMeta.vue"

const AUTHOR = { name: "Devzair", type: "organization" as const }

function render(overrides: Record<string, unknown> = {}) {
  return mount(ResourceMeta, {
    props: {
      author: AUTHOR,
      publishedAt: "2026-01-15T10:00:00Z",
      updatedAt: "2026-01-15T10:00:00Z",
      expertiseIds: [],
      ...overrides,
    },
  })
}

describe("ResourceMeta", () => {
  it("n'affiche PAS la date de mise à jour quand elle est identique à la publication", () => {
    const wrapper = render()
    expect(wrapper.text()).not.toContain("Mis à jour le")
  })

  it("affiche la date de mise à jour quand elle diffère", () => {
    const wrapper = render({ updatedAt: "2026-02-01T10:00:00Z" })
    expect(wrapper.text()).toContain("Mis à jour le")
    expect(wrapper.text()).toMatch(/1(er)? février 2026/)
  })

  it("rend les puces d'expertise cliquables vers /expertises/{slug} pour un pillar publié", () => {
    const wrapper = render({ expertiseIds: ["concevoir"] })
    const link = wrapper.find("a[href='/expertises/concevoir']")
    expect(link.exists()).toBe(true)
  })

  it("ignore silencieusement un expertiseId inconnu (pas de lien mort, pas de libellé fabriqué)", () => {
    const wrapper = render({ expertiseIds: ["gouvernance-cosmique"] })
    // Aucune puce ne doit apparaître
    expect(wrapper.find(".resource-meta__chip").exists()).toBe(false)
    expect(wrapper.text()).not.toContain("gouvernance-cosmique")
  })

  it("expose les dates via <time datetime>", () => {
    const wrapper = render()
    const time = wrapper.find("time")
    expect(time.attributes("datetime")).toBe("2026-01-15T10:00:00Z")
  })

  it("utilise une <dl> pour la relation label/valeur (accessibilité)", () => {
    const wrapper = render()
    expect(wrapper.find("dl").exists()).toBe(true)
    expect(wrapper.findAll("dt").length).toBeGreaterThan(0)
    expect(wrapper.findAll("dd").length).toBeGreaterThan(0)
  })
})
