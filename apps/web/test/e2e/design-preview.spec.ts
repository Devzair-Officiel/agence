import { expect, test } from '@playwright/test'

// Smoke test unique — Phase 2.
//
// Vérifie que `/design-preview` s'ouvre sans erreur JS ni avertissement
// vue-router ("R0004: No match found") : ces avertissements ont déjà causé
// une régression visible (cf. correctifs sur les NuxtLink `external`).
// On garde le périmètre étroit : rien d'autre n'est encore assez stable
// pour être testé en E2E de façon utile.

test('design-preview renders without console errors or router warnings', async ({ page }) => {
  const consoleErrors: string[] = []
  const routerWarnings: string[] = []

  page.on('console', (message) => {
    const text = message.text()
    if (message.type() === 'error') {
      consoleErrors.push(text)
    }
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
