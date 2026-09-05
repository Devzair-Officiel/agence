import { expect, test } from "@playwright/test"

// E2E — Pages détail /realisations/[slug] (SEO-COM-4)
//
// Vérifie pour chaque étude de cas publiée :
//   - HTTP 200 ;
//   - H1 = nom du projet (en SSR) ;
//   - title/meta description spécifiques ;
//   - fil d'Ariane : Accueil → Réalisations → Nom du projet ;
//   - BreadcrumbList JSON-LD ;
//   - lien retour vers /realisations ;
//   - lien vers le site client (href externe) si disponible ;
//   - lien vers /services/creation-site-internet pour les vitrines.
//
// Vérifie également le comportement 404 pour un slug inconnu,
// e-shop-admin (planned) et haramain-prestige (planned — autorisation
// de publication client non confirmée).

const PUBLISHED_STUDIES = [
  {
    slug: "kitchen-meat",
    name: "Kitchen Meat",
    hasExternalLink: true,
    hasServiceLink: true,
  },
  {
    slug: "nidemiel",
    name: "Nidemiel",
    hasExternalLink: true,
    hasServiceLink: false,
  },
  {
    slug: "mizan",
    name: "Mizan",
    hasExternalLink: true,
    hasServiceLink: false,
  },
  {
    slug: "al-mumayiz",
    name: "Al Mumayiz",
    hasExternalLink: true,
    hasServiceLink: true,
  },
]

async function fetchSSR(
  request: import("@playwright/test").APIRequestContext,
  path: string,
) {
  const response = await request.get(path)
  const body = await response.text()
  return { response, body, status: response.status() }
}

for (const study of PUBLISHED_STUDIES) {
  const PATH = `/realisations/${study.slug}`

  test.describe(`/realisations/${study.slug}`, () => {
    test("renvoie HTTP 200", async ({ request }) => {
      const { status } = await fetchSSR(request, PATH)
      expect(status).toBe(200)
    })

    test("expose le nom du projet en SSR", async ({ request }) => {
      const { body } = await fetchSSR(request, PATH)
      expect(body).toContain(study.name)
    })

    test("expose title et meta description avec le nom du projet", async ({
      request,
    }) => {
      const { body } = await fetchSSR(request, PATH)
      expect(body).toMatch(new RegExp(`<title>[^<]*${study.name}[^<]*<\\/title>`, "i"))
      expect(body).toMatch(/<meta[^>]+name="description"[^>]+content="[^"]{20,}"/i)
    })

    test("affiche le H1 correspondant au nom du projet", async ({ page }) => {
      await page.goto(PATH)
      await expect(page.locator("h1")).toContainText(study.name)
    })

    test("affiche un H1 unique", async ({ page }) => {
      await page.goto(PATH)
      const headings = await page.locator("h1").all()
      expect(headings).toHaveLength(1)
    })

    test("fil d'Ariane : Accueil → Réalisations → projet", async ({ page }) => {
      await page.goto(PATH)
      const breadcrumb = page.getByRole("navigation", { name: /fil d.ariane/i })
      await expect(breadcrumb).toBeVisible()
      await expect(breadcrumb.getByRole("link", { name: "Accueil" })).toBeVisible()
      await expect(
        breadcrumb.getByRole("link", { name: "Réalisations" }),
      ).toBeVisible()
      await expect(breadcrumb.getByText(study.name)).toBeVisible()
    })

    test("BreadcrumbList JSON-LD présent en SSR", async ({ request }) => {
      const { body } = await fetchSSR(request, PATH)
      expect(body).toContain("BreadcrumbList")
      expect(body).toContain("/realisations")
    })

    test("affiche un lien retour vers /realisations", async ({ page }) => {
      await page.goto(PATH)
      await expect(
        page.locator('a[href="/realisations"]').first(),
      ).toBeVisible()
    })

    if (study.hasExternalLink) {
      test("affiche le lien vers le site client en ligne", async ({ page }) => {
        await page.goto(PATH)
        const extLink = page.locator('a[target="_blank"]').first()
        await expect(extLink).toBeVisible()
        await expect(extLink).toHaveAttribute("rel", /noopener/)
      })
    }

    if (study.hasServiceLink) {
      test("propose le lien de maillage vers /services/creation-site-internet", async ({
        page,
      }) => {
        await page.goto(PATH)
        await expect(
          page.locator('a[href="/services/creation-site-internet"]').first(),
        ).toBeVisible()
      })
    }
  })
}

test.describe("Slugs inconnus ou non publiés → 404", () => {
  test("e-shop-admin (planned) retourne 404", async ({ request }) => {
    const { status } = await fetchSSR(request, "/realisations/e-shop-admin")
    expect(status).toBe(404)
  })

  test("haramain-prestige (planned) retourne 404", async ({ request }) => {
    const { status } = await fetchSSR(request, "/realisations/haramain-prestige")
    expect(status).toBe(404)
  })

  test("slug inexistant retourne 404", async ({ request }) => {
    const { status } = await fetchSSR(request, "/realisations/projet-qui-nexiste-pas")
    expect(status).toBe(404)
  })
})
