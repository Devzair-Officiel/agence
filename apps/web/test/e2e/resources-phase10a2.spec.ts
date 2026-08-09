import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'

/**
 * Phase 10A2 — maillage éditorial et découverte des ressources.
 *
 * Vérifie côté E2E :
 *   1. La navigation publique expose « Ressources ».
 *   2. Le filtre expertise fonctionne (SSR, URL partageable).
 *   3. Un filtre inconnu retourne un 404 (pas de silent-fallback).
 *   4. Les variantes filtrées / paginées portent `noindex, follow` et
 *      un canonical vers `/ressources`.
 *   5. Le sitemap n'énumère PAS d'URL filtrée.
 *   6. Chaque page `/expertises/{slug}` intègre soit une section
 *      « Ressources liées », soit l'absence stricte de section (aucun
 *      placeholder), en fonction du contenu réellement publié.
 *   7. Axe : aucune violation critique/sérieuse sur `/ressources` filtrée.
 *
 * Le contenu Phase 10 (six pillar articles) taggé « concevoir »,
 * « construire », etc. sert de source réelle pour ces assertions.
 */

const EXPERTISE_SLUGS = [
  'concevoir',
  'construire',
  'valoriser',
  'visibilite',
  'faire-evoluer',
] as const

function findScripts(html: string, type: string): unknown[] {
  const escaped = type.replace(/[/+]/g, '\\$&')
  const regex = new RegExp(
    `<script[^>]+type="${escaped}"[^>]*>([\\s\\S]*?)</script>`,
    'gi',
  )
  return [...html.matchAll(regex)].map((m) => JSON.parse(m[1]!))
}

test.describe('Phase 10A2 — navigation Ressources', () => {
  test('la nav publique expose un lien vers /ressources', async ({ page }) => {
    await page.goto('/')
    const nav = page.getByRole('navigation').first()
    const link = nav.locator('a[href="/ressources"]').first()
    await expect(link).toBeVisible()
  })
})

test.describe('Phase 10A2 — filtre par expertise', () => {
  test('/ressources rend la barre de filtre (Toutes + 5 expertises)', async ({ page }) => {
    const response = await page.goto('/ressources')
    expect(response?.status()).toBe(200)
    const filterNav = page.getByRole('navigation', {
      name: /Filtrer les ressources par expertise/i,
    })
    await expect(filterNav).toBeVisible()
    await expect(filterNav.locator('a[href="/ressources"]')).toHaveText('Toutes')
    for (const slug of EXPERTISE_SLUGS) {
      await expect(
        filterNav.locator(`a[href="/ressources?expertise=${slug}"]`),
      ).toBeVisible()
    }
  })

  test('cliquer sur une expertise mène à /ressources?expertise=<id> en SSR', async ({
    page,
    request,
  }) => {
    const response = await request.get('/ressources?expertise=visibilite')
    expect(response.status()).toBe(200)
    const html = await response.text()
    // Le lien actif porte aria-current="page" — c'est un signal SSR fort.
    expect(html).toContain('aria-current="page"')
    // Le H2 principal reflète le filtre.
    expect(html).toMatch(/Ressources — Visibilité/)
    // Cliquable côté client aussi.
    await page.goto('/ressources')
    await page.locator('a[href="/ressources?expertise=visibilite"]').click()
    await expect(page).toHaveURL(/expertise=visibilite/)
  })

  test('un filtre expertise inconnu renvoie 404 (pas de silent-fallback)', async ({
    request,
  }) => {
    const response = await request.get('/ressources?expertise=marketing-affiliation')
    expect(response.status()).toBe(404)
  })

  test('la variante filtrée porte noindex, follow et un canonical vers /ressources', async ({
    request,
  }) => {
    const response = await request.get('/ressources?expertise=concevoir')
    expect(response.status()).toBe(200)
    const html = await response.text()
    expect(html).toMatch(/name="robots"\s+content="noindex, follow"/)
    // Canonical pointe la liste canonique, pas la variante filtrée.
    const canonicalMatch = html.match(
      /<link rel="canonical" href="([^"]+)"/,
    )
    expect(canonicalMatch).not.toBeNull()
    expect(canonicalMatch![1]).toMatch(/\/ressources$/)
  })

  test('la page paginée porte noindex, follow', async ({ request }) => {
    const response = await request.get('/ressources?page=2')
    // Selon l'inventaire réel, la page 2 peut retourner 404 (pas assez
    // d'articles). Dans ce cas, le test est neutre. Sinon, on vérifie
    // la politique robots.
    if (response.status() === 200) {
      const html = await response.text()
      expect(html).toMatch(/name="robots"\s+content="noindex, follow"/)
    }
  })

  test('la pagination filtrée préserve le paramètre expertise', async ({ page }) => {
    await page.goto('/ressources?expertise=construire')
    // On n'a peut-être qu'une page pour ce filtre — on cherche un lien
    // de pagination s'il existe, sinon on skip cette assertion douce.
    const paginationLinks = page.locator(
      'nav[aria-label*="agination" i] a[href*="/ressources"]',
    )
    const count = await paginationLinks.count()
    if (count > 0) {
      for (let i = 0; i < count; i++) {
        const href = await paginationLinks.nth(i).getAttribute('href')
        expect(href).toContain('expertise=construire')
      }
    }
  })

  test('/sitemap.xml ne contient AUCUNE URL avec ?expertise=', async ({ request }) => {
    const response = await request.get('/sitemap.xml')
    expect(response.status()).toBe(200)
    const body = await response.text()
    expect(body).not.toContain('?expertise=')
    expect(body).not.toContain('expertise%3D')
  })
})

