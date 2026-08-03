import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'
import { openMobileNavigation } from './support/mobile-nav'

// Contrôles E2E de la section finale « Parlons de votre projet » sur la home.
//
// Depuis la Phase 7C, le formulaire de contact vit sur la page dédiée
// `/contact` : la section finale n'est plus qu'un panneau CTA compact
// (eyebrow, H2, paragraphe, bouton). Le bouton et tous les CTA de layout
// (hero, header desktop, menu mobile, footer) pointent désormais sur la
// route Nuxt `/contact` — plus aucun fragment `#contact` n'est visé par
// la navigation principale.
//
// L'ancre `id="contact"` reste posée sur `<section>` pour ne pas casser
// d'anciens liens (`/#contact` en cache moteur, signets, partages). Elle
// n'est plus la cible de la navigation principale, donc elle n'est pas
// testée ici comme destination.
//
// Cette suite couvre :
//   - SSR : ancre, eyebrow, H2, paragraphe verbatim ;
//   - position : la section reste la dernière fille directe de `.home-page` ;
//   - absence de tout formulaire embarqué (il vit sur `/contact`) ;
//   - un unique CTA visible dans la section, pointant sur `/contact` ;
//   - CTA hero primaire, header desktop, footer et menu mobile → `/contact` ;
//   - responsive (320/390/768/1440) sans débordement horizontal ;
//   - Axe WCAG 2.2 AA sans violation `serious` / `critical` ;
//   - `prefers-reduced-motion` : contenu visible, aucune animation résiduelle.
//
// La couverture fonctionnelle du formulaire (soumission, erreurs API,
// honeypot, rate limit) est portée par `contact-form.spec.ts` qui vise
// directement `/contact`.

const CONTACT_SECTION = '#contact'
const CTA_TITLE = 'Construisons une présence digitale à la hauteur de votre entreprise.'
const CTA_HREF = '/contact'

