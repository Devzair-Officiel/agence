import { expect, test } from '@playwright/test'

// Smoke + contrôles sémantiques sur `/design-preview`.
//
// Vise à protéger la page interne de vérification visuelle contre les
// régressions structurelles courantes : un seul H1, meta robots noindex,
// skip-link fonctionnel, header et footer présents, pas d'erreur console
// ni d'avertissement vue-router. Aucun contenu marketing n'est vérifié :
// la page est un outil de dev, pas une page publique.

test.describe('/design-preview', () => {
  test('renders without console errors or router warnings', async ({ page }) => {
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

    const response = await page.goto('/design-preview')
    expect(response?.status(), 'HTTP status').toBeLessThan(400)
    await expect(page).toHaveTitle(/Devzair/)
    expect(consoleErrors, 'console.error').toEqual([])
    expect(routerWarnings, 'vue-router warnings').toEqual([])
  })

  test('exposes exactly one H1', async ({ page }) => {
    await page.goto('/design-preview')
    await expect(page.locator('h1')).toHaveCount(1)
  })

  test('is marked as non-indexable', async ({ page }) => {
    await page.goto('/design-preview')
    const robots = await page.locator('meta[name="robots"]').getAttribute('content')
    expect(robots ?? '').toMatch(/noindex/i)
    expect(robots ?? '').toMatch(/nofollow/i)
  })

  test('has a skip link that targets the main content', async ({ page }) => {
    await page.goto('/design-preview')
    const skipLink = page.locator('a.skip-link')
    await expect(skipLink).toHaveCount(1)
    const href = await skipLink.getAttribute('href')
    expect(href).toBe('#main-content')
    const target = page.locator(href!)
    await expect(target).toHaveCount(1)
    // Le main doit être un vrai <main> pour rester utile aux lecteurs d'écran.
    expect(await target.evaluate((el) => el.tagName.toLowerCase())).toBe('main')
  })

  test('renders the site header and footer', async ({ page }) => {
    await page.goto('/design-preview')
    await expect(page.locator('header.site-header')).toHaveCount(1)
    await expect(page.locator('footer.site-footer')).toHaveCount(1)
  })

  test('does not display any fake contact information', async ({ page }) => {
    // Aucune valeur fictive ne doit apparaître tant que les coordonnées
    // ne sont pas validées (cf. site.contact = null). On protège la règle
    // via un simple scan des motifs de contact les plus courants.
    await page.goto('/design-preview')
    const bodyText = (await page.locator('body').innerText()).toLowerCase()
    expect(bodyText).not.toMatch(/@example\.com/)
    expect(bodyText).not.toMatch(/lorem ipsum/)
    expect(bodyText).not.toMatch(/john doe/)
    // Le seul mailto autorisé serait celui du footer si `site.contact.email`
    // était renseigné, ce qui n'est pas le cas aujourd'hui.
    await expect(page.locator('a[href^="mailto:"]')).toHaveCount(0)
    await expect(page.locator('a[href^="tel:"]')).toHaveCount(0)
  })
})
