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
  heroImage: null,
}

const MEDIA_UUID = "0193b1a0-1c7d-7000-8000-000000000001"

const articleWithHeroLegacy: ArticleSummary = {
  ...article,
  heroImage: {
    url: `/api/media/${MEDIA_UUID}`,
    alt: "Vignette test",
    width: 1200,
    height: 675,
    mimeType: "image/webp",
    card: null,
    hero: null,
  },
}

const articleWithHeroVariants: ArticleSummary = {
  ...article,
  heroImage: {
    url: `/api/media/${MEDIA_UUID}`,
    alt: "Vignette test",
    width: 1200,
    height: 675,
    mimeType: "image/webp",
    card: { url: `/api/media/${MEDIA_UUID}/card`, width: 768, height: 432 },
    hero: { url: `/api/media/${MEDIA_UUID}/hero`, width: 1200, height: 675 },
  },
}

describe("ResourceListItem", () => {
  it("rend un <article> contenant un lien vers /ressources/{slug}", () => {
    const wrapper = mount(ResourceListItem, { props: { article } })
    expect(wrapper.element.tagName.toLowerCase()).toBe("article")
    const link = wrapper.find("a")
    expect(link.attributes("href")).toBe("/ressources/un-titre-de-ressource")
  })

  it("affiche titre, extrait, expertise et date lisible française", () => {
    const wrapper = mount(ResourceListItem, { props: { article } })
    const text = wrapper.text()
    expect(text).toContain("Un titre de ressource")
    expect(text).toContain("Un extrait explicite.")
    expect(text).toMatch(/15 janvier 2026/)
  })

  it("affiche le label de la première expertise dans les métas", () => {
    const wrapper = mount(ResourceListItem, { props: { article } })
    expect(wrapper.text()).toContain("Concevoir")
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

  it("n'affiche pas de figure si heroImage est null", () => {
    const wrapper = mount(ResourceListItem, { props: { article } })
    expect(wrapper.find("figure").exists()).toBe(false)
  })

  it("utilise l'url originale et les dimensions originales pour un asset legacy (card null)", () => {
    const wrapper = mount(ResourceListItem, { props: { article: articleWithHeroLegacy } })
    const img = wrapper.find("img")
    expect(img.attributes("src")).toBe(`/api/media/${MEDIA_UUID}`)
    expect(img.attributes("width")).toBe("1200")
    expect(img.attributes("height")).toBe("675")
    expect(img.attributes("alt")).toBe("Vignette test")
  })

  it("utilise l'url et les dimensions du variant card quand disponible", () => {
    const wrapper = mount(ResourceListItem, { props: { article: articleWithHeroVariants } })
    const img = wrapper.find("img")
    expect(img.attributes("src")).toBe(`/api/media/${MEDIA_UUID}/card`)
    expect(img.attributes("width")).toBe("768")
    expect(img.attributes("height")).toBe("432")
  })
})
