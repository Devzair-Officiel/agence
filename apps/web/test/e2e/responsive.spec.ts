import { expect, test } from '@playwright/test'

// Contrôles responsive de base sur `/`.
//
// Vérifie qu'aux trois largeurs canoniques (mobile 390, tablette 768,
// desktop 1440) la page ne déborde pas horizontalement et que le header,
// le contenu principal et le footer restent visibles. On ne teste rien
// au pixel près. Les suites `home-*.spec.ts` couvrent le contenu éditorial
// et des breakpoints supplémentaires (320, 1024).

const viewports = [
  { name: 'mobile 390x844', width: 390, height: 844 },
  { name: 'tablet 768x1024', width: 768, height: 1024 },
  { name: 'desktop 1440x1000', width: 1440, height: 1000 },
]

for (const viewport of viewports) {
  test.describe(`Responsive — ${viewport.name}`, () => {
    test.use({ viewport: { width: viewport.width, height: viewport.height } })

    test.beforeEach(async ({ page }) => {
      await page.goto('/')
    })

    test('does not overflow horizontally', async ({ page }) => {
      const overflow = await page.evaluate(() => {
        const { scrollWidth, clientWidth } = document.documentElement
        return scrollWidth - clientWidth
      })
      // Tolérance 1px pour les scrollbars sub-pixel.
      expect(overflow).toBeLessThanOrEqual(1)
    })

    test('header, main and footer are rendered', async ({ page }) => {
      await expect(page.locator('header.site-header')).toBeVisible()
      await expect(page.locator('main#main-content')).toBeVisible()
      await expect(page.locator('footer.site-footer')).toBeVisible()
    })

    test('body text is at least 14px', async ({ page }) => {
      // On mesure un vrai paragraphe de prose, pas un « eyebrow » (label
      // typographique en Space Mono à 11 px, considéré comme UI et non
      // comme texte de lecture) ni un « detail » mono du hero.
      const size = await page
        .locator('main p:not(.base-eyebrow):not(.home-hero__reassurance-detail)')
        .first()
        .evaluate((el) => Number.parseFloat(getComputedStyle(el).fontSize))
      expect(size).toBeGreaterThanOrEqual(14)
    })
  })
}
