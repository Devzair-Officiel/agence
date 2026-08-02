import { beforeEach, describe, expect, it, vi } from "vitest"
import { usePageSeo } from "~/composables/usePageSeo"

// Contexte des tests :
//
// usePageSeo dépend de trois composables Nuxt auto-importés (useRuntimeConfig,
// useSeoMeta, useHead). On les stubbe ici pour ne tester QUE la logique de
// composition SEO (canonical, ogUrl, robots, image).
//
// Aucune assertion ne dépend d'un dispatch Nuxt réel : on capture les objets
// passés à useSeoMeta et useHead, puis on les compare aux attentes.

interface CaptureBag {
  seo: Record<string, unknown> | null
  head: Record<string, unknown> | null
}

function installStubs(siteUrl: string, indexable: boolean): CaptureBag {
  const captures: CaptureBag = { seo: null, head: null }

  const globals = globalThis as unknown as {
    useRuntimeConfig: () => unknown
    useSeoMeta: (input: Record<string, unknown>) => void
    useHead: (input: Record<string, unknown>) => void
  }

  globals.useRuntimeConfig = vi.fn(() => ({
    public: { siteUrl, siteIndexable: indexable },
  }))
  globals.useSeoMeta = vi.fn((input: Record<string, unknown>) => {
    captures.seo = input
  })
  globals.useHead = vi.fn((input: Record<string, unknown>) => {
    captures.head = input
  })

  return captures
}

describe("usePageSeo — page indexable", () => {
  let captures: CaptureBag
  beforeEach(() => {
    captures = installStubs("https://devzair.fr", true)
  })

  it("émet un canonical absolu et les OG cohérents pour la racine", () => {
    usePageSeo({
      title: "Devzair — Agence digitale",
      description: "Nous concevons des sites, applications et stratégies.",
      path: "/",
    })

    expect(captures.seo).toMatchObject({
      title: "Devzair — Agence digitale",
      description: "Nous concevons des sites, applications et stratégies.",
      ogTitle: "Devzair — Agence digitale",
      ogDescription: "Nous concevons des sites, applications et stratégies.",
      ogType: "website",
      ogUrl: "https://devzair.fr/",
      ogSiteName: "Devzair",
      ogLocale: "fr_FR",
      twitterCard: "summary",
    })
    expect(captures.seo).not.toHaveProperty("robots")
    expect(captures.head).toEqual({
      link: [{ rel: "canonical", href: "https://devzair.fr/" }],
    })
  })

  it("supprime les paramètres de tracking du canonical", () => {
    usePageSeo({
      title: "Services",
      description: "Offre Devzair",
      path: "/services?utm_source=x",
    })
    expect(captures.head).toEqual({
      link: [{ rel: "canonical", href: "https://devzair.fr/services" }],
    })
  })

  it("passe une image OG absolue à partir d'un chemin relatif", () => {
    usePageSeo({
      title: "Article",
      description: "Résumé",
      path: "/ressources/mon-article",
      image: "/og/mon-article.png",
      imageAlt: "Illustration",
      type: "article",
    })
    expect(captures.seo).toMatchObject({
      ogType: "article",
      ogImage: "https://devzair.fr/og/mon-article.png",
      ogImageAlt: "Illustration",
      twitterCard: "summary_large_image",
      twitterImage: "https://devzair.fr/og/mon-article.png",
    })
  })

  it("laisse une URL d'image déjà absolue intacte", () => {
    usePageSeo({
      title: "T",
      description: "D",
      path: "/",
      image: "https://cdn.example.com/x.png",
    })
    expect(captures.seo).toMatchObject({
      ogImage: "https://cdn.example.com/x.png",
    })
  })
})

describe("usePageSeo — page noindex", () => {
  let captures: CaptureBag
  beforeEach(() => {
    captures = installStubs("https://devzair.fr", true)
  })

  it("force robots noindex, nofollow et n'émet PAS de canonical", () => {
    usePageSeo({
      title: "Design preview",
      description: "Page interne",
      path: "/design-preview",
      noindex: true,
    })
    expect(captures.seo).toMatchObject({
      robots: "noindex, nofollow",
    })
    // Head reste null : un canonical sur une page noindex ne fait que semer
    // le doute pour un moteur qui l'aurait crawlée par accident.
    expect(captures.head).toBeNull()
  })
})

describe("usePageSeo — directive robots explicite", () => {
  let captures: CaptureBag
  beforeEach(() => {
    captures = installStubs("https://devzair.fr", true)
  })

  it("laisse la directive explicite prendre le dessus sur noindex par défaut", () => {
    usePageSeo({
      title: "T",
      description: "D",
      path: "/services",
      robots: "index, nofollow",
    })
    expect(captures.seo).toMatchObject({ robots: "index, nofollow" })
    // La page reste indexable via canonical : robots explicite ≠ noindex.
    expect(captures.head).toEqual({
      link: [{ rel: "canonical", href: "https://devzair.fr/services" }],
    })
  })
})

describe("usePageSeo — canonical hors localhost", () => {
  let captures: CaptureBag
  beforeEach(() => {
    captures = installStubs("http://localhost:3001", false)
  })

  it("respecte l'origin fourni sans forcer https", () => {
    usePageSeo({ title: "T", description: "D", path: "/services" })
    expect(captures.head).toEqual({
      link: [{ rel: "canonical", href: "http://localhost:3001/services" }],
    })
  })
})
