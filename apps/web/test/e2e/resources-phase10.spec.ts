import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'

/**
 * Tests E2E Phase 10 — contenu éditorial réel des 6 articles pillar.
 *
 * Ces tests dépendent de la présence en base des 6 slugs Phase 10
 * publiés (voir `scripts/editorial-content-bootstrap.sh`). Ils sont
 * indépendants du jeu de fixtures `e2e-8b2-*` (autres suites).
 *
 * Portée : présence dans le listing/sitemap, SEO SSR (title, description,
 * canonical, OG, JSON-LD BlogPosting + BreadcrumbList), rendu du corps
 * Markdown, et scan Axe sur /ressources + un article.
 */

const PHASE10_SLUGS = [
  'creer-site-internet-professionnel',
  'site-vitrine-ou-sur-mesure',
  'seo-creation-site-internet',
  'application-metier-remplacer-excel',
  'ameliorer-visibilite-locale-entreprise',
  'maintenance-site-internet',
] as const

test.describe('Phase 10 — contenu éditorial réel', () => {
  test('les 6 slugs Phase 10 sont visibles dans le listing paginé', async ({ page }) => {
    const seen = new Set<string>()
    let currentPage = 1
    const maxPages = 20
    while (currentPage <= maxPages) {
      const url = currentPage === 1 ? '/ressources' : `/ressources?page=${currentPage}`
      const response = await page.goto(url)
      if (response?.status() !== 200) break
      const items = page.locator('article a[href^="/ressources/"]')
      const hrefs = await items.evaluateAll((els) =>
        els.map((e) => (e as HTMLAnchorElement).getAttribute('href') ?? ''),
      )
      for (const href of hrefs) {
        for (const slug of PHASE10_SLUGS) {
          if (href === `/ressources/${slug}`) seen.add(slug)
        }
      }
      if (hrefs.length < 6) break
      currentPage += 1
    }
    for (const slug of PHASE10_SLUGS) {
      expect(seen.has(slug), `slug Phase 10 ${slug} visible dans le listing`).toBe(true)
    }
  })

  test('/sitemap.xml contient les 6 URLs Phase 10', async ({ request }) => {
    const response = await request.get('/sitemap.xml')
    expect(response.status()).toBe(200)
    const body = await response.text()
    for (const slug of PHASE10_SLUGS) {
      expect(body, `sitemap contient ${slug}`).toContain(`/ressources/${slug}`)
    }
  })

  test('article Phase 10 sert un HTML SSR complet (title, canonical, OG, H1)', async ({
    request,
  }) => {
    const slug = 'creer-site-internet-professionnel'
    const response = await request.get(`/ressources/${slug}`)
    expect(response.status()).toBe(200)
    const html = await response.text()

    expect(html).toMatch(/<title>[^<]*Devzair[^<]*<\/title>/)
    expect(html).toContain(`<link rel="canonical" href="`)
    expect(html).toContain(`/ressources/${slug}"`)
    expect(html).toMatch(/property="og:type"\s+content="article"/)
    expect(html).toMatch(/property="og:title"/)
    expect(html).toMatch(/<h1[^>]*>[^<]*Comment créer un site internet professionnel/)
  })

  test('article Phase 10 injecte BlogPosting + BreadcrumbList JSON-LD', async ({ request }) => {
    const slug = 'seo-creation-site-internet'
    const response = await request.get(`/ressources/${slug}`)
    const body = await response.text()

    const scripts = [
      ...body.matchAll(
        /<script[^>]+type="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/gi,
      ),
    ].map((m) => JSON.parse(m[1]))

    const blogPosting = scripts.find((s) => s['@type'] === 'BlogPosting')
    expect(blogPosting, 'BlogPosting JSON-LD présent').toBeDefined()
    expect(blogPosting.headline).toMatch(/SEO/)
    expect(blogPosting.datePublished).toMatch(/^2026-08-/)
    expect(blogPosting.publisher).toEqual({
      '@id': expect.stringMatching(/#organization$/),
    })

    const breadcrumb = scripts.find((s) => s['@type'] === 'BreadcrumbList')
    expect(breadcrumb, 'BreadcrumbList JSON-LD présent').toBeDefined()
    expect(breadcrumb.itemListElement).toHaveLength(3)
    expect(breadcrumb.itemListElement[0].name).toBe('Accueil')
    expect(breadcrumb.itemListElement[1].name).toBe('Ressources')
  })

  test('article Phase 10 rend le corps Markdown avec sections H2 lisibles', async ({ page }) => {
    await page.goto('/ressources/site-vitrine-ou-sur-mesure')
    await expect(page.locator('h1')).toHaveText(
      /Site vitrine ou site sur mesure/,
    )
    await expect(page.locator('.resource-content')).toBeVisible()
    const h2Count = await page.locator('.resource-content h2').count()
    expect(h2Count).toBeGreaterThanOrEqual(3)
    // Lien interne vers un autre article Phase 10 présent.
    await expect(
      page.locator('.resource-content a[href="/ressources/creer-site-internet-professionnel"]'),
    ).toHaveCount(1)
    // CTA vers /contact présent.
    await expect(page.locator('a[href="/contact"]').first()).toBeVisible()
  })
})

test.describe('Phase 10 — Axe (accessibility)', () => {
  test('aucune violation sérieuse/critique sur /ressources', async ({ page }) => {
    await page.goto('/ressources')
    const results = await new AxeBuilder({ page })
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'])
      .analyze()
    const blocking = results.violations.filter(
      (v) => v.impact === 'critical' || v.impact === 'serious',
    )
    if (blocking.length > 0) {
      console.log(
        `Axe /ressources:\n${blocking
          .map((v) => `- [${v.impact}] ${v.id}: ${v.help}`)
          .join('\n')}`,
      )
    }
    expect(blocking).toEqual([])
  })

  test('aucune violation sérieuse/critique sur un article Phase 10', async ({ page }) => {
    await page.goto('/ressources/creer-site-internet-professionnel')
    const results = await new AxeBuilder({ page })
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'])
      .analyze()
    const blocking = results.violations.filter(
      (v) => v.impact === 'critical' || v.impact === 'serious',
    )
    if (blocking.length > 0) {
      console.log(
        `Axe article:\n${blocking
          .map((v) => `- [${v.impact}] ${v.id}: ${v.help}`)
          .join('\n')}`,
      )
    }
    expect(blocking).toEqual([])
  })
})
