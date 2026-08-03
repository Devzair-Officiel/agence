import { expect, test } from '@playwright/test'

// Contrôles structurels globaux sur `/` — reprise directe de la couverture
// utile de l'ancien `design-preview.spec.ts` (supprimé Phase 5D).
//
// Vérifie :
//   - la page rend sans erreur console ni warning vue-router ;
//   - un seul H1 ;
//   - le skip link cible bien `#main-content` (un vrai `<main>`) ;
//   - header et footer sont présents ;
//   - aucune valeur fictive de contact ne s'est glissée (mailto/tel/example.com).
//
// Les contrôles éditoriaux (contenu, sections) vivent dans les suites
// `home-*.spec.ts`. Les contrôles de comportement (clavier, mobile-nav,
// reduced-motion, responsive) vivent dans leurs suites dédiées.

test.describe('/ (home) — contrôles structurels', () => {
  test('rend sans erreur console ni warning vue-router', async ({ page }) => {
    const consoleErrors: string[] = []
    const routerWarnings: string[] = []

    page.on('console', (message) => {
      const text = message.text()
      if (message.type() === 'error') consoleErrors.push(text)
      if (text.includes('[Vue Router warn]') || text.includes('R0004')) {
        routerWarnings.push(text)
      }
    })
    page.on('pageerror', (error) => {
      consoleErrors.push(error.message)
    })

    const response = await page.goto('/')
    expect(response?.status(), 'HTTP status').toBeLessThan(400)
    await expect(page).toHaveTitle(/Devzair/)
    expect(consoleErrors, 'console.error').toEqual([])
    expect(routerWarnings, 'vue-router warnings').toEqual([])
  })

  test('expose exactement un H1', async ({ page }) => {
    await page.goto('/')
    await expect(page.locator('h1')).toHaveCount(1)
  })

  test('le skip link cible un vrai <main>', async ({ page }) => {
    await page.goto('/')
    const skipLink = page.locator('a.skip-link')
    await expect(skipLink).toHaveCount(1)
    const href = await skipLink.getAttribute('href')
    expect(href).toBe('#main-content')
    const target = page.locator(href!)
    await expect(target).toHaveCount(1)
    expect(await target.evaluate((el) => el.tagName.toLowerCase())).toBe('main')
  })

  test('rend le header et le footer du site', async ({ page }) => {
    await page.goto('/')
    await expect(page.locator('header.site-header')).toHaveCount(1)
    await expect(page.locator('footer.site-footer')).toHaveCount(1)
  })

  test("n'affiche aucune coordonnée fictive tant que site.contact est null", async ({
    page,
  }) => {
    await page.goto('/')
    const bodyText = (await page.locator('body').innerText()).toLowerCase()
    expect(bodyText).not.toMatch(/@example\./)
    expect(bodyText).not.toMatch(/lorem ipsum/)
    expect(bodyText).not.toMatch(/john doe/)
    // Aucun mailto/tel tant que site.contact.email / site.contact.phone
    // ne sont pas renseignés (site.ts). Le seul lien de contact autorisé
    // est la route `/contact` (via primaryCta).
    await expect(page.locator('a[href^="mailto:"]')).toHaveCount(0)
    await expect(page.locator('a[href^="tel:"]')).toHaveCount(0)
  })
})
