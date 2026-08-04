import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'

// Contrôles E2E du formulaire de contact.
//
// Depuis le split de Phase 7C, le formulaire n'est plus embarqué dans la
// section CTA de la home mais servi sur la page dédiée `/contact` (routage
// SSR, pré-rendu, canonique propre). Tous les tests visent donc directement
// `/contact` — la home ne contient plus qu'un bouton pointant vers cette page.
//
// Périmètre :
//   - présence SSR : le <form> et ses champs essentiels rendent côté serveur ;
//   - happy path : token dev-noop + soumission → 200 accepted → bandeau succès ;
//   - erreurs 400 : messages par champ, aria-invalid, focus sur premier champ ;
//   - honeypot rempli : la réponse (silencieuse côté API) reste indistinguable
//     du succès (le composable renvoie « accepted » — c'est le serveur qui
//     décide de purger la demande) ;
//   - rate limit 429 : bandeau global avec Retry-After ;
//   - passe Axe WCAG 2.2 AA restreinte au form ;
//   - prefers-reduced-motion : le champ reste focusable, pas d'animation résiduelle.
//
// Les appels API sont interceptés via `page.route` : la suite ne dépend pas du
// backend Symfony et reste stable en CI même sans stack complète.

const CONTACT_PATH = '/contact'
const FORM = 'form.contact-form'
const API_URL = '**/api/contact'

// Contenu minimum valide côté client (miroir du DTO Symfony).
const VALID_MESSAGE =
  'Nous souhaitons refondre notre site vitrine pour clarifier notre offre commerciale.'

async function waitForFormHydration(
  page: import('@playwright/test').Page,
): Promise<void> {
  // Synchronisation d'hydratation via l'attribut `data-hydrated` posé par
  // `ContactForm.vue` dans son `onMounted` (DEV-045). C'est le SEUL signal
  // qui garantit que Vue a fini d'attacher `@submit.prevent` et les
  // `v-model` sur le form : le dev-notice de TurnstileWidget ne suffit pas
  // (les enfants montent avant le parent en Vue 3). Sans cette attente, le
  // clic sur `button[type="submit"]` déclenche une soumission native
  // (POST → même URL) qui recharge la page et masque toute erreur de
  // validation ou d'appel API mocké. Timeout 30 s : en local le web
  // tourne en `nuxt dev` (JIT Vite), qui peut dépasser 15 s au premier
  // hit froid sur `/contact`. Un warm-up (`test.beforeAll` ci-dessous)
  // couvre le cold-start ; la marge défensive gère les runs répétés
  // (repeat×N charge N fois la page dans le même contexte).
  await expect(page.locator('form.contact-form[data-hydrated="true"]')).toBeVisible({
    timeout: 30_000,
  })
}

async function fillValidForm(page: import('@playwright/test').Page): Promise<void> {
  await waitForFormHydration(page)
  await page.locator('input[name="name"]').fill('Alice Dupont')
  await page.locator('input[name="email"]').fill('alice@example.com')
  await page.locator('input[name="company"]').fill('Devzair Studio')
  await page.locator('input[name="telephone"]').fill('+33 6 12 34 56 78')
  // Les radios `projectType` et la checkbox `consent` sont visuellement
  // masquées (position:absolute, opacity:0, pointer-events:none) : on
  // clique le <label> parent — pattern natif où le clic label déclenche
  // l'activation de l'input associé. `.check()` sur l'input direct
  // échoue au check d'actionabilité Playwright (élément non
  // interactif). Voir DEV-045.
  await page
    .locator('label.contact-form__project-option')
    .filter({ hasText: "Refonte d'un site existant" })
    .click()
  await expect(page.locator('input[name="projectType"][value="refonte"]')).toBeChecked()
  await page.locator('textarea[name="message"]').fill(VALID_MESSAGE)
  await page.locator('label.contact-form__consent-label').click()
  await expect(page.locator('input[name="consent"]')).toBeChecked()
}

