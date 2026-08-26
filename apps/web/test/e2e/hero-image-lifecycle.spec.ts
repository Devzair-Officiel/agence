import { execFileSync } from 'node:child_process'
import { resolve } from 'node:path'
import process from 'node:process'
import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'

// Phase 9B — Cycle complet « image principale d'un article ».
//
// Cette suite couvre le parcours de bout en bout et vérifie surtout la
// PROPRIÉTÉ que la Phase 9B a introduite : l'accessibilité publique d'un
// média n'est PAS immuable — elle dépend de l'existence d'au moins un
// article Published qui le référence. C'est précisément cette propriété
// qui interdit `Cache-Control: immutable` sur `/media/{id}` (cf.
// GetPublicArticleMediaController).
//
// Deux scénarios distincts sérialisés :
//   1. « lifecycle » : login → upload média → création brouillon → set
//      hero → publish → vérifs (public /ressources/{slug} + OG + JSON-LD
//      + /media/{id} = 200) → archive → /media/{id} = 404 (les octets
//      existent toujours, mais l'accès public est révoqué) → restore →
//      /media/{id} reste 404 (l'article redevient Draft, plus référencé
//      par un Published) ;
//   2. « shared » : upload d'un média, deux articles publiés A et B qui
//      le référencent, archivage de A seul → /media/{id} reste 200 car
//      B référence toujours l'asset (contrat « médias partagés »).
//
// Fixtures et nettoyage : le préfixe strict `e2e-9b-` (slugs d'article
// ET noms de fichier média) est purgé par `scripts/e2e-9b-hero-image.sh`
// AVANT et APRÈS la suite. Le script supprime aussi les fichiers physiques
// sur le volume `api_var` — indispensable pour ne pas laisser d'orphelins.
//
// Le `beforeAll` / `afterAll` ci-dessous invoquent ce script en local :
// la suite est ainsi autonome (plus besoin d'un `load`/`clear` manuel
// externe pour ne pas contaminer les autres specs qui suivent
// alphabétiquement — `resources.spec.ts` en particulier). Le script bash
// reste la seule source de vérité (aucun DELETE SQL dupliqué ici) ; en
// CI, le workflow répète explicitement ces mêmes `load`/`clear` en
// périmètre job pour garantir la propreté même si Playwright plante
// avant `afterAll`.
//
// Serial obligatoire : le firewall Symfony applique un throttling par
// email+IP, et le rate limiter media upload plafonne à 20 uploads / 10 min.

const ADMIN_BASE_URL = process.env.ADMIN_BASE_URL ?? 'http://localhost:3001'
const PUBLIC_BASE_URL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:3001'
const ADMIN_EMAIL = process.env.ADMIN_TEST_EMAIL ?? 'e2e-admin@example.test'
const ADMIN_PASSWORD = process.env.ADMIN_TEST_PASSWORD ?? 'DevzairAdmin!2026'

const WCAG_TAGS = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']

// Préfixes fixes — DOIVENT correspondre aux constantes du script bash
// `scripts/e2e-9b-hero-image.sh`. Toute divergence casse le nettoyage.
const SLUG_PREFIX = 'e2e-9b-'
const FILENAME_PREFIX = 'e2e-9b-'

// PNG 4×4 pixels réellement produit par GD, figé en hexadécimal. Le pipeline
// d'upload le décode avec `imagecreatefrompng` : générer le fixture avec la
// même bibliothèque garantit qu'il passe la validation formelle. ~100 octets,
// largement en-deçà des bornes (8 Mio, 8000×8000 px).
const TINY_PNG = Buffer.from(
  '89504e470d0a1a0a0000000d494844520000000400000004080200000026930929' +
    '000000097048597300000ec400000ec401952b0e1b0000001449444154089963e412' +
    '91638001260624809b03000ca80044af72a26c0000000049454e44ae426082',
  'hex',
)

// Résolu depuis `apps/web` (cwd de Playwright — voir `playwright.config.ts`,
// pas de `working-directory` custom) vers la racine monorepo, où
// `scripts/e2e-9b-hero-image.sh` est checké-in. `execFileSync` sans shell :
// pas d'injection possible, argv fixé. `process.cwd()` évite les subtilités
// `__dirname` vs `import.meta.url` selon le mode CJS/ESM de Playwright.
const CLEANUP_SCRIPT = resolve(
  process.cwd(),
  '..',
  '..',
  'scripts',
  'e2e-9b-hero-image.sh',
)

