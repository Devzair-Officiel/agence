import { expect, test } from '@playwright/test'

const footerSelector = 'footer.site-footer'

test.describe('Footer Devzair', () => {
  test('est présent dans le HTML SSR avec ses trois groupes et ses vrais liens', async ({
    request,
  }) => {
    const response = await request.get('/')
    expect(response.ok()).toBe(true)

    const html = await response.text()
    expect(html).toMatch(/<footer class="site-footer"(?:\s|>)/)
    expect(html).toContain('Agence digitale')
    expect(html).toContain('Sites –')
    expect(html).toContain('Expertises')
    expect(html).toContain('Services')
    expect(html).toContain('Découvrir')
    expect(html).toContain('Parler de votre projet')
    expect(html).toContain('Retour en haut')
  })

  test('rend cinq expertises, les CTAs et aucun lien vide', async ({ page }) => {
    await page.goto('/')

    const footer = page.locator(footerSelector)
    await expect(footer).toBeVisible()
    await expect(footer.locator('.site-footer__column-title')).toHaveText([
      'Expertises',
      'Services',
      'Découvrir',
    ])
    await expect(footer.locator('a[href^="/expertises/"]')).toHaveCount(5)

    const discoverCol = footer.locator('.site-footer__column').nth(2)
    await expect(discoverCol.locator('a[href="/ressources"]')).toHaveCount(1)
    const cta = discoverCol.locator('.site-footer__cta[href="/contact"]')
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

  test('empile marque et colonnes de navigation sans débordement sur mobile', async ({
    page,
  }) => {
    await page.setViewportSize({ width: 390, height: 844 })
    await page.goto('/')

    const footer = page.locator(footerSelector)
    const brand = await footer.locator('.site-footer__brand').boundingBox()
    const col0 = await footer.locator('.site-footer__column').nth(0).boundingBox()
    const col1 = await footer.locator('.site-footer__column').nth(1).boundingBox()
    const legal = await footer.locator('.site-footer__legal').boundingBox()

    expect(brand).not.toBeNull()
    expect(col0).not.toBeNull()
    expect(col1).not.toBeNull()
    expect(legal).not.toBeNull()
    expect(brand!.y + brand!.height).toBeLessThan(col0!.y)
    expect(col0!.y + col0!.height).toBeLessThan(col1!.y)
    expect(col1!.y + col1!.height).toBeLessThanOrEqual(legal!.y)

    const overflow = await page.evaluate(
      () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    )
    expect(overflow).toBeLessThanOrEqual(1)
  })

  test('conserve la marque au-dessus des trois colonnes lisibles sur tablette', async ({
    page,
  }) => {
    await page.setViewportSize({ width: 768, height: 1024 })
    await page.goto('/')

    const footer = page.locator(footerSelector)
    const brand = await footer.locator('.site-footer__brand').boundingBox()
    const col0 = await footer.locator('.site-footer__column').nth(0).boundingBox()
    const col1 = await footer.locator('.site-footer__column').nth(1).boundingBox()

    expect(brand).not.toBeNull()
    expect(col0).not.toBeNull()
    expect(col1).not.toBeNull()
    expect(brand!.y + brand!.height).toBeLessThan(col0!.y)
    expect(Math.abs(col0!.y - col1!.y)).toBeLessThanOrEqual(1)
    expect(col0!.x + col0!.width).toBeLessThanOrEqual(col1!.x)
  })

  for (const width of [1024, 1440]) {
    test(`équilibre la marque et les trois colonnes de navigation à ${width}px`, async ({
      page,
    }) => {
      await page.setViewportSize({ width, height: 1000 })
      await page.goto('/')

      const footer = page.locator(footerSelector)
      const brand = await footer.locator('.site-footer__brand').boundingBox()
      const col0 = await footer.locator('.site-footer__column').nth(0).boundingBox()
      const col1 = await footer.locator('.site-footer__column').nth(1).boundingBox()
      const col2 = await footer.locator('.site-footer__column').nth(2).boundingBox()

      expect(brand).not.toBeNull()
      expect(col0).not.toBeNull()
      expect(col1).not.toBeNull()
      expect(col2).not.toBeNull()
      expect(Math.abs(brand!.y - col0!.y)).toBeLessThanOrEqual(1)
      expect(col0!.x).toBeGreaterThan(brand!.x + brand!.width)
      expect(col1!.x).toBeGreaterThan(col0!.x)
      expect(col2!.x).toBeGreaterThan(col1!.x)
      expect(brand!.width).toBeGreaterThan(col0!.width)
      expect(brand!.width).toBeGreaterThan(col2!.width)
    })
  }

  test('neutralise les transitions du footer avec prefers-reduced-motion', async ({
    page,
  }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' })
    await page.goto('/')

    const transitionDuration = await page
      .locator(`${footerSelector} .site-footer__cta`)
      .first()
      .evaluate((element) =>
        Number.parseFloat(getComputedStyle(element).transitionDuration),
      )

    // `animations.css` fixe globalement les transitions à 0,01 ms avec
    // `!important` en mode réduit : la durée calculée vaut donc 0,00001 s.
    expect(transitionDuration).toBeLessThanOrEqual(0.00001)
  })
})
