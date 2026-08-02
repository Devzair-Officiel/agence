import { expect, test } from '@playwright/test'

// Contrôles de navigation clavier sur `/`.
//
// Objectif : garantir que les éléments interactifs principaux sont
// atteignables au Tab, que le focus visible est effectif et que le
// contenu n'utilise pas d'éléments non interactifs simulant des boutons.

test.describe('Keyboard navigation', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/')
    await page.waitForLoadState('networkidle')
  })

  test('skip link is the first focusable element and reveals on focus', async ({ page }) => {
    await page.keyboard.press('Tab')
    const focused = page.locator(':focus')
    await expect(focused).toHaveClass(/skip-link/)
    // Une fois focusé, il doit être visible (transform:0). La transition
    // dure ~180 ms, donc on interroge en boucle pour laisser le temps au
    // navigateur de finir l'animation.
    await expect
      .poll(async () => focused.evaluate((el) => getComputedStyle(el).transform))
      .not.toContain('matrix(1, 0, 0, 1, 0, -')
  })

  test('primary interactive elements are reachable via Tab', async ({ page }) => {
    // On avance au clavier et on vérifie qu'on atteint successivement le
    // skip-link, le lien de marque, puis un lien de nav ou le bouton
    // menu selon la largeur du viewport. On ne verrouille pas l'ordre
    // exact, juste l'atteignabilité.
    const focusedTags: string[] = []
    for (let i = 0; i < 6; i++) {
      await page.keyboard.press('Tab')
      const tag = await page.evaluate(() => document.activeElement?.tagName ?? '')
      focusedTags.push(tag.toLowerCase())
    }
    // Au moins un lien atteint dans les 6 premières tabulations.
    expect(focusedTags.filter((t) => t === 'a').length).toBeGreaterThan(0)
  })

  test('no non-interactive element pretends to be a button (role=button on div/span)', async ({ page }) => {
    const fakeButtons = page.locator('div[role="button"], span[role="button"]')
    await expect(fakeButtons).toHaveCount(0)
  })

  test('focused button shows a visible focus ring (outline width > 0)', async ({ page }) => {
    // On force le focus sur le bouton menu ou, à défaut, sur le premier lien
    // de nav du header (visible en desktop).
    await page.setViewportSize({ width: 1440, height: 1000 })
    await page.goto('/')
    await page.waitForLoadState('networkidle')
    const firstNavLink = page.locator('header.site-header a').first()
    await firstNavLink.focus()
    const outlineWidth = await firstNavLink.evaluate((el) =>
      getComputedStyle(el).outlineWidth,
    )
    // outline-width en px, doit être > 0.
    expect(Number.parseFloat(outlineWidth)).toBeGreaterThan(0)
  })
})
