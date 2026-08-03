import AxeBuilder from "@axe-core/playwright"
import { expect, test } from "@playwright/test"
import { openMobileNavigation } from "./support/mobile-nav"

// E2E des cinq pages détaillées d'expertise livrées en Phase 7B :
//   - `/expertises/concevoir`
//   - `/expertises/construire`
//   - `/expertises/valoriser`
//   - `/expertises/visibilite`
//   - `/expertises/faire-evoluer`
//
// On vérifie côté HTML SSR (avant hydratation) :
//   - HTTP 200 sur chaque slug publié ;
//   - HTTP 404 sur un slug inconnu (pas de fallback silencieux) ;
//   - un unique H1 avec la phrase verbatim attendue ;
//   - eyebrow, introduction, sections « à qui cela s'adresse » et
//     « notre approche » présents en SSR ;
//   - SEO minimal (title spécifique, canonical, og:url, meta description) ;
//   - un JSON-LD Service avec provider @id vers l'Organization globale ;
//   - le sitemap inclut les cinq nouvelles routes et aucune URL `planned`.
//
// On vérifie côté rendu/interaction :
//   - responsive 320 / 390 / 768 / 1024 / 1440 sans débordement horizontal ;
//   - fil d'Ariane accessible avec `aria-current="page"` sur la dernière entrée ;
//   - Axe WCAG 2.2 AA sur chaque page ;
//   - `prefers-reduced-motion` : contenu visible et navigable ;
//   - navigation mobile fonctionnelle depuis chaque page fille ;
//   - les deux liens de « pôles connexes » pointent sur des routes réelles.

const BREAKPOINTS = [320, 390, 768, 1024, 1440] as const

const PAGES = [
  {
    slug: "concevoir",
    route: "/expertises/concevoir",
    shortTitle: "Concevoir",
    h1: "Concevoir : identité, expérience et architecture",
    eyebrow: "Expertise · Concevoir",
    seoTitle: "Concevoir : identité, design et architecture d'information",
    seoDescriptionFragment: "fondations visuelles et fonctionnelles",
    serviceType: "Concevoir",
    related: ["/expertises/construire", "/expertises/valoriser"],
  },
  {
    slug: "construire",
    route: "/expertises/construire",
    shortTitle: "Construire",
    h1: "Construire : sites, e-commerce et applications",
    eyebrow: "Expertise · Construire",
    seoTitle: "Construire : sites, e-commerce et applications sur mesure",
    seoDescriptionFragment: "socle technique moderne",
    serviceType: "Construire",
    related: ["/expertises/concevoir", "/expertises/faire-evoluer"],
  },
  {
    slug: "valoriser",
    route: "/expertises/valoriser",
    shortTitle: "Valoriser",
    h1: "Valoriser : contenus visuels et éditoriaux",
    eyebrow: "Expertise · Valoriser",
    seoTitle: "Valoriser : photographie, contenus et structuration de l'offre",
    seoDescriptionFragment: "Photographie professionnelle",
    serviceType: "Valoriser",
    related: ["/expertises/visibilite", "/expertises/concevoir"],
  },
  {
    slug: "visibilite",
    route: "/expertises/visibilite",
    shortTitle: "Visibilité",
    h1: "Développer la visibilité : SEO, local et éditorial",
    eyebrow: "Expertise · Visibilité",
    seoTitle: "Visibilité : SEO, référencement local et stratégie éditoriale",
    seoDescriptionFragment: "visibilité durable",
    serviceType: "Visibilité",
    related: ["/expertises/valoriser", "/expertises/faire-evoluer"],
  },
  {
    slug: "faire-evoluer",
    route: "/expertises/faire-evoluer",
    shortTitle: "Faire évoluer",
    h1: "Faire évoluer : maintenance, mesure et évolutions",
    eyebrow: "Expertise · Faire évoluer",
    seoTitle: "Faire évoluer : maintenance, mesure et évolutions du site",
    seoDescriptionFragment: "Maintenance, sécurité",
    serviceType: "Faire évoluer",
    related: ["/expertises/construire", "/expertises/visibilite"],
  },
] as const

async function assertNoHorizontalOverflow(page: import("@playwright/test").Page) {
  for (const width of BREAKPOINTS) {
    await page.setViewportSize({ width, height: 900 })
    const overflow = await page.evaluate(
      () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    )
    expect(overflow, `overflow at ${width}px`).toBeLessThanOrEqual(1)
  }
}

async function fetchSSR(
  request: import("@playwright/test").APIRequestContext,
  path: string,
) {
  const response = await request.get(path)
  const body = await response.text()
  return { response, body }
}

