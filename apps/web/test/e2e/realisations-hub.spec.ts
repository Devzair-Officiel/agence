import { expect, test } from "@playwright/test"

// E2E — Hub /realisations (SEO-COM-4)
//
// Vérifie dans le HTML SSR (avant hydratation) et après rendu :
//   - HTTP 200 ;
//   - H1 unique présent en SSR ;
//   - balise title, meta description avec "réalisations" ;
//   - quatre cartes publiées (pas e-shop-admin, pas haramain-prestige) ;
//   - liens vers les quatre pages détail ;
//   - lien de maillage vers /services/creation-site-internet ;
//   - fil d'Ariane : Accueil → Réalisations ;
//   - BreadcrumbList JSON-LD présent dans le HTML SSR.

const HUB_PATH = "/realisations"

async function fetchSSR(
  request: import("@playwright/test").APIRequestContext,
  path: string,
) {
  const response = await request.get(path)
  expect(response.status(), `${path} status`).toBe(200)
  const body = await response.text()
  return { response, body }
}

test.describe("/realisations — hub SEO-COM-4", () => {
  test("renvoie HTTP 200", async ({ request }) => {
    const { response } = await fetchSSR(request, HUB_PATH)
    expect(response.status()).toBe(200)
  })

  test("expose un H1 unique en SSR", async ({ page, request }) => {
    const { body } = await fetchSSR(request, HUB_PATH)
    expect(body).toContain("Des projets web conçus pour un besoin réel")

    await page.goto(HUB_PATH)
    const headings = await page.locator("h1").all()
    expect(headings).toHaveLength(1)
    await expect(page.locator("h1")).toContainText(
      "Des projets web conçus pour un besoin réel",
    )
  })

  test("expose le SEO complet dans le HTML initial", async ({ request }) => {
    const { body } = await fetchSSR(request, HUB_PATH)
    expect(body).toMatch(
      /<title>[^<]*[Rr]\u00e9alisations[^<]*[Dd]evzair[^<]*<\/title>/,
    )
    expect(body).toMatch(
      /<meta[^>]+name="description"[^>]+content="[^"]*r\u00e9alis[^"]*"/i,
    )
  })

  test("affiche les quatre études de cas publiées", async ({ page }) => {
    await page.goto(HUB_PATH)
    await expect(page.getByRole("link", { name: /Kitchen Meat/i })).toBeVisible()
    await expect(page.getByRole("link", { name: /Nidemiel/i })).toBeVisible()
    await expect(page.getByRole("link", { name: /Mizan/i })).toBeVisible()
    await expect(page.getByRole("link", { name: /Al Mumayiz/i })).toBeVisible()
  })

  test("n'affiche pas e-shop-admin (planned)", async ({ page }) => {
    await page.goto(HUB_PATH)
    const body = await page.content()
    expect(body).not.toContain("/realisations/e-shop-admin")
  })

  test("les cartes pointent vers les pages détail correctes", async ({ page }) => {
    await page.goto(HUB_PATH)
    await expect(
      page.locator('a[href="/realisations/kitchen-meat"]').first(),
    ).toBeVisible()
    await expect(
      page.locator('a[href="/realisations/nidemiel"]').first(),
    ).toBeVisible()
    await expect(
      page.locator('a[href="/realisations/mizan"]').first(),
    ).toBeVisible()
    await expect(
      page.locator('a[href="/realisations/al-mumayiz"]').first(),
    ).toBeVisible()
  })

  test("n'affiche pas haramain-prestige (planned)", async ({ page }) => {
    await page.goto(HUB_PATH)
    const body = await page.content()
    expect(body).not.toContain("/realisations/haramain-prestige")
  })

  test("propose le lien de maillage vers /services/creation-site-internet", async ({
    page,
  }) => {
    await page.goto(HUB_PATH)
    await expect(
      page.locator('a[href="/services/creation-site-internet"]').first(),
    ).toBeVisible()
  })

  test("fil d'Ariane : Accueil → Réalisations", async ({ page }) => {
    await page.goto(HUB_PATH)
    const breadcrumb = page.getByRole("navigation", { name: /fil d.ariane/i })
    await expect(breadcrumb).toBeVisible()
    await expect(breadcrumb.getByRole("link", { name: "Accueil" })).toBeVisible()
    await expect(breadcrumb.getByText("Réalisations")).toBeVisible()
  })

  test("BreadcrumbList JSON-LD présent en SSR", async ({ request }) => {
    const { body } = await fetchSSR(request, HUB_PATH)
    expect(body).toContain("BreadcrumbList")
    expect(body).toContain("/realisations")
  })
})
