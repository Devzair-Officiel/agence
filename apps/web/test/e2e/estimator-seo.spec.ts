import { expect, test } from "@playwright/test"

// E2E SEO — /estimer-mon-projet après SEO-COM-3.
//
// Vérifie dans le HTML SSR (avant hydratation) :
//   - HTTP 200 ;
//   - H1 unique avec la phrase orientée budget/prix ;
//   - balise title, meta description ;
//   - contenu éditorial présent en SSR (EstimatorEditorial) ;
//   - lien de maillage vers /services/creation-site-internet.

const ESTIMATOR_PATH = "/estimer-mon-projet"

async function fetchSSR(
  request: import("@playwright/test").APIRequestContext,
  path: string,
) {
  const response = await request.get(path)
  expect(response.status(), `${path} status`).toBe(200)
  const body = await response.text()
  return { response, body }
}

test.describe("/estimer-mon-projet — SEO et contenu éditorial SSR", () => {
  test("renvoie 200 avec H1 unique orienté budget", async ({ page, request }) => {
    const { body } = await fetchSSR(request, ESTIMATOR_PATH)
    expect(body).toContain("Quel budget prévoir pour votre projet web ?")

    await page.goto(ESTIMATOR_PATH)
    const headings = await page.locator("h1").all()
    expect(headings).toHaveLength(1)
    await expect(page.locator("h1")).toContainText(
      "Quel budget prévoir pour votre projet web ?",
    )
  })

  test("expose le SEO complet dans le HTML initial", async ({ request }) => {
    const { body } = await fetchSSR(request, ESTIMATOR_PATH)
    expect(body).toMatch(
      /<title>[^<]*[Bb]udget[^<]*[Dd]evzair[^<]*<\/title>/,
    )
    expect(body).toMatch(
      /<meta[^>]+name="description"[^>]+content="[^"]*budget[^"]*"/i,
    )
  })

  test("publie le contenu éditorial EstimatorEditorial en SSR", async ({ request }) => {
    const { body } = await fetchSSR(request, ESTIMATOR_PATH)
    // Section transparence prix
    expect(body).toContain("Pourquoi une fourchette")
    // Section facteurs de variation
    expect(body).toContain("Ce qui fait r\u00e9ellement varier")
    // Section principe Devzair
    expect(body).toContain("Investir l\u00e0 o\u00f9 \u00e7a compte")
    // FAQ
    expect(body).toContain("Questions fr\u00e9quentes")
    expect(body).toContain("engage-t-elle")
  })

  test("propose le lien de maillage vers /services/creation-site-internet", async ({
    page,
  }) => {
    await page.goto(ESTIMATOR_PATH)
    await expect(
      page.locator('a[href="/services/creation-site-internet"]').first(),
    ).toBeVisible()
  })

  test("le contenu éditorial apparaît après le configurateur dans le DOM", async ({
    request,
  }) => {
    const { body } = await fetchSSR(request, ESTIMATOR_PATH)
    const shellPos = body.indexOf("data-hydrated")
    // La section éditoriale est identifiée par son aria-label stable
    const editorialPos = body.indexOf("Comprendre le budget d")
    expect(shellPos).toBeGreaterThan(-1)
    expect(editorialPos).toBeGreaterThan(shellPos)
  })
})
