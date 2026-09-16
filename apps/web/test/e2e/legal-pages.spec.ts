import AxeBuilder from "@axe-core/playwright"
import { expect, test } from "@playwright/test"

// E2E — Pages légales /mentions-legales et /politique-de-confidentialite
//
// Périmètre :
//   - rendu SSR (status 200, HTML contient le contenu clé) ;
//   - meta robots : noindex, follow (pas d'indexation, pas de nofollow) ;
//   - absence du sitemap (pages réglementaires, pas de valeur SEO) ;
//   - présence dans le footer (SiteFooter.vue via legalNavigation) ;
//   - contenu LCEN minimal : éditeur, hébergeur ;
//   - données RGPD : base légale intérêt légitime sur politique de confidentialité ;
//   - accessibilité Axe WCAG 2.2 AA.

const PAGES = [
  {
    path: "/mentions-legales",
    title: "Mentions légales",
    expectedContent: [
      "835 317 413",
      "OVH SAS",
      "Roubaix",
      "AURELIEN BOUDON",
      "39 avenue Edouard Herriot",
      "06 87 76 37 84",
    ],
  },
  {
    path: "/politique-de-confidentialite",
    title: "Politique de confidentialité",
    expectedContent: ["835 317 413", "intérêt légitime", "trente-six", "vingt-quatre"],
  },
] as const

// ─── SSR ─────────────────────────────────────────────────────────────────────

test.describe("Pages légales — rendu SSR", () => {
  for (const { path, title, expectedContent } of PAGES) {
    test(`${path} retourne 200 avec un H1 et le contenu clé`, async ({ request }) => {
      const response = await request.get(path)
      expect(response.status()).toBe(200)
      const html = await response.text()
      expect(html).toContain(title)
      for (const snippet of expectedContent) {
        expect(html, `${path} should contain "${snippet}"`).toContain(snippet)
      }
    })
  }
})

// ─── Meta robots ─────────────────────────────────────────────────────────────

test.describe("Pages légales — meta robots noindex", () => {
  for (const { path } of PAGES) {
    test(`${path} déclare meta robots noindex, follow`, async ({ request }) => {
      const response = await request.get(path)
      const html = await response.text()
      // noindex présent
      expect(html).toMatch(/<meta[^>]+name="robots"[^>]+content="[^"]*noindex[^"]*"/i)
      // follow présent (pas nofollow)
      expect(html).toMatch(/<meta[^>]+name="robots"[^>]+content="[^"]*follow[^"]*"/i)
      // ne contient pas "nofollow"
      const robotsMatch = html.match(/<meta[^>]+name="robots"[^>]+content="([^"]+)"/i)
      if (robotsMatch) {
        expect(robotsMatch[1]).not.toContain("nofollow")
      }
    })
  }
})

// ─── Sitemap ─────────────────────────────────────────────────────────────────

test("les pages légales sont absentes du sitemap.xml", async ({ request }) => {
  const response = await request.get("/sitemap.xml")
  if (!response.ok()) return // sitemap non disponible en preprod sans indexation
  const xml = await response.text()
  expect(xml).not.toContain("/mentions-legales")
  expect(xml).not.toContain("/politique-de-confidentialite")
})

// ─── Footer ──────────────────────────────────────────────────────────────────

test.describe("Pages légales — présence dans le footer", () => {
  test("le footer de la page d'accueil contient les liens légaux", async ({ page }) => {
    await page.goto("/")
    const footer = page.locator("footer.site-footer")
    await expect(footer).toBeVisible()
    await expect(footer.locator('a[href="/mentions-legales"]')).toBeVisible()
    await expect(footer.locator('a[href="/politique-de-confidentialite"]')).toBeVisible()
  })

  test("le lien Mentions légales du footer mène bien à la page", async ({ page }) => {
    await page.goto("/")
    await page.locator('footer a[href="/mentions-legales"]').click()
    await expect(page).toHaveURL("/mentions-legales")
    await expect(page.locator("h1")).toContainText("Mentions légales")
  })

  test("le lien Politique de confidentialité du footer mène bien à la page", async ({
    page,
  }) => {
    await page.goto("/")
    await page.locator('footer a[href="/politique-de-confidentialite"]').click()
    await expect(page).toHaveURL("/politique-de-confidentialite")
    await expect(page.locator("h1")).toContainText("Politique de confidentialité")
  })
})

// ─── Mentions légales — contenu LCEN ────────────────────────────────────────

test.describe("Mentions légales — contenu LCEN", () => {
  test("affiche le nom commercial Devzair", async ({ page }) => {
    await page.goto("/mentions-legales")
    await expect(page.locator("main")).toContainText("Devzair")
  })

  test("affiche le SIREN et le SIRET", async ({ page }) => {
    await page.goto("/mentions-legales")
    const main = page.locator("main")
    await expect(main).toContainText("835 317 413")
  })

  test("affiche les informations hébergeur (OVH SAS, Roubaix, téléphone)", async ({ page }) => {
    await page.goto("/mentions-legales")
    const main = page.locator("main")
    await expect(main).toContainText("OVH SAS")
    await expect(main).toContainText("Roubaix")
    await expect(main).toContainText("+33 9 72 10 10 07")
  })

  test("le lien vers la politique de confidentialité est présent", async ({ page }) => {
    await page.goto("/mentions-legales")
    const link = page.locator('a[href="/politique-de-confidentialite"]').first()
    await expect(link).toBeVisible()
  })
})

