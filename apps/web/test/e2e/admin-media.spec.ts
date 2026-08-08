import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'

// Phase 9A — Fondation média éditoriale (Symfony/Twig SSR).
//
// Cette suite couvre le parcours ADMIN complet de téléversement d'un média :
// login → bibliothèque vide → formulaire d'upload → POST d'une petite image
// contrôlée générée à la volée → PRG vers la bibliothèque → apparition du
// média dans la liste → ouverture de la prévisualisation privée → vérification
// des en-têtes serveur (Content-Type, Cache-Control, X-Robots-Tag) → logout.
//
// Comme la Phase 8C3/8C4, les médias sont CRÉÉS via l'interface admin
// elle-même (upload réel via `/admin/media/new`) — c'est précisément ce qu'on
// veut tester. Le nettoyage repose sur le préfixe strict `e2e-9a-` purgé par
// `scripts/e2e-admin-media.sh` (qui supprime aussi les fichiers physiques
// sur le volume `api_var`, pas seulement les lignes en base).
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
// `imagepng`) puis figé en hexadécimal. Le pipeline serveur ré-encode via
// `imagecreatefrompng` : générer le fixture avec la même bibliothèque garantit
// que le décodage passe (une PNG trop minimaliste ou avec un chunk IDAT non
// standard fait échouer `imagecreatefrompng` et déclenche InvalidImageException).
// Poids : ~100 octets ; largement en-deçà de 8 Mio et de 8000×8000 px.
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

test.describe.serial('Admin — médias éditoriaux (Phase 9A)', () => {
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
    await page.getByRole('link', { name: 'Médias' }).click()
    await page.waitForURL(`${ADMIN_BASE_URL}/admin/media`)

    await expect(
      page.getByRole('heading', { name: 'Médias éditoriaux', level: 1 }),
    ).toBeVisible()

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

  test('cycle complet : upload PNG → PRG → média visible → preview privée', async ({
    page,
  }) => {
    const filename = `${FILENAME_PREFIX}cycle-${Date.now()}.png`

    await login(page)

    // --- Bibliothèque avant upload : état de départ propre --------------
    await page.goto(`${ADMIN_BASE_URL}/admin/media`)
    await expect(
      page.getByRole('cell', { name: filename }),
      'Aucun résidu ne doit exister pour ce nom (fixture: scripts/e2e-admin-media.sh load).',
    ).toHaveCount(0)

    // --- Ouverture du formulaire d'upload -------------------------------
    await page.getByRole('link', { name: 'Téléverser un média' }).click()
    await page.waitForURL(`${ADMIN_BASE_URL}/admin/media/new`)

    // --- Injection du binaire in-memory + soumission --------------------
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

    // --- Le média apparaît dans la table de la bibliothèque -------------
    const row = page.getByRole('row').filter({ hasText: filename })
    await expect(row).toBeVisible()
    await expect(row).toContainText('image/png')

    // --- Ouverture de la prévisualisation privée ------------------------
    const previewLink = row.getByRole('link', {
      name: `Ouvrir la prévisualisation du média ${filename}`,
    })
    await expect(previewLink).toBeVisible()
    const previewHref = await previewLink.getAttribute('href')
    expect(previewHref, 'href preview').toMatch(
      /^\/admin\/media\/[0-9a-f-]{36}\/preview$/,
    )

    // On requête directement la preview via le request context (mêmes
    // cookies de session que la page) pour inspecter les en-têtes bruts,
    // ce que `page.goto` sur un binaire ne permet pas de faire proprement.
    const previewResponse = await page.request.get(
      `${ADMIN_BASE_URL}${previewHref}`,
    )
    expect(previewResponse.status(), 'Preview privée doit renvoyer 200').toBe(200)

    const headers = previewResponse.headers()
    expect(headers['content-type']).toBe('image/png')
    expect(headers['content-disposition']).toBe('inline')
    // Preview STRICTEMENT privée : jamais mise en cache partagé, jamais
    // indexée, jamais embarquée dans un <iframe> tiers.
    expect(headers['cache-control']).toContain('private')
    expect(headers['cache-control']).toContain('no-store')
    expect(headers['x-robots-tag']).toBe('noindex, nofollow')
    expect(headers['x-frame-options']).toBe('DENY')
    expect(headers['x-content-type-options']).toBe('nosniff')

    // Le corps doit démarrer par le magic byte PNG et être non-trivial.
    const body = await previewResponse.body()
    expect(body[0]).toBe(0x89)
    expect(body[1]).toBe(0x50)
    expect(body[2]).toBe(0x4e)
    expect(body[3]).toBe(0x47)
    expect(body.length).toBeGreaterThan(50)
  })

  test('preview anonyme : redirection vers /admin/login (aucune fuite binaire)', async ({
    page,
  }) => {
    // Un UUID canonique mais absent en base — le firewall doit intercepter
    // AVANT même la résolution de l'asset. Aucune image ne doit fuiter.
    const fakeUuid = '00000000-0000-0000-0000-000000000000'
    await page.goto(`${ADMIN_BASE_URL}/admin/media/${fakeUuid}/preview`, {
      waitUntil: 'domcontentloaded',
    })
    await expect(page).toHaveURL(`${ADMIN_BASE_URL}/admin/login`)
  })

  test('en-têtes de sécurité admin appliqués aux pages HTML médias', async ({
    request,
  }) => {
    // Session HTTP directe pour lire les en-têtes bruts (pas de navigation).
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

// Le logout admin est déjà exercé de bout en bout par admin.spec.ts (login
// puis clic sur le bouton « Se déconnecter » qui porte le token CSRF requis
// par le firewall). Le dupliquer ici imposerait d'aller chercher un formulaire
// de logout côté dashboard (aucun bouton n'est rendu dans _layout.html.twig
// pour les pages de contenu) ou de fabriquer manuellement un POST avec token
// CSRF — deux détours qui n'ajoutent rien à la validation du pipeline média.
