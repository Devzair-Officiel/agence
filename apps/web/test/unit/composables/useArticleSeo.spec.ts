import { beforeEach, describe, expect, it, vi } from "vitest"
import { usePageSeo } from "~/composables/usePageSeo"
import type { ArticleDetail } from "~/types/editorial"

// Nuxt auto-imports `usePageSeo` en runtime — pas en test. On l'installe
// comme global AVANT de charger useArticleSeo (qui l'invoque à l'exécution).
;(globalThis as unknown as { usePageSeo: typeof usePageSeo }).usePageSeo = usePageSeo

// Import après l'installation du global pour que l'import composable ne
// tente pas de le résoudre au moment du parsing.
const { useArticleSeo } = await import("~/composables/useArticleSeo")

/**
 * Vérifie les deux blocs JSON-LD `BlogPosting` et `BreadcrumbList`
 * injectés par la page détail. On stubbe useRuntimeConfig / useHead /
 * useSeoMeta pour capturer les objets.
 */

interface CaptureBag {
  scripts: Array<Record<string, unknown>>
  seoCalls: Array<Record<string, unknown>>
  linkCalls: Array<Record<string, unknown>>
}

function installStubs(siteUrl: string): CaptureBag {
  const captures: CaptureBag = { scripts: [], seoCalls: [], linkCalls: [] }
  const globals = globalThis as unknown as {
    useRuntimeConfig: () => unknown
    useHead: (input: Record<string, unknown>) => void
    useSeoMeta: (input: Record<string, unknown>) => void
  }
  globals.useRuntimeConfig = vi.fn(() => ({ public: { siteUrl } }))
  globals.useSeoMeta = vi.fn((input: Record<string, unknown>) => {
    captures.seoCalls.push(input)
  })
  globals.useHead = vi.fn((input: Record<string, unknown>) => {
    if (Array.isArray(input.script)) {
      for (const s of input.script) captures.scripts.push(s as Record<string, unknown>)
    }
    if (Array.isArray(input.link)) {
      for (const l of input.link) captures.linkCalls.push(l as Record<string, unknown>)
    }
  })
  return captures
}

const ARTICLE: ArticleDetail = {
  id: "01H",
  slug: "un-article",
  title: "Un titre",
  excerpt: "Extrait.",
  author: { name: "Devzair", type: "organization" },
  expertiseIds: ["concevoir"],
  publishedAt: "2026-01-15T10:00:00Z",
  updatedAt: "2026-02-10T12:00:00Z",
  bodyMarkdown: "# T",
  contentHtml: "<p>T</p>",
  seo: { title: "SEO", description: "Description SEO." },
}

function parseJsonLdByType(captures: CaptureBag, type: string) {
  const script = captures.scripts.find((s) => {
    const raw = s.innerHTML as string
    return typeof raw === "string" && raw.includes(`"@type":"${type}"`)
  })
  expect(script, `bloc JSON-LD ${type} introuvable`).toBeDefined()
  return JSON.parse(script!.innerHTML as string) as Record<string, unknown>
}

describe("useArticleSeo", () => {
  let captures: CaptureBag
  beforeEach(() => {
    captures = installStubs("https://devzair.fr")
  })

  it("émet un BlogPosting avec headline, dates et publisher référencé par @id", () => {
    useArticleSeo({ article: ARTICLE, path: "/ressources/un-article" })
    const jsonLd = parseJsonLdByType(captures, "BlogPosting")
    expect(jsonLd.headline).toBe("Un titre")
    expect(jsonLd.datePublished).toBe("2026-01-15T10:00:00Z")
    expect(jsonLd.dateModified).toBe("2026-02-10T12:00:00Z")
    expect(jsonLd.mainEntityOfPage).toBe("https://devzair.fr/ressources/un-article")
    expect(jsonLd.publisher).toEqual({ "@id": "https://devzair.fr/#organization" })
  })

  it("émet un BreadcrumbList Accueil → Ressources → Article", () => {
    useArticleSeo({ article: ARTICLE, path: "/ressources/un-article" })
    const jsonLd = parseJsonLdByType(captures, "BreadcrumbList")
    const items = jsonLd.itemListElement as Array<Record<string, unknown>>
    expect(items).toHaveLength(3)
    expect(items[0]!.name).toBe("Accueil")
    expect(items[1]!.name).toBe("Ressources")
    expect(items[2]!.name).toBe("Un titre")
    expect(items[2]!.item).toBe("https://devzair.fr/ressources/un-article")
  })

  it("emploie l'author.type pour choisir Person vs Organization dans le JSON-LD", () => {
    const personArticle: ArticleDetail = {
      ...ARTICLE,
      author: { name: "Aurélien Boudon", type: "person" },
    }
    useArticleSeo({ article: personArticle, path: "/ressources/un-article" })
    const jsonLd = parseJsonLdByType(captures, "BlogPosting")
    const author = jsonLd.author as Record<string, unknown>
    expect(author["@type"]).toBe("Person")
    expect(author.name).toBe("Aurélien Boudon")
  })

  it("délègue title/description à usePageSeo (type article)", () => {
    useArticleSeo({ article: ARTICLE, path: "/ressources/un-article" })
    // usePageSeo appelle useSeoMeta — on retrouve le title et le description
    const seo = captures.seoCalls[0]!
    expect(seo.title).toBe("SEO")
    expect(seo.description).toBe("Description SEO.")
    expect(seo.ogType).toBe("article")
  })
})
