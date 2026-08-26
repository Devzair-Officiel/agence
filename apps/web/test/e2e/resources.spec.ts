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

  test('le hero et sa carte occupent toute la hauteur disponible avec un fondu bas', async ({
    page,
  }) => {
    for (const viewport of [
      { width: 390, height: 844 },
      { width: 1440, height: 1000 },
    ]) {
      await page.setViewportSize(viewport)
      await page.goto('/ressources')

      const hero = page.locator('.resources-hero')
      const backdrop = hero.locator('.resources-hero__backdrop')
      const [heroBox, backdropBox] = await Promise.all([
        hero.boundingBox(),
        backdrop.boundingBox(),
      ])
      expect(heroBox).not.toBeNull()
      expect(backdropBox).not.toBeNull()
      expect(heroBox!.height).toBeGreaterThanOrEqual(
        viewport.height - heroBox!.y - 1,
      )
      expect(backdropBox!.height).toBeCloseTo(heroBox!.height, 0)

      const fade = await hero.evaluate((element) => {
        const styles = getComputedStyle(element, '::after')
        return {
          backgroundImage: styles.backgroundImage,
          height: Number.parseFloat(styles.height),
        }
      })
      expect(fade.backgroundImage).toContain('linear-gradient')
      expect(fade.height).toBeGreaterThan(heroBox!.height * 0.2)

      const readingHalo = await hero
        .locator('.resources-hero__container')
        .evaluate((element) => {
          const styles = getComputedStyle(element, '::before')
          return {
            maskImage: styles.maskImage || styles.webkitMaskImage,
            opacity: Number.parseFloat(styles.opacity),
          }
        })
      expect(readingHalo.maskImage).toContain('radial-gradient')
      expect(readingHalo.opacity).toBeGreaterThanOrEqual(0.9)
    }
  })

  test('la souris laisse une trace décorative temporaire sur la carte', async ({
    page,
  }) => {
    await page.setViewportSize({ width: 1280, height: 900 })
    await page.goto('/ressources')

    const hero = page.locator('.resources-hero')
    await expect(hero.locator('.resources-pointer-trail')).toHaveAttribute(
      'data-pointer-trail-ready',
      'true',
    )
    const heroBox = await hero.boundingBox()
    expect(heroBox).not.toBeNull()

    await page.mouse.move(heroBox!.x + 80, heroBox!.y + heroBox!.height * 0.3)
    await page.mouse.move(
      heroBox!.x + heroBox!.width * 0.75,
      heroBox!.y + heroBox!.height * 0.65,
      { steps: 8 },
    )

    const trailPoints = hero.locator('.resources-pointer-trail__point')
    await expect.poll(() => trailPoints.count()).toBeGreaterThan(1)

    const animationName = await trailPoints.first().evaluate(
      (element) => getComputedStyle(element).animationName,
    )
    expect(animationName).toContain('resources-pointer-trail-fade')
    await expect(trailPoints).toHaveCount(0, { timeout: 2_000 })

    await page.emulateMedia({ reducedMotion: 'reduce' })
    await page.mouse.move(heroBox!.x + 100, heroBox!.y + 200)
    await page.mouse.move(heroBox!.x + 600, heroBox!.y + 500, { steps: 5 })
    await expect(trailPoints).toHaveCount(0)
  })

  test('rend une grille paginée (max 6 items par page, tous les fixtures visibles)', async ({
    page,
  }) => {
    // Phase 10 : la position exacte des fixtures e2e-8b2-* dans la pagination
    // dépend du contenu réel désormais publié (dates > fixtures). Le test
    // reste déterministe en parcourant toutes les pages jusqu'à retrouver
    // les 7 slugs published-* fixtures dans l'ordre chronologique inverse
    // relatif (published-7 apparaît avant published-2 dans la séquence).
    const seenFixtureHrefs: string[] = []
    let currentPage = 1
    const maxPages = 20
    while (currentPage <= maxPages) {
      const url = currentPage === 1 ? '/ressources' : `/ressources?page=${currentPage}`
      const response = await page.goto(url)
      if (response?.status() !== 200) break
      const items = page.locator('article a[href^="/ressources/"]')
      const count = await items.count()
      expect(count).toBeLessThanOrEqual(6)
      if (count === 0) break
      const hrefs = await items.evaluateAll((els) =>
        els.map((e) => (e as HTMLAnchorElement).getAttribute('href') ?? ''),
      )
      for (const href of hrefs) {
        if (href.includes(`${FIXTURE_PREFIX}published-`)) seenFixtureHrefs.push(href)
      }
      if (count < 6) break
      currentPage += 1
    }

    // Les 7 fixtures publiées doivent toutes apparaître, dans l'ordre 7 → 1.
    expect(seenFixtureHrefs).toHaveLength(7)
    expect(seenFixtureHrefs[0]).toBe(`/ressources/${FIXTURE_PREFIX}published-7`)
    expect(seenFixtureHrefs[6]).toBe(`/ressources/${FIXTURE_PREFIX}published-1`)
  })

  test('n\'expose ni brouillon ni archivé ni futur sur aucune page du listing', async ({
    page,
  }) => {
    // Balayage complet des pages pour garantir l'absence des slugs interdits,
    // quel que soit le volume de contenu réel.
    let currentPage = 1
    const maxPages = 20
    while (currentPage <= maxPages) {
      const url = currentPage === 1 ? '/ressources' : `/ressources?page=${currentPage}`
      const response = await page.goto(url)
      if (response?.status() !== 200) break
      const html = await page.content()
      expect(html).not.toContain(DRAFT_SLUG)
      expect(html).not.toContain(ARCHIVED_SLUG)
      expect(html).not.toContain(FUTURE_SLUG)
      const count = await page.locator('article a[href^="/ressources/"]').count()
      if (count < 6) break
      currentPage += 1
    }
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

  test('un clic de pagination recharge les données et met à jour la page courante', async ({
    page,
  }) => {
    await page.goto('/ressources')
    await page.locator('[data-pointer-trail-ready="true"]').waitFor()

    const pagination = page.locator('nav[aria-label="Pagination des ressources"]')
    const firstArticle = page.locator('article a[href^="/ressources/"]').first()
    const firstPageArticleHref = await firstArticle.getAttribute('href')
    const pageRoot = page.locator('.resources-index')
    await pageRoot.evaluate((element) => {
      element.setAttribute('data-pagination-instance', 'preserved')
    })

    const pageTwoLink = pagination.getByRole('link', { name: 'Aller à la page 2' })
    await pageTwoLink.scrollIntoViewIfNeeded()

    await pageTwoLink.click()

    await expect(page).toHaveURL(/\/ressources\?page=2$/)
    await expect(pagination.locator('span[aria-current="page"]')).toHaveText('2')
    await expect(firstArticle).not.toHaveAttribute('href', firstPageArticleHref!)
    await expect(pagination.locator('a[aria-current="page"]')).toHaveCount(0)
    await expect(pageRoot).toHaveAttribute('data-pagination-instance', 'preserved')
    await expect(page).toHaveTitle(/Ressources — page 2/)
    await expect(page.locator('meta[name="robots"]')).toHaveAttribute(
      'content',
      'noindex, follow',
    )
    await expect
      .poll(() => page.locator('#resources-list-title').evaluate(
        (element) => Math.round(element.getBoundingClientRect().top),
      ))
      .toBe(105)

    await pagination.getByRole('link', { name: 'Précédente' }).click()
    await expect(page).toHaveURL(/\/ressources$/)
    await expect(pagination.locator('span[aria-current="page"]')).toHaveText('1')
    await expect(firstArticle).toHaveAttribute('href', firstPageArticleHref!)
    await expect
      .poll(() => page.locator('#resources-list-title').evaluate(
        (element) => Math.round(element.getBoundingClientRect().top),
      ))
      .toBe(105)
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
