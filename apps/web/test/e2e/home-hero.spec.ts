import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'

// Contrôles bout-en-bout sur la page d'accueil publique `/`.
//
// Portée :
//   - le hero (H1, introduction, deux CTA, réassurance) ;
//   - le graphe SVG des cinq pôles (rôle, titre, cinq noeuds) ;
//   - le SEO réel (title, description, canonical, OG, Twitter, lang) ;
//   - le mode `prefers-reduced-motion` (état final visible sans animation) ;
//   - Axe (aucune violation sérieuse ou critique) ;
//   - trois largeurs canoniques (390, 768, 1440) sans débordement.
//
// On ne vérifie pas ici le fond éditorial des sections suivantes : elles
// seront ajoutées en Phase 5B.

test.describe('/ (home)', () => {
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

    const response = await page.goto('/')
    expect(response?.status(), 'HTTP status').toBe(200)
    expect(consoleErrors, 'console.error').toEqual([])
    expect(routerWarnings, 'vue-router warnings').toEqual([])
  })

  test('exposes exactly one H1 with the marketing sentence', async ({ page }) => {
    await page.goto('/')
    const h1 = page.locator('h1')
    await expect(h1).toHaveCount(1)
    const text = (await h1.innerText()).replace(/\s+/g, ' ').trim()
    expect(text).toContain('Des solutions digitales complètes')
    expect(text).toContain('visible, crédible et efficace')
    expect(text).toContain('en ligne.')
  })

  test('renders the introduction, both CTAs and the reassurance list', async ({ page }) => {
    await page.goto('/')

    // Le texte apparaît aussi dans le pied de page (site.description), on cible
    // explicitement le paragraphe de tête du hero pour rester en mode strict.
    await expect(page.locator('.home-hero__lead')).toContainText(
      'Sites internet, applications métier',
    )

    const primary = page.locator('.home-hero__ctas a[href="/contact"]')
    const secondary = page.locator('.home-hero__ctas a[href="#realisations"]')
    await expect(primary).toBeVisible()
    await expect(primary).toContainText('Parler de votre projet')
    await expect(secondary).toBeVisible()
    await expect(secondary).toContainText('Découvrir nos réalisations')

    const items = page.locator('.home-hero__reassurance li')
    await expect(items).toHaveCount(2)
  })

  test('renders the ecosystem SVG with the five pillars', async ({ page }) => {
    await page.goto('/')
    const svg = page.locator('svg.home-ecosystem-graph')
    await expect(svg).toHaveCount(1)
    await expect(svg).toHaveAttribute('role', 'img')

    // Nom accessible via aria-label (plus de <title> interne, qui déclenchait
    // le tooltip natif du navigateur au survol).
    await expect(svg).toHaveAttribute('aria-label', /cinq pôles/)
    await expect(svg.locator('title')).toHaveCount(0)
    await expect(svg.locator('.home-ecosystem-graph__pillar')).toHaveCount(5)
    await expect(svg.locator('.home-ecosystem-graph__pillar-link')).toHaveCount(5)

    for (const label of [
      'Concevoir',
      'Construire',
      'Valoriser',
      'Visibilité',
      'Faire évoluer',
    ]) {
      await expect(svg.getByText(label, { exact: true }).first()).toBeVisible()
    }
  })

  test('emits the canonical, Open Graph and Twitter meta correctly', async ({ page }) => {
    await page.goto('/')

    await expect(page).toHaveTitle(
      /Agence digitale pour sites web, applications et visibilité/,
    )

    const html = page.locator('html')
    await expect(html).toHaveAttribute('lang', 'fr')

    const description = await page
      .locator('meta[name="description"]')
      .getAttribute('content')
    expect(description ?? '').toContain(
      'Devzair accompagne les entreprises dans la création',
    )

    const canonical = await page
      .locator('link[rel="canonical"]')
      .getAttribute('href')
    expect(canonical).toMatch(/\/$/)

    const ogType = await page
      .locator('meta[property="og:type"]')
      .getAttribute('content')
    expect(ogType).toBe('website')

    const ogTitle = await page
      .locator('meta[property="og:title"]')
      .getAttribute('content')
    expect(ogTitle ?? '').toContain('Agence digitale')

    const twitterCard = await page
      .locator('meta[name="twitter:card"]')
      .getAttribute('content')
    // Pas de logo dispo aujourd'hui → summary (pas summary_large_image).
    expect(twitterCard).toBe('summary')
  })

  test('respects prefers-reduced-motion by showing the final state at once', async ({
    browser,
  }) => {
    const context = await browser.newContext({ reducedMotion: 'reduce' })
    const page = await context.newPage()
    await page.goto('/')

    const spokeOffset = await page
      .locator('.home-ecosystem-graph__spoke')
      .first()
      .evaluate((el) =>
        Number.parseFloat(getComputedStyle(el).strokeDashoffset || '0'),
      )
    expect(spokeOffset).toBe(0)

    const pillarOpacity = await page
      .locator('.home-ecosystem-graph__pillar')
      .first()
      .evaluate((el) => Number.parseFloat(getComputedStyle(el).opacity))
    expect(pillarOpacity).toBe(1)

    await context.close()
  })

  test('passes Axe with no serious or critical violation', async ({ page }) => {
    await page.goto('/')

    const results = await new AxeBuilder({ page })
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'])
      .analyze()

    const blocking = results.violations.filter(
      (v) => v.impact === 'critical' || v.impact === 'serious',
    )

    if (blocking.length > 0) {
      const lines = blocking.map(
        (v) =>
          `- [${v.impact}] ${v.id}: ${v.help}\n    ${v.nodes
            .map((n) => n.target.join(' '))
            .join('\n    ')}`,
      )
      console.log(`Axe blocking violations:\n${lines.join('\n')}`)
    }

    expect(blocking, 'Axe serious/critical violations').toEqual([])
  })
})

