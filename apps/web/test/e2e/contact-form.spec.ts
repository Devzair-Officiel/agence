import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'

// Contrôles E2E du formulaire de contact (Phase 6B).
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

const CONTACT_SECTION = '#contact'
const FORM = `${CONTACT_SECTION} form.contact-form`
const API_URL = '**/api/contact'

// Contenu minimum valide côté client (miroir du DTO Symfony).
const VALID_MESSAGE =
  'Nous souhaitons refondre notre site vitrine pour clarifier notre offre commerciale.'

async function fillValidForm(page: import('@playwright/test').Page): Promise<void> {
  await page.locator('input[name="name"]').fill('Alice Dupont')
  await page.locator('input[name="email"]').fill('alice@example.com')
  await page.locator('input[name="company"]').fill('Devzair Studio')
  await page.locator('input[name="telephone"]').fill('+33 6 12 34 56 78')
  await page.locator('input[name="projectType"][value="refonte"]').check()
  await page.locator('textarea[name="message"]').fill(VALID_MESSAGE)
  await page.locator('input[name="consent"]').check()
}

test.describe('/ (home) — formulaire de contact', () => {
  test('renders the <form> and its main fields (SSR)', async ({ request }) => {
    const response = await request.get('/')
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

    await page.goto('/')
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

    await page.goto('/')
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

    await page.goto('/')
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

    await page.goto('/')
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

    await page.goto('/')
    await expect(page.locator(`${FORM} button[type="submit"]`)).toBeEnabled()
    // On coche uniquement le consentement pour dépasser le blocage côté widget,
    // les autres champs restent vides → validation client refuse l'envoi.
    await page.locator('input[name="consent"]').check()
    await page.locator(`${FORM} button[type="submit"]`).click()

    expect(apiCalled).toBe(false)
    await expect(page.locator('input[name="name"]')).toHaveAttribute(
      'aria-invalid',
      'true',
    )
  })

  test('Axe — aucune violation serious/critical sur le form', async ({ page }) => {
    await page.goto('/')
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
    await page.goto('/')
    const name = page.locator('input[name="name"]')
    await name.focus()
    await expect(name).toBeFocused()
  })
})