test.describe('/ (home) — CTA final #contact', () => {
  test('renders the #contact section with eyebrow, H2 and paragraph verbatim (SSR)', async ({
    request,
  }) => {
    const response = await request.get('/')
    expect(response.status()).toBe(200)
    const body = await response.text()
    // Ancre présente dans le HTML servi (pas seulement après hydratation).
    expect(body).toMatch(/id="contact"/)
    expect(body).toContain(CTA_TITLE)
    expect(body).toContain('Parlons de votre projet')
    expect(body).toContain(
      'Un premier échange nous permettra de comprendre votre besoin, de clarifier les priorités et de définir une direction adaptée à votre activité.',
    )
  })

  test('the #contact section is the last direct child of .home-page', async ({
    page,
  }) => {
    await page.goto('/')
    const lastSectionId = await page.evaluate(() => {
      const sections = document.querySelectorAll('main .home-page > section')
      const last = sections[sections.length - 1] as HTMLElement | undefined
      return last?.id ?? null
    })
    expect(lastSectionId).toBe('contact')
  })

  test('exposes exactly one H2 with the exact editorial title', async ({ page }) => {
    await page.goto('/')
    const contact = page.locator(CONTACT_SECTION)
    const h2s = await contact.locator('h2').allInnerTexts()
    expect(h2s).toEqual([CTA_TITLE])
  })

  test('does not render any fake contact link (no mailto, no tel, no href="#")', async ({
    page,
  }) => {
    await page.goto('/')
    const contact = page.locator(CONTACT_SECTION)
    // Le contact passe désormais par le bouton vers `/contact` — aucun lien
    // direct mail/tél ne doit subsister dans la section.
    await expect(contact.locator('a[href^="mailto:"]')).toHaveCount(0)
    await expect(contact.locator('a[href^="tel:"]')).toHaveCount(0)
    await expect(contact.locator('a[href="#"]')).toHaveCount(0)
    await expect(contact.locator('a[href=""]')).toHaveCount(0)
  })

  test('does not leak internal implementation details in visible text', async ({
    page,
  }) => {
    await page.goto('/')
    const text = (await page.locator(CONTACT_SECTION).innerText()).toLowerCase()
    expect(text).not.toMatch(/phase\s*6/)
    expect(text).not.toMatch(/todo/)
    expect(text).not.toMatch(/lorem ipsum/)
  })

  test('no <form> is embedded on the home anymore (form lives on /contact)', async ({
    page,
  }) => {
    await page.goto('/')
    // Contrat Phase 7C : plus aucun formulaire sur la home. Le formulaire
    // vit exclusivement sur `/contact` (voir `contact-form.spec.ts`).
    await expect(page.locator('form.contact-form')).toHaveCount(0)
  })

  test('renders a single CTA in the section pointing to /contact', async ({
    page,
  }) => {
    await page.goto('/')
    const contact = page.locator(CONTACT_SECTION)
    const buttons = contact.locator('.home-cta__actions a.base-button')
    await expect(buttons).toHaveCount(1)
    await expect(buttons.first()).toHaveAttribute('href', CTA_HREF)
    await expect(buttons.first()).toContainText('Parler de votre projet')
  })

  test('hero primary CTA navigates to /contact', async ({ page }) => {
    await page.goto('/')
    const heroCta = page
      .locator('.home-hero__ctas a', { hasText: 'Parler de votre projet' })
      .first()
    await expect(heroCta).toBeVisible()
    await expect(heroCta).toHaveAttribute('href', CTA_HREF)
    await heroCta.click()
    await expect(page).toHaveURL(/\/contact$/)
    await expect(page.locator('h1')).toContainText('présence digitale')
  })

  test('desktop header CTA navigates to /contact', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 })
    await page.goto('/')
    const headerCta = page.locator('header.site-header .site-header__cta')
    await expect(headerCta).toBeVisible()
    // Le lien header porte NuxtLink[to="/contact"] → rendu <a href="/contact">.
    await expect(headerCta).toHaveAttribute('href', CTA_HREF)
    await headerCta.click()
    await expect(page).toHaveURL(/\/contact$/)
    await expect(page.locator('h1')).toContainText('présence digitale')
  })

  test('footer link « Parler de votre projet » points to /contact', async ({
    page,
  }) => {
    await page.goto('/')
    const footerLink = page
      .locator('footer.site-footer a', { hasText: 'Parler de votre projet' })
      .first()
    await expect(footerLink).toHaveAttribute('href', CTA_HREF)
  })

  test('mobile menu opens, activates the CTA, closes, and lands on /contact', async ({
    page,
  }) => {
    await page.setViewportSize({ width: 390, height: 844 })

    // Capture toute erreur console liée à la navigation (hydration mismatch,
    // routeur, teleport). Le scénario doit se dérouler sans en produire.
    const consoleErrors: string[] = []
    page.on('console', (msg) => {
      if (msg.type() === 'error') consoleErrors.push(msg.text())
    })

    await page.goto('/', { waitUntil: 'load' })

    // Ouverture retentable : couvre la course éventuelle entre l'attachement
    // du handler Vue et le premier clic (cf. support/mobile-nav.ts).
    const dialog = await openMobileNavigation(page)
    const menuButton = page.locator('button[aria-controls="mobile-navigation"]')
    await expect(menuButton).toHaveAttribute('aria-expanded', 'true')

    // Le CTA du menu mobile est un lien vers /contact, positionné en pied de dialog.
    const dialogCta = dialog
      .locator('a', { hasText: 'Parler de votre projet' })
      .first()
    await expect(dialogCta).toBeVisible()
    await expect(dialogCta).toHaveAttribute('href', CTA_HREF)
    await dialogCta.click()

    // Le menu se ferme, l'URL cible /contact.
    await expect(dialog).toHaveCount(0)
    await expect(menuButton).toHaveAttribute('aria-expanded', 'false')
    await expect(page).toHaveURL(/\/contact$/)
    await expect(page.locator('h1')).toContainText('présence digitale')
    // Scroll restauré (pas de scroll-lock résiduel).
    await expect(page.locator('html')).not.toHaveClass(/is-scroll-locked/)

    // Aucune erreur console durant l'ouverture, la navigation et la fermeture.
    const navigationErrors = consoleErrors.filter((line) =>
      /navigat|hydrat|router|teleport|vue|nuxt/i.test(line),
    )
    expect(navigationErrors, `unexpected console errors: ${navigationErrors.join('\n')}`).toEqual([])
  })

  test('no horizontal overflow at 320 / 390 / 768 / 1440', async ({ page }) => {
    for (const width of [320, 390, 768, 1440]) {
      await page.setViewportSize({ width, height: 900 })
      await page.goto('/')
      const overflow = await page.evaluate(() => {
        const { scrollWidth, clientWidth } = document.documentElement
        return scrollWidth - clientWidth
      })
      expect(overflow, `overflow at ${width}px`).toBeLessThanOrEqual(1)
    }
  })

  test('Axe — no serious or critical violation on the #contact section', async ({
    page,
  }) => {
    await page.goto('/')
    const results = await new AxeBuilder({ page })
      .include('#contact')
      .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'])
      .analyze()
    const blocking = results.violations.filter(
      (v) => v.impact === 'critical' || v.impact === 'serious',
    )
    if (blocking.length > 0) {
      console.log(
        `Axe blocking violations:\n${blocking
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

  test('prefers-reduced-motion : content visible, no residual animation', async ({
    page,
  }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' })
    await page.goto('/')
    const contact = page.locator(CONTACT_SECTION)
    await expect(contact.locator('h2')).toBeVisible()
    const opacity = await contact
      .locator('h2')
      .evaluate((el) => getComputedStyle(el).opacity)
    expect(Number.parseFloat(opacity)).toBe(1)
  })
})
