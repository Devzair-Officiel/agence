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

test.describe("Direction propre à /expertises/valoriser (Editorial Studio)", () => {
  test("porte le H2 audience validé « Quand votre savoir-faire mérite d'être mieux montré. »", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/valoriser")
    expect(body).toMatch(
      /Quand votre savoir-faire m(?:é|&#233;|&eacute;)rite d(?:'|&#39;|&apos;)(?:ê|&#234;|&ecirc;)tre mieux montr(?:é|&#233;|&eacute;)\./,
    )
  })

  test("porte le H2 approche validé « De la matière au message. »", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/valoriser")
    expect(body).toMatch(
      /De la mati(?:è|&#232;|&egrave;)re au message\./,
    )
  })

  test("expose les cinq étapes de la frise dans l'ordre exact (Observer → Décliner)", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/valoriser")
    // « Structurer » apparaît aussi dans la section audience (Montrer /
    // Expliquer / Structurer) située AVANT la frise. On restreint la
    // vérification d'ordre à la portion du HTML qui suit le H2 de la frise
    // (« De la matière au message. »).
    const flowAnchor = body.search(/De la mati(?:è|&#232;|&egrave;)re au message/)
    expect(flowAnchor, "ancre 'De la matière au message' trouvée").toBeGreaterThanOrEqual(0)
    const flowSlice = body.slice(flowAnchor)
    const steps = ["Observer", "Préparer", "Produire", "Structurer", "Décliner"]
    let lastIndex = -1
    for (const label of steps) {
      const idx = flowSlice.indexOf(label)
      expect(idx, `${label} présent dans la frise SSR`).toBeGreaterThanOrEqual(0)
      expect(
        idx,
        `${label} après ${steps[steps.indexOf(label) - 1] ?? "start"}`,
      ).toBeGreaterThan(lastIndex)
      lastIndex = idx
    }
  })

  test("rend la frise éditoriale dans une vraie liste ordonnée", async ({
    page,
  }) => {
    await page.goto("/expertises/valoriser")
    const list = page.locator(".valoriser-flow__list")
    await expect(list).toHaveCount(1)
    const items = list.locator(".valoriser-flow__item")
    await expect(items).toHaveCount(5)
    await expect(items.first().locator("h3")).toHaveText("Observer")
  })

  test("rend le visuel éditorial du hero comme purement décoratif (aria-hidden)", async ({
    page,
  }) => {
    await page.goto("/expertises/valoriser")
    const visual = page.locator(".valoriser-visual")
    await expect(visual).toHaveCount(1)
    await expect(visual).toHaveAttribute("aria-hidden", "true")
  })

  test("expose les trois situations d'audience Montrer / Expliquer / Structurer", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/valoriser")
    for (const verb of ["Montrer", "Expliquer", "Structurer"]) {
      expect(body).toContain(verb)
    }
  })

  test("expose les trois principes narratifs Voix / Image / Clarté", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/valoriser")
    for (const label of ["Voix", "Image", "Clart"]) {
      expect(body).toContain(label)
    }
  })

  test("rend le content kit dans une <ol> avec cinq modules », un par livrable", async ({
    page,
  }) => {
    await page.goto("/expertises/valoriser")
    const board = page.locator(".valoriser-kit__board")
    await expect(board).toHaveCount(1)
    const modules = board.locator(".valoriser-kit__module")
    await expect(modules).toHaveCount(5)
  })

  test("ne déborde pas à 1920 px (revue visuelle desktop dédiée)", async ({
    page,
  }) => {
    await page.goto("/expertises/valoriser")
    await page.setViewportSize({ width: 1920, height: 1080 })
    const overflow = await page.evaluate(
      () =>
        document.documentElement.scrollWidth -
        document.documentElement.clientWidth,
    )
    expect(overflow, "overflow @1920").toBeLessThanOrEqual(1)
  })

  test("expose le nœud central Valoriser du bloc « Aller plus loin » comme non-cliquable", async ({
    page,
  }) => {
    await page.goto("/expertises/valoriser")
    const center = page.locator(".valoriser-related__node--center")
    await expect(center).toHaveCount(1)
    await expect(center).toHaveAttribute("aria-hidden", "true")
    // Le centre n'est pas un lien ; c'est un <div>.
    const tag = await center.evaluate((el) => el.tagName)
    expect(tag).toBe("DIV")
  })

  test("ordonne les pôles connexes Amont (Concevoir) / Aval (Visibilité) dans le rendu", async ({
    page,
  }) => {
    await page.goto("/expertises/valoriser")
    const nodes = page.locator(".valoriser-related__node")
    await expect(nodes).toHaveCount(3)
    // Ordre attendu : Concevoir (amont) → Valoriser (centre) → Visibilité (aval).
    await expect(nodes.nth(0).locator("h3")).toHaveText("Concevoir")
    await expect(nodes.nth(1).locator("h3")).toHaveText("Valoriser")
    await expect(nodes.nth(2).locator("h3")).toHaveText("Visibilité")
  })

  test("expose le CTA final validé « Votre activité a de la valeur. Donnons-lui la forme qu'elle mérite. »", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/valoriser")
    expect(body).toMatch(
      /Votre activit(?:é|&#233;|&eacute;) a de la valeur\. Donnons-lui la forme qu(?:'|&#39;|&apos;)elle m(?:é|&#233;|&eacute;)rite\./,
    )
  })

  test("expose les CTA validés (Parler de vos contenus + Découvrir Visibilité)", async ({
    page,
  }) => {
    await page.goto("/expertises/valoriser")
    await expect(
      page.getByRole("link", { name: /Parler de vos contenus/i }).first(),
    ).toBeVisible()
    await expect(
      page.getByRole("link", { name: /D(?:é|e)couvrir Visibilit(?:é|e)/i }).first(),
    ).toBeVisible()
    await expect(
      page.locator('a[href="/expertises/visibilite"]').first(),
    ).toBeVisible()
    await expect(
      page.locator('a[href="/expertises/concevoir"]').first(),
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

    await page.goto("/expertises/valoriser", { waitUntil: "networkidle" })
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

test.describe("Direction propre à /expertises/visibilite (Search Territory / Signal Map)", () => {
  test("porte le H2 audience validé « Trois territoires de visibilité. »", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/visibilite")
    expect(body).toMatch(
      /Trois territoires de visibilit(?:é|&#233;|&eacute;)\./,
    )
  })

  test("porte le H2 approche validé « Le parcours d'un signal, de l'intention à la mesure. »", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/visibilite")
    expect(body).toMatch(
      /Le parcours d(?:'|&#39;|&apos;)un signal, de l(?:'|&#39;|&apos;)intention (?:à|&#224;|&agrave;) la mesure\./,
    )
  })

  test("expose les cinq étapes du parcours dans l'ordre exact (Intentions → Mesure)", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/visibilite")
    const pathAnchor = body.search(
      /Le parcours d(?:'|&#39;|&apos;)un signal/,
    )
    expect(pathAnchor, "ancre 'Le parcours d'un signal' trouvée").toBeGreaterThanOrEqual(0)
    const pathSlice = body.slice(pathAnchor)
    const steps = ["Intentions", "Fondations", "Pages", "Présence", "Mesure"]
    let lastIndex = -1
    for (const label of steps) {
      const idx = pathSlice.indexOf(label)
      expect(idx, `${label} présent dans la frise SSR`).toBeGreaterThanOrEqual(0)
      expect(
        idx,
        `${label} après ${steps[steps.indexOf(label) - 1] ?? "start"}`,
      ).toBeGreaterThan(lastIndex)
      lastIndex = idx
    }
  })

  test("rend la frise signal dans une vraie liste ordonnée", async ({
    page,
  }) => {
    await page.goto("/expertises/visibilite")
    const list = page.locator(".visibilite-path__list")
    await expect(list).toHaveCount(1)
    const items = list.locator(".visibilite-path__item")
    await expect(items).toHaveCount(5)
    await expect(items.first().locator("h3")).toHaveText("Intentions")
  })

  test("rend le visuel territoire du hero comme purement décoratif (aria-hidden)", async ({
    page,
  }) => {
    await page.goto("/expertises/visibilite")
    const visual = page.locator(".visibilite-visual")
    await expect(visual).toHaveCount(1)
    await expect(visual).toHaveAttribute("aria-hidden", "true")
  })

  test("expose les trois zones d'audience (Être trouvé / Être présent localement / Rester visible)", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/visibilite")
    for (const fragment of [
      "Être trouvé",
      "Être présent localement",
      "Rester visible dans la durée",
    ]) {
      expect(body).toContain(fragment)
    }
  })

  test("expose les trois principes narratifs Durée / Pertinence / Proximité", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/visibilite")
    for (const label of ["Durée", "Pertinence", "Proximité"]) {
      expect(body).toContain(label)
    }
  })

  test("rend les cinq couches de visibilité dans une <ol>, une par livrable", async ({
    page,
  }) => {
    await page.goto("/expertises/visibilite")
    const stack = page.locator(".visibilite-layers__stack")
    await expect(stack).toHaveCount(1)
    const layers = stack.locator(".visibilite-layers__layer")
    await expect(layers).toHaveCount(5)
  })

  test("ne déborde pas à 1920 px (revue visuelle desktop dédiée)", async ({
    page,
  }) => {
    await page.goto("/expertises/visibilite")
    await page.setViewportSize({ width: 1920, height: 1080 })
    const overflow = await page.evaluate(
      () =>
        document.documentElement.scrollWidth -
        document.documentElement.clientWidth,
    )
    expect(overflow, "overflow @1920").toBeLessThanOrEqual(1)
  })

  test("expose le nœud central Visibilité du bloc « Aller plus loin » comme non-cliquable", async ({
    page,
  }) => {
    await page.goto("/expertises/visibilite")
    const center = page.locator(".visibilite-related__node--center")
    await expect(center).toHaveCount(1)
    await expect(center).toHaveAttribute("aria-hidden", "true")
    const tag = await center.evaluate((el) => el.tagName)
    expect(tag).toBe("DIV")
  })

  test("ordonne les pôles connexes Amont (Valoriser) / Aval (Faire évoluer) dans le rendu", async ({
    page,
  }) => {
    await page.goto("/expertises/visibilite")
    const nodes = page.locator(".visibilite-related__node")
    await expect(nodes).toHaveCount(3)
    await expect(nodes.nth(0).locator("h3")).toHaveText("Valoriser")
    await expect(nodes.nth(1).locator("h3")).toHaveText("Visibilité")
    await expect(nodes.nth(2).locator("h3")).toHaveText("Faire évoluer")
  })

  test("expose le CTA final validé « Développer la visibilité … sans dépendre de la publicité payante. »", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/visibilite")
    expect(body).toMatch(
      /D(?:é|&#233;|&eacute;)velopper la visibilit(?:é|&#233;|&eacute;) de votre entreprise, sans d(?:é|&#233;|&eacute;)pendre[\s\S]{0,30}de la publicit(?:é|&#233;|&eacute;) payante\./,
    )
  })

  test("expose les CTA validés (Parler de votre visibilité + Découvrir Faire évoluer)", async ({
    page,
  }) => {
    await page.goto("/expertises/visibilite")
    await expect(
      page.getByRole("link", { name: /Parler de votre visibilit(?:é|e)/i }).first(),
    ).toBeVisible()
    await expect(
      page.getByRole("link", { name: /D(?:é|e)couvrir Faire (?:é|e)voluer/i }).first(),
    ).toBeVisible()
    await expect(
      page.locator('a[href="/expertises/faire-evoluer"]').first(),
    ).toBeVisible()
    await expect(
      page.locator('a[href="/expertises/valoriser"]').first(),
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

    await page.goto("/expertises/visibilite", { waitUntil: "networkidle" })
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

test.describe("Direction propre à /expertises/faire-evoluer (Living System / Continuous Care)", () => {
  test("porte le H2 audience validé « Quand la mise en ligne n'est que le début. »", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/faire-evoluer")
    expect(body).toMatch(
      /Quand la mise en ligne n(?:'|&#39;|&apos;)est que le d(?:é|&#233;|&eacute;)but\./,
    )
  })

  test("porte le H2 approche validé « Observer. Prioriser. Améliorer. Recommencer. »", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/faire-evoluer")
    expect(body).toMatch(
      /Observer\. Prioriser\. Am(?:é|&#233;|&eacute;)liorer\. Recommencer\./,
    )
  })

  test("expose les cinq étapes de la boucle dans l'ordre exact (Observer → Réévaluer)", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/faire-evoluer")
    // Le H2 « Observer. Prioriser. Améliorer. Recommencer. » précède la <ol>
    // et contient déjà « Observer » + « Prioriser ». On ancre après la classe
    // `faire-evoluer-loop__list` pour comparer l'ordre des étapes de la liste
    // seule, indépendamment du titre qui les évoque en amont.
    const listAnchor = body.indexOf("faire-evoluer-loop__list")
    expect(listAnchor, "ancre '.faire-evoluer-loop__list' trouvée").toBeGreaterThanOrEqual(0)
    const loopSlice = body.slice(listAnchor)
    const steps = ["Observer", "Maintenir", "Prioriser", "Livrer", "Réévaluer"]
    let lastIndex = -1
    for (const label of steps) {
      const idx = loopSlice.indexOf(label)
      expect(idx, `${label} présent dans la boucle SSR`).toBeGreaterThanOrEqual(0)
      expect(
        idx,
        `${label} après ${steps[steps.indexOf(label) - 1] ?? "start"}`,
      ).toBeGreaterThan(lastIndex)
      lastIndex = idx
    }
  })

  test("expose visuellement le retour cyclique « Réévaluer → recommencer »", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/faire-evoluer")
    expect(body).toMatch(
      /R(?:é|&#233;|&eacute;)(?:é|&#233;|&eacute;)valuer\s*(?:→|&#8594;|&rarr;)\s*recommencer/,
    )
  })

  test("rend la boucle continue dans une vraie liste ordonnée avec cinq étapes", async ({
    page,
  }) => {
    await page.goto("/expertises/faire-evoluer")
    const list = page.locator(".faire-evoluer-loop__list")
    await expect(list).toHaveCount(1)
    const items = list.locator(".faire-evoluer-loop__step")
    await expect(items).toHaveCount(5)
    await expect(items.first().locator("h3")).toHaveText("Observer")
    await expect(items.last().locator("h3")).toHaveText("Réévaluer")
  })

  test("rend le visuel cyclique du hero comme purement décoratif (aria-hidden)", async ({
    page,
  }) => {
    await page.goto("/expertises/faire-evoluer")
    const visual = page.locator(".faire-evoluer-visual")
    await expect(visual).toHaveCount(1)
    await expect(visual).toHaveAttribute("aria-hidden", "true")
    const svg = visual.locator("svg").first()
    await expect(svg).toHaveAttribute("aria-hidden", "true")
  })

  test("expose les trois phases d'audience Maintenir / Comprendre / Adapter", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/faire-evoluer")
    for (const label of ["Maintenir", "Comprendre", "Adapter"]) {
      expect(body).toContain(label)
    }
  })

  test("expose les trois principes narratifs Santé / Clarté / Rythme", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/faire-evoluer")
    for (const label of ["Santé", "Clarté", "Rythme"]) {
      expect(body).toContain(label)
    }
  })

  test("expose les cinq cadences narratives (Continu / Mesuré / Cycle court / Priorisé / Point de situation)", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/faire-evoluer")
    for (const label of [
      "Continu",
      "Mesuré",
      "Cycle court",
      "Priorisé",
      "Point de situation",
    ]) {
      expect(body).toContain(label)
    }
  })

  test("rend la partition des cadences dans une <ol> avec cinq entrées", async ({
    page,
  }) => {
    await page.goto("/expertises/faire-evoluer")
    const list = page.locator(".faire-evoluer-cadence__list")
    await expect(list).toHaveCount(1)
    const items = list.locator(".faire-evoluer-cadence__item")
    await expect(items).toHaveCount(5)
  })

  test("n'expose ni faux dashboard, ni uptime, ni pourcentage inventé, ni SLA", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/faire-evoluer")
    expect(body).not.toMatch(/uptime/i)
    expect(body).not.toMatch(/\b99\s*%/)
    expect(body).not.toMatch(/\bSLA\b/i)
    expect(body).not.toMatch(/dashboard/i)
    expect(body).not.toMatch(/v\d+\.\d+\.\d+/)
  })

  test("ne déborde pas à 1920 px (revue visuelle desktop dédiée)", async ({
    page,
  }) => {
    await page.goto("/expertises/faire-evoluer")
    await page.setViewportSize({ width: 1920, height: 1080 })
    const overflow = await page.evaluate(
      () =>
        document.documentElement.scrollWidth -
        document.documentElement.clientWidth,
    )
    expect(overflow, "overflow @1920").toBeLessThanOrEqual(1)
  })

  test("expose le nœud central Faire évoluer du bloc « Aller plus loin » comme non-cliquable", async ({
    page,
  }) => {
    await page.goto("/expertises/faire-evoluer")
    const center = page.locator(".faire-evoluer-related__node--center")
    await expect(center).toHaveCount(1)
    await expect(center).toHaveAttribute("aria-hidden", "true")
    const tag = await center.evaluate((el) => el.tagName)
    expect(tag).toBe("DIV")
  })

  test("ordonne les pôles connexes Amont (Construire) → Faire évoluer (centre) → Amont (Visibilité)", async ({
    page,
  }) => {
    await page.goto("/expertises/faire-evoluer")
    const nodes = page.locator(".faire-evoluer-related__node")
    await expect(nodes).toHaveCount(3)
    await expect(nodes.nth(0).locator("h3")).toHaveText("Construire")
    await expect(nodes.nth(1).locator("h3")).toHaveText("Faire évoluer")
    await expect(nodes.nth(2).locator("h3")).toHaveText("Visibilité")
  })

  test("expose le CTA final validé « Votre site est en ligne. Gardons-le utile, fiable et capable d'évoluer. »", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, "/expertises/faire-evoluer")
    expect(body).toMatch(
      /Votre site est en ligne\. Gardons-le utile, fiable et capable d(?:'|&#39;|&apos;)(?:é|&#233;|&eacute;)voluer\./,
    )
  })

  test("expose les CTA validés (Parler de votre suivi + Découvrir Construire)", async ({
    page,
  }) => {
    await page.goto("/expertises/faire-evoluer")
    await expect(
      page.getByRole("link", { name: /Parler de votre suivi/i }).first(),
    ).toBeVisible()
    await expect(
      page.getByRole("link", { name: /D(?:é|e)couvrir Construire/i }).first(),
    ).toBeVisible()
    await expect(
      page.locator('a[href="/expertises/construire"]').first(),
    ).toBeVisible()
    await expect(
      page.locator('a[href="/expertises/visibilite"]').first(),
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

    await page.goto("/expertises/faire-evoluer", { waitUntil: "networkidle" })
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

test.describe("Cinq directions dédiées : aucune page ne retombe sur le gabarit générique", () => {
  // Chaque pôle publié doit exposer sa signature CSS propre — présence d'un
  // conteneur `.{slug}-page`. Le gabarit générique se distingue par la classe
  // `.expertise-page__paragraph` que les cinq directions dédiées n'utilisent
  // plus. Cette vérification empêche toute régression silencieuse si un
  // futur refactor supprimait par erreur une branche `is<Pillar>`.
  const SIGNATURES = [
    { route: "/expertises/concevoir", selector: ".concevoir-page" },
    { route: "/expertises/construire", selector: ".construire-page" },
    { route: "/expertises/valoriser", selector: ".valoriser-page" },
    { route: "/expertises/visibilite", selector: ".visibilite-page" },
    { route: "/expertises/faire-evoluer", selector: ".faire-evoluer-page" },
  ] as const

  for (const s of SIGNATURES) {
    test(`${s.route} rend son gabarit dédié (${s.selector}) et non le fallback générique`, async ({
      page,
    }) => {
      await page.goto(s.route)
      await expect(page.locator(s.selector)).toHaveCount(1)
      // Le fallback rend un paragraphe `.expertise-page__paragraph` que les
      // gabarits dédiés n'utilisent plus : sa présence indiquerait que la
      // page a rétrogradé sur `<template v-else>`.
      await expect(page.locator(".expertise-page__paragraph")).toHaveCount(0)
    })
  }
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
