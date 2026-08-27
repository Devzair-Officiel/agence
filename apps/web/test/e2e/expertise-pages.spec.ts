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

test.describe("Hydratation SSR ↔ client sur /expertises/concevoir", () => {
  // Garde-fou anti-régression pour un bug historique de mismatch éditorial
  // (dates formatées sans `timeZone`) : le SSR (Docker/Nitro en `UTC`) et
  // le client (navigateur en `Europe/Paris`) produisaient deux textes
  // différents pour un même ISO à cheval sur minuit UTC, ce que Vue
  // signale par un warning « Hydration text content mismatch » ou
  // « Hydration completed but contains mismatches ».
  //
  // Méthode : brancher les listeners AVANT `page.goto` (sinon les
  // premiers warnings émis pendant l'hydratation sont perdus), attendre
  // le marqueur `data-hydrated="true"` posé par `SiteHeader.vue` puis
  // `networkidle` pour laisser tous les composants finir leur hydratation,
  // et échouer si un seul message contient un signal de mismatch.
  test("ne déclenche aucun avertissement de mismatch d'hydratation", async ({
    page,
  }) => {
    const hydrationSignals: string[] = []
    const capture = (message: string) => {
      if (
        message.includes("Hydration") ||
        message.includes("hydration mismatch") ||
        message.includes("contains mismatches")
      ) {
        hydrationSignals.push(message)
      }
    }
    page.on("console", (msg) => {
      if (msg.type() === "warning" || msg.type() === "error") {
        capture(msg.text())
      }
    })
    page.on("pageerror", (err) => capture(err.message))

    await page.goto("/expertises/concevoir", { waitUntil: "networkidle" })
    // Le marqueur `data-hydrated="true"` est posé dans `onMounted` par
    // `SiteHeader.vue`. Sur viewport desktop le bouton menu mobile est
    // masqué par CSS (`display: none`), donc `toBeVisible` échouerait ;
    // on attend uniquement l'attribut, qui est indépendant de la
    // visibilité et signale la fin d'hydratation du header.
    const hydrationMarker = page.locator(
      'button[aria-controls="mobile-navigation"]',
    )
    await expect(hydrationMarker).toHaveAttribute("data-hydrated", "true", {
      timeout: 30_000,
    })

    expect(
      hydrationSignals,
      `Vue a signalé un mismatch d'hydratation :\n${hydrationSignals.join("\n")}`,
    ).toEqual([])
  })
})