const viewports = [
  { name: 'small mobile 320x800', width: 320, height: 800 },
  { name: 'mobile 390x844', width: 390, height: 844 },
  { name: 'tablet 768x1024', width: 768, height: 1024 },
  { name: 'laptop 1024x768', width: 1024, height: 768 },
  { name: 'desktop 1440x900', width: 1440, height: 900 },
  { name: 'wide desktop 1920x1080', width: 1920, height: 1080 },
]

for (const viewport of viewports) {
  test.describe(`/ (home) — ${viewport.name}`, () => {
    test.use({ viewport: { width: viewport.width, height: viewport.height } })

    test('does not overflow horizontally', async ({ page }) => {
      await page.goto('/')
      const overflow = await page.evaluate(() => {
        const { scrollWidth, clientWidth } = document.documentElement
        return scrollWidth - clientWidth
      })
      expect(overflow).toBeLessThanOrEqual(1)
    })

    test('shows the H1, both CTAs and the SVG graph', async ({ page }) => {
      await page.goto('/')
      await expect(page.locator('h1')).toBeVisible()
      await expect(
        page.locator('.home-hero__ctas a[href="/contact"]'),
      ).toBeVisible()
      await expect(
        page.locator('.home-hero__ctas a[href="#realisations"]'),
      ).toBeVisible()
      await expect(page.locator('svg.home-ecosystem-graph')).toBeVisible()
    })

    test('keeps every graph label and description inside the visible SVG', async ({
      page,
    }) => {
      await page.emulateMedia({ reducedMotion: 'reduce' })
      await page.goto('/')
      const svg = page.locator('svg.home-ecosystem-graph')
      await expect(svg.locator('.home-ecosystem-graph__pillar').first()).toBeVisible()

      const svgBox = await svg.boundingBox()
      expect(svgBox).not.toBeNull()
      const texts = svg.locator(
        '.home-ecosystem-graph__pillar-label, .home-ecosystem-graph__pillar-description',
      )
      await expect(texts).toHaveCount(10)

      for (const textNode of await texts.all()) {
        const box = await textNode.boundingBox()
        expect(box).not.toBeNull()
        expect(box!.x).toBeGreaterThanOrEqual(svgBox!.x - 1)
        expect(box!.y).toBeGreaterThanOrEqual(svgBox!.y - 1)
        expect(box!.x + box!.width).toBeLessThanOrEqual(
          svgBox!.x + svgBox!.width + 1,
        )
        expect(box!.y + box!.height).toBeLessThanOrEqual(
          svgBox!.y + svgBox!.height + 1,
        )
      }
    })
  })
}

test('reflows the hero without overlap at a 200% desktop-zoom equivalent', async ({
  page,
}) => {
  // À 200 % sur un viewport desktop de 1440 px, la largeur de mise en page
  // disponible est équivalente à 720 CSS px.
  await page.setViewportSize({ width: 720, height: 900 })
  await page.goto('/')

  const overflow = await page.evaluate(
    () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
  )
  expect(overflow).toBeLessThanOrEqual(1)

  const content = await page.locator('.home-hero__content').boundingBox()
  const visual = await page.locator('.home-hero__visual').boundingBox()
  expect(content).not.toBeNull()
  expect(visual).not.toBeNull()
  expect(visual!.y).toBeGreaterThanOrEqual(content!.y + content!.height - 1)
})
