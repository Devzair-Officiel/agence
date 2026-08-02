import { expect, test } from '@playwright/test'

// Contrôle `prefers-reduced-motion` sur `/design-preview`.
//
// La règle est déclarée globalement dans `animations.css` :
//   * réduit à 0.01ms toutes les animations et transitions ;
//   * neutralise la classe `dv-reveal`.
// Ce test émule la préférence utilisateur et vérifie que :
//   - le contenu reste visible (opacité 1 sur les éléments animables) ;
//   - les transitions sont fortement réduites ;
//   - la navigation reste fonctionnelle (le menu mobile s'ouvre et se ferme).

test.describe('prefers-reduced-motion', () => {
  test.use({
    reducedMotion: 'reduce',
    viewport: { width: 390, height: 844 },
  })

  test.beforeEach(async ({ page }) => {
    // Certains contextes de test.use ne propagent pas toujours l'option
    // reducedMotion au bon moment ; on force l'émulation ici pour rester sûr.
    await page.emulateMedia({ reducedMotion: 'reduce' })
    await page.goto('/design-preview')
    await page.waitForLoadState('networkidle')
  })

  test('main content stays visible', async ({ page }) => {
    // Le contenu ne s'appuie pas sur une classe animation-only pour
    // apparaître : opacity 1 par défaut. On sanity-check quand même sur
    // un H1 et un paragraphe.
    const h1 = page.locator('main h1').first()
    await expect(h1).toBeVisible()
    const opacity = await h1.evaluate((el) => getComputedStyle(el).opacity)
    expect(Number.parseFloat(opacity)).toBe(1)
  })

  test('transitions are neutralised on interactive elements', async ({ page }) => {
    // On mesure la durée de transition sur un BaseButton visible sur mobile
    // (le CTA du header est masqué en dessous de 1024 px). La règle globale
    // force `transition-duration: 0.01ms` en reduced-motion.
    const button = page.locator('main .base-button').first()
    await expect(button).toBeVisible()
    const duration = await button.evaluate((el) =>
      getComputedStyle(el).transitionDuration,
    )
    // La valeur peut être "0.01ms" ou "0s" selon le navigateur. On tolère
    // toute valeur inférieure ou égale à 20ms.
    const parsed = duration
      .split(',')
      .map((d) => {
        const trimmed = d.trim()
        if (trimmed.endsWith('ms')) return Number.parseFloat(trimmed)
        if (trimmed.endsWith('s')) return Number.parseFloat(trimmed) * 1000
        return Number.NaN
      })
      .filter((n) => !Number.isNaN(n))
    expect(parsed.every((ms) => ms <= 20)).toBe(true)
  })

  test('mobile navigation still opens and closes without motion', async ({ page }) => {
    const button = page.locator('button[aria-controls="mobile-navigation"]')
    await button.click()
    await expect(page.locator('#mobile-navigation')).toBeVisible()
    await page.keyboard.press('Escape')
    await expect(page.locator('#mobile-navigation')).toHaveCount(0)
  })
})
