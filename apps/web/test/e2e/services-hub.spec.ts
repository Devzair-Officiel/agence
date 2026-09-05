import AxeBuilder from "@axe-core/playwright"
import { expect, test } from "@playwright/test"
import { servicePages } from "../../app/config/service-pages"

// E2E du hub `/services` livré en SEO-COM-1.
//
// On vérifie côté HTML SSR (avant hydratation) :
//   - HTTP 200 et absence d'erreur console ;
//   - un unique H1 avec la phrase verbatim attendue ;
//   - eyebrow, introduction et les trois blocs d'orientation présents en SSR ;
//   - SEO minimal (title, canonical ou noindex, og:url, meta description) ;
//   - aucun lien vers une route `/services/{slug}` tant que tous sont `planned` ;
//   - /services présent dans le sitemap ;
//   - aucun slug de service `planned` dans le sitemap.
//
// On vérifie côté rendu/interaction :
//   - responsive 320 / 390 / 768 / 1024 / 1440 sans débordement horizontal ;
//   - Axe WCAG 2.2 AA ;
//   - `prefers-reduced-motion` : contenu visible et navigable ;
//   - `/services/{slug}` → 404 (route guard actif).

const BREAKPOINTS = [320, 390, 768, 1024, 1440] as const

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

test.describe("/services — SSR et contenu éditorial", () => {
  test("renvoie 200 avec un H1 unique verbatim", async ({ page, request }) => {
    const consoleErrors: string[] = []
    page.on("console", (msg) => {
      if (msg.type() === "error") consoleErrors.push(msg.text())
    })

    const { body } = await fetchSSR(request, "/services")
    expect(body).toContain("Créer, rendre visible et faire évoluer votre présence en ligne.")

    await page.goto("/services")
    const headings = await page.locator("h1").all()
    expect(headings).toHaveLength(1)
    await expect(page.locator("h1")).toHaveText(
      "Créer, rendre visible et faire évoluer votre présence en ligne.",
    )
    expect(consoleErrors).toEqual([])
  })

  test("publie l'eyebrow et les huit titres de service en SSR", async ({ request }) => {
    const { body } = await fetchSSR(request, "/services")
    expect(body).toContain("Nos services")
    for (const service of servicePages) {
      expect(body).toContain(service.title)
    }
  })

  test("publie les trois blocs d'orientation en SSR", async ({ request }) => {
    const { body } = await fetchSSR(request, "/services")
    // Fragments caractéristiques des trois blocs — encodage HTML possible pour apostrophes.
    expect(body).toMatch(/Vous n(?:'|&#39;)avez pas encore de site/)
    expect(body).toContain("Vous avez un site, mais peu de visiteurs qualifiés.")
    expect(body).toMatch(/Vous avez un site en ligne et avez besoin d(?:'|&#39;)un suivi dans le temps\./)
  })

  test("expose le SEO complet dans le HTML initial", async ({ request }) => {
    const { body } = await fetchSSR(request, "/services")
    expect(body).toMatch(
      /<title>[^<]*Nos services web[^<]*Devzair[^<]*<\/title>/i,
    )
    expect(body).toMatch(
      /<meta[^>]+name="description"[^>]+content="[^"]*e-commerce[^"]*"/i,
    )
    const hasCanonical = /<link[^>]+rel="canonical"[^>]+href="[^"]+"/i.test(body)
    const hasNoindex = /<meta[^>]+name="robots"[^>]+content="[^"]*noindex[^"]*"/i.test(body)
    expect(hasCanonical || hasNoindex, "canonical or noindex").toBe(true)
    expect(body).toMatch(/<meta[^>]+property="og:url"[^>]+content="https?:\/\/[^"]+"/i)
  })

  test("ne contient aucun lien vers une page service planifiée", async ({ page }) => {
    await page.goto("/services")
    const plannedRoutes = servicePages
      .filter((s) => s.status === "planned")
      .map((s) => s.route)
    for (const route of plannedRoutes) {
      const count = await page.locator(`a[href="${route}"]`).count()
      expect(count, `lien mort vers ${route}`).toBe(0)
    }
  })

  test("la carte creation-site-internet est un lien cliquable", async ({ page }) => {
    await page.goto("/services")
    const card = page.locator('a[href="/services/creation-site-internet"]').first()
    await expect(card).toBeVisible()
    await expect(card).toContainText("Création de site internet")
  })

  test("la carte site-e-commerce est maintenant un lien cliquable (SEO-COM-5A)", async ({ page }) => {
    await page.goto("/services")
    const card = page.locator('a[href="/services/site-e-commerce"]').first()
    await expect(card).toBeVisible()
    await expect(card).toContainText("Site e-commerce")
  })

  test("la carte application-web-metier est maintenant un lien cliquable (SEO-COM-5A)", async ({ page }) => {
    await page.goto("/services")
    const card = page.locator('a[href="/services/application-web-metier"]').first()
    await expect(card).toBeVisible()
    await expect(card).toContainText("Application web métier")
  })

  test("propose les CTA vers /estimer-mon-projet et /contact", async ({ page }) => {
    await page.goto("/services")
    await expect(page.locator('a[href="/estimer-mon-projet"]').first()).toBeVisible()
    await expect(page.locator('a[href="/contact"]').first()).toBeVisible()
  })
})

test.describe("/services — sitemap", () => {
  test("/services est inclus dans le sitemap", async ({ request }) => {
    const response = await request.get("/sitemap.xml")
    const body = await response.text()
    expect(body).toContain("/services")
  })

  test("aucun slug de service planifié n'apparaît dans le sitemap", async ({ request }) => {
    const response = await request.get("/sitemap.xml")
    const body = await response.text()
    for (const service of servicePages) {
      if (service.status === "planned") {
        expect(body, `slug planifié ${service.slug} dans le sitemap`).not.toContain(
          service.route,
        )
      }
    }
  })
})

test.describe("/services/{slug} — route guard", () => {
  test("retourne 200 pour les trois services publiés", async ({ request }) => {
    for (const slug of ["creation-site-internet", "site-e-commerce", "application-web-metier"]) {
      const response = await request.get(`/services/${slug}`)
      expect(response.status(), `${slug} status`).toBe(200)
    }
  })

  test("retourne 404 pour un slug de service planifié", async ({ request }) => {
    const response = await request.get("/services/design-ui-ux-identite-visuelle")
    expect(response.status()).toBe(404)
  })

  test("retourne 404 pour un slug inexistant", async ({ request }) => {
    const response = await request.get("/services/slug-inconnu-xyz")
    expect(response.status()).toBe(404)
  })
})

test.describe("/services — responsive et accessibilité", () => {
  test("respecte la responsivité 320 / 390 / 768 / 1024 / 1440", async ({ page }) => {
    await page.goto("/services")
    await assertNoHorizontalOverflow(page)
  })

  test("passe Axe WCAG 2.2 AA (aucune violation serious/critical)", async ({ page }) => {
    await page.goto("/services")
    const results = await new AxeBuilder({ page })
      .withTags(["wcag2a", "wcag2aa", "wcag21a", "wcag21aa", "wcag22aa"])
      .analyze()
    const blocking = results.violations.filter(
      (v) => v.impact === "critical" || v.impact === "serious",
    )
    if (blocking.length > 0) {
      console.log(
        `Axe /services:\n${blocking
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
    await page.goto("/services")
    await expect(page.locator("h1")).toBeVisible()
    await expect(
      page.locator('a[href="/estimer-mon-projet"]').first(),
    ).toBeVisible()
    await context.close()
  })
})
