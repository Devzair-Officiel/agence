import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'

// Phase 8C4 — Prévisualisation éditoriale authentifiée (Symfony/Twig SSR).
//
// Cette suite couvre le parcours ADMIN complet de prévisualisation :
// création d'un brouillon → édition → prévisualisation → publication →
// vérification côté site public → archivage → 404 public → restauration
// → nouvelle prévisualisation en brouillon → logout. Comme la Phase 8C3,
// les articles sont CRÉÉS via l'interface admin elle-même — le nettoyage
// repose sur le préfixe strict `e2e-8c4-preview-` purgé par
// `scripts/e2e-admin-preview.sh`.
//
// Serial + slugs uniques par test : le firewall Symfony applique un
// throttling par email+IP, l'admin ne peut pas se connecter en parallèle
// depuis plusieurs contextes sans saturer le compteur.

const ADMIN_BASE_URL = process.env.ADMIN_BASE_URL ?? 'http://localhost:3001'
const ADMIN_EMAIL = process.env.ADMIN_TEST_EMAIL ?? 'e2e-admin@example.test'
const ADMIN_PASSWORD = process.env.ADMIN_TEST_PASSWORD ?? 'DevzairAdmin!2026'

const WCAG_TAGS = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']

// Préfixe fixe — DOIT correspondre à `E2E_SLUG_PREFIX` dans
// `scripts/e2e-admin-preview.sh`. Toute divergence casse le nettoyage.
const SLUG_PREFIX = 'e2e-8c4-preview-'

async function login(page: import('@playwright/test').Page): Promise<void> {
  await page.goto(`${ADMIN_BASE_URL}/admin/login`)
  await page.getByLabel('Adresse email').fill(ADMIN_EMAIL)
  await page.getByLabel('Mot de passe').fill(ADMIN_PASSWORD)
  await page.getByRole('button', { name: 'Se connecter' }).click()
  await page.waitForURL(`${ADMIN_BASE_URL}/admin`)
}

