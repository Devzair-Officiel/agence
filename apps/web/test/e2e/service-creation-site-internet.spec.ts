import AxeBuilder from "@axe-core/playwright"
import { expect, test } from "@playwright/test"
import { servicePages } from "../../app/config/service-pages"

// E2E de `/services/creation-site-internet` livré en SEO-COM-2.
//
// On vérifie côté HTML SSR (avant hydratation) :
//   - HTTP 200 et absence d'erreur console ;
//   - un unique H1 avec la phrase attendue ;
//   - balise title, meta description, canonical ou noindex, og:url en SSR ;
//   - fil d'Ariane HTML visible (nav Fil d'Ariane) ;
//   - JSON-LD Service et BreadcrumbList présents dans le HTML SSR ;
//   - sections éditoriales principales présentes en SSR ;
//   - CTA vers /estimer-mon-projet et /contact présents ;
//   - /services/creation-site-internet dans le sitemap ;
//   - slugs planifiés absents du sitemap.
//
// On vérifie côté rendu/interaction :
//   - responsive 390 / 768 / 1440 sans débordement horizontal ;
//   - Axe WCAG 2.2 AA (aucune violation serious/critical) ;
//   - 404 pour tout slug planifié ou inconnu (guard présent depuis SEO-COM-1).

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

test.describe("/services/creation-site-internet — HTTP et SSR", () => {
  test("renvoie 200 sans erreur console avec H1 unique correct", async ({ page, request }) => {
    const consoleErrors: string[] = []
    page.on("console", (msg) => {
      if (msg.type() === "error") consoleErrors.push(msg.text())
    })

    const { body } = await fetchSSR(request, "/services/creation-site-internet")
    expect(body).toContain(
      "Un site internet professionnel, pensé pour votre activité et votre image.",
    )

    await page.goto("/services/creation-site-internet")
    const headings = await page.locator("h1").all()
    expect(headings).toHaveLength(1)
    await expect(page.locator("h1")).toHaveText(
      "Un site internet professionnel, pensé pour votre activité et votre image.",
    )
    expect(consoleErrors).toEqual([])
  })

  test("expose le SEO complet dans le HTML initial", async ({ request }) => {
    const { body } = await fetchSSR(request, "/services/creation-site-internet")
    expect(body).toMatch(/<title>[^<]*Création de site internet[^<]*Devzair[^<]*<\/title>/i)
    expect(body).toMatch(
      /<meta[^>]+name="description"[^>]+content="[^"]*site internet[^"]*"/i,
    )
    const hasCanonical = /<link[^>]+rel="canonical"[^>]+href="[^"]+"/i.test(body)
    const hasNoindex = /<meta[^>]+name="robots"[^>]+content="[^"]*noindex[^"]*"/i.test(body)
    expect(hasCanonical || hasNoindex, "canonical or noindex").toBe(true)
    expect(body).toMatch(/<meta[^>]+property="og:url"[^>]+content="https?:\/\/[^"]+"/i)
  })

  test("publie le fil d'Ariane HTML dans le SSR", async ({ request }) => {
    const { body } = await fetchSSR(request, "/services/creation-site-internet")
    expect(body).toContain('aria-label="Fil d')
    expect(body).toContain("Accueil")
    expect(body).toContain("Services")
    expect(body).toContain("Site internet")
  })

  test("inclut le JSON-LD Service dans le HTML SSR", async ({ request }) => {
    const { body } = await fetchSSR(request, "/services/creation-site-internet")
    expect(body).toContain('"@type":"Service"')
    expect(body).toContain('"Création de site internet"')
  })

  test("inclut le JSON-LD BreadcrumbList dans le HTML SSR", async ({ request }) => {
    const { body } = await fetchSSR(request, "/services/creation-site-internet")
    expect(body).toContain('"@type":"BreadcrumbList"')
    expect(body).toContain('"Accueil"')
    expect(body).toContain('"Services"')
  })
})

test.describe("/services/creation-site-internet — contenu éditorial SSR", () => {
  test("publie les sections éditoriales principales en SSR", async ({ request }) => {
    const { body } = await fetchSSR(request, "/services/creation-site-internet")
    // Section situations
    expect(body).toContain("Vos clients potentiels vous cherchent")
    // Section design
    expect(body).toContain("ne devrait pas ressembler")
    // Section périmètre
    expect(body).toContain("Ce que comprend le projet")
    // Section budget
    expect(body).toContain("fait r")
    // Section réalisations
    expect(body).toContain("Kitchen Meat")
    expect(body).toContain("Haramain Prestige")
    expect(body).toContain("Al Mumayiz")
    // FAQ
    expect(body).toContain("Combien de temps")
  })

  test("propose les CTA vers /estimer-mon-projet et /contact", async ({ page }) => {
    await page.goto("/services/creation-site-internet")
    await expect(page.locator('a[href="/estimer-mon-projet"]').first()).toBeVisible()
    await expect(page.locator('a[href="/contact"]').first()).toBeVisible()
  })
})

test.describe("/services/creation-site-internet — sitemap", () => {
  test("/services/creation-site-internet est dans le sitemap", async ({ request }) => {
    const response = await request.get("/sitemap.xml")
    const body = await response.text()
    expect(body).toContain("/services/creation-site-internet")
  })

  test("les slugs planifiés n'apparaissent pas dans le sitemap", async ({ request }) => {
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
  test("retourne 404 pour un slug de service planifié", async ({ request }) => {
    const plannedService = servicePages.find((s) => s.status === "planned")
    const response = await request.get(`/services/${plannedService!.slug}`)
    expect(response.status()).toBe(404)
  })

  test("retourne 404 pour un slug inexistant", async ({ request }) => {
    const response = await request.get("/services/slug-inconnu-xyz-abc")
    expect(response.status()).toBe(404)
  })
})

test.describe("/services/creation-site-internet — responsive et accessibilité", () => {
  test("respecte la responsivité 390 / 768 / 1440", async ({ page }) => {
    await page.goto("/services/creation-site-internet")
    await assertNoHorizontalOverflow(page)
  })

  test("passe Axe WCAG 2.2 AA (aucune violation serious/critical)", async ({ page }) => {
    await page.goto("/services/creation-site-internet")
    const results = await new AxeBuilder({ page })
      .withTags(["wcag2a", "wcag2aa", "wcag21a", "wcag21aa", "wcag22aa"])
      .analyze()
    const blocking = results.violations.filter(
      (v) => v.impact === "critical" || v.impact === "serious",
    )
    if (blocking.length > 0) {
      console.log(
        `Axe /services/creation-site-internet:\n${blocking
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
})
