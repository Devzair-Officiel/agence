import { expect, test } from "@playwright/test"

// E2E — Récapitulatif dynamique + Besoin complémentaire /estimer-mon-projet
//
// Périmètre :
//   - Scénario A : le récapitulatif s'enrichit au fil des étapes ;
//   - Scénario B : le bouton « Modifier » ramène à l'étape précédente ;
//   - Scénario C : le champ « besoin complémentaire » n'est jamais transmis.
//
// Aucun appel API, aucun prix — tout reste en mémoire.

const ESTIMATOR_PATH = "/estimer-mon-projet"

test.describe("EstimatorSummary E2E", () => {
  test("A — le récapitulatif s'enrichit étape par étape", async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)

    // Étape 1 : choisir un type de projet
    await page.locator("label:has(input[value='vitrinesite'])").click()
    const aside = page.locator("aside[aria-label='Récapitulatif de votre projet']")
    await expect(aside).toBeVisible()
    await expect(aside).toContainText("Site vitrine / institutionnel")

    // Avancer à l'étape 2
    await page.getByRole("button", { name: /Continuer/i }).click()
    await expect(page.getByRole("progressbar")).toHaveAttribute("aria-valuenow", "2")

    // Le récapitulatif doit toujours afficher le type de projet
    await expect(aside).toContainText("Site vitrine / institutionnel")

    // Choisir une situation
    await page.locator("label:has(input[value='none'])").click()
    await expect(aside).toContainText("Non, c'est une création")

    // Avancer à l'étape 3
    await page.getByRole("button", { name: /Continuer/i }).click()
    await expect(page.getByRole("progressbar")).toHaveAttribute("aria-valuenow", "3")

    // Le récapitulatif doit afficher les deux premières réponses
    await expect(aside).toContainText("Site vitrine / institutionnel")
    await expect(aside).toContainText("Non, c'est une création")
  })

  test("B — Modifier ramène à l'étape ciblée", async ({ page }) => {
    await page.goto(ESTIMATOR_PATH)

    // Remplir étape 1
    await page.locator("label:has(input[value='ecommerce'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()

    // Remplir étape 2
    await page.locator("label:has(input[value='none'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await expect(page.getByRole("progressbar")).toHaveAttribute("aria-valuenow", "3")

    // Cliquer sur "Modifier" pour l'étape 1 (Type de projet)
    await page.getByRole("button", { name: /Modifier : Type de projet/i }).click()
    await expect(page.getByRole("progressbar")).toHaveAttribute("aria-valuenow", "1")

    // La section "Type de projet" doit afficher "En cours"
    const aside = page.locator("aside[aria-label='Récapitulatif de votre projet']")
    await expect(aside).toContainText("En cours")
  })

  test("C — le champ besoin complémentaire n'est jamais dans le payload réseau", async ({
    page,
  }) => {
    // Intercepter les éventuelles requêtes vers /api/estimate
    const apiRequests: string[] = []
    page.on("request", (req) => {
      if (req.url().includes("/api/estimate")) {
        apiRequests.push(req.postData() ?? "")
      }
    })

    await page.goto(ESTIMATOR_PATH)

    // Remplir jusqu'à l'étape fonctionnalités (étape 4, chemin standard)
    await page.locator("label:has(input[value='vitrinesite'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await page.locator("label:has(input[value='none'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await page.locator("label:has(input[value='small'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()

    // À l'étape 4, saisir un besoin complémentaire
    await page.fill("#additional-feature-note", "Système de chat en temps réel")
    await expect(page.locator(".features-step__counter")).toContainText("29 / 400")

    // Continuer jusqu'à la fin et vérifier qu'aucune requête API n'a été envoyée
    // (ou si elle l'est, que le champ n'est pas dedans)
    await page.getByRole("button", { name: /Continuer/i }).click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await page.getByRole("button", { name: /Continuer/i }).click()

    for (const body of apiRequests) {
      expect(body).not.toContain("additional_feature_note")
      expect(body).not.toContain("additionalFeatureNote")
      expect(body).not.toContain("Système de chat")
    }
  })

  test("champ besoin complémentaire : counter et textarea visibles à l'étape 4", async ({
    page,
  }) => {
    await page.goto(ESTIMATOR_PATH)

    // Naviguer jusqu'à l'étape 4
    await page.locator("label:has(input[value='vitrinesite'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await page.locator("label:has(input[value='none'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await page.locator("label:has(input[value='small'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()

    const textarea = page.locator("#additional-feature-note")
    await expect(textarea).toBeVisible()
    await expect(textarea).toHaveAttribute("maxlength", "400")

    const counter = page.locator(".features-step__counter")
    await expect(counter).toContainText("0 / 400")

    await textarea.fill("Besoin de test")
    await expect(counter).toContainText("14 / 400")

    const disclaimer = page.locator(".features-step__disclaimer")
    await expect(disclaimer).toContainText("Ce besoin sera étudié avec vous")
  })

  test("récapitulatif affiche le besoin complémentaire sous Fonctionnalités", async ({
    page,
  }) => {
    await page.goto(ESTIMATOR_PATH)

    // Naviguer jusqu'à l'étape 4
    await page.locator("label:has(input[value='vitrinesite'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await page.locator("label:has(input[value='none'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()
    await page.locator("label:has(input[value='small'])").click()
    await page.getByRole("button", { name: /Continuer/i }).click()

    await page.fill("#additional-feature-note", "Chatbot IA")
    await page.getByRole("button", { name: /Continuer/i }).click()

    // À l'étape 5, le récapitulatif montre le besoin complémentaire
    const aside = page.locator("aside[aria-label='Récapitulatif de votre projet']")
    await expect(aside).toContainText("Chatbot IA")
  })
})
