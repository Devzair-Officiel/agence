import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'

// Phase 8C1 — Vérification E2E du flow d'authentification admin.
//
// L'admin est rendu par Symfony (Twig SSR) derrière Caddy, pas par Nuxt :
// on cible donc explicitement le domaine complet via `ADMIN_BASE_URL`
// (fallback `http://localhost:3001` — le port exposé par Caddy en dev,
// voir `compose.yaml`). Aucun autre spec ne dépend de cette URL.
//
// Prérequis dev/CI : un administrateur E2E dédié doit exister en base
// AVANT le lancement de la suite. L'email par défaut `e2e-admin@example.test`
// utilise le TLD réservé `example.test` (RFC 6761) — jamais routable, jamais
// confondable avec un vrai compte de production. Le compte est distinct du
// compte admin de l'opérateur local pour éviter que la suite ne perturbe
// la session de travail (throttling, mot de passe rotaté). Créer via :
//   docker compose exec -T api php bin/console app:admin:create-user \
//     --email=e2e-admin@example.test --display-name='E2E Admin' \
//     --password-stdin <<< 'DevzairAdmin!2026'
// Surcharger `ADMIN_TEST_EMAIL` / `ADMIN_TEST_PASSWORD` si le compte de CI
// diffère.
//
// Scope :
//  - la page /admin/login est accessible (WCAG 2.2 AA, sérieux/critique) ;
//  - un login valide amène sur /admin (dashboard) ;
//  - le dashboard est accessible ;
//  - un login invalide reste sur /admin/login avec un message générique
//    (aucune divulgation d'existence d'email) ;
//  - le logout invalide la session et renvoie vers /admin/login.
//
// Non couvert (par choix Phase 8C1) : rotation de session (déjà couvert par
// PHPUnit), throttling (rejoue 5 échecs → test flaky en E2E), création
// éditoriale (Phase 8C2+).

const ADMIN_BASE_URL = process.env.ADMIN_BASE_URL ?? 'http://localhost:3001'
const ADMIN_EMAIL = process.env.ADMIN_TEST_EMAIL ?? 'e2e-admin@example.test'
const ADMIN_PASSWORD = process.env.ADMIN_TEST_PASSWORD ?? 'DevzairAdmin!2026'

const WCAG_TAGS = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']

// Serial : le firewall Symfony applique un throttling (5 tentatives / 15 min
// par email+IP). Deux clients Playwright frappant en parallèle sur le même
// compte peuvent saturer le compteur et faire échouer un login pourtant
// valide. Serial + email distinct pour le cas « invalide » évite tout
// entrelacement.
test.describe.serial('Admin — authentification (Phase 8C1)', () => {
  test('la page de connexion est accessible (Axe WCAG 2.2 AA)', async ({ page }) => {
    await page.goto(`${ADMIN_BASE_URL}/admin/login`)
    await expect(page.getByRole('heading', { name: 'Connexion' })).toBeVisible()

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
      console.log(`Axe blocking violations on /admin/login:\n${lines.join('\n')}`)
    }

    expect(blocking, 'Axe serious/critical violations on /admin/login').toEqual([])
  })

  test('login valide → dashboard accessible + logout renvoie sur /admin/login', async ({
    page,
  }) => {
    await page.goto(`${ADMIN_BASE_URL}/admin/login`)
    await page.getByLabel('Adresse email').fill(ADMIN_EMAIL)
    await page.getByLabel('Mot de passe').fill(ADMIN_PASSWORD)
    await page.getByRole('button', { name: 'Se connecter' }).click()

    // Le firewall Symfony redirige vers /admin (default_target_path).
    await page.waitForURL(`${ADMIN_BASE_URL}/admin`)
    await expect(page.getByRole('heading', { name: 'Tableau de bord' })).toBeVisible()

    // Axe sur le dashboard authentifié — l'utilisateur passe l'essentiel de
    // son temps ici, la conformité doit y être garantie au même niveau.
    const dashboardScan = await new AxeBuilder({ page }).withTags(WCAG_TAGS).analyze()
    const dashboardBlocking = dashboardScan.violations.filter(
      (v) => v.impact === 'critical' || v.impact === 'serious',
    )
    if (dashboardBlocking.length > 0) {
      const lines = dashboardBlocking.map(
        (v) =>
          `- [${v.impact}] ${v.id}: ${v.help}\n    ${v.nodes
            .map((n) => n.target.join(' '))
            .join('\n    ')}`,
      )
      console.log(`Axe blocking violations on /admin:\n${lines.join('\n')}`)
    }
    expect(dashboardBlocking, 'Axe serious/critical violations on /admin').toEqual([])

    // Logout via le formulaire POST (le bouton porte le token CSRF).
    await page.getByRole('button', { name: 'Se déconnecter' }).click()
    await page.waitForURL(`${ADMIN_BASE_URL}/admin/login`)
    await expect(page.getByRole('heading', { name: 'Connexion' })).toBeVisible()

    // Après logout, une tentative directe sur /admin renvoie vers /admin/login.
    await page.goto(`${ADMIN_BASE_URL}/admin`)
    await page.waitForURL(/\/admin\/login/)
  })

  test('login invalide reste sur /admin/login avec un message générique', async ({
    page,
  }) => {
    // Email dédié (inexistant) : évite d'incrémenter le compteur de throttling
    // rattaché à l'admin réel, ce qui protégerait les prochains runs locaux.
    const ghostEmail = `playwright-ghost-${Date.now()}@example.invalid`

    await page.goto(`${ADMIN_BASE_URL}/admin/login`)
    await page.getByLabel('Adresse email').fill(ghostEmail)
    await page.getByLabel('Mot de passe').fill('MotDePasseVolontairementFaux!')
    await page.getByRole('button', { name: 'Se connecter' }).click()

    await page.waitForURL(`${ADMIN_BASE_URL}/admin/login`)
    // Message générique : ne révèle jamais si l'email existe.
    await expect(page.getByRole('alert')).toContainText('Identifiants invalides')
  })

  test('les headers de sécurité admin sont bien posés', async ({ request }) => {
    const response = await request.get(`${ADMIN_BASE_URL}/admin/login`)
    expect(response.status()).toBe(200)
    const headers = response.headers()

    expect(headers['content-security-policy']).toContain("default-src 'none'")
    expect(headers['x-frame-options']).toBe('DENY')
    expect(headers['x-content-type-options']).toBe('nosniff')
    expect(headers['referrer-policy']).toBe('no-referrer')
    expect(headers['x-robots-tag']).toBe('noindex, nofollow')
  })
})
