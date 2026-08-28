import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'

// Phase 9A — Fondation média éditoriale (Symfony/Twig SSR).
// Phase R5  — Refonte médiathèque visuelle (grille de cartes).
//
// Cette suite couvre le parcours ADMIN complet de téléversement d'un média :
// login → bibliothèque vide → formulaire d'upload → POST d'une petite image
// contrôlée générée à la volée → PRG vers la bibliothèque → apparition du
// média dans la grille → ouverture de la prévisualisation privée → vérification
// des en-têtes serveur (Content-Type, Cache-Control, X-Robots-Tag) → logout.
//
// Serial + noms uniques par test : le firewall Symfony applique un throttling
// par email+IP, et le rate limiter admin_media_upload plafonne à 20 uploads
// par admin sur 10 min — mieux vaut sérialiser pour rester très loin du seuil
// et éviter tout entrelacement au niveau du nettoyage.

const ADMIN_BASE_URL = process.env.ADMIN_BASE_URL ?? 'http://localhost:3001'
const ADMIN_EMAIL = process.env.ADMIN_TEST_EMAIL ?? 'e2e-admin@example.test'
const ADMIN_PASSWORD = process.env.ADMIN_TEST_PASSWORD ?? 'DevzairAdmin!2026'

const WCAG_TAGS = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']

// Préfixe fixe — DOIT correspondre à `E2E_FILENAME_PREFIX` dans
// `scripts/e2e-admin-media.sh`. Toute divergence casse le nettoyage.
const FILENAME_PREFIX = 'e2e-9a-'

// PNG 4×4 pixels réellement produit par GD (`imagecreatetruecolor` +
// `imagepng`) puis figé en hexadécimal.
const TINY_PNG = Buffer.from(
  '89504e470d0a1a0a0000000d494844520000000400000004080200000026930929' +
    '000000097048597300000ec400000ec401952b0e1b0000001449444154089963e412' +
    '91638001260624809b03000ca80044af72a26c0000000049454e44ae426082',
  'hex',
)

async function login(page: import('@playwright/test').Page): Promise<void> {
  await page.goto(`${ADMIN_BASE_URL}/admin/login`)
  await page.getByLabel('Adresse email').fill(ADMIN_EMAIL)
  await page.getByLabel('Mot de passe').fill(ADMIN_PASSWORD)
  await page.getByRole('button', { name: 'Se connecter' }).click()
  await page.waitForURL(`${ADMIN_BASE_URL}/admin`)
}

async function scanAxe(
  page: import('@playwright/test').Page,
  label: string,
): Promise<void> {
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
    console.log(`Axe blocking violations on ${label}:\n${lines.join('\n')}`)
  }
  expect(blocking, `Axe serious/critical violations on ${label}`).toEqual([])
}

