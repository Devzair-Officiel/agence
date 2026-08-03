import AxeBuilder from "@axe-core/playwright"
import { expect, test } from "@playwright/test"

// E2E des pages institutionnelles livrées en Phase 7A :
//   - `/agence` — positionnement, fonctionnement, valeurs, pont vers /expertises
//   - `/expertises` — vue d'ensemble des cinq pôles, pont vers /agence
//
// On vérifie côté HTML SSR (avant hydratation) :
//   - HTTP 200 et absence d'erreur console ;
//   - un unique H1 avec la phrase verbatim attendue ;
//   - eyebrow et introduction verbatim présents dans le HTML initial ;
//   - SEO minimal (title spécifique, canonical, og:url, meta description) ;
//   - politique globale d'indexation respectée (noindex en preprod) ;
//   - lien réel vers /#contact et vers la page sœur ;
//   - absence de liens morts vers `/expertises/{slug}`.
//
// On vérifie côté rendu/interaction :
//   - responsive 390 / 768 / 1440 sans débordement horizontal ;
//   - Axe WCAG 2.2 AA sur chaque page ;
//   - `prefers-reduced-motion` : contenu visible et navigable.
//
// Les tests inter-pages vérifient que le maillage `/agence` ↔ `/expertises`
// fonctionne réellement (navigation clic → nouvelle page → H1 attendu).

const BREAKPOINTS = [390, 768, 1440] as const

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
  expect(response.status(), `${path} status`).toBe(200)
  const body = await response.text()
  return { response, body }
}