test.describe("Direction propre à /expertises/concevoir (Digital Blueprint)", () => {
  test("expose les cinq étapes de la méthode dans l'ordre exact", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/concevoir")
    const steps = ["Objectifs", "Structure", "Parcours", "Interface", "Système"]
    let lastIndex = -1
    for (const label of steps) {
      const idx = body.indexOf(label)
      expect(idx, `${label} présent en SSR`).toBeGreaterThanOrEqual(0)
      expect(idx, `${label} après ${steps[steps.indexOf(label) - 1] ?? "start"}`).toBeGreaterThan(
        lastIndex,
      )
      lastIndex = idx
    }
  })

  test("porte les titres validés du brief éditorial", async ({ request }) => {
    const { body } = await fetchSSR(request, "/expertises/concevoir")
    expect(body).toMatch(/Avant de construire, il faut d(?:é|&#233;|&eacute;)cider\./)
    expect(body).toMatch(/Du flou au syst(?:è|&#232;|&egrave;)me\./)
    expect(body).toMatch(/Vous avez le projet\. Construisons d/)
  })

  test("expose les CTA validés du brief éditorial", async ({ page }) => {
    await page.goto("/expertises/concevoir")
    await expect(page.getByRole("link", { name: /Parler de votre projet/i }).first()).toBeVisible()
    await expect(page.getByRole("link", { name: /D(?:é|e)couvrir Construire/i }).first()).toBeVisible()
    await expect(page.locator('a[href="/expertises/construire"]').first()).toBeVisible()
  })

  test("rend le visuel blueprint comme purement décoratif (aria-hidden)", async ({
    page,
  }) => {
    await page.goto("/expertises/concevoir")
    const blueprint = page.locator(".concevoir-blueprint")
    await expect(blueprint).toHaveCount(1)
    await expect(blueprint).toHaveAttribute("aria-hidden", "true")
    const svg = blueprint.locator("svg")
    await expect(svg).toHaveAttribute("aria-hidden", "true")
  })

  test("rend la carte de processus dans une vraie liste ordonnée", async ({ page }) => {
    await page.goto("/expertises/concevoir")
    const list = page.locator(".concevoir-process__list")
    await expect(list).toHaveCount(1)
    const items = list.locator(".concevoir-process__item")
    await expect(items).toHaveCount(5)
    // Le premier item porte bien le titre de la première étape validée.
    await expect(items.first().locator("h3")).toHaveText("Objectifs")
  })
})

test.describe("Direction propre à /expertises/construire (Product Assembly)", () => {
  test("porte le H2 audience validé « Quand le projet doit devenir un outil. »", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/construire")
    expect(body).toMatch(/Quand le projet doit devenir un outil\./)
  })

  test("porte le H2 approche validé « Construire par couches. Valider à chaque étape. »", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/construire")
    expect(body).toMatch(
      /Construire par couches\. Valider (?:à|&#224;|&agrave;) chaque (?:é|&#233;|&eacute;)tape\./,
    )
  })

  test("expose les cinq étapes du pipeline dans l'ordre exact (Socle → Validation)", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/construire")
    const steps = ["Socle", "Interfaces", "Logique métier", "Connexions", "Validation"]
    let lastIndex = -1
    for (const label of steps) {
      const idx = body.indexOf(label)
      expect(idx, `${label} présent en SSR`).toBeGreaterThanOrEqual(0)
      expect(
        idx,
        `${label} après ${steps[steps.indexOf(label) - 1] ?? "start"}`,
      ).toBeGreaterThan(lastIndex)
      lastIndex = idx
    }
  })

  test("rend le pipeline de fabrication dans une vraie liste ordonnée", async ({
    page,
  }) => {
    await page.goto("/expertises/construire")
    const list = page.locator(".construire-flow__list")
    await expect(list).toHaveCount(1)
    const items = list.locator(".construire-flow__item")
    await expect(items).toHaveCount(5)
    await expect(items.first().locator("h3")).toHaveText("Socle")
  })

  test("rend le visuel système comme purement décoratif (aria-hidden)", async ({
    page,
  }) => {
    await page.goto("/expertises/construire")
    const visual = page.locator(".construire-system")
    await expect(visual).toHaveCount(1)
    await expect(visual).toHaveAttribute("aria-hidden", "true")
    const svg = visual.locator("svg")
    await expect(svg).toHaveAttribute("aria-hidden", "true")
  })

  test("expose les trois situations d'audience Présenter / Vendre / Organiser", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/construire")
    for (const verb of ["Présenter", "Vendre", "Organiser"]) {
      expect(body).toContain(verb)
    }
  })

  test("ne déborde pas à 1920 px (revue visuelle desktop dédiée)", async ({
    page,
  }) => {
    await page.goto("/expertises/construire")
    await page.setViewportSize({ width: 1920, height: 1080 })
    const overflow = await page.evaluate(
      () =>
        document.documentElement.scrollWidth -
        document.documentElement.clientWidth,
    )
    expect(overflow, "overflow @1920").toBeLessThanOrEqual(1)
  })

  test("sépare visuellement le panneau CTA du footer navy-deep", async ({
    page,
  }) => {
    await page.goto("/expertises/construire")
    const panel = page.locator(".construire-callout__panel")
    await expect(panel).toHaveCount(1)
    const panelBg = await panel.evaluate((el) =>
      window.getComputedStyle(el).backgroundColor,
    )
    const footer = page.locator("footer.site-footer")
    await expect(footer).toBeVisible()
    const footerBg = await footer.evaluate((el) =>
      window.getComputedStyle(el).backgroundColor,
    )
    expect(panelBg, "panneau CTA et footer doivent avoir des fonds distincts").not.toBe(
      footerBg,
    )
  })

  test("rend le CTA secondaire cream lisible sur le panneau navy (computed styles)", async ({
    page,
  }) => {
    await page.goto("/expertises/construire")
    const btn = page
      .locator('a[href="/expertises/concevoir"]')
      .filter({ hasText: /D(?:é|e)couvrir Concevoir/i })
      .first()
    await btn.scrollIntoViewIfNeeded()

    const rest = await btn.evaluate((el) => {
      const cs = window.getComputedStyle(el)
      return {
        color: cs.color,
        backgroundColor: cs.backgroundColor,
        borderTopColor: cs.borderTopColor,
        borderTopWidth: cs.borderTopWidth,
      }
    })

    const cream = "rgb(244, 241, 234)"

    // Texte cream — pas d'ink sombre (l'ancienne valeur était rgb(22, 25, 28)).
    expect(rest.color).toBe(cream)
    // Fond transparent.
    expect(rest.backgroundColor).toBe("rgba(0, 0, 0, 0)")
    // Bordure cream franche — pas la border-strong ink à 28 % qui disparaissait
    // sur le panneau navy.
    expect(rest.borderTopColor).toBe(cream)
    expect(rest.borderTopWidth).not.toBe("0px")

    await btn.hover()
    // Laisser la transition CSS (color/background) se poser avant lecture.
    await page.waitForTimeout(200)
    const hover = await btn.evaluate((el) => {
      const cs = window.getComputedStyle(el)
      return {
        color: cs.color,
        backgroundColor: cs.backgroundColor,
      }
    })
    // Hover : fond cream + texte sombre (navy-elevated ≈ rgb(20, 30, 44) —
    // on tolère le calcul RGB proche via un test structurel : pas cream, pas
    // transparent).
    expect(hover.backgroundColor).not.toBe("rgba(0, 0, 0, 0)")
    expect(hover.color).not.toBe(cream)

    await page.mouse.move(0, 0)
    await btn.focus()
    await page.waitForTimeout(100)
    const focus = await btn.evaluate((el) => {
      const cs = window.getComputedStyle(el)
      return {
        outlineColor: cs.outlineColor,
        outlineWidth: cs.outlineWidth,
        outlineStyle: cs.outlineStyle,
      }
    })
    expect(focus.outlineColor).toBe(cream)
    expect(focus.outlineStyle).toBe("solid")
    expect(focus.outlineWidth).not.toBe("0px")
  })

  test("expose le CTA final validé « Vous avez le projet. Construisons le produit. »", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/construire")
    expect(body).toMatch(
      /Vous avez le projet\. Construisons le produit\./,
    )
  })

  test("expose les CTA validés (Nous parler du projet + Découvrir Concevoir)", async ({
    page,
  }) => {
    await page.goto("/expertises/construire")
    await expect(
      page.getByRole("link", { name: /Nous parler du projet/i }).first(),
    ).toBeVisible()
    await expect(
      page.getByRole("link", { name: /D(?:é|e)couvrir Concevoir/i }).first(),
    ).toBeVisible()
    await expect(
      page.locator('a[href="/expertises/concevoir"]').first(),
    ).toBeVisible()
    await expect(
      page.locator('a[href="/expertises/faire-evoluer"]').first(),
    ).toBeVisible()
  })

  test("ne déclenche aucun avertissement de mismatch d'hydratation", async ({
    page,
  }) => {
    const hydrationSignals: string[] = []
    const capture = (message: string) => {
      if (
        message.includes("Hydration") ||
        message.includes("hydration mismatch") ||
        message.includes("contains mismatches")
      ) {
        hydrationSignals.push(message)
      }
    }
    page.on("console", (msg) => {
      if (msg.type() === "warning" || msg.type() === "error") {
        capture(msg.text())
      }
    })
    page.on("pageerror", (err) => capture(err.message))

    await page.goto("/expertises/construire", { waitUntil: "networkidle" })
    const hydrationMarker = page.locator(
      'button[aria-controls="mobile-navigation"]',
    )
    await expect(hydrationMarker).toHaveAttribute("data-hydrated", "true", {
      timeout: 30_000,
    })

    expect(
      hydrationSignals,
      `Vue a signalé un mismatch d'hydratation :\n${hydrationSignals.join("\n")}`,
    ).toEqual([])
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

  test("depuis Construire, un lien /expertises reste atteignable (breadcrumb ou footer)", async ({
    page,
  }) => {
    await page.goto("/expertises/construire")
    // Le callout Construire ne renvoie plus vers /expertises (secondaire =
    // /expertises/concevoir, choix éditorial du brief pour refermer la
    // boucle du cycle). Le lien vers l'ombrelle des pôles reste néanmoins
    // atteignable ailleurs sur la page (breadcrumb en tête, footer en pied).
    const links = page.locator('a[href="/expertises"]')
    await expect(links.first()).toBeVisible()
    await links.first().click()
    await expect(page).toHaveURL(/\/expertises\/?$/)
    await expect(page.locator("h1")).toHaveText(
      "Cinq pôles complémentaires pour construire une présence digitale cohérente.",
    )
  })
})
