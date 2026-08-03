import { afterEach, beforeEach, describe, expect, it, vi } from "vitest"
import { mount } from "@vue/test-utils"
import { expertisePages } from "~/config/expertise-pages"

// La route dynamique `/expertises/{slug}` lit `useRoute().params.slug` et
// lance `createError({ statusCode: 404, fatal: true })` si le slug n'est ni
// connu ni publié. On stubbe ces composables Nuxt globalement, avec un
// `currentSlug` piloté depuis chaque test.

interface SeoCall {
  title: string
  description: string
  path: string
  type?: string
}

interface SchemaCall {
  title: string
  description: string
  path: string
  serviceType: string
}

const seoCalls: SeoCall[] = []
const schemaCalls: SchemaCall[] = []
let currentSlug = ""

const globals = globalThis as unknown as {
  useRoute: () => { params: { slug: string } }
  createError: (input: unknown) => Error & Record<string, unknown>
  usePageSeo: (input: SeoCall) => void
  useExpertiseServiceSchema: (input: SchemaCall) => void
}

globals.useRoute = vi.fn(() => ({ params: { slug: currentSlug } }))
globals.usePageSeo = vi.fn((input: SeoCall) => {
  seoCalls.push(input)
})
globals.useExpertiseServiceSchema = vi.fn((input: SchemaCall) => {
  schemaCalls.push(input)
})
globals.createError = vi.fn((input: unknown) => {
  const err = new Error("nuxt-error") as Error & Record<string, unknown>
  Object.assign(err, input as Record<string, unknown>)
  throw err
})

async function mountWithSlug(slug: string) {
  currentSlug = slug
  vi.resetModules()
  const module = await import("~/pages/expertises/[slug].vue")
  return mount(module.default)
}

describe("/expertises/[slug].vue — résolution", () => {
  beforeEach(() => {
    seoCalls.length = 0
    schemaCalls.length = 0
  })

  afterEach(() => {
    vi.clearAllMocks()
  })

  it("résout chaque slug publié et rend le H1 verbatim", async () => {
    for (const page of expertisePages.filter((p) => p.status === "published")) {
      const wrapper = await mountWithSlug(page.slug)
      const h1 = wrapper.findAll("h1")
      expect(h1).toHaveLength(1)
      expect(h1[0]!.text()).toBe(page.title)
      wrapper.unmount()
    }
  })

  it("lance createError({ statusCode: 404, fatal: true }) pour un slug inconnu", async () => {
    await expect(mountWithSlug("slug-inexistant")).rejects.toMatchObject({
      statusCode: 404,
      fatal: true,
    })
  })

  it("publie exactement UN appel usePageSeo par page publiée", async () => {
    const page = expertisePages.find((p) => p.slug === "concevoir")!
    await mountWithSlug(page.slug)
    expect(seoCalls).toHaveLength(1)
    expect(seoCalls[0]).toMatchObject({
      title: page.seoTitle,
      description: page.seoDescription,
      path: page.route,
      type: "website",
    })
  })

  it("publie exactement UN appel useExpertiseServiceSchema par page publiée", async () => {
    const page = expertisePages.find((p) => p.slug === "construire")!
    await mountWithSlug(page.slug)
    expect(schemaCalls).toHaveLength(1)
    expect(schemaCalls[0]).toMatchObject({
      title: page.title,
      description: page.seoDescription,
      path: page.route,
      serviceType: page.shortTitle,
    })
  })

  it("rend le fil d'Ariane Accueil > Expertises > shortTitle", async () => {
    const page = expertisePages.find((p) => p.slug === "valoriser")!
    const wrapper = await mountWithSlug(page.slug)
    const nav = wrapper.get('nav[aria-label="Fil d\'Ariane"]')
    const items = nav.findAll("li")
    expect(items).toHaveLength(3)
    // Les items intermédiaires contiennent le libellé + un séparateur
    // décoratif ; on assert sur le lien lui-même pour rester strict.
    const links = nav.findAll("a")
    expect(links).toHaveLength(2)
    expect(links[0]!.text()).toBe("Accueil")
    expect(links[1]!.text()).toBe("Expertises")
    expect(nav.get('[aria-current="page"]').text()).toBe(page.shortTitle)
  })

  it("ne rend jamais de placeholder ou de contenu Lorem", async () => {
    const wrapper = await mountWithSlug("concevoir")
    const text = wrapper.text().toLowerCase()
    expect(text).not.toMatch(/lorem ipsum/)
    expect(text).not.toMatch(/placeholder/)
    expect(text).not.toMatch(/\btodo\b/)
  })

  it("ne référence aucune route inconnue de expertise-pages.ts", async () => {
    const wrapper = await mountWithSlug("concevoir")
    const hrefs = wrapper.findAll("a").map((a) => a.attributes("href") ?? "")
    const knownRoutes = new Set(expertisePages.map((p) => p.route))
    for (const href of hrefs) {
      if (href.startsWith("/expertises/")) {
        expect(knownRoutes.has(href)).toBe(true)
      }
    }
  })
})