async function createDraft(
  page: import('@playwright/test').Page,
  slug: string,
  title: string,
): Promise<string> {
  await page.goto(`${ADMIN_BASE_URL}/admin/articles/new`)
  await page.getByLabel(/Slug \(URL publique, immuable/).fill(slug)
  await page.getByLabel('Titre', { exact: true }).fill(title)
  await page
    .getByLabel('Chapô (résumé court)')
    .fill('Un chapô suffisamment long pour satisfaire la borne minimale imposée par le domaine éditorial.')
  await page
    .getByLabel('Corps (Markdown)')
    .fill(
      '## Introduction\n\nCorps de démonstration pour la prévisualisation Phase 8C4.\n\n### Détail\n\n- Un item\n- Deux items\n\n```\ncode-block\n```',
    )
  await page.getByLabel('Titre SEO').fill('Titre SEO E2E déterministe pour la Phase 8C4 preview')
  await page
    .getByLabel('Meta description')
    .fill('Description SEO E2E — assez longue pour dépasser la borne minimale imposée par le domaine.')
  await page.getByLabel('Nom affiché').fill('Devzair')
  await page.getByRole('checkbox', { name: 'Concevoir' }).check()
  await page.getByRole('button', { name: 'Créer le brouillon' }).click()
  await page.waitForURL(/\/admin\/articles\/[0-9a-f-]{36}\/edit$/)
  const uuid = page.url().match(/\/admin\/articles\/([0-9a-f-]{36})\/edit$/)?.[1]
  expect(uuid, 'UUID extractible de l\'URL d\'édition').toBeTruthy()

  return uuid as string
}

test.describe.serial('Admin — prévisualisation éditoriale (Phase 8C4)', () => {
  test('anonyme : accès à /preview redirige vers /admin/login', async ({ page }) => {
    // Sans authentification, le firewall doit intercepter avant même
    // que le contrôleur ne soit invoqué. Aucun titre d'article ne doit
    // apparaître dans le HTML rendu.
    const fakeUuid = '00000000-0000-0000-0000-000000000000'
    const response = await page.goto(
      `${ADMIN_BASE_URL}/admin/articles/${fakeUuid}/preview`,
      { waitUntil: 'domcontentloaded' },
    )
    expect(response, 'Réponse HTTP attendue').not.toBeNull()
    // Le firewall Symfony redirige vers /admin/login (302 → GET login page).
    await expect(page).toHaveURL(`${ADMIN_BASE_URL}/admin/login`)
  })

  test('preview d\'un brouillon : bandeau privé, prose rendue, Axe WCAG 2.2 AA', async ({
    page,
  }) => {
    const slug = `${SLUG_PREFIX}draft-${Date.now()}`
    const title = `E2E preview brouillon — ${slug}`

    await login(page)
    const uuid = await createDraft(page, slug, title)

    // Lien « Prévisualiser » visible depuis la page d'édition.
    await expect(page.getByRole('link', { name: 'Prévisualiser cet article' })).toBeVisible()
    await page.getByRole('link', { name: 'Prévisualiser cet article' }).click()
    await page.waitForURL(new RegExp(`/admin/articles/${uuid}/preview$`))

    // Bandeau « Prévisualisation privée » présent et badge draft affiché.
    await expect(page.locator('.preview-banner')).toContainText('Prévisualisation privée')
    await expect(page.locator('.status-badge.status-draft')).toContainText('Brouillon')

    // Le contenu Markdown est bien rendu (H2 « Introduction » présent).
    await expect(page.locator('.preview-prose h2', { hasText: 'Introduction' })).toBeVisible()
    await expect(page.locator('.preview-prose h3', { hasText: 'Détail' })).toBeVisible()
    await expect(page.locator('.preview-prose li').first()).toContainText('Un item')

    // Actions Draft attendues : « Publier » et « Archiver » ; pas de « Restaurer ».
    await expect(
      page.getByRole('button', { name: 'Publier ce brouillon' }),
    ).toBeVisible()
    await expect(
      page.getByRole('button', { name: 'Archiver et retirer du site public' }),
    ).toBeVisible()
    await expect(page.getByRole('button', { name: 'Restaurer comme brouillon' })).toHaveCount(0)

    // Axe : aucune violation `serious` ou `critical` (WCAG 2.2 AA).
    const results = await new AxeBuilder({ page }).withTags(WCAG_TAGS).analyze()
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
      console.log(`Axe blocking violations on /admin/articles/${uuid}/preview:\n${lines.join('\n')}`)
    }
    expect(blocking, 'Axe serious/critical violations on preview').toEqual([])
  })

  test('cycle complet : preview → publier → visible côté public → archiver → 404 public → restaurer → preview draft', async ({
    page,
  }) => {
    const slug = `${SLUG_PREFIX}cycle-${Date.now()}`
    const title = `E2E cycle preview — ${slug}`

    await login(page)
    const uuid = await createDraft(page, slug, title)

    // --- Preview draft ---------------------------------------------------
    await page.goto(`${ADMIN_BASE_URL}/admin/articles/${uuid}/preview`)
    await expect(page.locator('.status-badge.status-draft')).toBeVisible()

    // --- Publier depuis la preview ---------------------------------------
    await page.getByRole('button', { name: 'Publier ce brouillon' }).click()
    await page.waitForURL(`${ADMIN_BASE_URL}/admin/articles`)

    // --- Vérification côté site public : la fiche est bien accessible ----
    // La route publique est portée par Nuxt sous `/ressources/{slug}`.
    // On cible la même origine (Caddy multiplexe admin + site sur :3001).
    const publicResponse = await page.goto(`${ADMIN_BASE_URL}/ressources/${slug}`)
    expect(publicResponse?.status(), 'Article publié doit être accessible publiquement').toBe(200)

    // --- Retour admin : preview affiche « Publié » ----------------------
    await page.goto(`${ADMIN_BASE_URL}/admin/articles/${uuid}/preview`)
    await expect(page.locator('.status-badge.status-published')).toContainText('Publié')
    await expect(page.getByRole('button', { name: 'Publier ce brouillon' })).toHaveCount(0)
    await expect(
      page.getByRole('button', { name: 'Archiver et retirer du site public' }),
    ).toBeVisible()

    // --- Archiver depuis la preview --------------------------------------
    await page
      .getByRole('button', { name: 'Archiver et retirer du site public' })
      .click()
    await page.waitForURL(`${ADMIN_BASE_URL}/admin/articles`)

    // --- Vérification côté site public : la fiche renvoie 404 -----------
    const archivedResponse = await page.goto(`${ADMIN_BASE_URL}/ressources/${slug}`)
    expect(archivedResponse?.status(), 'Article archivé doit renvoyer 404').toBe(404)

    // --- Retour admin : preview affiche « Archivé » et « Restaurer » ----
    await page.goto(`${ADMIN_BASE_URL}/admin/articles/${uuid}/preview`)
    await expect(page.locator('.status-badge.status-archived')).toContainText('Archivé')
    await expect(page.getByRole('button', { name: 'Publier ce brouillon' })).toHaveCount(0)
    await expect(
      page.getByRole('button', { name: 'Archiver et retirer du site public' }),
    ).toHaveCount(0)
    await expect(
      page.getByRole('button', { name: 'Restaurer comme brouillon' }),
    ).toBeVisible()

    // --- Restaurer et re-preview le brouillon ---------------------------
    await page.getByRole('button', { name: 'Restaurer comme brouillon' }).click()
    // La restauration redirige vers l'édition — on rebascule vers preview.
    await page.waitForURL(new RegExp(`/admin/articles/${uuid}/edit$`))
    await page.goto(`${ADMIN_BASE_URL}/admin/articles/${uuid}/preview`)
    await expect(page.locator('.status-badge.status-draft')).toContainText('Brouillon')
  })

  test('en-têtes de sécurité admin appliqués à la preview', async ({ request }) => {
    // Session HTTP directe pour lire les en-têtes bruts (pas de navigation).
    await request.post(`${ADMIN_BASE_URL}/admin/login`, {
      form: {
        _username: ADMIN_EMAIL,
        _password: ADMIN_PASSWORD,
      },
    })

    // On appelle une URL de preview quelconque (le UUID malformé renvoie 404
    // mais les en-têtes sont posés par le subscriber `/admin/*` avant le
    // contrôleur — c'est justement ce qu'on veut valider). En cas de
    // session absente le firewall pose ces mêmes en-têtes sur la 302.
    const response = await request.get(
      `${ADMIN_BASE_URL}/admin/articles/00000000-0000-0000-0000-000000000000/preview`,
    )
    const headers = response.headers()
    expect(headers['content-security-policy']).toContain("default-src 'none'")
    expect(headers['x-frame-options']).toBe('DENY')
    expect(headers['x-robots-tag']).toBe('noindex, nofollow')
    expect(headers['cache-control']).toContain('no-store')
    expect(headers['cache-control']).toContain('private')
    expect(headers['referrer-policy']).toBe('no-referrer')
    expect(headers['x-content-type-options']).toBe('nosniff')
  })

  test('XSS : un titre contenant du HTML est rendu comme texte, pas comme markup', async ({
    page,
  }) => {
    const slug = `${SLUG_PREFIX}xss-${Date.now()}`
    // Le titre contient un caractère `<` — le domaine l'accepte (contrainte
    // de longueur uniquement), mais Twig doit l'échapper dans le rendu.
    const title = `E2E XSS <img> — ${slug}`

    await login(page)
    const uuid = await createDraft(page, slug, title)
    await page.goto(`${ADMIN_BASE_URL}/admin/articles/${uuid}/preview`)

    // Le titre `<img>` doit apparaître comme texte visible, pas comme balise.
    // Aucune balise <img> ne doit être présente dans la page — la preview
    // n'utilise pas d'image de couverture pour l'instant.
    const imgCount = await page.locator('img').count()
    expect(imgCount, 'Aucune balise <img> injectée via le titre').toBe(0)
    // Le texte apparaît intégralement dans le H1 ou dans la meta.
    await expect(page.locator('body')).toContainText('<img>')
  })

  test('responsive : la preview est utilisable en mobile / tablette / desktop', async ({
    page,
  }) => {
    const slug = `${SLUG_PREFIX}responsive-${Date.now()}`
    const title = `E2E preview responsive — ${slug}`

    await login(page)
    const uuid = await createDraft(page, slug, title)

    for (const viewport of [
      { width: 390, height: 844, name: 'mobile' },
      { width: 768, height: 1024, name: 'tablette' },
      { width: 1440, height: 900, name: 'desktop' },
    ]) {
      await page.setViewportSize({ width: viewport.width, height: viewport.height })
      await page.goto(`${ADMIN_BASE_URL}/admin/articles/${uuid}/preview`)
      await expect(
        page.locator('.preview-banner'),
        `Bandeau visible en ${viewport.name}`,
      ).toBeVisible()
      await expect(
        page.locator('.status-badge.status-draft'),
        `Badge visible en ${viewport.name}`,
      ).toBeVisible()
    }
  })

  test('clavier : le lien « Prévisualiser » et les actions sont focusables', async ({
    page,
  }) => {
    const slug = `${SLUG_PREFIX}keyboard-${Date.now()}`
    const title = `E2E preview clavier — ${slug}`

    await login(page)
    const uuid = await createDraft(page, slug, title)

    await page.goto(`${ADMIN_BASE_URL}/admin/articles/${uuid}/preview`)

    // Le bouton « Publier ce brouillon » doit pouvoir recevoir le focus.
    const publishBtn = page.getByRole('button', { name: 'Publier ce brouillon' })
    await publishBtn.focus()
    await expect(publishBtn).toBeFocused()

    // Le lien « Retour à l'édition » doit également être focusable.
    const backLink = page.getByRole('link', { name: '← Retour à l\'édition' })
    await backLink.focus()
    await expect(backLink).toBeFocused()
  })
})
