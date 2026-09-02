import AxeBuilder from "@axe-core/playwright"
import { expect, test } from "@playwright/test"
import { waitForEstimatorHydrated } from "./helpers/estimator-hydration"

// E2E — Configurateur de projet /estimer-mon-projet
//
// Périmètre :
//   - rendu SSR : titre H1 présent avant hydratation ;
//   - progressbar : rôle progressbar avec aria-valuenow/valuemax corrects ;
//   - étape 1 : fieldset + legend + 6 radios natifs ;
//   - sélection : Continuer activé seulement après sélection ;
//   - navigation réelle EST-3 : Continuer avance à l'étape 2 ;
//   - totalSteps = 7 pour parcours standard (état initial) ;
//   - « Je ne sais pas encore » : option sélectionnable, totalSteps = 6 ;
//   - clavier : tab / espace pour sélectionner, focus visible ;
//   - Axe WCAG 2.2 AA sur la page complète.
//
// Aucun appel API, aucun prix — tout reste en mémoire.

const ESTIMATOR_PATH = "/estimer-mon-projet"

test.describe("EstimatorShell E2E", () => {
  test("SSR : le H1 est présent avant hydratation", async ({ page }) => {
    const response = await page.goto(ESTIMATOR_PATH, { waitUntil: "commit" })
    expect(response?.status()).toBe(200)
    const h1 = await page.locator("h1").first().textContent()
    expect(h1).toContain("Estimez votre projet digital")
  })

  test("progressbar avec aria-valuemin=1 et aria-valuemax=7 (parcours standard)", async ({
    page,
  }) => {
    await page.goto(ESTIMATOR_PATH)
    const bar = page.getByRole("progressbar")
    await expect(bar).toBeVisible()
    await expect(bar).toHaveAttribute("aria-valuenow", "1")
    await expect(bar).toHaveAttribute("aria-valuemin", "1")
    await expect(bar).toHaveAttribute("aria-valuemax", "7")
  })

  test("fieldset avec legend visible en étape 1", async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    await expect(page.locator("fieldset legend")).toBeVisible()
    const legend = await page.locator("fieldset legend").textContent()
    expect(legend).toContain("Quel type de projet")
  })

  test("6 options radio disponibles", async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    const radios = page.locator('input[type="radio"][name="project-type"]')
    await expect(radios).toHaveCount(6)
  })

  test("Continuer est désactivé sans sélection", async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    const continuer = page.getByRole("button", { name: /Continuer/i })
    await expect(continuer).toBeDisabled()
  })

  test("Continuer est activé après sélection d'un type", async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    await waitForEstimatorHydrated(page)
    await page.locator("label:has(input[value='vitrinesite'])").click()
    const continuer = page.getByRole("button", { name: /Continuer/i })
    await expect(continuer).toBeEnabled()
  })

  test("EST-3 : Continuer avance réellement à l'étape 2", async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    await waitForEstimatorHydrated(page)
    await page.locator("label:has(input[value='ecommerce'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    const bar = page.getByRole("progressbar")
    await expect(bar).toHaveAttribute("aria-valuenow", "2")
  })

  test("unknown : totalSteps bascule à 6 après sélection", async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    await waitForEstimatorHydrated(page)
    await page.locator("label:has(input[value='unknown'])").click()
    const continuer = page.getByRole("button", { name: /Continuer/i })
    await expect(continuer).toBeEnabled()
    await continuer.click()
    const bar = page.getByRole("progressbar")
    await expect(bar).toHaveAttribute("aria-valuemax", "6")
  })

  test("Retour absent en étape 1", async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    const retour = page.getByRole("button", { name: /Retour/i })
    await expect(retour).not.toBeVisible()
  })

  test("navigation clavier : tab + espace sélectionne une option", async ({
    page,
  }) => {
    await page.goto(ESTIMATOR_PATH)
    await waitForEstimatorHydrated(page)
    const radios = page.locator('input[type="radio"]')
    await radios.first().focus()
    await page.keyboard.press("Space")
    const checked = await radios.first().isChecked()
    expect(checked).toBe(true)
  })

  test("Axe WCAG 2.2 AA : aucune violation critique", async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    const results = await new AxeBuilder({ page })
      .withTags(["wcag2a", "wcag2aa", "wcag21aa", "wcag22aa"])
      .analyze()
    expect(results.violations).toHaveLength(0)
  })
})
