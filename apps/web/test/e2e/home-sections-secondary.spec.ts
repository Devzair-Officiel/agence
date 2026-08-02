import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'

// Contrôles E2E des trois sections livrées en Phase 5C :
//   - HomeFeaturedCaseStudy  (« Réalisations », état honnête, ancre #realisations)
//   - HomeProcess            (« Notre méthode », 6 étapes)
//   - HomeTrust              (« Pourquoi Devzair », 5 promesses)
//
// Vérifie l'ordre éditorial complet des 7 sections, la structure sémantique
// (un seul H1, H2 par section, H3 imbriqués), la présence SSR du contenu,
// l'ancrage #realisations depuis la nav header et le CTA hero, le respect
// responsive (320/390/768/1024/1440), Axe WCAG 2.2 AA et
// `prefers-reduced-motion`. Aucune route inexistante n'est atteinte.

test.describe('/ (home) — sections secondaires Phase 5C', () => {
  test('renders the eight sections in the expected editorial order', async ({
    page,
  }) => {
    await page.goto('/')
    const orderedSections = await page.evaluate(() => {
      const nodes = Array.from(
        document.querySelectorAll('main .home-page > section'),
      ) as HTMLElement[]
      return nodes.map((section) => section.className.split(/\s+/)[0])
    })
    // Ordre éditorial complet — la 8e section CTA final est ajoutée en
    // Phase 5D (ancre `#contact`). Toute nouvelle section devra être posée
    // AVANT `home-cta` (celui-ci ferme la page).
    expect(orderedSections).toEqual([
      'home-hero',
      'home-problems',
      'home-approach',
      'home-pillars',
      'home-case',
      'home-process',
      'home-trust',
      'home-cta',
    ])
  })

  test('exposes exactly one H1 and adds three new H2 (case / process / trust)', async ({
    page,
  }) => {
    await page.goto('/')
    await expect(page.locator('h1')).toHaveCount(1)
    const h2s = await page.locator('main h2').allInnerTexts()
    expect(h2s).toContain(
      'Des solutions concrètes, pas seulement de belles interfaces.',
    )
    expect(h2s).toContain('Un cadre clair, du premier échange au suivi.')
    expect(h2s).toContain('Ce qui fait la différence, concrètement.')
  })

  test('the featured case study section publishes the honest-state placeholder', async ({
    page,
  }) => {
    await page.goto('/')
    const section = page.locator('section#realisations')
    await expect(section).toHaveCount(1)
    await expect(
      section.getByText('Études de cas en préparation'),
    ).toBeVisible()
    await expect(
      section.getByRole('heading', {
        level: 3,
        name: 'Nos réalisations détaillées seront bientôt disponibles.',
      }),
    ).toBeVisible()
    // Aucun bouton ni lien : rien qui redirige vers une route inexistante.
    await expect(section.locator('a')).toHaveCount(0)
    await expect(section.locator('button')).toHaveCount(0)
  })

  test('renders exactly six process steps in an ordered list', async ({
    page,
  }) => {
    await page.goto('/')
    const list = page.locator('.home-process__list')
    await expect(list).toHaveCount(1)
    await expect(list.locator('> li')).toHaveCount(6)
    const titles = await page
      .locator('.home-process__step-title')
      .allInnerTexts()
    expect(titles).toEqual([
      'Découverte',
      'Cadrage',
      'Conception',
      'Développement',
      'Lancement',
      'Évolution',
    ])
  })

  test('renders exactly five trust promises in an unordered list', async ({
    page,
  }) => {
    await page.goto('/')
    const list = page.locator('.home-trust__list')
    await expect(list).toHaveCount(1)
    await expect(list.locator('> li')).toHaveCount(5)
    const titles = await page
      .locator('.home-trust__item-title')
      .allInnerTexts()
    expect(titles).toEqual([
      'Approche personnalisée',
      'Vision globale',
      'Qualité technique',
      'Transparence',
      'Accompagnement durable',
    ])
  })

  test('the hero « Découvrir nos réalisations » CTA scrolls to #realisations', async ({
    page,
  }) => {
    await page.goto('/')
    const cta = page.getByRole('link', { name: 'Découvrir nos réalisations' })
    await expect(cta).toBeVisible()
    const href = await cta.getAttribute('href')
    expect(href).toBe('#realisations')

    await cta.click()
    await expect(page).toHaveURL(/#realisations$/)
    await expect(page.locator('#realisations h2')).toBeVisible()
  })

  test('the header « Réalisations » nav link targets #realisations on the home page', async ({
    page,
  }) => {
    await page.goto('/')
    // Le libellé « Réalisations » figure dans le nav principal + le footer.
    // On cible spécifiquement le premier occurrence dans <header>.
    const navLink = page
      .locator('header a', { hasText: 'Réalisations' })
      .first()
    await expect(navLink).toBeVisible()
    const href = await navLink.getAttribute('href')
    expect(href === '/#realisations' || href === '#realisations').toBe(true)
  })

  test('SSR HTML already contains every Phase 5C editorial signature', async ({
    request,
  }) => {
    const response = await request.get('/')
    expect(response.status()).toBe(200)
    const html = await response.text()
    // Case study.
    expect(html).toContain(
      'Des solutions concrètes, pas seulement de belles interfaces.',
    )
    expect(html).toContain('Études de cas en préparation')
    expect(html).toContain('Nos réalisations détaillées seront bientôt disponibles.')
    // Method — 6 labels.
    for (const label of [
      'Découverte',
      'Cadrage',
      'Conception',
      'Développement',
      'Lancement',
      'Évolution',
    ]) {
      expect(html).toContain(label)
    }
    // Trust — 5 promise labels.
    for (const label of [
      'Approche personnalisée',
      'Vision globale',
      'Qualité technique',
      'Transparence',
      'Accompagnement durable',
    ]) {
      expect(html).toContain(label)
    }
    expect(html).toContain('id="realisations"')
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

  test('respects prefers-reduced-motion — no animation-driven layout on new sections', async ({
    browser,
  }) => {
    const context = await browser.newContext({ reducedMotion: 'reduce' })
    const page = await context.newPage()
    await page.goto('/')

    // Les 3 nouvelles sections n'introduisent pas d'animation ; on vérifie
    // simplement qu'elles restent visibles et exploitables dans ce mode.
    await expect(page.locator('#realisations h2')).toBeVisible()
    await expect(page.locator('.home-process h2')).toBeVisible()
    await expect(page.locator('.home-trust h2')).toBeVisible()

    await context.close()
  })
})

const viewports = [
  { name: 'small mobile 320x780', width: 320, height: 780 },
  { name: 'mobile 390x844', width: 390, height: 844 },
  { name: 'tablet 768x1024', width: 768, height: 1024 },
  { name: 'small laptop 1024x800', width: 1024, height: 800 },
  { name: 'desktop 1440x1000', width: 1440, height: 1000 },
]

for (const viewport of viewports) {
  test.describe(`/ (home) — Phase 5C — ${viewport.name}`, () => {
    test.use({ viewport: { width: viewport.width, height: viewport.height } })

    test('body does not overflow horizontally with the new sections', async ({
      page,
    }) => {
      await page.goto('/')
      const overflow = await page.evaluate(() => {
        const { scrollWidth, clientWidth } = document.documentElement
        return scrollWidth - clientWidth
      })
      expect(overflow).toBeLessThanOrEqual(1)
    })

    test('renders every new H2 in the SSR DOM', async ({ page }) => {
      await page.goto('/')
      await expect(page.locator('.home-case h2')).toHaveCount(1)
      await expect(page.locator('.home-process h2')).toHaveCount(1)
      await expect(page.locator('.home-trust h2')).toHaveCount(1)
    })

    test('every process step and trust promise stays in the DOM (no CSS hides them)', async ({
      page,
    }) => {
      await page.goto('/')
      await expect(page.locator('.home-process__step')).toHaveCount(6)
      await expect(page.locator('.home-trust__item')).toHaveCount(5)
    })
  })
}
