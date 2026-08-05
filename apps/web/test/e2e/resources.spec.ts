import { expect, test } from '@playwright/test'

/**
 * Tests E2E Phase 8B2 — parcours ressources éditoriales.
 *
 * Topologie (correction 4 du brief) : Playwright appelle Caddy
 * (`PLAYWRIGHT_BASE_URL=http://localhost:3001`) qui route vers Nuxt/Nitro
 * (SSR + endpoints internes `/_editorial/*`, `/__sitemap__/*`) et Symfony
 * (endpoints publics `/api/*`). Aucun accès direct au container Nitro.
 *
 * Pré-requis : le jeu de fixtures dédié `e2e-8b2-*` doit être chargé
 * (voir `scripts/e2e-fixtures.sh load`). Le script est invoqué en amont
 * par le workflow CI ou manuellement en local. Playwright n'orchestre
 * PAS Docker (correction 7).
 *
 * Jeu attendu : 7 publiés visibles, 1 brouillon / 1 archivé / 1 futur
 * invisibles (voir `E2eFixturesCommand`).
 */

const FIXTURE_PREFIX = 'e2e-8b2-'
const KNOWN_PUBLISHED_SLUG = `${FIXTURE_PREFIX}published-3`
const DRAFT_SLUG = `${FIXTURE_PREFIX}draft-1`
const ARCHIVED_SLUG = `${FIXTURE_PREFIX}archived-1`
const FUTURE_SLUG = `${FIXTURE_PREFIX}future-1`

test.describe('Phase 8B2 — /ressources (listing SSR)', () => {
  test('renvoie 200 et un H1 unique', async ({ page }) => {
    const response = await page.goto('/ressources')
    expect(response?.status()).toBe(200)
    const headings = await page.locator('h1').allTextContents()
    expect(headings, 'un seul H1 sur la page listing').toHaveLength(1)
    expect(headings[0]?.trim()).not.toBe('')
  })

  test('affiche 6 items publiés en page 1 (les plus récents d\'abord)', async ({ page }) => {
    await page.goto('/ressources')
    const items = page.locator('article a[href^="/ressources/"]')
    await expect(items).toHaveCount(6)
    // Ordre : published-7 (juillet) doit précéder published-2 (février).
    const hrefs = await items.evaluateAll((els) =>
      els.map((e) => (e as HTMLAnchorElement).getAttribute('href')),
    )
    expect(hrefs[0]).toBe(`/ressources/${FIXTURE_PREFIX}published-7`)
    expect(hrefs[5]).toBe(`/ressources/${FIXTURE_PREFIX}published-2`)
  })

  test('n\'expose ni brouillon ni archivé ni futur dans le SSR', async ({ page }) => {
    const response = await page.goto('/ressources?page=2')
    expect(response?.status()).toBe(200)
    const html = await page.content()
    expect(html).not.toContain(DRAFT_SLUG)
    expect(html).not.toContain(ARCHIVED_SLUG)
    expect(html).not.toContain(FUTURE_SLUG)
  })

  test('la pagination expose une nav aria-label et une URL propre pour la page 1', async ({
    page,
  }) => {
    await page.goto('/ressources?page=2')
    const nav = page.locator('nav[aria-label="Pagination des ressources"]')
    await expect(nav).toBeVisible()
    // Le lien retour vers page 1 pointe vers "/ressources" (pas "?page=1").
    const firstPageLink = nav.locator('a[href="/ressources"]').first()
    await expect(firstPageLink).toHaveCount(1)
  })

  test('?page=1 est redirigé en 301 vers /ressources (URL canonique unique)', async ({
    request,
  }) => {
    const response = await request.get('/ressources?page=1', { maxRedirects: 0 })
    expect(response.status()).toBe(301)
    const location = response.headers()['location']
    expect(location).toBe('/ressources')
  })

  test('?page=99 (hors bornes) renvoie 404', async ({ page }) => {
    const response = await page.goto('/ressources?page=99')
    expect(response?.status()).toBe(404)
  })
})

test.describe('Phase 8B2 — /ressources/{slug} (détail SSR)', () => {
  test('renvoie 200 avec le titre en H1 et le contenu HTML rendu', async ({ page }) => {
    const response = await page.goto(`/ressources/${KNOWN_PUBLISHED_SLUG}`)
    expect(response?.status()).toBe(200)
    await expect(page.locator('h1')).toHaveText(/Article publié E2E n°3/)
    // Le paragraphe converti depuis Markdown doit être présent (frontière
    // de confiance ResourceContent — seul point v-html du site).
    await expect(page.locator('.resource-content')).toBeVisible()
    await expect(page.locator('.resource-content h2')).toContainText('Section 3')
  })

  test('injecte JSON-LD BlogPosting et BreadcrumbList dans le HTML SSR', async ({
    request,
  }) => {
    const response = await request.get(`/ressources/${KNOWN_PUBLISHED_SLUG}`)
    const body = await response.text()

    const scripts = [
      ...body.matchAll(
        /<script[^>]+type="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/gi,
      ),
    ].map((m) => JSON.parse(m[1]))

    const types = scripts.map((s) => s['@type'] as string | undefined)
    expect(types).toContain('BlogPosting')
    expect(types).toContain('BreadcrumbList')

    const blogPosting = scripts.find((s) => s['@type'] === 'BlogPosting')
    expect(blogPosting.headline).toBe('Article publié E2E n°3')
    expect(blogPosting.datePublished).toMatch(/^2026-03-15/)
    // Publisher référencé par @id (organisation déclarée au niveau site).
    expect(blogPosting.publisher).toEqual({ '@id': expect.stringMatching(/#organization$/) })
  })

  test('slug inconnu → 404 (pas de HTML de page rendu)', async ({ page }) => {
    const response = await page.goto('/ressources/slug-qui-nexiste-pas-xyz')
    expect(response?.status()).toBe(404)
  })

  test('slug brouillon → 404 (masqué au public)', async ({ page }) => {
    const response = await page.goto(`/ressources/${DRAFT_SLUG}`)
    expect(response?.status()).toBe(404)
  })

  test('slug archivé → 404 (masqué au public)', async ({ page }) => {
    const response = await page.goto(`/ressources/${ARCHIVED_SLUG}`)
    expect(response?.status()).toBe(404)
  })

  test('slug futur (publishedAt en 2035) → 404', async ({ page }) => {
    const response = await page.goto(`/ressources/${FUTURE_SLUG}`)
    expect(response?.status()).toBe(404)
  })
})

test.describe('Phase 8B2 — sitemap dynamique', () => {
  test('/sitemap.xml inclut les 7 URLs publiées et aucune URL masquée', async ({
    request,
  }) => {
    const response = await request.get('/sitemap.xml')
    expect(response.status()).toBe(200)
    const body = await response.text()

    const locs = [...body.matchAll(/<loc>([^<]+)<\/loc>/g)].map((m) => m[1])
    const publishedLocs = locs.filter((l) => l.includes(`${FIXTURE_PREFIX}published-`))
    expect(publishedLocs).toHaveLength(7)

    expect(locs.some((l) => l.includes(DRAFT_SLUG))).toBe(false)
    expect(locs.some((l) => l.includes(ARCHIVED_SLUG))).toBe(false)
    expect(locs.some((l) => l.includes(FUTURE_SLUG))).toBe(false)
  })
})