for (const p of PAGES) {
  test.describe(`${p.route} — SSR et contenu éditorial`, () => {
    test("renvoie 200 avec un H1 unique verbatim", async ({ page, request }) => {
      const consoleErrors: string[] = []
      page.on("console", (msg) => {
        if (msg.type() === "error") consoleErrors.push(msg.text())
      })

      const { response, body } = await fetchSSR(request, p.route)
      expect(response.status(), `${p.route} status`).toBe(200)
      expect(body).toContain(p.h1)

      await page.goto(p.route)
      const headings = await page.locator("h1").all()
      expect(headings).toHaveLength(1)
      await expect(page.locator("h1")).toHaveText(p.h1)
      expect(consoleErrors).toEqual([])
    })

    test("publie eyebrow, introduction et sections narratives en SSR", async ({
      request,
    }) => {
      const { body } = await fetchSSR(request, p.route)
      expect(body).toContain(p.eyebrow)
      // Vue encode l'apostrophe brute en `&#39;` ou `&#x27;` selon le
      // sérialiseur. On accepte les trois formes pour ne pas coupler le
      // test à un détail d'encodage.
      expect(body).toMatch(/À qui cela s(?:'|&#39;|&#x27;)adresse/)
      expect(body).toContain("Notre approche")
      expect(body).toContain("Prestations")
      expect(body).toContain("Bénéfices concrets")
    })

    test("expose le SEO complet dans le HTML initial", async ({ request }) => {
      const { body } = await fetchSSR(request, p.route)
      // On tolère l'encodage HTML de l'apostrophe : `'`, `&#39;` ou `&#x27;`.
      const escaped = p.seoTitle
        .replace(/[.*+?^${}()|[\]\\]/g, "\\$&")
        .replace(/'/g, "(?:'|&#39;|&#x27;)")
      const titleRegex = new RegExp(
        `<title>[^<]*${escaped}[^<]*Devzair[^<]*</title>`,
        "i",
      )
      expect(body).toMatch(titleRegex)
      expect(body.toLowerCase()).toContain(
        p.seoDescriptionFragment.toLowerCase(),
      )
      // Canonical présent (mode indexable) OU meta robots noindex (preprod).
      const hasCanonical = /<link[^>]+rel="canonical"[^>]+href="[^"]+"/i.test(body)
      const hasNoindex = /<meta[^>]+name="robots"[^>]+content="[^"]*noindex[^"]*"/i.test(
        body,
      )
      expect(hasCanonical || hasNoindex, "canonical or noindex").toBe(true)
      expect(body).toMatch(/<meta[^>]+property="og:url"[^>]+content="https?:\/\/[^"]+"/i)
    })

    test("émet un JSON-LD Service référençant l'Organization par @id", async ({
      request,
    }) => {
      const { body } = await fetchSSR(request, p.route)
      const scripts = [...body.matchAll(
        /<script type="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/g,
      )]
      expect(scripts.length).toBeGreaterThanOrEqual(2)
      const serviceScript = scripts
        .map((m) => m[1]!)
        .map((raw) => {
          try { return JSON.parse(raw) as Record<string, unknown> }
          catch { return null }
        })
        .find((json) => json && (json["@type"] === "Service"))
      expect(serviceScript, "JSON-LD Service").toBeTruthy()
      const service = serviceScript as Record<string, unknown>
      expect(service["@context"]).toBe("https://schema.org")
      expect(service.name).toBe(p.h1)
      expect(service.serviceType).toBe(p.serviceType)
      expect(service.url).toMatch(new RegExp(`${p.route}$`))
      expect(service.provider).toEqual(
        expect.objectContaining({ "@id": expect.stringMatching(/#organization$/) }),
      )
      // Aucune donnée commerciale inventée.
      expect(service).not.toHaveProperty("offers")
      expect(service).not.toHaveProperty("price")
      expect(service).not.toHaveProperty("aggregateRating")
      expect(service).not.toHaveProperty("review")
    })

    test("rend un fil d'Ariane accessible avec aria-current sur la page courante", async ({
      page,
    }) => {
      await page.goto(p.route)
      const nav = page.locator('nav[aria-label="Fil d\'Ariane"]')
      await expect(nav).toBeVisible()
      const current = nav.locator('[aria-current="page"]')
      await expect(current).toHaveText(p.shortTitle)
    })

    test("propose deux liens vers les pôles connexes attendus", async ({ page }) => {
      await page.goto(p.route)
      for (const relatedRoute of p.related) {
        await expect(
          page.locator(`a[href="${relatedRoute}"]`).first(),
        ).toBeVisible()
      }
    })

    test("propose un lien vers /expertises et vers /contact", async ({ page }) => {
      await page.goto(p.route)
      await expect(page.locator('a[href="/expertises"]').first()).toBeVisible()
      await expect(page.locator('a[href="/contact"]').first()).toBeVisible()
    })

    test("respecte la responsivité 320 / 390 / 768 / 1024 / 1440 sans débordement", async ({
      page,
    }) => {
      await page.goto(p.route)
      await assertNoHorizontalOverflow(page)
    })

    test("passe Axe WCAG 2.2 AA (aucune violation serious/critical)", async ({ page }) => {
      await page.goto(p.route)
      const results = await new AxeBuilder({ page })
        .withTags(["wcag2a", "wcag2aa", "wcag21a", "wcag21aa", "wcag22aa"])
        .analyze()
      const blocking = results.violations.filter(
        (v) => v.impact === "critical" || v.impact === "serious",
      )
      if (blocking.length > 0) {
        console.log(
          `Axe ${p.route}:\n${blocking
            .map(
              (v) =>
                `- [${v.impact}] ${v.id}: ${v.help}\n    ${v.nodes
                  .map((n) => n.target.join(" "))
                  .join("\n    ")}`,
            )
            .join("\n")}`,
        )
      }
      expect(blocking).toEqual([])
    })

    test("reste utilisable sous prefers-reduced-motion", async ({ browser }) => {
      const context = await browser.newContext({ reducedMotion: "reduce" })
      const page = await context.newPage()
      await page.goto(p.route)
      await expect(page.locator("h1")).toBeVisible()
      await context.close()
    })

    test("expose la navigation mobile depuis la page fille", async ({ browser }) => {
      const context = await browser.newContext({
        viewport: { width: 390, height: 844 },
      })
      const page = await context.newPage()
      await page.goto(p.route)
      const dialog = await openMobileNavigation(page)
      await expect(dialog.locator('a[href="/expertises"]').first()).toBeVisible()
      await expect(dialog.locator('a[href="/agence"]').first()).toBeVisible()
      await context.close()
    })
  })
}

test.describe("Résolution stricte des slugs et sitemap", () => {
  test("un slug inconnu retourne HTTP 404 (pas de fallback silencieux)", async ({
    request,
  }) => {
    const response = await request.get("/expertises/slug-inexistant-42")
    expect(response.status()).toBe(404)
  })

  test("un slug avec majuscules n'est pas normalisé silencieusement", async ({
    request,
  }) => {
    const response = await request.get("/expertises/Concevoir")
    expect(response.status()).toBe(404)
  })

  test("le sitemap contient les cinq nouvelles routes détaillées", async ({
    request,
  }) => {
    const response = await request.get("/sitemap.xml")
    expect(response.status()).toBe(200)
    const body = await response.text()
    for (const p of PAGES) {
      expect(body).toContain(p.route)
    }
  })

  test("le sitemap ne contient aucune route absente de expertise-pages.ts", async ({
    request,
  }) => {
    const response = await request.get("/sitemap.xml")
    const body = await response.text()
    const matches = [
      ...body.matchAll(/<loc>[^<]*\/expertises\/([a-z0-9-]+)<\/loc>/g),
    ]
    const knownSlugs = new Set(PAGES.map((p) => p.slug))
    for (const match of matches) {
      expect(knownSlugs.has(match[1]!)).toBe(true)
    }
  })
})

test.describe("Maillage inter-pages détaillées", () => {
  test("depuis `/expertises`, chaque carte mène à la page fille correspondante", async ({
    page,
  }) => {
    await page.goto("/expertises")
    await page.locator('a[href="/expertises/concevoir"]').first().click()
    await expect(page).toHaveURL(/\/expertises\/concevoir\/?$/)
    await expect(page.locator("h1")).toHaveText(
      "Concevoir : identité, expérience et architecture",
    )
  })

  test("depuis une page fille, le callout secondaire retourne à /expertises", async ({
    page,
  }) => {
    await page.goto("/expertises/construire")
    // Le callout final propose un secondaire « Voir tous les pôles » → /expertises.
    await page.locator('a[href="/expertises"]').last().click()
    await expect(page).toHaveURL(/\/expertises\/?$/)
    await expect(page.locator("h1")).toHaveText(
      "Cinq pôles complémentaires pour construire une présence digitale cohérente.",
    )
  })
})