function runCleanup(action: 'load' | 'clear'): void {
  execFileSync(CLEANUP_SCRIPT, [action], {
    stdio: 'inherit',
  })
}

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

async function fillArticleForm(
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
    .fill('## Introduction\n\nCorps Markdown de test E2E Phase 9B — contenu crédible mais sans PII.')
  await page.getByLabel('Titre SEO').fill('Titre SEO E2E Phase 9B — image principale de test')
  await page
    .getByLabel('Meta description')
    .fill('Description SEO E2E Phase 9B — assez longue pour dépasser la borne minimale imposée par le domaine.')
  await page.getByLabel('Nom affiché').fill('Devzair')
  await page.getByRole('checkbox', { name: 'Concevoir' }).check()
}

async function uploadMedia(
  page: import('@playwright/test').Page,
  filename: string,
): Promise<void> {
  await page.goto(`${ADMIN_BASE_URL}/admin/media/new`)
  await page.setInputFiles('input#media-file', {
    name: filename,
    mimeType: 'image/png',
    buffer: TINY_PNG,
  })
  await page.getByRole('button', { name: 'Téléverser' }).click()
  await page.waitForURL(`${ADMIN_BASE_URL}/admin/media`)
  await expect(page.locator('.alert-success')).toContainText(filename)
}

async function createDraft(
  page: import('@playwright/test').Page,
  slug: string,
  title: string,
): Promise<string> {
  await page.goto(`${ADMIN_BASE_URL}/admin/articles/new`)
  await fillArticleForm(page, slug, title)
  await page.getByRole('button', { name: 'Créer le brouillon' }).click()
  await page.waitForURL(/\/admin\/articles\/[0-9a-f-]{36}\/edit$/)
  const uuid = page.url().match(/\/admin\/articles\/([0-9a-f-]{36})\/edit$/)?.[1]
  expect(uuid, 'UUID article extractible').toBeTruthy()
  return uuid as string
}

async function attachHeroImage(
  page: import('@playwright/test').Page,
  articleUuid: string,
  filename: string,
  altText: string,
): Promise<string> {
  await page.goto(`${ADMIN_BASE_URL}/admin/articles/${articleUuid}/image`)
  // Le chooser liste tous les médias — on cherche notre upload par filename.
  const label = page.locator('label.hero-image-grid__item', { hasText: filename })
  await expect(label).toBeVisible()
  const mediaUuid = (await label.locator('input[type="radio"]').getAttribute('value')) as string
  expect(mediaUuid, 'UUID média extractible').toMatch(/^[0-9a-f-]{36}$/)
  await label.click()
  await page.getByRole('textbox', { name: 'Texte alternatif' }).fill(altText)
  await page.getByRole('button', { name: 'Enregistrer cette image principale' }).click()
  // PRG vers /edit après enregistrement.
  await page.waitForURL(new RegExp(`/admin/articles/${articleUuid}/edit$`))
  return mediaUuid
}

async function publishArticle(
  page: import('@playwright/test').Page,
  articleUuid: string,
): Promise<void> {
  await page.goto(`${ADMIN_BASE_URL}/admin/articles/${articleUuid}/edit`)
  await page.getByRole('button', { name: 'Publier ce brouillon' }).click()
  await page.waitForURL(`${ADMIN_BASE_URL}/admin/articles`)
}

async function archiveArticle(
  page: import('@playwright/test').Page,
  articleUuid: string,
): Promise<void> {
  await page.goto(`${ADMIN_BASE_URL}/admin/articles/${articleUuid}/edit`)
  await page
    .getByRole('button', { name: 'Archiver et retirer du site public' })
    .click()
  await page.waitForURL(`${ADMIN_BASE_URL}/admin/articles`)
}

async function restoreArticle(
  page: import('@playwright/test').Page,
  articleUuid: string,
): Promise<void> {
  await page.goto(`${ADMIN_BASE_URL}/admin/articles/${articleUuid}/edit`)
  await page.getByRole('button', { name: 'Restaurer comme brouillon' }).click()
  await page.waitForURL(new RegExp(`/admin/articles/${articleUuid}/edit$`))
}