test.describe.serial('Admin — médias éditoriaux (Phase 9A / R5)', () => {
  test('anonyme : accès à /admin/media redirige vers /admin/login', async ({
    page,
  }) => {
    await page.goto(`${ADMIN_BASE_URL}/admin/media`, {
      waitUntil: 'domcontentloaded',
    })
    await expect(page).toHaveURL(`${ADMIN_BASE_URL}/admin/login`)
  })

  test('bibliothèque accessible (Axe WCAG 2.2 AA)', async ({ page }) => {
    await login(page)
    // Scope au <aside> pour éviter l'ambiguïté avec le lien KPI "Voir les médias →"
    await page.locator('aside').getByRole('link', { name: 'Médias', exact: true }).click()
    await page.waitForURL(`${ADMIN_BASE_URL}/admin/media`)

    await expect(
      page.getByRole('heading', { name: 'Médias éditoriaux', level: 1 }),
    ).toBeVisible()

    // CTA upload présent dans le header
    await expect(
      page.getByRole('link', { name: /Téléverser un média/ }),
    ).toBeVisible()

    // État vide ou grille : l'un des deux doit être présent
    const hasGrid = await page.locator('.admin-media-grid').count()
    const hasEmpty = await page.locator('.admin-media-empty').count()
    expect(hasGrid + hasEmpty, 'Grille ou état vide attendu').toBeGreaterThan(0)

    await scanAxe(page, '/admin/media')
  })

  test('formulaire d\'upload accessible (Axe WCAG 2.2 AA)', async ({ page }) => {
    await login(page)
    await page.goto(`${ADMIN_BASE_URL}/admin/media/new`)

    await expect(
      page.getByRole('heading', { name: 'Téléverser un média', level: 1 }),
    ).toBeVisible()

    // Champ file exposé et libellé lié — vérification explicite avant Axe.
    await expect(page.locator('input#media-file[type="file"]')).toBeVisible()
    await expect(page.getByLabel('Fichier image')).toBeVisible()

    await scanAxe(page, '/admin/media/new')
  })

  test('cycle complet : upload PNG → PRG → média visible dans la grille → preview privée', async ({
    page,
  }) => {
    const filename = `${FILENAME_PREFIX}cycle-${Date.now()}.png`

    await login(page)

    // --- Bibliothèque avant upload : aucune carte avec ce nom ne doit exister.
    await page.goto(`${ADMIN_BASE_URL}/admin/media`)
    await expect(
      page.locator('.admin-media-card', { hasText: filename }),
      'Aucun résidu ne doit exister pour ce nom (fixture: scripts/e2e-admin-media.sh load).',
    ).toHaveCount(0)

    // --- Ouverture du formulaire d'upload
    await page.getByRole('link', { name: /Téléverser un média/ }).first().click()
    await page.waitForURL(`${ADMIN_BASE_URL}/admin/media/new`)

    // --- Injection du binaire in-memory + soumission
    await page.setInputFiles('input#media-file', {
      name: filename,
      mimeType: 'image/png',
      buffer: TINY_PNG,
    })
    await page.getByRole('button', { name: 'Téléverser' }).click()

    // --- PRG : le POST /admin/media/new redirige (303 See Other) vers
    //     /admin/media avec un flash de succès.
    await page.waitForURL(`${ADMIN_BASE_URL}/admin/media`)
    await expect(page.locator('.alert-success')).toContainText(filename)

    // --- Le média apparaît dans la grille de la bibliothèque
    const card = page.locator('.admin-media-card', { hasText: filename })
    await expect(card).toBeVisible()
    // Type affiché en format humain (PNG, pas image/png)
    await expect(card).toContainText('PNG')

    // --- Lien preview avec aria-label correct
    const previewLink = card.getByRole('link', {
      name: `Prévisualiser « ${filename} »`,
    })
    await expect(previewLink).toBeVisible()
    const previewHref = await previewLink.getAttribute('href')
    expect(previewHref, 'href preview').toMatch(
      /^\/admin\/media\/[0-9a-f-]{36}\/preview$/,
    )

    // On requête directement la preview via le request context (mêmes
    // cookies de session que la page) pour inspecter les en-têtes bruts.
    const previewResponse = await page.request.get(
      `${ADMIN_BASE_URL}${previewHref}`,
    )
    expect(previewResponse.status(), 'Preview privée doit renvoyer 200').toBe(200)

    const headers = previewResponse.headers()
    expect(headers['content-type']).toBe('image/png')
    expect(headers['content-disposition']).toBe('inline')
    expect(headers['cache-control']).toContain('private')
    expect(headers['cache-control']).toContain('no-store')
    expect(headers['x-robots-tag']).toBe('noindex, nofollow')
    expect(headers['x-frame-options']).toBe('DENY')
    expect(headers['x-content-type-options']).toBe('nosniff')

    const body = await previewResponse.body()
    expect(body[0]).toBe(0x89)
    expect(body[1]).toBe(0x50)
    expect(body[2]).toBe(0x4e)
    expect(body[3]).toBe(0x47)
    expect(body.length).toBeGreaterThan(50)
  })

  test('médiathèque R5 — structure grille et métadonnées', async ({ page }) => {
    const filename = `${FILENAME_PREFIX}r5-meta-${Date.now()}.png`

    await login(page)
    await page.goto(`${ADMIN_BASE_URL}/admin/media/new`)
    await page.setInputFiles('input#media-file', {
      name: filename,
      mimeType: 'image/png',
      buffer: TINY_PNG,
    })
    await page.getByRole('button', { name: 'Téléverser' }).click()
    await page.waitForURL(`${ADMIN_BASE_URL}/admin/media`)

    // La grille est présente
    await expect(page.locator('.admin-media-grid')).toBeVisible()

    // La carte du média est visible avec ses métadonnées
    const card = page.locator('.admin-media-card', { hasText: filename })
    await expect(card).toBeVisible()

    // Filename affiché dans la carte
    await expect(card.locator('.admin-media-card__filename')).toContainText(filename)

    // Dimensions présentes (TINY_PNG = 4×4)
    await expect(card.locator('.admin-media-card__dimensions')).toBeVisible()

    // Type humain (PNG) et poids présents
    await expect(card.locator('.admin-media-card__type-weight')).toContainText('PNG')
    await expect(card.locator('.admin-media-card__type-weight')).toContainText('Kio')

    // Date présente (attribut datetime ISO-8601)
    const timeEl = card.locator('.admin-media-card__date')
    await expect(timeEl).toBeVisible()
    const datetime = await timeEl.getAttribute('datetime')
    expect(datetime, 'datetime ISO-8601').toMatch(/^\d{4}-\d{2}-\d{2}T/)

    // L'image aperçu est présente
    await expect(card.locator('.admin-media-card__image')).toBeVisible()

    // Lien preview accessible
    await expect(
      card.getByRole('link', { name: `Prévisualiser « ${filename} »` }),
    ).toBeVisible()

    await scanAxe(page, '/admin/media (avec médias)')
  })

  test('médiathèque R5 — breakpoints sans débordement horizontal', async ({ page }) => {
    await login(page)
    await page.goto(`${ADMIN_BASE_URL}/admin/media`)

    const viewports = [
      { width: 390,  height: 844  },
      { width: 768,  height: 1024 },
      { width: 1024, height: 768  },
      { width: 1440, height: 900  },
    ]

    for (const vp of viewports) {
      await page.setViewportSize(vp)
      // Laisser le layout recalculer
      await page.waitForTimeout(50)
      const scrollWidth = await page.evaluate(() => document.body.scrollWidth)
      expect(
        scrollWidth,
        `Pas de débordement horizontal à ${vp.width} px`,
      ).toBeLessThanOrEqual(vp.width)
    }
  })

  test('médiathèque R5 — 390 px : grille visible, focus clavier sur preview', async ({ page }) => {
    await login(page)
    await page.goto(`${ADMIN_BASE_URL}/admin/media`)
    await page.setViewportSize({ width: 390, height: 844 })

    const grid = page.locator('.admin-media-grid')
    const hasGrid = await grid.count()
    if (hasGrid > 0) {
      await expect(grid).toBeVisible()
      // Premier lien preview reçoit le focus clavier
      const firstPreview = page.locator('.admin-media-card__preview').first()
      await firstPreview.focus()
      await expect(firstPreview).toBeFocused()
    } else {
      // État vide acceptable (DB propre)
      await expect(page.locator('.admin-media-empty')).toBeVisible()
    }
  })

  test('preview anonyme : redirection vers /admin/login (aucune fuite binaire)', async ({
    page,
  }) => {
    const fakeUuid = '00000000-0000-0000-0000-000000000000'
    await page.goto(`${ADMIN_BASE_URL}/admin/media/${fakeUuid}/preview`, {
      waitUntil: 'domcontentloaded',
    })
    await expect(page).toHaveURL(`${ADMIN_BASE_URL}/admin/login`)
  })

  test('en-têtes de sécurité admin appliqués aux pages HTML médias', async ({
    request,
  }) => {
    await request.post(`${ADMIN_BASE_URL}/admin/login`, {
      form: {
        _username: ADMIN_EMAIL,
        _password: ADMIN_PASSWORD,
      },
    })

    for (const path of ['/admin/media', '/admin/media/new']) {
      const response = await request.get(`${ADMIN_BASE_URL}${path}`)
      const headers = response.headers()
      expect(
        headers['content-security-policy'],
        `CSP présent sur ${path}`,
      ).toContain("default-src 'none'")
      expect(headers['x-frame-options'], `X-Frame-Options sur ${path}`).toBe('DENY')
      expect(headers['x-robots-tag'], `X-Robots-Tag sur ${path}`).toBe(
        'noindex, nofollow',
      )
      expect(headers['cache-control'], `Cache-Control sur ${path}`).toContain(
        'no-store',
      )
      expect(headers['cache-control'], `Cache-Control sur ${path}`).toContain(
        'private',
      )
      expect(headers['referrer-policy'], `Referrer-Policy sur ${path}`).toBe(
        'no-referrer',
      )
      expect(
        headers['x-content-type-options'],
        `X-Content-Type-Options sur ${path}`,
      ).toBe('nosniff')
    }
  })
})

// Le logout admin est déjà exercé de bout en bout par admin.spec.ts.
