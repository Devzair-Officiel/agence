import { expect, test } from '@playwright/test'

const footerSelector = 'footer.site-footer'

test.describe('Footer Devzair', () => {
  test('est présent dans le HTML SSR avec ses deux groupes et ses vrais liens', async ({
    request,
  }) => {
    const response = await request.get('/')
    expect(response.ok()).toBe(true)

    const html = await response.text()
    expect(html).toMatch(/<footer class="site-footer"(?:\s|>)/)
    expect(html).toContain('Agence digitale')
    expect(html).toContain('Sites. Applications.')
    expect(html).toContain('Expertises')
    expect(html).toContain('Découvrir')
    expect(html).toContain('Parler de votre projet')
    expect(html).toContain('Retour en haut')
  })

  test('rend cinq expertises, le CTA intégré et aucun lien vide', async ({ page }) => {
    await page.goto('/')

    const footer = page.locator(footerSelector)
    await expect(footer).toBeVisible()
    await expect(footer.locator('.site-footer__column-title')).toHaveText([
      'Expertises',
      'Découvrir',
    ])
    await expect(footer.locator('a[href^="/expertises/"]')).toHaveCount(5)

    const discover = footer.locator('.site-footer__column').nth(1)
    await expect(discover.locator('a[href="/ressources"]')).toHaveCount(1)
    const cta = discover.locator('.site-footer__cta[href="/contact"]')
    await expect(cta).toHaveText(/Parler de votre projet/)

    await cta.focus()
    const ctaMetrics = await cta.evaluate((element) => {
      const styles = getComputedStyle(element)
      return {
        height: element.getBoundingClientRect().height,
        outlineWidth: Number.parseFloat(styles.outlineWidth),
      }
    })
    expect(ctaMetrics.height).toBeGreaterThanOrEqual(40)
    expect(ctaMetrics.outlineWidth).toBeGreaterThan(0)

    await expect(footer.locator('.site-footer__wordmark')).toHaveCount(0)
    await expect(footer.locator('.site-footer__link-arrow')).toHaveCount(0)
    await expect(footer.locator('a[href=""], a:not([href])')).toHaveCount(0)
  })

  test('empile marque, Expertises et Découvrir sans débordement sur mobile', async ({
    page,
  }) => {
    await page.setViewportSize({ width: 390, height: 844 })
    await page.goto('/')

    const footer = page.locator(footerSelector)
    const brand = await footer.locator('.site-footer__brand').boundingBox()
    const expertise = await footer.locator('.site-footer__column').nth(0).boundingBox()
    const discover = await footer.locator('.site-footer__column').nth(1).boundingBox()
    const legal = await footer.locator('.site-footer__legal').boundingBox()

    expect(brand).not.toBeNull()
    expect(expertise).not.toBeNull()
    expect(discover).not.toBeNull()
    expect(legal).not.toBeNull()
    expect(brand!.y + brand!.height).toBeLessThan(expertise!.y)
    expect(expertise!.y + expertise!.height).toBeLessThan(discover!.y)
    expect(discover!.y + discover!.height).toBeLessThanOrEqual(legal!.y)

    const overflow = await page.evaluate(
      () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    )
    expect(overflow).toBeLessThanOrEqual(1)
  })

  test('conserve la marque au-dessus de deux colonnes lisibles sur tablette', async ({
    page,
  }) => {
    await page.setViewportSize({ width: 768, height: 1024 })
    await page.goto('/')

    const footer = page.locator(footerSelector)
    const brand = await footer.locator('.site-footer__brand').boundingBox()
    const expertise = await footer.locator('.site-footer__column').nth(0).boundingBox()
    const discover = await footer.locator('.site-footer__column').nth(1).boundingBox()

    expect(brand).not.toBeNull()
    expect(expertise).not.toBeNull()
    expect(discover).not.toBeNull()
    expect(brand!.y + brand!.height).toBeLessThan(expertise!.y)
    expect(Math.abs(expertise!.y - discover!.y)).toBeLessThanOrEqual(1)
    expect(expertise!.x + expertise!.width).toBeLessThanOrEqual(discover!.x)
  })

  for (const width of [1024, 1440]) {
    test(`équilibre la marque et les deux colonnes de navigation à ${width}px`, async ({
      page,
    }) => {
      await page.setViewportSize({ width, height: 1000 })
      await page.goto('/')

      const footer = page.locator(footerSelector)
      const brand = await footer.locator('.site-footer__brand').boundingBox()
      const expertise = await footer.locator('.site-footer__column').nth(0).boundingBox()
      const discover = await footer.locator('.site-footer__column').nth(1).boundingBox()

      expect(brand).not.toBeNull()
      expect(expertise).not.toBeNull()
      expect(discover).not.toBeNull()
      expect(Math.abs(brand!.y - expertise!.y)).toBeLessThanOrEqual(1)
      expect(expertise!.x).toBeGreaterThan(brand!.x + brand!.width)
      expect(discover!.x).toBeGreaterThan(expertise!.x)
      expect(brand!.width).toBeGreaterThan(expertise!.width)
      expect(brand!.width).toBeGreaterThan(discover!.width)
    })
  }

  test('neutralise les transitions du footer avec prefers-reduced-motion', async ({
    page,
  }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' })
    await page.goto('/')

    const transitionDuration = await page
      .locator(`${footerSelector} .site-footer__cta`)
      .evaluate((element) =>
        Number.parseFloat(getComputedStyle(element).transitionDuration),
      )

    // `animations.css` fixe globalement les transitions à 0,01 ms avec
    // `!important` en mode réduit : la durée calculée vaut donc 0,00001 s.
    expect(transitionDuration).toBeLessThanOrEqual(0.00001)
  })
})