test.describe.serial('Phase 9B — cycle complet image principale', () => {
  // Purge préventive : garantit un état de départ propre même si un run
  // précédent a crashé avant `afterAll`. Le script est idempotent : sans
  // résidu, il ne fait rien de destructeur, seulement des DELETE ciblés
  // `slug LIKE 'e2e-9b-%'` et `original_filename LIKE 'e2e-9b-%'`.
  test.beforeAll(() => {
    runCleanup('load')
  })

  // Purge symétrique : s'exécute même si un test précédent a échoué
  // (Playwright appelle `afterAll` en toute circonstance). L'échec du
  // script fait rougir la suite — pas de `|| true` masquant, pas de
  // `try/catch` qui absorberait un résidu.
  test.afterAll(() => {
    runCleanup('clear')
  })

  test('lifecycle : upload → hero → publish → verif public → archive → 404 → restore → 404', async ({
    page,
  }) => {
    const stamp = Date.now()
    const slug = `${SLUG_PREFIX}lifecycle-${stamp}`
    const filename = `${FILENAME_PREFIX}lifecycle-${stamp}.png`
    const altText = 'Illustration éditoriale de test — cycle complet Phase 9B.'
    const title = `E2E cycle image principale — ${slug}`

    await login(page)

    // --- Upload d'un média via le pipeline admin réel ---------------------
    await uploadMedia(page, filename)

    // --- Création du brouillon --------------------------------------------
    const articleUuid = await createDraft(page, slug, title)

    // --- Chooser hero image accessible (Axe WCAG 2.2 AA) ------------------
    await page.goto(`${ADMIN_BASE_URL}/admin/articles/${articleUuid}/image`)
    await scanAxe(page, '/admin/articles/{id}/image')

    // --- Set hero + alt text ----------------------------------------------
    const mediaUuid = await attachHeroImage(page, articleUuid, filename, altText)

    // --- Prévisualisation admin avec image (Axe WCAG 2.2 AA) --------------
    await page.goto(`${ADMIN_BASE_URL}/admin/articles/${articleUuid}/preview`)
    await scanAxe(page, '/admin/articles/{id}/preview (avec hero image)')

    // --- Publish ----------------------------------------------------------
    await publishArticle(page, articleUuid)

    // --- Vérifs page publique /ressources/{slug} --------------------------
    const publicUrl = `${PUBLIC_BASE_URL}/ressources/${slug}`
    const publicResponse = await page.goto(publicUrl)
    expect(publicResponse?.status(), 'article publié → 200').toBe(200)

    // Le `<img>` du hero doit exister avec les 4 attributs éditoriaux critiques.
    const heroImg = page.locator('.resource-hero__image')
    await expect(heroImg).toBeVisible()
    await expect(heroImg).toHaveAttribute('alt', altText)
    // width/height explicites → CLS = 0.
    await expect(heroImg).toHaveAttribute('width', /^\d+$/)
    await expect(heroImg).toHaveAttribute('height', /^\d+$/)
    // LCP-critical → eager + fetchpriority=high (voir ResourceHero.vue).
    await expect(heroImg).toHaveAttribute('loading', 'eager')
    await expect(heroImg).toHaveAttribute('fetchpriority', 'high')
    // src pointe sur /media/{uuid} (route publique).
    await expect(heroImg).toHaveAttribute('src', new RegExp(`/media/${mediaUuid}$`))

    // Scan Axe sur la page publique avec image.
    await scanAxe(page, `/ressources/${slug} (avec hero image)`)

    // --- Vérifs OG + JSON-LD BlogPosting ----------------------------------
    const detailBody = await (await page.request.get(publicUrl)).text()

    // Open Graph : og:image absolu + og:image:alt.
    expect(detailBody, 'og:image dans le HTML SSR').toMatch(
      /<meta[^>]+property="og:image"[^>]+content="[^"]*\/media\/[0-9a-f-]{36}"/,
    )
    expect(detailBody, 'og:image:alt dans le HTML SSR').toContain('og:image:alt')

    // JSON-LD BlogPosting doit exposer `image` en ImageObject absolu.
    const scripts = [
      ...detailBody.matchAll(
        /<script[^>]+type="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/gi,
      ),
    ].map((m) => JSON.parse(m[1] as string))
    const blogPosting = scripts.find((s) => s['@type'] === 'BlogPosting')
    expect(blogPosting, 'BlogPosting présent').toBeTruthy()
    expect(blogPosting.image['@type']).toBe('ImageObject')
    expect(blogPosting.image.url).toMatch(new RegExp(`/media/${mediaUuid}$`))
    expect(typeof blogPosting.image.width).toBe('number')
    expect(typeof blogPosting.image.height).toBe('number')

    // --- Vérif /media/{id} = 200 + Cache-Control revalidatable ------------
    const mediaUrl = `${PUBLIC_BASE_URL}/api/media/${mediaUuid}`
    const mediaResponse = await page.request.get(mediaUrl)
    expect(mediaResponse.status()).toBe(200)
    const cacheControl = mediaResponse.headers()['cache-control'] ?? ''
    expect(cacheControl).toContain('public')
    expect(cacheControl).toContain('max-age=3600')
    expect(cacheControl).toContain('must-revalidate')
    // Rappel du contrat : `immutable` est proscrit car l'accessibilité
    // publique du média est mutable (dépend d'un article Published).
    expect(cacheControl).not.toContain('immutable')
    // ETag fort (pas `W/`).
    const etag = mediaResponse.headers()['etag'] ?? ''
    expect(etag).toMatch(/^"[a-f0-9]+"$/)

    // --- Archive → l'article n'est plus Published → /media/{id} = 404 -----
    await archiveArticle(page, articleUuid)

    // Cache éventuel côté Nitro : on force une revalidation en variant l'URL
    // (Symfony est direct-hit, Nitro ne cache pas /media/{id}). Un simple
    // GET suffit — pas de bust nécessaire.
    const mediaAfterArchive = await page.request.get(mediaUrl)
    expect(
      mediaAfterArchive.status(),
      'archive de l\'article référençant → /media/{id} bascule en 404',
    ).toBe(404)

    // --- Restore → l'article redevient Draft (jamais Published) → toujours 404 -
    // C'est exactement pour ça que `immutable` est interdit : sans
    // revalidation, un client garderait le 200 d'origine et servirait un
    // média qui n'est plus autorisé publiquement.
    await restoreArticle(page, articleUuid)
    const mediaAfterRestore = await page.request.get(mediaUrl)
    expect(
      mediaAfterRestore.status(),
      'restore ramène en Draft (pas Published) → /media/{id} reste 404',
    ).toBe(404)
  })

  test('médias partagés : archiver l\'article A garde /media/{id} à 200 tant que B est publié', async ({
    page,
  }) => {
    const stamp = Date.now()
    const slugA = `${SLUG_PREFIX}shared-a-${stamp}`
    const slugB = `${SLUG_PREFIX}shared-b-${stamp}`
    const filename = `${FILENAME_PREFIX}shared-${stamp}.png`
    const altText = 'Illustration éditoriale — cas des médias partagés (Phase 9B).'

    await login(page)

    // Upload d'un média unique.
    await uploadMedia(page, filename)

    // Deux articles distincts qui pointent sur ce même média, tous deux publiés.
    const uuidA = await createDraft(page, slugA, `E2E shared A — ${slugA}`)
    const mediaUuid = await attachHeroImage(page, uuidA, filename, altText)
    await publishArticle(page, uuidA)

    const uuidB = await createDraft(page, slugB, `E2E shared B — ${slugB}`)
    // Le chooser retrouve le même média (par nom de fichier) et renvoie le
    // même UUID que pour A — invariant du chooser SSR.
    const mediaUuidB = await attachHeroImage(page, uuidB, filename, altText)
    expect(mediaUuidB).toBe(mediaUuid)
    await publishArticle(page, uuidB)

    // À ce stade, le média est référencé par A ET B, tous deux publiés.
    const mediaUrl = `${PUBLIC_BASE_URL}/api/media/${mediaUuid}`
    const beforeArchive = await page.request.get(mediaUrl)
    expect(beforeArchive.status()).toBe(200)

    // Archiver SEULEMENT A.
    await archiveArticle(page, uuidA)

    // B est toujours publié → /media/{id} doit rester 200.
    const afterArchiveA = await page.request.get(mediaUrl)
    expect(
      afterArchiveA.status(),
      'B reste publié → média toujours accessible publiquement',
    ).toBe(200)

    // Nettoyage post-assertion : archiver B pour ne pas contaminer les
    // specs qui suivent alphabétiquement (e.g. resources.spec.ts qui
    // vérifie l'ordre des 6 items les plus récents sur /ressources).
    // La cleanup script `e2e-9b-hero-image.sh` tourne AVANT la suite,
    // pas ENTRE les fichiers de test — c'est au spec producteur de
    // rendre son état invisible côté public dès que l'assertion est
    // faite. L'archivage suffit : l'article reste en base mais
    // disparaît de /ressources et de /media/{id}.
    await archiveArticle(page, uuidB)
  })
})
