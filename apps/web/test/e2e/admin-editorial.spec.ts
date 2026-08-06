import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'

// Phase 8C3 — Interface d'administration éditoriale (Symfony/Twig SSR).
//
// Cette suite couvre le parcours ADMIN complet et sensible : création d'un
// brouillon via le formulaire, édition, publication, archivage, restauration.
// Contrairement à la Phase 8B2 qui préchargeait un jeu déterministe via un
// fixtures runner, ici la suite CRÉE les articles au fil de l'exécution —
// c'est précisément ce qu'on veut tester. Le nettoyage repose sur le
// préfixe strict `e2e-8c3-` purgé par `scripts/e2e-admin-articles.sh`.
//
// L'admin est rendu par Symfony derrière Caddy — on cible donc explicitement
// `ADMIN_BASE_URL` (fallback `http://localhost:3001`). Aucun autre spec n'y
// touche : la Phase 8C1 (admin.spec.ts) porte l'authentification, la
// Phase 8C3 porte le cycle éditorial.
//
// Serial + slugs uniques par test : le firewall Symfony applique un
// throttling par email+IP, et l'admin ne peut pas se connecter en parallèle
// depuis plusieurs contextes sans saturer le compteur. Chaque test utilise
// un slug distinct pour éviter tout entrelacement au niveau des DELETE.

const ADMIN_BASE_URL = process.env.ADMIN_BASE_URL ?? 'http://localhost:3001'
const ADMIN_EMAIL = process.env.ADMIN_TEST_EMAIL ?? 'e2e-admin@example.test'
const ADMIN_PASSWORD = process.env.ADMIN_TEST_PASSWORD ?? 'DevzairAdmin!2026'

const WCAG_TAGS = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']

// Préfixe fixe — DOIT correspondre à `E2E_SLUG_PREFIX` dans
// `scripts/e2e-admin-articles.sh`. Toute divergence casse le nettoyage.
const SLUG_PREFIX = 'e2e-8c3-'

async function login(page: import('@playwright/test').Page): Promise<void> {
  await page.goto(`${ADMIN_BASE_URL}/admin/login`)
  await page.getByLabel('Adresse email').fill(ADMIN_EMAIL)
  await page.getByLabel('Mot de passe').fill(ADMIN_PASSWORD)
  await page.getByRole('button', { name: 'Se connecter' }).click()
  await page.waitForURL(`${ADMIN_BASE_URL}/admin`)
}

