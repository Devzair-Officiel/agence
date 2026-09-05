import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'

/**
 * SEO-COM-6 — article "site-internet-pas-cher".
 *
 * Couvre : HTTP 200, SSR complet (H1, title, description, canonical),
 * BlogPosting + BreadcrumbList JSON-LD, maillage sortant, présence dans
 * le listing et le sitemap, Axe WCAG 2.2 AA, et no-overflow responsive.
 */

const SLUG = 'site-internet-pas-cher'
const ARTICLE_URL = `/ressources/${SLUG}`

test.describe('resource site-internet-pas-cher — SSR', () => {
  test('retourne HTTP 200', async ({ request }) => {
    const response = await request.get(ARTICLE_URL)
    expect(response.status()).toBe(200)
  })

  test("H1 contient le titre de l'article", async ({ request }) => {
    const html = await (await request.get(ARTICLE_URL)).text()
    expect(html).toMatch(/Site internet pas cher/)
  })

  test('title SEO correct', async ({ request }) => {
    const html = await (await request.get(ARTICLE_URL)).text()
    expect(html).toMatch(
      /<title>[^<]*Site internet pas cher[^<]*Devzair[^<]*<\/title>/,
    )
  })

  test('meta description presente et non vide', async ({ request }) => {
    const html = await (await request.get(ARTICLE_URL)).text()
    expect(html).toMatch(/name="description"\s+content="[^"]{10,}"/)
  })

  test("canonical pointe vers l'URL de l'article", async ({ request }) => {
    const html = await (await request.get(ARTICLE_URL)).text()
    expect(html).toContain(`/ressources/${SLUG}`)
    expect(html).toContain('rel="canonical"')
  })
})

test.describe('resource site-internet-pas-cher — JSON-LD', () => {
  test('BlogPosting JSON-LD present avec headline et datePublished', async ({ request }) => {
    const html = await (await request.get(ARTICLE_URL)).text()
    const scripts = [
      ...html.matchAll(
        /<script[^>]+type="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/gi,
      ),
    ].map((m) => JSON.parse(m[1]))

    const blogPosting = scripts.find((s) => s['@type'] === 'BlogPosting')
    expect(blogPosting, 'BlogPosting present').toBeDefined()
    expect(blogPosting.headline).toMatch(/pas cher/i)
    expect(blogPosting.datePublished).toMatch(/^2026-09-05/)
  })

  test('BreadcrumbList JSON-LD present avec 3 niveaux', async ({ request }) => {
    const html = await (await request.get(ARTICLE_URL)).text()
    const scripts = [
      ...html.matchAll(
        /<script[^>]+type="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/gi,
      ),
    ].map((m) => JSON.parse(m[1]))

    const breadcrumb = scripts.find((s) => s['@type'] === 'BreadcrumbList')
    expect(breadcrumb, 'BreadcrumbList present').toBeDefined()
    expect(breadcrumb.itemListElement).toHaveLength(3)
    expect(breadcrumb.itemListElement[0].name).toBe('Accueil')
    expect(breadcrumb.itemListElement[1].name).toBe('Ressources')
  })
})

test.describe('resource site-internet-pas-cher — maillage sortant', () => {
  test('lien vers /estimer-mon-projet present', async ({ request }) => {
    const html = await (await request.get(ARTICLE_URL)).text()
    expect(html).toContain('/estimer-mon-projet')
  })

  test('lien vers /services/photographie-creation-contenu present', async ({ request }) => {
    const html = await (await request.get(ARTICLE_URL)).text()
    expect(html).toContain('/services/photographie-creation-contenu')
  })

  test('lien vers /services/maintenance-accompagnement present', async ({ request }) => {
    const html = await (await request.get(ARTICLE_URL)).text()
    expect(html).toContain('/services/maintenance-accompagnement')
  })
})

test.describe('resource site-internet-pas-cher — listing et sitemap', () => {
  test('article visible dans le listing /ressources', async ({ page }) => {
    let found = false
    let currentPage = 1
    const maxPages = 20
    while (currentPage <= maxPages && !found) {
      const url = currentPage === 1 ? '/ressources' : `/ressources?page=${currentPage}`
      const response = await page.goto(url)
      if (response?.status() !== 200) break
      const hrefs = await page
        .locator('article a[href^="/ressources/"]')
        .evaluateAll((els) =>
          els.map((e) => (e as HTMLAnchorElement).getAttribute('href') ?? ''),
        )
      if (hrefs.includes(ARTICLE_URL)) {
        found = true
        break
      }
      if (hrefs.length < 6) break
      currentPage += 1
    }
    expect(found, `${SLUG} visible dans le listing`).toBe(true)
  })

  test("/sitemap.xml contient l'URL de l'article", async ({ request }) => {
    const response = await request.get('/sitemap.xml')
    expect(response.status()).toBe(200)
    const body = await response.text()
    expect(body).toContain(ARTICLE_URL)
  })
})

test.describe('resource site-internet-pas-cher — Axe WCAG 2.2 AA', () => {
  test('aucune violation critique/serieuse', async ({ page }) => {
    await page.goto(ARTICLE_URL)
    const results = await new AxeBuilder({ page })
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'])
      .analyze()
    const blocking = results.violations.filter(
      (v) => v.impact === 'critical' || v.impact === 'serious',
    )
    if (blocking.length > 0) {
      console.log(
        `Axe ${ARTICLE_URL}:\n${blocking
          .map((v) => `- [${v.impact}] ${v.id}: ${v.help}`)
          .join('\n')}`,
      )
    }
    expect(blocking).toEqual([])
  })
})

test.describe('resource site-internet-pas-cher — responsive', () => {
  const viewports = [
    { name: 'mobile 390', width: 390, height: 844 },
    { name: 'tablet 768', width: 768, height: 1024 },
    { name: 'desktop 1024', width: 1024, height: 768 },
    { name: 'desktop 1440', width: 1440, height: 1000 },
  ]

  for (const vp of viewports) {
    test(`pas de debordement horizontal a ${vp.name}`, async ({ page }) => {
      await page.setViewportSize({ width: vp.width, height: vp.height })
      await page.goto(ARTICLE_URL)
      const overflow = await page.evaluate(() => {
        const { scrollWidth, clientWidth } = document.documentElement
        return scrollWidth - clientWidth
      })
      expect(overflow, `overflow a ${vp.name}`).toBeLessThanOrEqual(1)
    })
  }
})
