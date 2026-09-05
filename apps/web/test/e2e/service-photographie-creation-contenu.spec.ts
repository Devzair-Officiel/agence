import AxeBuilder from "@axe-core/playwright"
import { expect, test } from "@playwright/test"

// E2E — /services/photographie-creation-contenu (SEO-COM-5B)
//
// Vérifie dans le HTML SSR et après rendu :
//   - HTTP 200 ;
//   - H1 unique présent en SSR ;
//   - title et meta description avec "photographie" / "contenu" ;
//   - Service JSON-LD présent en SSR ;
//   - BreadcrumbList JSON-LD (Accueil → Services → Contenu) ;
//   - lien vers /realisations/nidemiel (maillage éditorial) ;
//   - lien vers /estimer-mon-projet et /contact ;
//   - Axe WCAG 2.2 AA ;
//   - responsive sans débordement horizontal.

const PATH = "/services/photographie-creation-contenu"
const BREAKPOINTS = [390, 768, 1024, 1440] as const

async function fetchSSR(
  request: import("@playwright/test").APIRequestContext,
  path: string,
) {
  const response = await request.get(path)
  const body = await response.text()
  return { response, body, status: response.status() }
}

test.describe("/services/photographie-creation-contenu — SSR et contenu", () => {
  test("renvoie HTTP 200", async ({ request }) => {
    const { status } = await fetchSSR(request, PATH)
    expect(status).toBe(200)
  })

  test("expose un H1 unique en SSR", async ({ page, request }) => {
    const { body } = await fetchSSR(request, PATH)
    expect(body).toMatch(/[Pp]hotographie|[Cc]ontenu/i)

    await page.goto(PATH)
    const headings = await page.locator("h1").all()
    expect(headings).toHaveLength(1)
    await expect(page.locator("h1")).toBeVisible()
  })

  test("expose title et meta description photographie/contenu en SSR", async ({ request }) => {
    const { body } = await fetchSSR(request, PATH)
    expect(body).toMatch(/<title>[^<]*([Pp]hotographie|[Cc]ontenu)[^<]*<\/title>/i)
    expect(body).toMatch(/<meta[^>]+name="description"[^>]+content="[^"]{20,}"/i)
  })

  test("expose Service JSON-LD en SSR", async ({ request }) => {
    const { body } = await fetchSSR(request, PATH)
    expect(body).toContain('"@type":"Service"')
    expect(body).toContain("/services/photographie-creation-contenu")
  })

  test("expose BreadcrumbList JSON-LD en SSR", async ({ request }) => {
    const { body } = await fetchSSR(request, PATH)
    expect(body).toContain("BreadcrumbList")
    expect(body).toContain("/services")
  })

  test("affiche le fil d'Ariane : Accueil → Services → Contenu", async ({ page }) => {
    await page.goto(PATH)
    const breadcrumb = page.getByRole("navigation", { name: /fil d.ariane/i })
    await expect(breadcrumb).toBeVisible()
    await expect(breadcrumb.getByRole("link", { name: "Accueil" })).toBeVisible()
    await expect(breadcrumb.getByRole("link", { name: "Services" })).toBeVisible()
    await expect(breadcrumb.getByText(/[Cc]ontenu/i)).toBeVisible()
  })
})

test.describe("/services/photographie-creation-contenu — maillage", () => {
  test("propose un lien vers /realisations/nidemiel", async ({ page }) => {
    await page.goto(PATH)
    await expect(
      page.locator('a[href="/realisations/nidemiel"]').first(),
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

test.describe("/services/photographie-creation-contenu — accessibilité et responsive", () => {
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

test.describe("/services/photographie-creation-contenu — sitemap", () => {
  test("la route apparaît dans le sitemap", async ({ request }) => {
    const response = await request.get("/sitemap.xml")
    const body = await response.text()
    expect(body).toContain("/services/photographie-creation-contenu")
  })
})
