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

const VALID_HERO_IMAGE = {
  url: "/api/media/0193b1a0-1c7d-7000-8000-000000000001",
  alt: "Équipe collaborant devant un écran.",
  width: 1600,
  height: 900,
  mime_type: "image/jpeg",
  card: null,
  hero: null,
}

const VALID_CARD_VARIANT = {
  url: "/api/media/0193b1a0-1c7d-7000-8000-000000000001/card",
  width: 768,
  height: 432,
}

const VALID_HERO_VARIANT = {
  url: "/api/media/0193b1a0-1c7d-7000-8000-000000000001/hero",
  width: 1600,
  height: 900,
}

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
    hero_image: null,
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

describe("mapping hero_image (Phase 9B)", () => {
  it("mappe hero_image null en heroImage null (contrat par défaut)", () => {
    const result = mapArticleDetailResponse(detail({ hero_image: null }))
    expect(result.heroImage).toBeNull()
  })

  it("mappe hero_image complet en heroImage camelCase avec dimensions (variants null pour legacy)", () => {
    const result = mapArticleDetailResponse(detail({ hero_image: VALID_HERO_IMAGE }))
    expect(result.heroImage).toEqual({
      url: "/api/media/0193b1a0-1c7d-7000-8000-000000000001",
      alt: "Équipe collaborant devant un écran.",
      width: 1600,
      height: 900,
      mimeType: "image/jpeg",
      card: null,
      hero: null,
    })
  })

  it("propage hero_image sur les items de liste", () => {
    const raw = {
      items: [summary({ hero_image: VALID_HERO_IMAGE })],
      pagination: { page: 1, per_page: 6, total: 1, total_pages: 1 },
    }
    const result = mapArticleListResponse(raw)
    expect(result.items[0]!.heroImage?.url).toBe(VALID_HERO_IMAGE.url)
    expect(result.items[0]!.heroImage?.mimeType).toBe("image/jpeg")
  })

  it("refuse l'absence de la clé hero_image (contrat explicite null)", () => {
    const raw = detail()
    delete raw.hero_image
    expect(() => mapArticleDetailResponse(raw)).toThrow(/hero_image/)
  })

  it("refuse une url hors du pattern /api/media/{uuid}", () => {
    const raw = detail({
      hero_image: { ...VALID_HERO_IMAGE, url: "https://cdn.tiers.example/media/abc.jpg" },
    })
    expect(() => mapArticleDetailResponse(raw)).toThrow(/hero_image\.url/)
  })

  it("refuse une largeur non entière", () => {
    const raw = detail({
      hero_image: { ...VALID_HERO_IMAGE, width: 1600.5 },
    })
    expect(() => mapArticleDetailResponse(raw)).toThrow(/width/)
  })

  it("refuse un alt vide (a11y non négociable)", () => {
    const raw = detail({
      hero_image: { ...VALID_HERO_IMAGE, alt: "" },
    })
    expect(() => mapArticleDetailResponse(raw)).toThrow(/alt/)
  })
})

describe("mapping hero_image variants WebP (Phase 9C)", () => {
  it("mappe card et hero quand ils sont présents", () => {
    const raw = detail({
      hero_image: {
        ...VALID_HERO_IMAGE,
        card: VALID_CARD_VARIANT,
        hero: VALID_HERO_VARIANT,
      },
    })
    const result = mapArticleDetailResponse(raw)
    expect(result.heroImage?.card).toEqual({ url: VALID_CARD_VARIANT.url, width: 768, height: 432 })
    expect(result.heroImage?.hero).toEqual({ url: VALID_HERO_VARIANT.url, width: 1600, height: 900 })
  })

  it("accepte card null et hero null (asset legacy sans variants)", () => {
    const result = mapArticleDetailResponse(detail({ hero_image: { ...VALID_HERO_IMAGE, card: null, hero: null } }))
    expect(result.heroImage?.card).toBeNull()
    expect(result.heroImage?.hero).toBeNull()
  })

  it("accepte hero_image sans clé card/hero (backend legacy antérieur 9C)", () => {
    const { card: _c, hero: _h, ...legacyImage } = VALID_HERO_IMAGE
    const result = mapArticleDetailResponse(detail({ hero_image: legacyImage }))
    expect(result.heroImage?.card).toBeNull()
    expect(result.heroImage?.hero).toBeNull()
  })

  it("refuse une url de variant hors pattern /api/media/{uuid}/card|hero", () => {
    const raw = detail({
      hero_image: {
        ...VALID_HERO_IMAGE,
        card: { ...VALID_CARD_VARIANT, url: "https://cdn.example.com/card.webp" },
      },
    })
    expect(() => mapArticleDetailResponse(raw)).toThrow(/hero_image\.card\.url/)
  })

  it("refuse une largeur de variant non entière", () => {
    const raw = detail({
      hero_image: {
        ...VALID_HERO_IMAGE,
        card: { ...VALID_CARD_VARIANT, width: 768.5 },
      },
    })
    expect(() => mapArticleDetailResponse(raw)).toThrow(/width/)
  })

  it("refuse une url /hero dans le champ card (rejet croisé)", () => {
    const raw = detail({
      hero_image: {
        ...VALID_HERO_IMAGE,
        card: { ...VALID_CARD_VARIANT, url: VALID_HERO_VARIANT.url },
        hero: VALID_HERO_VARIANT,
      },
    })
    expect(() => mapArticleDetailResponse(raw)).toThrow(/hero_image\.card\.url/)
  })

  it("refuse une url /card dans le champ hero (rejet croisé)", () => {
    const raw = detail({
      hero_image: {
        ...VALID_HERO_IMAGE,
        card: VALID_CARD_VARIANT,
        hero: { ...VALID_HERO_VARIANT, url: VALID_CARD_VARIANT.url },
      },
    })
    expect(() => mapArticleDetailResponse(raw)).toThrow(/hero_image\.hero\.url/)
  })
})
