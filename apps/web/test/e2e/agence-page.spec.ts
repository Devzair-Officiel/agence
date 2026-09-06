import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'

// Suite E2E de la page `/agence`.
//
// Couvre les invariants SEO-COM-8 :
//   - HTTP 200, H1 verbatim, title/meta/canonical ;
//   - pilier « Intégrée » (remplace « Globale ») ;
//   - lien contextuel vers /services dans la section Fonctionnement ;
//   - lien /realisations dans le callout final ;
//   - lien /contact (hero CTA) ;
//   - aucune formulation freelance / voix singulière « je » ;
//   - Axe WCAG 2.2 AA sans violation serious/critical ;
//   - responsive 390/768/1024/1440 sans overflow horizontal.

test.describe('/agence', () => {
  test('répond HTTP 200 et sert le bon titre de page', async ({ request }) => {
    const response = await request.get('/agence')
    expect(response.status()).toBe(200)
    const body = await response.text()
    expect(body).toContain('Agence digitale à taille humaine')
  })

  test('expose un unique H1 avec le titre éditorial verbatim', async ({ page }) => {
    await page.goto('/agence')
    const h1s = page.locator('h1')
    await expect(h1s).toHaveCount(1)
    await expect(h1s.first()).toContainText('Une agence digitale à taille humaine')
  })

  test('publie title, meta description et canonical corrects (SSR)', async ({
    request,
  }) => {
    const response = await request.get('/agence')
    expect(response.status()).toBe(200)
    const body = await response.text()
    expect(body).toContain('Agence digitale à taille humaine')
    expect(body).toContain('agence digitale à taille humaine')
    expect(body).toMatch(/canonical[^>]*\/agence/)
  })

  test('contient « agence digitale à taille humaine » dans le SSR', async ({
    request,
  }) => {
    const response = await request.get('/agence')
    const body = await response.text()
    expect(body.toLowerCase()).toContain('agence digitale à taille humaine')
  })

  test('expose le pilier « Intégrée » (ancien « Globale »)', async ({ page }) => {
    await page.goto('/agence')
    await expect(page.getByText('Intégrée', { exact: true })).toBeVisible()
    // Aucun vestige de l'ancienne valeur
    const text = await page.locator('.agence-hero__pillars').innerText()
    expect(text).not.toContain('Globale')
  })

  test('expose le lead reformulé (sans « équipe réduite »)', async ({ page }) => {
    await page.goto('/agence')
    const lead = page.locator('.agence-hero__lead')
    await expect(lead).toBeVisible()
    const text = await lead.innerText()
    expect(text).toContain('interlocuteur direct')
    expect(text).not.toContain('équipe réduite')
  })

  test('expose un lien contextuel vers /services dans le corps éditorial', async ({
    page,
  }) => {
    await page.goto('/agence')
    // Le lien discret "Voir le détail de nos services" est dans la section
    // Fonctionnement — distinct du lien "Services" du header.
    const link = page.locator('.agence-page__services-link')
    await expect(link).toBeVisible()
    await expect(link).toHaveAttribute('href', '/services')
  })

  test('expose un lien vers /realisations dans le callout final', async ({
    page,
  }) => {
    await page.goto('/agence')
    // Callout final : secondary button → /realisations
    const link = page.locator('a[href="/realisations"]').first()
    await expect(link).toBeVisible()
  })

  test('expose un lien vers /contact (CTA hero)', async ({ page }) => {
    await page.goto('/agence')
    const link = page.locator('a[href="/contact"]').first()
    await expect(link).toBeVisible()
  })

  test('ne contient aucune formulation freelance ni voix singulière stratégique', async ({
    page,
  }) => {
    await page.goto('/agence')
    const text = (await page.locator('main').innerText()).toLowerCase()
    // Voix plurielle obligatoire (AGENTS.md §2 — « nous »)
    expect(text).not.toMatch(/\bje\b/)
    expect(text).not.toMatch(/\bmon agence\b/)
    expect(text).not.toMatch(/\bfreelance\b/)
    expect(text).not.toMatch(/lorem ipsum/)
    expect(text).not.toMatch(/todo/)
    expect(text).not.toMatch(/phase\s*\d/)
  })

  test('ne publie aucune donnée fictive (chiffres, témoignages, fondation)', async ({
    page,
  }) => {
    await page.goto('/agence')
    const text = (await page.locator('main').innerText()).toLowerCase()
    expect(text).not.toMatch(/fondée en \d{4}/)
    expect(text).not.toMatch(/plus de \d+\s*(clients|projets|années)/)
    expect(text).not.toMatch(/@example\./)
  })

  test('Axe — aucune violation serious/critical sur /agence', async ({ page }) => {
    await page.goto('/agence')
    const results = await new AxeBuilder({ page })
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'])
      .analyze()
    const blocking = results.violations.filter(
      (v) => v.impact === 'critical' || v.impact === 'serious',
    )
    if (blocking.length > 0) {
      console.log(
        blocking
          .map(
            (v) =>
              `[${v.impact}] ${v.id}: ${v.help}\n  ${v.nodes
                .map((n) => n.target.join(' '))
                .join('\n  ')}`,
          )
          .join('\n'),
      )
    }
    expect(blocking, 'Axe serious/critical violations').toEqual([])
  })
})

// ----- Responsive -----

const viewports = [
  { name: '390 mobile', width: 390, height: 844 },
  { name: '768 tablette', width: 768, height: 1024 },
  { name: '1024 laptop', width: 1024, height: 768 },
  { name: '1440 desktop', width: 1440, height: 900 },
]

for (const vp of viewports) {
  test(`/agence — ${vp.name} — aucun overflow horizontal`, async ({ page }) => {
    await page.setViewportSize({ width: vp.width, height: vp.height })
    await page.goto('/agence')
    const overflow = await page.evaluate(() => {
      const { scrollWidth, clientWidth } = document.documentElement
      return scrollWidth - clientWidth
    })
    expect(overflow, `overflow à ${vp.width}px`).toBeLessThanOrEqual(1)
  })

  test(`/agence — ${vp.name} — H1 visible`, async ({ page }) => {
    await page.setViewportSize({ width: vp.width, height: vp.height })
    await page.goto('/agence')
    await expect(page.locator('h1')).toBeVisible()
  })
}