// ─── Mentions légales — identité éditeur (DEV-LEGAL-1) ───────────────────────

test.describe("Mentions légales — identité éditeur LCEN (DEV-LEGAL-1)", () => {
  test("affiche le nom de l'éditeur AURELIEN BOUDON", async ({ page }) => {
    await page.goto("/mentions-legales")
    await expect(page.locator("main")).toContainText("AURELIEN BOUDON")
  })

  test("affiche l'adresse de l'éditeur", async ({ page }) => {
    await page.goto("/mentions-legales")
    await expect(page.locator("main")).toContainText("39 avenue Edouard Herriot")
  })

  test("affiche le téléphone de l'éditeur", async ({ page }) => {
    await page.goto("/mentions-legales")
    await expect(page.locator("main")).toContainText("06 87 76 37 84")
  })

  test("affiche la forme juridique Entrepreneur individuel", async ({ page }) => {
    await page.goto("/mentions-legales")
    await expect(page.locator("main")).toContainText("Entrepreneur individuel")
  })

  test("les trois informations obligatoires LCEN sont présentes dans le HTML SSR", async ({
    request,
  }) => {
    const response = await request.get("/mentions-legales")
    expect(response.status()).toBe(200)
    const html = await response.text()
    expect(html, "SSR doit contenir le nom").toContain("AURELIEN BOUDON")
    expect(html, "SSR doit contenir l'adresse").toContain("39 avenue Edouard Herriot")
    expect(html, "SSR doit contenir le téléphone").toContain("06 87 76 37 84")
  })
})

// ─── Politique de confidentialité — contenu RGPD ────────────────────────────

test.describe("Politique de confidentialité — contenu RGPD", () => {
  test("mentionne la base légale intérêt légitime", async ({ page }) => {
    await page.goto("/politique-de-confidentialite")
    await expect(page.locator("main")).toContainText("intérêt légitime")
  })

  test("mentionne la durée de conservation contact (trente-six mois)", async ({ page }) => {
    await page.goto("/politique-de-confidentialite")
    await expect(page.locator("main")).toContainText("trente-six")
  })

  test("mentionne la durée de conservation estimateur/partenariat (vingt-quatre mois)", async ({ page }) => {
    await page.goto("/politique-de-confidentialite")
    await expect(page.locator("main")).toContainText("vingt-quatre")
  })

  test("mentionne la CNIL", async ({ page }) => {
    await page.goto("/politique-de-confidentialite")
    await expect(page.locator("main")).toContainText("CNIL")
  })

  test("le lien vers /contact est présent pour exercer les droits", async ({ page }) => {
    await page.goto("/politique-de-confidentialite")
    await expect(page.locator('a[href="/contact"]').first()).toBeVisible()
  })
})

// ─── Politique de confidentialité — corrections pré-production ──────────────

test.describe("Politique de confidentialité — corrections pré-production", () => {
  test("ne contient plus l'ancien texte erroné sur les données collectées", async ({ request }) => {
    const response = await request.get("/politique-de-confidentialite")
    const html = await response.text()
    expect(html).not.toContain("Aucune autre donnée n'est collectée")
  })

  test("mentionne le stockage PostgreSQL pour les leads estimateur et partenariats", async ({ page }) => {
    await page.goto("/politique-de-confidentialite")
    await expect(page.locator("main")).toContainText("PostgreSQL")
  })

  test("mentionne l'estimateur de projet dans les données collectées", async ({ page }) => {
    await page.goto("/politique-de-confidentialite")
    await expect(page.locator("main")).toContainText("estimateur")
  })

  test("mentionne le formulaire de partenariat dans les données collectées", async ({ page }) => {
    await page.goto("/politique-de-confidentialite")
    await expect(page.locator("main")).toContainText("partenariat")
  })
})

// ─── Mentions légales — corrections pré-production ───────────────────────────

test.describe("Mentions légales — corrections pré-production", () => {
  test("ne contient plus la clause juridictionnelle erronée de Versailles", async ({ request }) => {
    const response = await request.get("/mentions-legales")
    const html = await response.text()
    expect(html).not.toContain("tribunaux compétents de Versailles seront seuls habilités")
  })
})

// ─── Accessibilité Axe WCAG 2.2 AA ──────────────────────────────────────────

test.describe("Pages légales — accessibilité Axe WCAG 2.2 AA", () => {
  for (const { path } of PAGES) {
    test(`${path} passe Axe sans violation critique`, async ({ page }) => {
      await page.goto(path)
      await expect(page.locator("h1")).toBeVisible()
      const results = await new AxeBuilder({ page })
        .withTags(["wcag2a", "wcag2aa", "wcag21a", "wcag21aa", "wcag22aa"])
        .analyze()
      expect(results.violations).toEqual([])
    })
  }
})
