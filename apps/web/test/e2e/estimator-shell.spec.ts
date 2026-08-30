import AxeBuilder from '@axe-core/playwright'
import { expect, test } from '@playwright/test'

// E2E — Configurateur de projet /estimer-mon-projet (EST-2)
//
// Périmètre :
//   - rendu SSR : titre H1 présent avant hydratation ;
//   - progression : rôle progressbar avec aria-valuenow/valuemax corrects ;
//   - étape 1 : fieldset + legend + 6 radios natifs ;
//   - sélection : Continuer activé seulement après sélection ;
//   - maxStep=1 : Continuer cliquable mais reste étape 1 (pas de step 2) ;
//   - "Je ne sais pas encore" : option sélectionnable, Continuer activé ;
//   - clavier : tab / espace pour sélectionner, focus visible ;
//   - Axe WCAG 2.2 AA sur la page complète.
//
// Aucun appel API, aucun prix — tout reste en mémoire.

const ESTIMATOR_PATH = '/estimer-mon-projet'

test.beforeAll(async ({ browser }) => {
  const page = await browser.newPage()
  await page.goto(ESTIMATOR_PATH, { waitUntil: 'domcontentloaded' })
  await page.close()
})

test.describe('EstimatorShell E2E', () => {
  test('SSR : le H1 est présent avant hydratation', async ({ page }) => {
    const response = await page.goto(ESTIMATOR_PATH, { waitUntil: 'commit' })
    expect(response?.status()).toBe(200)
    const h1 = await page.locator('h1').first().textContent()
    expect(h1).toContain('Estimez votre projet digital')
  })

  test('progressbar avec aria-valuemin=1 et aria-valuemax=7', async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    const bar = page.getByRole('progressbar')
    await expect(bar).toBeVisible()
    await expect(bar).toHaveAttribute('aria-valuenow', '1')
    await expect(bar).toHaveAttribute('aria-valuemin', '1')
    await expect(bar).toHaveAttribute('aria-valuemax', '7')
  })

  test('fieldset avec legend visible en étape 1', async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    await expect(page.locator('fieldset legend')).toBeVisible()
    const legend = await page.locator('fieldset legend').textContent()
    expect(legend).toContain('Quel type de projet')
  })

  test('6 options radio disponibles', async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    const radios = page.locator('input[type="radio"][name="project-type"]')
    await expect(radios).toHaveCount(6)
  })

  test('Continuer est désactivé sans sélection', async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    const continuer = page.getByRole('button', { name: /Continuer/i })
    await expect(continuer).toBeDisabled()
  })

  test('Continuer est activé après sélection d\'un type', async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    await page.locator('input[value="vitrinesite"]').check()
    const continuer = page.getByRole('button', { name: /Continuer/i })
    await expect(continuer).toBeEnabled()
  })

  test('EST-2 maxStep=1 : Continuer cliqué reste en étape 1', async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    await page.locator('input[value="ecommerce"]').check()
    await page.getByRole('button', { name: /Continuer/i }).click()
    const bar = page.getByRole('progressbar')
    await expect(bar).toHaveAttribute('aria-valuenow', '1')
  })

  test('«\u00a0Je ne sais pas encore\u00a0» est sélectionnable et active Continuer', async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    await page.locator('input[value="unknown"]').check()
    const continuer = page.getByRole('button', { name: /Continuer/i })
    await expect(continuer).toBeEnabled()
  })

  test('Retour absent en étape 1', async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    const retour = page.getByRole('button', { name: /Retour/i })
    await expect(retour).not.toBeVisible()
  })

  test('navigation clavier : tab + espace sélectionne une option', async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    await page.keyboard.press('Tab')
    const radios = page.locator('input[type="radio"]')
    await radios.first().focus()
    await page.keyboard.press('Space')
    const checked = await radios.first().isChecked()
    expect(checked).toBe(true)
  })

  test('Axe WCAG 2.2 AA : aucune violation critique', async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    const results = await new AxeBuilder({ page })
      .withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa'])
      .analyze()
    expect(results.violations).toHaveLength(0)
  })
})
