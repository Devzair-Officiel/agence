import AxeBuilder from "@axe-core/playwright"
import { expect, test } from "@playwright/test"

// E2E — Navigation complète du questionnaire estimateur (EST-3)
//
// Périmètre :
//   - parcours complets : vitrinesite, ecommerce, businessapp, refonte, other ;
//   - parcours unknown (6 étapes, objectif-first) ;
//   - back navigation avec conservation des réponses ;
//   - changement de ProjectType + nettoyage conditionnel ;
//   - aucun POST /api/estimate émis ;
//   - aucun prix affiché à aucune étape ;
//   - isComplete uniquement sur la dernière étape du parcours courant ;
//   - Axe WCAG 2.2 AA sur les étapes clés ;
//   - responsive (390, 768, 1440) sur étape 1.
//
// Aucun appel API, aucun prix.

const ESTIMATOR_PATH = "/estimer-mon-projet"

// ─── Helpers ─────────────────────────────────────────────────────────────────

/** Avance jusqu'à l'étape 2 standard en ayant sélectionné projectType. */
async function goToStep2Standard(
  page: import("@playwright/test").Page,
  type: string,
) {
  await page.goto(ESTIMATOR_PATH)
  await page.locator(`label:has(input[value='${type}'])`).click()
  await page.getByRole("button", { name: /Continuer/i }).click()
}

/** Complete les étapes 2 et 3 du parcours standard (situation + scope). */
async function completeRequiredStepsStandard(
  page: import("@playwright/test").Page,
  type: string,
) {
  await goToStep2Standard(page, type)
  // étape 2 : situation
  await page.locator("label:has(input[value='none'])").click()
  await page.getByRole("button", { name: /Continuer/i }).click()
  // étape 3 : scope
  await page.locator("label:has(input[value='small'])").click()
  await page.getByRole("button", { name: /Continuer/i }).click()
}

// ─── Parcours standard ───────────────────────────────────────────────────────

