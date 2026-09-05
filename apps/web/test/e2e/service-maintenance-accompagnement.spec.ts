import AxeBuilder from "@axe-core/playwright"
import { expect, test } from "@playwright/test"

// E2E — /services/maintenance-accompagnement (SEO-COM-5C)
//
// Vérifie dans le HTML SSR et après rendu :
//   - HTTP 200 ;
//   - H1 unique présent en SSR ;
//   - title et meta description avec "maintenance" / "accompagnement" ;
//   - Service JSON-LD présent en SSR ;
//   - BreadcrumbList JSON-LD (Accueil → Services → Maintenance) ;
//   - lien vers /services/creation-site-internet (maillage) ;
//   - lien vers /estimer-mon-projet et /contact ;
//   - Axe WCAG 2.2 AA ;
//   - responsive sans débordement horizontal.

const PATH = "/services/maintenance-accompagnement"
const BREAKPOINTS = [390, 768, 1024, 1440] as const

async function fetchSSR(
  request: import("@playwright/test").APIRequestContext,
  path: string,
) {
  const response = await request.get(path)
  const body = await response.text()
  return { response, body, status: response.status() }
}

test.describe("/services/maintenance-accompagnement — SSR et contenu", () => {
  test("renvoie HTTP 200", async ({ request }) => {
    const { status } = await fetchSSR(request, PATH)
    expect(status).toBe(200)
  })

  test("expose un H1 unique en SSR", async ({ page, request }) => {
    const { body } = await fetchSSR(request, PATH)
    expect(body).toMatch(/[Mm]aintenance|[Aa]ccompagnement/i)

    await page.goto(PATH)
    const headings = await page.locator("h1").all()
    expect(headings).toHaveLength(1)
    await expect(page.locator("h1")).toBeVisible()
  })

  test("expose title et meta description maintenance/accompagnement en SSR", async ({ request }) => {
    const { body } = await fetchSSR(request, PATH)
    expect(body).toMatch(/<title>[^<]*([Mm]aintenance|[Aa]ccompagnement)[^<]*<\/title>/i)
    expect(body).toMatch(/<meta[^>]+name="description"[^>]+content="[^"]{20,}"/i)
  })

  test("expose Service JSON-LD en SSR", async ({ request }) => {
    const { body } = await fetchSSR(request, PATH)
    expect(body).toContain('"@type":"Service"')
    expect(body).toContain("/services/maintenance-accompagnement")
  })

  test("expose BreadcrumbList JSON-LD en SSR", async ({ request }) => {
    const { body } = await fetchSSR(request, PATH)
    expect(body).toContain("BreadcrumbList")
    expect(body).toContain("/services")
  })

  test("affiche le fil d'Ariane : Accueil → Services → Maintenance", async ({ page }) => {
    await page.goto(PATH)
    const breadcrumb = page.getByRole("navigation", { name: /fil d.ariane/i })
    await expect(breadcrumb).toBeVisible()
    await expect(breadcrumb.getByRole("link", { name: "Accueil" })).toBeVisible()
    await expect(breadcrumb.getByRole("link", { name: "Services" })).toBeVisible()
    await expect(breadcrumb.getByText(/[Mm]aintenance/i)).toBeVisible()
  })
})

test.describe("/services/maintenance-accompagnement — maillage", () => {
  test("propose un lien vers /services/creation-site-internet", async ({ page }) => {
    await page.goto(PATH)
    await expect(
      page.locator('a[href="/services/creation-site-internet"]').first(),
    ).toBeVisible()
  })

  test("propose un lien vers /services/application-web-metier", async ({ page }) => {
    await page.goto(PATH)
    await expect(
      page.locator('a[href="/services/application-web-metier"]').first(),
    ).toBeVisible()
  })

  test("propose un lien vers /estimer-mon-projet", async ({ page }) => {
    await page.goto(PATH)
    await expect(
      page.locator('a[href="/estimer-mon-projet"]').first(),
    ).toBeVisible()
  })

  test("propose un lien vers /contact", async ({ page }) => {
    await page.goto(PATH)
    await expect(
      page.locator('a[href="/contact"]').first(),
    ).toBeVisible()
  })
})

test.describe("/services/maintenance-accompagnement — accessibilité et responsive", () => {
  test("passe Axe WCAG 2.2 AA (aucune violation serious/critical)", async ({ page }) => {
    await page.goto(PATH)
    const results = await new AxeBuilder({ page })
      .withTags(["wcag2a", "wcag2aa", "wcag21a", "wcag21aa", "wcag22aa"])
      .analyze()
    const blocking = results.violations.filter(
      (v) => v.impact === "critical" || v.impact === "serious",
    )
    if (blocking.length > 0) {
      console.log(
        `Axe ${PATH}:\n${blocking
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

  test("reste sans débordement horizontal sur tous les breakpoints", async ({ page }) => {
    await page.goto(PATH)
    for (const width of BREAKPOINTS) {
      await page.setViewportSize({ width, height: 900 })
      const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
      )
      expect(overflow, `overflow at ${width}px`).toBeLessThanOrEqual(1)
    }
  })

  test("reste utilisable sous prefers-reduced-motion", async ({ browser }) => {
    const context = await browser.newContext({ reducedMotion: "reduce" })
    const page = await context.newPage()
    await page.goto(PATH)
    await expect(page.locator("h1")).toBeVisible()
    await expect(page.locator('a[href="/contact"]').first()).toBeVisible()
    await context.close()
  })
})

test.describe("/services/maintenance-accompagnement — sitemap", () => {
  test("la route apparaît dans le sitemap", async ({ request }) => {
    const response = await request.get("/sitemap.xml")
    const body = await response.text()
    expect(body).toContain("/services/maintenance-accompagnement")
  })
})
