import { expect, test } from '@playwright/test'

// Contrôles E2E du menu mobile sur `/`.
//
// Le composant est un dialog modal teleporté sur `<body>`. On vérifie ici
// les comportements les plus sensibles à une régression :
//   - la cible tactile du bouton d'ouverture ;
//   - la gestion d'`aria-expanded` ;
//   - le focus qui entre dans le dialog puis revient au bouton ;
//   - Escape ferme ;
//   - le scroll de la page est verrouillé pendant l'ouverture ;
//   - un clic sur un lien de navigation ferme le menu.

test.describe('Mobile navigation', () => {
  test.use({ viewport: { width: 390, height: 844 } })

  test.beforeEach(async ({ page }) => {
    await page.goto('/')
    // Attend l'hydratation Vue avant de simuler des interactions.
    await page.waitForLoadState('networkidle')
  })

  const menuButton = 'button[aria-controls="mobile-navigation"]'
  const dialog = '#mobile-navigation'

  test('menu button meets the 44x44 tactile target', async ({ page }) => {
    const button = page.locator(menuButton)
    await expect(button).toBeVisible()
    const box = await button.boundingBox()
    expect(box, 'menu button bounding box').not.toBeNull()
    expect(box!.width).toBeGreaterThanOrEqual(44)
    expect(box!.height).toBeGreaterThanOrEqual(44)
  })

  test('aria-expanded starts false', async ({ page }) => {
    await expect(page.locator(menuButton)).toHaveAttribute('aria-expanded', 'false')
  })

  test('opens, moves focus into the dialog, then closes via Escape and restores focus', async ({ page }) => {
    const button = page.locator(menuButton)

    await button.click()

    await expect(page.locator(dialog)).toBeVisible()
    await expect(button).toHaveAttribute('aria-expanded', 'true')

    // Le focus doit être dans le dialog (bouton de fermeture).
    await expect(page.locator(`${dialog} button[aria-label="Fermer le menu"]`)).toBeFocused()

    // Le scroll de la page est verrouillé au niveau du <html>.
    await expect(page.locator('html')).toHaveClass(/is-scroll-locked/)

    await page.keyboard.press('Escape')

    await expect(page.locator(dialog)).toHaveCount(0)
    await expect(button).toHaveAttribute('aria-expanded', 'false')
    await expect(page.locator('html')).not.toHaveClass(/is-scroll-locked/)
    // Focus restauré sur le bouton d'ouverture (via requestAnimationFrame).
    await expect(button).toBeFocused()
  })

  test('closes when a navigation link is clicked', async ({ page }) => {
    await page.locator(menuButton).click()
    await expect(page.locator(dialog)).toBeVisible()

    // On clique sur le premier lien de nav. Comme la cible est encore
    // externe (routes pas encore livrées, prop `external` posée sur
    // `NuxtLink`), la navigation ne survient pas immédiatement — on force
    // l'attente sur la disparition du dialog.
    await page.locator(`${dialog} .mobile-navigation__link`).first().click()
    await expect(page.locator(dialog)).toHaveCount(0)
  })

  test('Tab and Shift+Tab stay within the dialog', async ({ page }) => {
    await page.locator(menuButton).click()
    await expect(page.locator(dialog)).toBeVisible()

    // Compte les éléments focusables déclarés par le composant.
    const focusables = page.locator(
      `${dialog} a[href], ${dialog} button:not([disabled])`,
    )
    const count = await focusables.count()
    expect(count).toBeGreaterThan(1)

    // Tab de la fin → doit revenir au premier (focus trap).
    for (let i = 0; i < count - 1; i++) {
      await page.keyboard.press('Tab')
    }
    // On est censé être sur le dernier élément focusable.
    await expect(focusables.last()).toBeFocused()
    await page.keyboard.press('Tab')
    await expect(focusables.first()).toBeFocused()

    // Shift+Tab depuis le premier → revient au dernier.
    await page.keyboard.press('Shift+Tab')
    await expect(focusables.last()).toBeFocused()
  })
})