test.describe("Parcours standard — 7 étapes", () => {
  for (const type of [
    "vitrinesite",
    "ecommerce",
    "businessapp",
    "refonte",
    "other",
  ] as const) {
    test(`${type} : totalSteps = 7`, async ({ page }) => {
      await goToStep2Standard(page, type)
      const bar = page.getByRole("progressbar")
      await expect(bar).toHaveAttribute("aria-valuenow", "2")
      await expect(bar).toHaveAttribute("aria-valuemax", "7")
    })
  }

  test("vitrinesite : parcours complet jusqu'à l'étape 7", async ({ page }) => {
    await completeRequiredStepsStandard(page, "vitrinesite")
    // étapes 4-6 optionnelles
    for (let step = 4; step <= 6; step++) {
      await expect(
        page.getByRole("button", { name: /Continuer/i }),
      ).toBeEnabled()
      await page.getByRole("button", { name: /Continuer/i }).click()
    }
    // étape 7 : bouton Terminer
    const bar = page.getByRole("progressbar")
    await expect(bar).toHaveAttribute("aria-valuenow", "7")
    await expect(page.getByRole("button", { name: /Terminer/i })).toBeVisible()
  })

  test("ecommerce : parcours complet jusqu'à l'étape 7", async ({ page }) => {
    await completeRequiredStepsStandard(page, "ecommerce")
    for (let step = 4; step <= 6; step++) {
      await page.getByRole("button", { name: /Continuer/i }).click()
    }
    await expect(page.getByRole("button", { name: /Terminer/i })).toBeVisible()
  })

  test("businessapp : parcours complet jusqu'à l'étape 7", async ({ page }) => {
    await completeRequiredStepsStandard(page, "businessapp")
    for (let step = 4; step <= 6; step++) {
      await page.getByRole("button", { name: /Continuer/i }).click()
    }
    await expect(page.getByRole("button", { name: /Terminer/i })).toBeVisible()
  })

  test("refonte : parcours complet jusqu'à l'étape 7", async ({ page }) => {
    await completeRequiredStepsStandard(page, "refonte")
    for (let step = 4; step <= 6; step++) {
      await page.getByRole("button", { name: /Continuer/i }).click()
    }
    await expect(page.getByRole("button", { name: /Terminer/i })).toBeVisible()
  })

  test("other : parcours complet jusqu'à l'étape 7 avec labels cohérents", async ({
    page,
  }) => {
    await page.goto(ESTIMATOR_PATH)
    await page.locator("label:has(input[value='other'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    // étape 2 : situation
    await expect(page.getByRole("progressbar")).toHaveAttribute(
      "aria-valuenow",
      "2",
    )
    await page.locator("label:has(input[value='none'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    // étape 3 : scope — labels spécifiques à "other"
    const legendText = await page.locator("legend").textContent()
    expect(legendText).toContain("envergure")
    await page.locator("label:has(input[value='small'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    // étapes 4-6 optionnelles
    for (let i = 0; i < 3; i++) {
      await page.getByRole("button", { name: /Continuer/i }).click()
    }
    await expect(page.getByRole("button", { name: /Terminer/i })).toBeVisible()
  })

  test("étape 2 (standard) : champ situation requis, Continuer bloqué", async ({
    page,
  }) => {
    await goToStep2Standard(page, "vitrinesite")
    await expect(
      page.getByRole("button", { name: /Continuer/i }),
    ).toBeDisabled()
    await page.locator("label:has(input[value='has_website'])").click()
    await expect(
      page.getByRole("button", { name: /Continuer/i }),
    ).toBeEnabled()
  })

  test("étape 3 (standard) : scope requis, Continuer bloqué", async ({
    page,
  }) => {
    await goToStep2Standard(page, "vitrinesite")
    await page.locator("label:has(input[value='none'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await expect(
      page.getByRole("button", { name: /Continuer/i }),
    ).toBeDisabled()
    await page.locator("label:has(input[value='medium'])").click()
    await expect(
      page.getByRole("button", { name: /Continuer/i }),
    ).toBeEnabled()
  })

  test("isComplete = false avant l'étape 7 (standard)", async ({ page }) => {
    await completeRequiredStepsStandard(page, "vitrinesite")
    // sur l'étape 4 : Terminer pas encore affiché
    await expect(
      page.getByRole("button", { name: /Terminer/i }),
    ).not.toBeVisible()
    // avancer jusqu'à étape 6 : Terminer toujours absent
    await page.getByRole("button", { name: /Continuer/i }).click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await expect(
      page.getByRole("button", { name: /Terminer/i }),
    ).not.toBeVisible()
    // étape 7 : Terminer présent
    await page.getByRole("button", { name: /Continuer/i }).click()
    await expect(
      page.getByRole("button", { name: /Terminer/i }),
    ).toBeVisible()
  })
})

// ─── Parcours unknown ────────────────────────────────────────────────────────

test.describe("Parcours unknown — 6 étapes (objectif-first)", () => {
  test("totalSteps = 6 dès l'étape 2", async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    await page.locator("label:has(input[value='unknown'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await expect(page.getByRole("progressbar")).toHaveAttribute(
      "aria-valuemax",
      "6",
    )
  })

  test("étape 2 : objectifs requis avant Continuer", async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    await page.locator("label:has(input[value='unknown'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await expect(
      page.getByRole("button", { name: /Continuer/i }),
    ).toBeDisabled()
    await page.locator("label:has(input[name='objectives'])").first().click()
    await expect(
      page.getByRole("button", { name: /Continuer/i }),
    ).toBeEnabled()
  })

  test("étape 3 (unknown) : situation requise avant Continuer", async ({
    page,
  }) => {
    await page.goto(ESTIMATOR_PATH)
    await page.locator("label:has(input[value='unknown'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await page.locator("label:has(input[name='objectives'])").first().click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await expect(
      page.getByRole("button", { name: /Continuer/i }),
    ).toBeDisabled()
    await page.locator("label:has(input[value='none'])").click()
    await expect(
      page.getByRole("button", { name: /Continuer/i }),
    ).toBeEnabled()
  })

  test("parcours unknown complet : étape 6 affiche Terminer", async ({
    page,
  }) => {
    await page.goto(ESTIMATOR_PATH)
    await page.locator("label:has(input[value='unknown'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await page.locator("label:has(input[name='objectives'])").first().click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await page.locator("label:has(input[value='none'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    // étapes 4-5 optionnelles
    await page.getByRole("button", { name: /Continuer/i }).click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    // étape 6
    await expect(page.getByRole("progressbar")).toHaveAttribute(
      "aria-valuenow",
      "6",
    )
    await expect(page.getByRole("button", { name: /Terminer/i })).toBeVisible()
  })

  test("isComplete = false avant l'étape 6 (unknown)", async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    await page.locator("label:has(input[value='unknown'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await page.locator("label:has(input[name='objectives'])").first().click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await page.locator("label:has(input[value='none'])").click()
    // étape 3 : Continuer présent, Terminer absent
    await expect(
      page.getByRole("button", { name: /Terminer/i }),
    ).not.toBeVisible()
    await page.getByRole("button", { name: /Continuer/i }).click()
    // étape 4 : Terminer absent
    await expect(
      page.getByRole("button", { name: /Terminer/i }),
    ).not.toBeVisible()
    await page.getByRole("button", { name: /Continuer/i }).click()
    // étape 5 : Terminer absent
    await expect(
      page.getByRole("button", { name: /Terminer/i }),
    ).not.toBeVisible()
    await page.getByRole("button", { name: /Continuer/i }).click()
    // étape 6 : Terminer présent
    await expect(page.getByRole("button", { name: /Terminer/i })).toBeVisible()
  })

  test("unknown : aucune conversion d'objectifs en ProjectType côté frontend", async ({
    page,
  }) => {
    await page.goto(ESTIMATOR_PATH)
    await page.locator("label:has(input[value='unknown'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    // Sélectionner "Vendre en ligne" qui pourrait naïvement être converti en ecommerce
    const sellOnline = page.locator("label:has(input[value='sell_online'])")
    if (await sellOnline.count()) {
      await sellOnline.click()
    } else {
      await page.locator("label:has(input[name='objectives'])").first().click()
    }
    await page.getByRole("button", { name: /Continuer/i }).click()
    // Doit rester 6 étapes (unknown), pas passer à 7 (ecommerce)
    await expect(page.getByRole("progressbar")).toHaveAttribute(
      "aria-valuemax",
      "6",
    )
  })
})

// ─── Back navigation ──────────────────────────────────────────────────────────

test.describe("Back navigation", () => {
  test("Retour actif dès l'étape 2", async ({ page }) => {
    await goToStep2Standard(page, "vitrinesite")
    await expect(page.getByRole("button", { name: /Retour/i })).toBeVisible()
  })

  test("Retour ramène à l'étape précédente", async ({ page }) => {
    await goToStep2Standard(page, "vitrinesite")
    await page.getByRole("button", { name: /Retour/i }).click()
    await expect(page.getByRole("progressbar")).toHaveAttribute(
      "aria-valuenow",
      "1",
    )
  })

  test("réponse situation préservée après Retour puis Suivant", async ({
    page,
  }) => {
    await goToStep2Standard(page, "vitrinesite")
    await page.locator("label:has(input[value='has_website'])").click()
    await page.getByRole("button", { name: /Retour/i }).click()
    // Retour sur étape 1, projectType vitrinesite toujours sélectionné
    await expect(
      page.locator("input[value='vitrinesite']"),
    ).toBeChecked()
    // Avancer de nouveau
    await page.getByRole("button", { name: /Continuer/i }).click()
    // La situation doit être pré-sélectionnée
    await expect(page.locator("input[value='has_website']")).toBeChecked()
  })

  test("scope préservé après Retour sur l'étape 2 puis re-avancement", async ({
    page,
  }) => {
    await completeRequiredStepsStandard(page, "vitrinesite")
    // Sur étape 4 (features), retourner à l'étape 3
    await page.getByRole("button", { name: /Retour/i }).click()
    await expect(page.getByRole("progressbar")).toHaveAttribute(
      "aria-valuenow",
      "3",
    )
    // Le scope 'small' doit être préservé
    await expect(page.locator("input[value='small']")).toBeChecked()
  })
})

// ─── Nettoyage conditionnel ───────────────────────────────────────────────────

test.describe("Nettoyage conditionnel sur changement de ProjectType", () => {
  test("features vitrinesite effacées lorsqu'on bascule sur businessapp", async ({
    page,
  }) => {
    // 1. Aller aux features vitrinesite (étape 4) et sélectionner
    await completeRequiredStepsStandard(page, "vitrinesite")
    const contactFormLabel = page.locator("label:has(input[value='contact_form'])")
    if (await contactFormLabel.count()) {
      await contactFormLabel.click()
    }

    // 2. Retourner jusqu'à l'étape 1
    await page.getByRole("button", { name: /Retour/i }).click()
    await page.getByRole("button", { name: /Retour/i }).click()
    await page.getByRole("button", { name: /Retour/i }).click()

    // 3. Choisir businessapp
    await page.locator("label:has(input[value='businessapp'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()

    // 4. Avancer jusqu'aux features (étape 4) via situation + scope
    await page.locator("label:has(input[value='none'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await page.locator("label:has(input[value='small'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()

    // 5. Vérifier que contact_form n'est plus coché
    const contactFormAfterSwitch = page.locator("input[value='contact_form']")
    if (await contactFormAfterSwitch.count()) {
      await expect(contactFormAfterSwitch).not.toBeChecked()
    }
    // (Si contact_form n'est pas dans businessapp features, sa disparition confirme le filtrage)
  })

  test("scale effacé lors du changement de type", async ({ page }) => {
    // Sélectionner vitrinesite → scale=large
    await goToStep2Standard(page, "vitrinesite")
    await page.locator("label:has(input[value='none'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await page.locator("label:has(input[value='large'])").click()

    // Retour à l'étape 1, changer de type
    await page.getByRole("button", { name: /Retour/i }).click()
    await page.getByRole("button", { name: /Retour/i }).click()
    await page.locator("label:has(input[value='ecommerce'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()

    // Avancer à l'étape 3 (scope)
    await page.locator("label:has(input[value='has_ecommerce'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()

    // large ne doit plus être pré-sélectionné
    await expect(page.locator("input[value='large']")).not.toBeChecked()
  })
})

// ─── Aucun appel API / aucun prix ─────────────────────────────────────────────

test.describe("Garanties d'isolation (pas d'API, pas de prix)", () => {
  test("aucun POST /api/estimate émis pendant un parcours complet", async ({
    page,
  }) => {
    const apiCalls: string[] = []
    page.on("request", (req) => {
      if (req.url().includes("/api/estimate")) apiCalls.push(req.url())
    })

    await completeRequiredStepsStandard(page, "vitrinesite")
    for (let i = 0; i < 4; i++) {
      await page.getByRole("button", { name: /(Continuer|Terminer)/i }).click()
    }

    expect(apiCalls).toHaveLength(0)
  })

  test("aucun prix (€) affiché à aucune étape du parcours", async ({
    page,
  }) => {
    await completeRequiredStepsStandard(page, "ecommerce")

    for (let i = 0; i < 4; i++) {
      const bodyText = await page.textContent("body")
      expect(bodyText).not.toMatch(/\d[\d\s]*\s*€/)
      expect(bodyText).not.toMatch(/MIN\s*\/\s*MAX|budget estimé/i)
      await page
        .getByRole("button", { name: /(Continuer|Terminer)/i })
        .click()
        .catch(() => {})
    }
  })
})

// ─── Accessibilité ───────────────────────────────────────────────────────────

test.describe("Accessibilité WCAG 2.2 AA", () => {
  test("étape 1 : aucune violation critique", async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)
    const results = await new AxeBuilder({ page })
      .withTags(["wcag2a", "wcag2aa", "wcag21aa", "wcag22aa"])
      .analyze()
    expect(results.violations).toHaveLength(0)
  })

  test("étape 2 standard (situation) : aucune violation critique", async ({
    page,
  }) => {
    await goToStep2Standard(page, "vitrinesite")
    const results = await new AxeBuilder({ page })
      .withTags(["wcag2a", "wcag2aa", "wcag21aa", "wcag22aa"])
      .analyze()
    expect(results.violations).toHaveLength(0)
  })

  test("étape 2 unknown (objectifs) : aucune violation critique", async ({
    page,
  }) => {
    await page.goto(ESTIMATOR_PATH)
    await page.locator("label:has(input[value='unknown'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    const results = await new AxeBuilder({ page })
      .withTags(["wcag2a", "wcag2aa", "wcag21aa", "wcag22aa"])
      .analyze()
    expect(results.violations).toHaveLength(0)
  })

  test("étape 3 standard (scope) : aucune violation critique", async ({
    page,
  }) => {
    await goToStep2Standard(page, "ecommerce")
    await page.locator("label:has(input[value='has_ecommerce'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    const results = await new AxeBuilder({ page })
      .withTags(["wcag2a", "wcag2aa", "wcag21aa", "wcag22aa"])
      .analyze()
    expect(results.violations).toHaveLength(0)
  })
})

// ─── Clavier ─────────────────────────────────────────────────────────────────

test.describe("Navigation clavier", () => {
  test("étape 1 : espace sélectionne la première option radio", async ({
    page,
  }) => {
    await page.goto(ESTIMATOR_PATH)
    await page.locator('input[type="radio"]').first().focus()
    await page.keyboard.press("Space")
    await expect(page.locator('input[type="radio"]').first()).toBeChecked()
  })

  test("étape 2 standard : tab + espace sélectionne une situation", async ({
    page,
  }) => {
    await goToStep2Standard(page, "vitrinesite")
    await page.locator('input[type="radio"][name="current-situation"]').first().focus()
    await page.keyboard.press("Space")
    await expect(
      page.locator('input[type="radio"][name="current-situation"]').first(),
    ).toBeChecked()
  })
})

// ─── Responsive ──────────────────────────────────────────────────────────────

test.describe("Responsive", () => {
  for (const [label, width, height] of [
    ["mobile 390", 390, 844],
    ["tablette 768", 768, 1024],
    ["desktop 1440", 1440, 900],
  ] as const) {
    test(`étape 1 affichée sur ${label}px`, async ({ page }) => {
      await page.setViewportSize({ width, height })
      await page.goto(ESTIMATOR_PATH)
      await expect(page.locator("fieldset legend")).toBeVisible()
      await expect(page.getByRole("progressbar")).toBeVisible()
    })

    test(`étape 2 standard affichée sur ${label}px`, async ({ page }) => {
      await page.setViewportSize({ width, height })
      await goToStep2Standard(page, "vitrinesite")
      await expect(page.locator("fieldset legend")).toBeVisible()
    })
  }
})
