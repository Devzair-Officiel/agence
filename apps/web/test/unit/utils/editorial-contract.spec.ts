import { describe, expect, it } from "vitest"
import {
  EditorialContractError,
  mapArticleDetailResponse,
  mapArticleListResponse,
} from "~/utils/editorial-contract"

/**
 * Frontière contractuelle avec l'API Symfony (snake_case → camelCase).
 *
 * Les tests couvrent : mapping heureux, rejet des types incorrects, rejet
 * des dates non ISO, rejet des slugs mal formés, propagation des erreurs
 * via EditorialContractError.
 */

const VALID_AUTHOR = { name: "Devzair", type: "organization" }
const VALID_DATE = "2026-01-15T10:00:00Z"

function summary(overrides: Record<string, unknown> = {}): Record<string, unknown> {
  return {
    id: "01H8ABCXYZ0000000000000000",
    slug: "un-titre-de-ressource",
    title: "Un titre",
    excerpt: "Un extrait.",
    author: VALID_AUTHOR,
    expertise_ids: ["concevoir"],
    published_at: VALID_DATE,
    updated_at: VALID_DATE,
    ...overrides,
  }
}

function detail(overrides: Record<string, unknown> = {}): Record<string, unknown> {
  return {
    ...summary(),
    body_markdown: "# Titre\nCorps",
    content_html: "<h2>Titre</h2><p>Corps</p>",
    seo: { title: "SEO Titre", description: "SEO description longue" },
    ...overrides,
  }
}

describe("mapArticleListResponse", () => {
  it("mappe items + pagination en camelCase", () => {
    const raw = {
      items: [summary()],
      pagination: { page: 1, per_page: 6, total: 1, total_pages: 1 },
    }
    const result = mapArticleListResponse(raw)
    expect(result.pagination.perPage).toBe(6)
    expect(result.pagination.totalPages).toBe(1)
    expect(result.items[0]!.publishedAt).toBe(VALID_DATE)
    expect(result.items[0]!.expertiseIds).toEqual(["concevoir"])
  })

  it("refuse un payload racine non-objet", () => {
    expect(() => mapArticleListResponse(null)).toThrow(EditorialContractError)
  })

  it("refuse un slug malformé", () => {
    const raw = {
      items: [summary({ slug: "Slug Avec Espace" })],
      pagination: { page: 1, per_page: 6, total: 1, total_pages: 1 },
    }
    expect(() => mapArticleListResponse(raw)).toThrow(/slug/i)
  })

  it("refuse une date non ISO 8601", () => {
    const raw = {
      items: [summary({ published_at: "15/01/2026" })],
      pagination: { page: 1, per_page: 6, total: 1, total_pages: 1 },
    }
    expect(() => mapArticleListResponse(raw)).toThrow(/ISO 8601/)
  })

  it("refuse un author.type inconnu", () => {
    const raw = {
      items: [summary({ author: { name: "X", type: "robot" } })],
      pagination: { page: 1, per_page: 6, total: 1, total_pages: 1 },
    }
    expect(() => mapArticleListResponse(raw)).toThrow(/author\.type/)
  })
})

describe("mapArticleDetailResponse", () => {
  it("mappe le détail avec contentHtml et bodyMarkdown", () => {
    const result = mapArticleDetailResponse(detail())
    expect(result.contentHtml).toContain("<h2>Titre</h2>")
    expect(result.bodyMarkdown).toBe("# Titre\nCorps")
    expect(result.seo.title).toBe("SEO Titre")
  })

  it("refuse un content_html manquant (frontière de confiance requiert HTML)", () => {
    const raw = detail()
    delete raw.content_html
    expect(() => mapArticleDetailResponse(raw)).toThrow(/content_html/)
  })

  it("accepte un body_markdown vide (contentHtml reste la source d'affichage)", () => {
    const result = mapArticleDetailResponse(detail({ body_markdown: "" }))
    expect(result.bodyMarkdown).toBe("")
    expect(result.contentHtml).toContain("<h2>")
  })

  it("refuse un objet seo absent", () => {
    const raw = detail()
    delete raw.seo
    expect(() => mapArticleDetailResponse(raw)).toThrow(/seo/)
  })
})