test.describe("/agence — SSR et contenu éditorial", () => {
  test("renvoie 200 avec un H1 unique verbatim", async ({ page, request }) => {
    const consoleErrors: string[] = []
    page.on("console", (msg) => {
      if (msg.type() === "error") consoleErrors.push(msg.text())
    })

    const { body } = await fetchSSR(request, "/agence")
    expect(body).toContain(
      "Une agence digitale à taille humaine, pensée pour accompagner les entreprises dans leur globalité.",
    )

    await page.goto("/agence")
    const headings = await page.locator("h1").all()
    expect(headings).toHaveLength(1)
    await expect(page.locator("h1")).toHaveText(
      "Une agence digitale à taille humaine, pensée pour accompagner les entreprises dans leur globalité.",
    )
    expect(consoleErrors).toEqual([])
  })

  test("publie l'eyebrow et l'introduction verbatim en SSR", async ({ request }) => {
    const { body } = await fetchSSR(request, "/agence")
    // L'apostrophe est encodée en HTML dans le rendu SSR de Vue
    // (`L&#39;agence`). On accepte l'une ou l'autre forme pour ne pas
    // dépendre d'un détail d'encodage.
    expect(body).toMatch(/L(?:'|&#39;)agence/)
    expect(body).toContain(
      "Devzair réunit stratégie, design, développement, contenus et visibilité afin de construire des solutions digitales cohérentes, utiles et évolutives.",
    )
  })

  test("expose le SEO complet dans le HTML initial", async ({ request }) => {
    const { body } = await fetchSSR(request, "/agence")
    expect(body).toMatch(
      /<title>[^<]*Agence digitale à taille humaine[^<]*Devzair[^<]*<\/title>/i,
    )
    expect(body).toMatch(
      /<meta[^>]+name="description"[^>]+content="[^"]*réunit stratégie, design, développement, contenus et SEO[^"]*"/i,
    )
    // Canonical présent (mode indexable) OU meta robots noindex (preprod par défaut).
    const hasCanonical = /<link[^>]+rel="canonical"[^>]+href="[^"]+"/i.test(body)
    const hasNoindex = /<meta[^>]+name="robots"[^>]+content="[^"]*noindex[^"]*"/i.test(
      body,
    )
    expect(hasCanonical || hasNoindex, "canonical or noindex").toBe(true)
    expect(body).toMatch(/<meta[^>]+property="og:url"[^>]+content="https?:\/\/[^"]+"/i)
  })

  test("propose un lien réel vers /expertises et vers /#contact", async ({ page }) => {
    await page.goto("/agence")
    await expect(page.locator('a[href="/expertises"]').first()).toBeVisible()
    await expect(page.locator('a[href="/#contact"]').first()).toBeVisible()
  })

  test("ne contient aucun lien vers `/expertises/{slug}` (Phase 7B)", async ({ page }) => {
    await page.goto("/agence")
    const badLinks = await page.locator('a[href^="/expertises/"]').count()
    expect(badLinks).toBe(0)
  })

  test("respecte la responsivité 390 / 768 / 1440 sans débordement", async ({ page }) => {
    await page.goto("/agence")
    await assertNoHorizontalOverflow(page)
  })

  test("passe Axe WCAG 2.2 AA (aucune violation serious/critical)", async ({ page }) => {
    await page.goto("/agence")
    const results = await new AxeBuilder({ page })
      .withTags(["wcag2a", "wcag2aa", "wcag21a", "wcag21aa", "wcag22aa"])
      .analyze()
    const blocking = results.violations.filter(
      (v) => v.impact === "critical" || v.impact === "serious",
    )
    if (blocking.length > 0) {
      console.log(
        `Axe /agence:\n${blocking
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
    await page.goto("/agence")
    await expect(page.locator("h1")).toBeVisible()
    await context.close()
  })
})

test.describe("/expertises — SSR et contenu éditorial", () => {
  test("renvoie 200 avec un H1 unique verbatim", async ({ page, request }) => {
    const consoleErrors: string[] = []
    page.on("console", (msg) => {
      if (msg.type() === "error") consoleErrors.push(msg.text())
    })

    const { body } = await fetchSSR(request, "/expertises")
    expect(body).toContain(
      "Cinq pôles complémentaires pour construire une présence digitale cohérente.",
    )

    await page.goto("/expertises")
    const headings = await page.locator("h1").all()
    expect(headings).toHaveLength(1)
    await expect(page.locator("h1")).toHaveText(
      "Cinq pôles complémentaires pour construire une présence digitale cohérente.",
    )
    expect(consoleErrors).toEqual([])
  })

  test("publie l'eyebrow et l'introduction verbatim en SSR", async ({ request }) => {
    const { body } = await fetchSSR(request, "/expertises")
    expect(body).toContain("Nos expertises")
    expect(body).toContain(
      "Chaque entreprise possède des besoins différents. Nous réunissons les expertises adaptées pour concevoir, construire, valoriser, rendre visible et faire évoluer votre projet.",
    )
  })

  test("expose le SEO complet dans le HTML initial", async ({ request }) => {
    const { body } = await fetchSSR(request, "/expertises")
    expect(body).toMatch(
      /<title>[^<]*Expertises web, design, contenu et SEO[^<]*Devzair[^<]*<\/title>/i,
    )
    expect(body).toMatch(
      /<meta[^>]+name="description"[^>]+content="[^"]*cinq pôles d[’']expertise Devzair[^"]*"/i,
    )
    const hasCanonical = /<link[^>]+rel="canonical"[^>]+href="[^"]+"/i.test(body)
    const hasNoindex = /<meta[^>]+name="robots"[^>]+content="[^"]*noindex[^"]*"/i.test(
      body,
    )
    expect(hasCanonical || hasNoindex).toBe(true)
    expect(body).toMatch(/<meta[^>]+property="og:url"[^>]+content="https?:\/\/[^"]+"/i)
  })

  test("affiche cinq cartes d'expertise en SSR", async ({ request, page }) => {
    const { body } = await fetchSSR(request, "/expertises")
    // Les cinq labels de pôle doivent être dans le HTML initial.
    for (const label of [
      "Concevoir",
      "Construire",
      "Valoriser",
      "Développer la visibilité",
      "Faire évoluer",
    ]) {
      expect(body).toContain(label)
    }
    await page.goto("/expertises")
    const cards = await page.locator(".expertise-overview-card").count()
    expect(cards).toBe(5)
  })

  test("rend chaque carte comme un lien vers `/expertises/{slug}` publié", async ({
    page,
  }) => {
    await page.goto("/expertises")
    // Depuis la Phase 7B, chaque carte est un `<NuxtLink>` (rendu <a>) qui
    // pointe vers sa page fille dédiée.
    const cardLinks = await page.locator(".expertise-overview-card").count()
    expect(cardLinks).toBe(5)
    for (const slug of [
      "concevoir",
      "construire",
      "valoriser",
      "visibilite",
      "faire-evoluer",
    ]) {
      await expect(page.locator(`a[href="/expertises/${slug}"]`).first()).toBeVisible()
    }
  })

  test("propose un lien réel vers /agence et vers /#contact", async ({ page }) => {
    await page.goto("/expertises")
    await expect(page.locator('a[href="/agence"]').first()).toBeVisible()
    await expect(page.locator('a[href="/#contact"]').first()).toBeVisible()
  })

  test("respecte la responsivité 390 / 768 / 1440 sans débordement", async ({ page }) => {
    await page.goto("/expertises")
    await assertNoHorizontalOverflow(page)
  })

  test("passe Axe WCAG 2.2 AA (aucune violation serious/critical)", async ({ page }) => {
    await page.goto("/expertises")
    const results = await new AxeBuilder({ page })
      .withTags(["wcag2a", "wcag2aa", "wcag21a", "wcag21aa", "wcag22aa"])
      .analyze()
    const blocking = results.violations.filter(
      (v) => v.impact === "critical" || v.impact === "serious",
    )
    if (blocking.length > 0) {
      console.log(
        `Axe /expertises:\n${blocking
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
    await page.goto("/expertises")
    await expect(page.locator("h1")).toBeVisible()
    await context.close()
  })
})

test.describe("Maillage inter-pages institutionnelles", () => {
  test("depuis /agence, le callout mène à /expertises", async ({ page }) => {
    await page.goto("/agence")
    await page.locator('a[href="/expertises"]').first().click()
    await expect(page).toHaveURL(/\/expertises\/?$/)
    await expect(page.locator("h1")).toHaveText(
      "Cinq pôles complémentaires pour construire une présence digitale cohérente.",
    )
  })

  test("depuis /expertises, le callout mène à /agence", async ({ page }) => {
    await page.goto("/expertises")
    await page.locator('a[href="/agence"]').first().click()
    await expect(page).toHaveURL(/\/agence\/?$/)
    await expect(page.locator("h1")).toHaveText(
      "Une agence digitale à taille humaine, pensée pour accompagner les entreprises dans leur globalité.",
    )
  })

  test("la navigation principale expose Agence, Expertises et Réalisations", async ({
    page,
  }) => {
    await page.goto("/agence")
    // Trois entrées + le lien de marque + le CTA
    const nav = page.locator(".site-header__nav")
    await expect(nav.locator('a[href="/agence"]').first()).toBeVisible()
    await expect(nav.locator('a[href="/expertises"]').first()).toBeVisible()
    await expect(nav.locator('a[href="/#realisations"]').first()).toBeVisible()
    // Le CTA « Parler de votre projet » reste présent et pointe sur /#contact.
    await expect(
      nav.locator('a.base-button[href="/#contact"]').first(),
    ).toBeVisible()
  })
})