test.describe('/contact — formulaire de contact', () => {
  // Warm-up : en dev (`nuxt dev`), le premier hit sur `/contact` déclenche la
  // compilation JIT Vite (bundle client + SSR), qui peut dépasser les 30 s.
  // Toucher la page une fois avant les tests garantit que les hits suivants
  // servent la version compilée ; aucun impact sur un build (`nuxt preview`)
  // où c'est un no-op très rapide.
  test.beforeAll(async ({ request }) => {
    await request.get(CONTACT_PATH, { timeout: 60_000 })
  })

  test('renders the <form> and its main fields (SSR)', async ({ request }) => {
    const response = await request.get(CONTACT_PATH)
    expect(response.status()).toBe(200)
    const body = await response.text()
    expect(body).toContain('class="contact-form')
    expect(body).toMatch(/name="name"/)
    expect(body).toMatch(/name="email"/)
    expect(body).toMatch(/name="message"/)
    expect(body).toMatch(/name="consent"/)
    // Honeypot présent dès le SSR (hors flux, tabindex=-1).
    expect(body).toMatch(/name="website"[^>]*tabindex="-1"/)
  })

  test('happy path : dev-noop token + 200 accepted → banner succès', async ({ page }) => {
    // Intercepte l'appel POST /api/contact et renvoie un 200 accepted.
    await page.route(API_URL, async (route) => {
      const request = route.request()
      expect(request.method()).toBe('POST')
      const body = request.postDataJSON() as Record<string, unknown>
      expect(body.email).toBe('alice@example.com')
      expect(body.turnstileToken).toBe('dev-noop')
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ status: 'accepted', request_id: 'req-e2e-1' }),
      })
    })

    await page.goto(CONTACT_PATH)
    // Attend que le TurnstileWidget émette son token dev-noop et libère le bouton.
    const submit = page.locator(`${FORM} button[type="submit"]`)
    await expect(submit).toBeEnabled()

    await fillValidForm(page)
    await submit.click()

    const successBanner = page.locator(`${FORM} .contact-form__status [role="status"]`)
    await expect(successBanner).toBeVisible()
    await expect(successBanner).toContainText('Message envoyé')
    await expect(successBanner).toContainText('req-e2e-1')

    // Les champs sont vidés après succès.
    await expect(page.locator('input[name="email"]')).toHaveValue('')
  })

  test('erreur 400 validation_failed → message par champ + aria-invalid', async ({ page }) => {
    await page.route(API_URL, async (route) => {
      await route.fulfill({
        status: 400,
        contentType: 'application/json',
        body: JSON.stringify({
          status: 'error',
          code: 'validation_failed',
          request_id: 'req-e2e-2',
          errors: {
            email: ["Cette adresse email n'est pas reconnue par notre système."],
          },
        }),
      })
    })

    await page.goto(CONTACT_PATH)
    await expect(page.locator(`${FORM} button[type="submit"]`)).toBeEnabled()
    await fillValidForm(page)
    await page.locator(`${FORM} button[type="submit"]`).click()

    const emailInput = page.locator('input[name="email"]')
    await expect(emailInput).toHaveAttribute('aria-invalid', 'true')
    await expect(page.locator(`${FORM}`)).toContainText(
      "Cette adresse email n'est pas reconnue",
    )
  })

  test('honeypot rempli + réponse 200 → succès (contrat inchangé côté UI)', async ({
    page,
  }) => {
    // La stratégie serveur pour le honeypot est un 202 silencieux : côté UI, on
    // ne doit rien voir de différent d'un succès classique. Ce test verrouille
    // ce contrat : même si le honeypot est rempli, le bandeau succès s'affiche.
    await page.route(API_URL, async (route) => {
      const body = route.request().postDataJSON() as Record<string, unknown>
      // Vérifie que le honeypot est bien transmis au serveur.
      expect(body.website).toBe('http://spam.example')
      await route.fulfill({
        status: 202,
        contentType: 'application/json',
        body: JSON.stringify({ status: 'accepted', request_id: 'req-e2e-honey' }),
      })
    })

    await page.goto(CONTACT_PATH)
    await expect(page.locator(`${FORM} button[type="submit"]`)).toBeEnabled()
    await fillValidForm(page)
    // On force la valeur du honeypot (bots réels : autofill navigateur).
    await page
      .locator('input[name="website"]')
      .evaluate((el, v) => {
        ;(el as HTMLInputElement).value = v
        el.dispatchEvent(new Event('input', { bubbles: true }))
      }, 'http://spam.example')

    await page.locator(`${FORM} button[type="submit"]`).click()
    await expect(
      page.locator(`${FORM} .contact-form__status [role="status"]`),
    ).toBeVisible()
  })

  test('503 temporary_error → bandeau global, valeurs saisies préservées', async ({
    page,
  }) => {
    // Contrat ADR-008 §7 : sur échec SMTP, l'API renvoie 503 temporary_error.
    // Le front doit afficher un bandeau explicite ET conserver toutes les
    // valeurs pour permettre une nouvelle tentative manuelle.
    await page.route(API_URL, async (route) => {
      await route.fulfill({
        status: 503,
        contentType: 'application/json',
        body: JSON.stringify({
          status: 'error',
          code: 'temporary_error',
          request_id: 'req-e2e-503',
        }),
      })
    })

    await page.goto(CONTACT_PATH)
    await expect(page.locator(`${FORM} button[type="submit"]`)).toBeEnabled()
    await fillValidForm(page)
    await page.locator(`${FORM} button[type="submit"]`).click()

    const errorBanner = page.locator(`${FORM} .contact-form__status [role="alert"]`)
    await expect(errorBanner).toBeVisible()
    await expect(errorBanner).toContainText('Service momentanément indisponible')
    await expect(errorBanner).toContainText(
      "Le service est momentanément indisponible. Votre message n'a pas été envoyé. Merci de réessayer plus tard.",
    )
    await expect(errorBanner).toContainText('req-e2e-503')

    // Les valeurs ne sont *pas* effacées — l'utilisateur peut ré-essayer.
    await expect(page.locator('input[name="name"]')).toHaveValue('Alice Dupont')
    await expect(page.locator('input[name="email"]')).toHaveValue('alice@example.com')
    await expect(page.locator('textarea[name="message"]')).toHaveValue(VALID_MESSAGE)
    await expect(page.locator('input[name="consent"]')).toBeChecked()
  })

  test('Turnstile désactivé (défaut) : aucun script Cloudflare chargé', async ({
    page,
  }) => {
    // ADR-008 §3 : quand `NUXT_PUBLIC_TURNSTILE_ENABLED=false` (défaut sûr),
    // aucune requête vers challenges.cloudflare.com ne doit être émise, et
    // aucun <script src="…cloudflare…"> ne doit être injecté dans le DOM.
    // Toute régression de ce contrat exposerait les visiteurs à un tracker
    // tiers sans consentement.
    const cloudflareRequests: string[] = []
    page.on('request', (request) => {
      if (request.url().includes('challenges.cloudflare.com')) {
        cloudflareRequests.push(request.url())
      }
    })

    await page.goto(CONTACT_PATH)
    await expect(page.locator(`${FORM} button[type="submit"]`)).toBeEnabled()

    // Un widget rendu → le bouton devrait être enabled car token dev-noop émis.
    const cfScripts = await page.locator('script[src*="challenges.cloudflare.com"]').count()
    expect(cfScripts).toBe(0)
    expect(cloudflareRequests).toEqual([])

    // Notice dev visible côté DOM : témoigne du mode noop. Timeout aligné
    // sur `waitForFormHydration` (30 s) — le premier hit après warm-up
    // reste parfois sous pression en repeat×N (bundle + fonts + hydratation).
    await expect(
      page.locator(`${FORM} .turnstile-widget__dev-notice`),
    ).toBeVisible({ timeout: 30_000 })
  })

  test('rate limit 429 → bandeau global avec Retry-After affiché', async ({ page }) => {
    await page.route(API_URL, async (route) => {
      await route.fulfill({
        status: 429,
        contentType: 'application/json',
        headers: { 'Retry-After': '42' },
        body: JSON.stringify({
          status: 'error',
          code: 'rate_limited',
          request_id: 'req-e2e-rl',
        }),
      })
    })

    await page.goto(CONTACT_PATH)
    await expect(page.locator(`${FORM} button[type="submit"]`)).toBeEnabled()
    await fillValidForm(page)
    await page.locator(`${FORM} button[type="submit"]`).click()

    const errorBanner = page.locator(`${FORM} .contact-form__status [role="alert"]`)
    await expect(errorBanner).toBeVisible()
    await expect(errorBanner).toContainText('Trop de tentatives')
    await expect(errorBanner).toContainText('42 secondes')
    await expect(errorBanner).toContainText('req-e2e-rl')
  })

  test('client-side validation blocks empty submit and surfaces field errors', async ({
    page,
  }) => {
    let apiCalled = false
    await page.route(API_URL, async (route) => {
      apiCalled = true
      await route.fulfill({ status: 200, body: '{}' })
    })

    await page.goto(CONTACT_PATH)
    await expect(page.locator(`${FORM} button[type="submit"]`)).toBeEnabled()
    // Le bouton est enabled dès le SSR — il faut attendre l'hydratation
    // client pour que `@submit.prevent="onSubmit"` soit effectivement
    // attaché sur le <form>, sinon le clic déclenche une soumission
    // native (POST → même URL) qui recharge la page et masque toute
    // erreur de validation. Voir waitForFormHydration pour le détail.
    await waitForFormHydration(page)
    // On coche uniquement le consentement (via le label — cf. commentaire
    // dans fillValidForm) ; les autres champs restent vides → validation
    // client refuse l'envoi et signale les erreurs par champ.
    await page.locator('label.contact-form__consent-label').click()
    await expect(page.locator('input[name="consent"]')).toBeChecked()
    await page.locator(`${FORM} button[type="submit"]`).click()

    expect(apiCalled).toBe(false)
    await expect(page.locator('input[name="name"]')).toHaveAttribute(
      'aria-invalid',
      'true',
    )
  })

  test('keeps the expected keyboard order across the four short fields', async ({
    page,
  }) => {
    await page.goto(CONTACT_PATH)
    const names = ['name', 'email', 'company', 'telephone'] as const
    await page.locator(`input[name="${names[0]}"]`).focus()

    for (let index = 0; index < names.length; index += 1) {
      await expect(page.locator(`input[name="${names[index]}"]`)).toBeFocused()
      if (index < names.length - 1) await page.keyboard.press('Tab')
    }
  })

  test('uses a predictable responsive grid from 320 to 1920 px', async ({ page }) => {
    const viewports = [
      { width: 320, height: 800 },
      { width: 390, height: 844 },
      { width: 768, height: 1024 },
      { width: 1024, height: 768 },
      { width: 1440, height: 900 },
      { width: 1920, height: 1080 },
    ]

    for (const viewport of viewports) {
      await page.setViewportSize(viewport)
      await page.goto(CONTACT_PATH)

      const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
      )
      expect(overflow, `horizontal overflow at ${viewport.width}px`).toBeLessThanOrEqual(1)

      const shortFields = ['name', 'email', 'company', 'telephone'] as const
      const boxes = await Promise.all(
        shortFields.map((name) => page.locator(`input[name="${name}"]`).boundingBox()),
      )
      expect(boxes.every(Boolean)).toBe(true)

      if (viewport.width < 720) {
        for (let index = 1; index < boxes.length; index += 1) {
          expect(Math.abs(boxes[index]!.x - boxes[0]!.x)).toBeLessThanOrEqual(1)
          expect(boxes[index]!.y).toBeGreaterThan(boxes[index - 1]!.y)
        }
      } else {
        expect(Math.abs(boxes[0]!.y - boxes[1]!.y)).toBeLessThanOrEqual(1)
        expect(Math.abs(boxes[2]!.y - boxes[3]!.y)).toBeLessThanOrEqual(1)
        expect(boxes[1]!.x).toBeGreaterThan(boxes[0]!.x + boxes[0]!.width)
        expect(boxes[3]!.x).toBeGreaterThan(boxes[2]!.x + boxes[2]!.width)
      }

      const project = await page.locator('.contact-form__project').boundingBox()
      const message = await page.locator('textarea[name="message"]').boundingBox()
      expect(project).not.toBeNull()
      expect(message).not.toBeNull()
      expect(Math.abs(project!.x - boxes[0]!.x)).toBeLessThanOrEqual(1)
      expect(Math.abs(message!.x - boxes[0]!.x)).toBeLessThanOrEqual(1)

      // Composition verticale (hero → timeline → form-card) : chaque
      // bloc doit être strictement en-dessous du précédent, et le form
      // card reste centré dans la largeur du container (max 780 px).
      const hero = await page.locator('.contact-page__hero').boundingBox()
      const timeline = await page.locator('.contact-page__timeline').boundingBox()
      const formCard = await page.locator('.contact-page__form-card').boundingBox()
      expect(hero).not.toBeNull()
      expect(timeline).not.toBeNull()
      expect(formCard).not.toBeNull()
      expect(timeline!.y).toBeGreaterThan(hero!.y + hero!.height - 1)
      expect(formCard!.y).toBeGreaterThan(timeline!.y + timeline!.height - 1)
    }
  })

  test('Axe — aucune violation serious/critical sur le form', async ({ page }) => {
    await page.goto(CONTACT_PATH)
    await expect(page.locator(`${FORM} button[type="submit"]`)).toBeEnabled()
    const results = await new AxeBuilder({ page })
      .include(FORM)
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'])
      .analyze()
    const blocking = results.violations.filter(
      (v) => v.impact === 'critical' || v.impact === 'serious',
    )
    if (blocking.length > 0) {
      console.log(
        `Axe blocking violations on contact form:\n${blocking
          .map(
            (v) =>
              `- [${v.impact}] ${v.id}: ${v.help}\n    ${v.nodes
                .map((n) => n.target.join(' '))
                .join('\n    ')}`,
          )
          .join('\n')}`,
      )
    }
    expect(blocking).toEqual([])
  })

  test('prefers-reduced-motion : champs focusables, pas d\'animation', async ({
    page,
  }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' })
    await page.goto(CONTACT_PATH)
    const name = page.locator('input[name="name"]')
    await name.focus()
    await expect(name).toBeFocused()
  })
})
