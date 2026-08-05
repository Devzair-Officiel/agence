import { describe, expect, it } from "vitest"
import { mount } from "@vue/test-utils"
import ResourceListItem from "~/components/resources/ResourceListItem.vue"
import type { ArticleSummary } from "~/types/editorial"

const article: ArticleSummary = {
  id: "01H",
  slug: "un-titre-de-ressource",
  title: "Un titre de ressource",
  excerpt: "Un extrait explicite.",
  author: { name: "Devzair", type: "organization" },
  expertiseIds: ["concevoir"],
  publishedAt: "2026-01-15T10:00:00Z",
  updatedAt: "2026-01-15T10:00:00Z",
}

describe("ResourceListItem", () => {
  it("rend un <article> contenant un lien vers /ressources/{slug}", () => {
    const wrapper = mount(ResourceListItem, { props: { article } })
    expect(wrapper.element.tagName.toLowerCase()).toBe("article")
    const link = wrapper.find("a")
    expect(link.attributes("href")).toBe("/ressources/un-titre-de-ressource")
  })

  it("affiche titre, extrait, auteur et date lisible française", () => {
    const wrapper = mount(ResourceListItem, { props: { article } })
    const text = wrapper.text()
    expect(text).toContain("Un titre de ressource")
    expect(text).toContain("Un extrait explicite.")
    expect(text).toContain("Devzair")
    expect(text).toMatch(/15 janvier 2026/)
  })

  it("expose la date machine via <time datetime>", () => {
    const wrapper = mount(ResourceListItem, { props: { article } })
    const time = wrapper.find("time")
    expect(time.attributes("datetime")).toBe("2026-01-15T10:00:00Z")
  })

  it("le titre est un H3 (la page porte H1+H2)", () => {
    const wrapper = mount(ResourceListItem, { props: { article } })
    expect(wrapper.find("h3").exists()).toBe(true)
  })
})
