import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'

// Contrôles E2E des trois sections livrées en Phase 5B :
//   - HomeProblems           (« Le constat »)
//   - HomeConnectedApproach  (« La réponse Devzair »)
//   - HomeExpertisePillars   (grille des cinq pôles, ancre #expertises)
//
// Vérifie l'ordre éditorial complet, la structure sémantique (un seul H1,
// H2 par section, H3 imbriqués), la présence des cinq pôles dans le DOM
// SSR, la navigation par ancre #expertises, le respect responsive
// (320/390/768/1440), Axe WCAG 2.2 AA et `prefers-reduced-motion`.

test.describe('/ (home) — sections primaires Phase 5B', () => {
  test('renders the four sections in the expected editorial order', async ({
    page,
  }) => {
    await page.goto('/')
    const orderedSections = await page.evaluate(() => {
      const nodes = Array.from(
        document.querySelectorAll('main .home-page > section'),
      ) as HTMLElement[]
      return nodes.map((section) => section.className.split(/\s+/)[0])
    })
    expect(orderedSections.slice(0, 4)).toEqual([
      'home-hero',
      'home-problems',
      'home-approach',
      'home-pillars',
    ])
  })

  test('exposes exactly one H1 and one H2 per new section', async ({ page }) => {
    await page.goto('/')
    await expect(page.locator('h1')).toHaveCount(1)

    const h2s = await page.locator('main h2').allInnerTexts()
    expect(h2s).toContain(
      'Beaucoup d’entreprises méritent mieux que leur présence en ligne actuelle.',
    )
    expect(h2s).toContain(
      'Une seule démarche, où chaque pôle passe le relais au suivant.',
    )
    expect(h2s).toContain(
      'Des expertises complémentaires, cinq pôles, une même démarche.',
    )
  })

  test('renders the five problems as a real ordered list in SSR HTML', async ({
    page,
  }) => {
    await page.goto('/')
    const list = page.locator('.home-problems__list')
    await expect(list).toHaveCount(1)
    await expect(list.locator('> li')).toHaveCount(5)
    const numbers = await page
      .locator('.home-problems__number')
      .allInnerTexts()
    expect(numbers.map((n) => n.trim())).toEqual([
      '01',
      '02',
      '03',
      '04',
      '05',
    ])
  })

  test('renders the five approach steps with the pillar labels in order', async ({
    page,
  }) => {
    await page.goto('/')
    const steps = page.locator('.home-approach__step')
    await expect(steps).toHaveCount(5)
    const titles = await page
      .locator('.home-approach__step-title')
      .allInnerTexts()
    expect(titles).toEqual([
      'Concevoir',
      'Construire',
      'Valoriser',
      'Développer la visibilité',
      'Faire évoluer',
    ])
    const connectors = page.locator('.home-approach__connector')
    await expect(connectors).toHaveCount(4)
    for (const connector of await connectors.all()) {
      await expect(connector).toHaveAttribute('aria-hidden', 'true')
    }
  })

  test('renders the five expertise cards under the #expertises anchor with three services each', async ({
    page,
  }) => {
    await page.goto('/')
    const section = page.locator('section#expertises')
    await expect(section).toHaveCount(1)

    const cards = section.locator('.home-pillars__card')
    await expect(cards).toHaveCount(5)

    const titles = await cards.locator('.home-pillars__card-title').allInnerTexts()
    expect(titles).toEqual([
      'Concevoir',
      'Construire',
      'Valoriser',
      'Développer la visibilité',
      'Faire évoluer',
    ])

    for (let index = 0; index < 5; index += 1) {
      const services = cards.nth(index).locator('.home-pillars__service')
      await expect(services).toHaveCount(3)
    }
  })

  test('the #expertises anchor is reachable from a link', async ({ page }) => {
    await page.goto('/#expertises')
    const heading = page.locator('#expertises h2')
    await expect(heading).toBeVisible()
  })

  test('SSR HTML already contains every pillar label and long description', async ({
    request,
  }) => {
    const response = await request.get('/')
    expect(response.status()).toBe(200)
    const html = await response.text()
    for (const label of [
      'Concevoir',
      'Construire',
      'Valoriser',
      'Développer la visibilité',
      'Faire évoluer',
    ]) {
      expect(html).toContain(label)
    }
    // Un extrait de chaque longDescription (résiste aux tirets insécables).
    expect(html).toContain('les fondations visuelles')
    expect(html).toContain('sites, plateformes e-commerce et applications')
    expect(html).toContain('contenus visuels et éditoriaux')
    expect(html).toContain('visibilité durable')
    expect(html).toContain('suivi dans le temps')
  })

  test('passes Axe with no serious or critical violation on the full page', async ({
    page,
  }) => {
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

  test('respects prefers-reduced-motion — no smooth scroll on the pillars carousel', async ({
    browser,
  }) => {
    const context = await browser.newContext({ reducedMotion: 'reduce' })
    const page = await context.newPage()
    await page.goto('/')

    const scrollBehavior = await page
      .locator('.home-pillars__grid')
      .evaluate((el) => getComputedStyle(el).scrollBehavior)
    expect(scrollBehavior).toBe('auto')

    await context.close()
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
  test.describe(`/ (home) — Phase 5B — ${viewport.name}`, () => {
    test.use({ viewport: { width: viewport.width, height: viewport.height } })

    test('body does not overflow horizontally', async ({ page }) => {
      await page.goto('/')
      const overflow = await page.evaluate(() => {
        const { scrollWidth, clientWidth } = document.documentElement
        return scrollWidth - clientWidth
      })
      expect(overflow).toBeLessThanOrEqual(1)
    })

    test('renders every H2 in the SSR DOM', async ({ page }) => {
      await page.goto('/')
      await expect(page.locator('.home-problems h2')).toHaveCount(1)
      await expect(page.locator('.home-approach h2')).toHaveCount(1)
      await expect(page.locator('.home-pillars h2')).toHaveCount(1)
    })

    test('every pillar is reachable in the DOM (mobile carousel keeps all five)', async ({
      page,
    }) => {
      await page.goto('/')
      await expect(page.locator('.home-pillars__card')).toHaveCount(5)
    })

    test('centers each connector between adjacent approach cards without overlap', async ({
      page,
    }) => {
      await page.goto('/')
      const cards = page.locator('.home-approach__step-card')
      const connectors = page.locator('.home-approach__connector')
      await expect(cards).toHaveCount(5)
      await expect(connectors).toHaveCount(4)

      const verticalGlyph = connectors
        .first()
        .locator('.home-approach__connector-glyph--vertical')
      const horizontalGlyph = connectors
        .first()
        .locator('.home-approach__connector-glyph--horizontal')
      if (viewport.width < 1100) {
        await expect(verticalGlyph).toBeVisible()
        await expect(horizontalGlyph).toBeHidden()
      } else {
        await expect(verticalGlyph).toBeHidden()
        await expect(horizontalGlyph).toBeVisible()
      }

      const cardBoxes = await Promise.all(
        (await cards.all()).map((card) => card.boundingBox()),
      )
      const connectorBoxes = await Promise.all(
        (await connectors.all()).map((connector) => connector.boundingBox()),
      )
      expect(cardBoxes.every(Boolean)).toBe(true)
      expect(connectorBoxes.every(Boolean)).toBe(true)

      for (let index = 0; index < 4; index += 1) {
        const current = cardBoxes[index]!
        const next = cardBoxes[index + 1]!
        const connector = connectorBoxes[index]!
        const connectorCenterX = connector.x + connector.width / 2
        const connectorCenterY = connector.y + connector.height / 2

        if (viewport.width < 1100) {
          expect(connector.y).toBeGreaterThanOrEqual(current.y + current.height - 1)
          expect(connector.y + connector.height).toBeLessThanOrEqual(next.y + 1)
          expect(Math.abs(connectorCenterX - (current.x + current.width / 2))).toBeLessThanOrEqual(2)
        } else {
          expect(connector.x).toBeGreaterThanOrEqual(current.x + current.width - 1)
          expect(connector.x + connector.width).toBeLessThanOrEqual(next.x + 1)
          const cardsCenterY =
            (current.y + current.height / 2 + next.y + next.height / 2) / 2
          expect(Math.abs(connectorCenterY - cardsCenterY)).toBeLessThanOrEqual(2)
        }
      }
    })
  })
}