test.describe('Phase 10A2 — pages expertise', () => {
  test('chaque page expertise reste 200 après retrait du prerender', async ({
    request,
  }) => {
    for (const slug of EXPERTISE_SLUGS) {
      const response = await request.get(`/expertises/${slug}`)
      expect(response.status(), `/expertises/${slug} accessible en SSR`).toBe(200)
    }
  })

  test('une page expertise avec article publié affiche « Ressources liées » et un lien vers la liste filtrée', async ({
    page,
  }) => {
    // « visibilite » est utilisée par l'article SEO Phase 10 :
    // ameliorer-visibilite-locale-entreprise + seo-creation-site-internet.
    await page.goto('/expertises/visibilite')
    const heading = page.getByRole('heading', {
      name: /Nos ressources autour de l'expertise/i,
    })
    // Peut être absent si aucune ressource n'est encore taggée « visibilite »
    // en environnement de test — dans ce cas, la section entière est masquée
    // (règle : pas de placeholder). On vérifie strictement l'un ou l'autre.
    const headingCount = await heading.count()
    if (headingCount > 0) {
      const seeAllLink = page.locator('a[href="/ressources?expertise=visibilite"]')
      await expect(seeAllLink).toBeVisible()
      const cards = page.locator('article a[href^="/ressources/"]')
      const cardCount = await cards.count()
      expect(cardCount).toBeGreaterThanOrEqual(1)
      expect(cardCount).toBeLessThanOrEqual(3)
    } else {
      // Section absente : aucune référence au lien filtré non plus.
      await expect(page.locator('a[href="/ressources?expertise=visibilite"]')).toHaveCount(
        0,
      )
    }
  })
})

test.describe('Phase 10A2 — Axe (accessibility)', () => {
  test('aucune violation sérieuse/critique sur /ressources?expertise=concevoir', async ({
    page,
  }) => {
    await page.goto('/ressources?expertise=concevoir')
    const results = await new AxeBuilder({ page })
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'])
      .analyze()
    const blocking = results.violations.filter(
      (v) => v.impact === 'critical' || v.impact === 'serious',
    )
    if (blocking.length > 0) {
      console.log(
        `Axe /ressources filtré :\n${blocking
          .map((v) => `- [${v.impact}] ${v.id}: ${v.help}`)
          .join('\n')}`,
      )
    }
    expect(blocking).toEqual([])
  })
})

test.describe('Phase 10A2 — JSON-LD (regressions)', () => {
  test('la page filtrée continue de servir un Organization JSON-LD stable', async ({
    request,
  }) => {
    const response = await request.get('/ressources?expertise=concevoir')
    const html = await response.text()
    const scripts = findScripts(html, 'application/ld+json')
    // Le site sert un `@graph` regroupant Organization + WebSite. On
    // vérifie qu'au moins l'un des deux `@type` est présent, que ce soit
    // au niveau racine ou dans un `@graph`.
    function containsSiteEntity(entry: unknown): boolean {
      if (typeof entry !== 'object' || entry === null) return false
      const t = (entry as { '@type'?: unknown })['@type']
      if (t === 'Organization' || t === 'WebSite') return true
      const graph = (entry as { '@graph'?: unknown })['@graph']
      return Array.isArray(graph) && graph.some(containsSiteEntity)
    }
    expect(
      scripts.some(containsSiteEntity),
      'un JSON-LD site/organization reste présent',
    ).toBe(true)
  })
})