async function fillDraftForm(
  page: import('@playwright/test').Page,
  slug: string,
  title: string,
): Promise<void> {
  await page.getByLabel(/Slug \(URL publique, immuable/).fill(slug)
  await page.getByLabel('Titre', { exact: true }).fill(title)
  await page
    .getByLabel('Chapô (résumé court)')
    .fill('Un chapô suffisamment long pour satisfaire la borne minimale imposée par le domaine éditorial.')
  await page
    .getByLabel('Corps (Markdown)')
    .fill('## Introduction\n\nCorps Markdown de test E2E — contenu crédible mais dépourvu de toute PII.')
  await page.getByLabel('Titre SEO').fill('Titre SEO E2E déterministe pour la Phase 8C3 admin')
  await page
    .getByLabel('Meta description')
    .fill('Description SEO E2E — assez longue pour dépasser la borne minimale imposée par le domaine.')
  await page.getByLabel('Nom affiché').fill('Devzair')
  // Un pilier suffit à satisfaire la validation ; on prend « Concevoir ».
  await page.getByRole('checkbox', { name: 'Concevoir' }).check()
}

test.describe.serial('Admin — édition éditoriale (Phase 8C3)', () => {
  test('la liste vide est accessible (Axe WCAG 2.2 AA)', async ({ page }) => {
    await login(page)
    await page.getByRole('link', { name: 'Articles' }).click()
    await page.waitForURL(`${ADMIN_BASE_URL}/admin/articles`)

    await expect(page.getByRole('heading', { name: 'Articles', level: 1 })).toBeVisible()

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
      console.log(`Axe blocking violations on /admin/articles:\n${lines.join('\n')}`)
    }
    expect(blocking, 'Axe serious/critical violations on /admin/articles').toEqual([])
  })

  test('création d\'un brouillon → redirection vers /edit et apparition dans la liste', async ({
    page,
  }) => {
    const slug = `${SLUG_PREFIX}create-${Date.now()}`
    const title = `E2E création — ${slug}`

    await login(page)
    await page.goto(`${ADMIN_BASE_URL}/admin/articles/new`)

    // Axe sur le formulaire de création — le lieu principal d'interaction admin.
    const formScan = await new AxeBuilder({ page }).withTags(WCAG_TAGS).analyze()
    const formBlocking = formScan.violations.filter(
      (v) => v.impact === 'critical' || v.impact === 'serious',
    )
    expect(formBlocking, 'Axe serious/critical violations on /admin/articles/new').toEqual([])

    await fillDraftForm(page, slug, title)
    await page.getByRole('button', { name: 'Créer le brouillon' }).click()

    // PRG : le POST redirige vers /admin/articles/{uuid}/edit.
    await page.waitForURL(/\/admin\/articles\/[0-9a-f-]{36}\/edit$/)
    await expect(page.getByRole('heading', { name: 'Édition du brouillon' })).toBeVisible()

    // Le badge affiche bien « Brouillon » et le slug est en lecture seule.
    await expect(page.locator('.status-badge.status-draft')).toContainText('Brouillon')
    await expect(page.locator('code', { hasText: slug })).toBeVisible()
    // Le champ input[name=slug] doit être absent (immuabilité côté serveur).
    await expect(page.locator('input[name="slug"]')).toHaveCount(0)

    // Retour à la liste : l'article apparaît sur la première page.
    await page.goto(`${ADMIN_BASE_URL}/admin/articles?status=draft`)
    await expect(page.getByRole('cell', { name: title })).toBeVisible()
    await expect(page.locator('code', { hasText: slug })).toBeVisible()
  })

  test('édition d\'un brouillon existant : modification du titre persistée', async ({ page }) => {
    const slug = `${SLUG_PREFIX}edit-${Date.now()}`
    const originalTitle = `E2E édition initiale — ${slug}`
    const updatedTitle = `E2E édition mise à jour — ${slug}`

    await login(page)
    await page.goto(`${ADMIN_BASE_URL}/admin/articles/new`)
    await fillDraftForm(page, slug, originalTitle)
    await page.getByRole('button', { name: 'Créer le brouillon' }).click()
    await page.waitForURL(/\/admin\/articles\/[0-9a-f-]{36}\/edit$/)

    // Modification du titre uniquement — les autres champs restent renseignés
    // grâce au rendu serveur du formulaire.
    await page.getByLabel('Titre', { exact: true }).fill(updatedTitle)
    await page.getByRole('button', { name: 'Enregistrer les modifications' }).click()

    // Après enregistrement, on reste sur la page d'édition (PRG en place).
    await page.waitForURL(/\/admin\/articles\/[0-9a-f-]{36}\/edit$/)
    await expect(page.getByLabel('Titre', { exact: true })).toHaveValue(updatedTitle)
  })

  test('cycle complet : publish → archive → restore', async ({ page }) => {
    const slug = `${SLUG_PREFIX}cycle-${Date.now()}`
    const title = `E2E cycle complet — ${slug}`

    await login(page)
    await page.goto(`${ADMIN_BASE_URL}/admin/articles/new`)
    await fillDraftForm(page, slug, title)
    await page.getByRole('button', { name: 'Créer le brouillon' }).click()
    await page.waitForURL(/\/admin\/articles\/[0-9a-f-]{36}\/edit$/)
    const editUrl = page.url()
    const uuid = editUrl.match(/\/admin\/articles\/([0-9a-f-]{36})\/edit$/)?.[1]
    expect(uuid, 'UUID extractible de l\'URL d\'édition').toBeTruthy()

    // --- Publish ---------------------------------------------------------
    await page.getByRole('button', { name: 'Publier ce brouillon' }).click()
    await page.waitForURL(`${ADMIN_BASE_URL}/admin/articles`)
    // L'article publié est visible sur la liste filtrée par « Publiés ».
    await page.goto(`${ADMIN_BASE_URL}/admin/articles?status=published`)
    await expect(page.getByRole('cell', { name: title })).toBeVisible()

    // La page d'édition affiche maintenant le badge « Publié » et l'action
    // libre est verrouillée — seul « Archiver » reste offert.
    await page.goto(`${ADMIN_BASE_URL}/admin/articles/${uuid}/edit`)
    await expect(page.locator('.status-badge.status-published')).toContainText('Publié')
    await expect(page.getByLabel('Titre', { exact: true })).toHaveCount(0)

    // --- Archive ---------------------------------------------------------
    await page
      .getByRole('button', { name: 'Archiver et retirer du site public' })
      .click()
    await page.waitForURL(`${ADMIN_BASE_URL}/admin/articles`)
    await page.goto(`${ADMIN_BASE_URL}/admin/articles?status=archived`)
    await expect(page.getByRole('cell', { name: title })).toBeVisible()

    // --- Restore ---------------------------------------------------------
    await page.goto(`${ADMIN_BASE_URL}/admin/articles/${uuid}/edit`)
    await expect(page.locator('.status-badge.status-archived')).toContainText('Archivé')
    await page.getByRole('button', { name: 'Restaurer comme brouillon' }).click()
    // Après restauration, on revient sur la page d'édition.
    await page.waitForURL(new RegExp(`/admin/articles/${uuid}/edit$`))
    await expect(page.locator('.status-badge.status-draft')).toContainText('Brouillon')
  })

  test('les headers de sécurité admin s\'appliquent aux pages /admin/articles/*', async ({
    request,
  }) => {
    // On instancie une session via l'API HTTP directement : Playwright gère
    // les cookies dans le request context après un login form-based.
    await request.post(`${ADMIN_BASE_URL}/admin/login`, {
      form: {
        _username: ADMIN_EMAIL,
        _password: ADMIN_PASSWORD,
      },
    })

    const response = await request.get(`${ADMIN_BASE_URL}/admin/articles`)
    // 200 (authentifié) ou 302 (le request context peut ne pas avoir la
    // session — dans ce cas, on ne valide que ce qui reste vrai côté login).
    // Les headers de sécurité DOIVENT être posés dans les deux cas.
    const headers = response.headers()
    expect(headers['content-security-policy']).toContain("default-src 'none'")
    expect(headers['x-frame-options']).toBe('DENY')
    expect(headers['x-robots-tag']).toBe('noindex, nofollow')
    expect(headers['cache-control']).toContain('no-store')
    expect(headers['cache-control']).toContain('private')
  })
})
