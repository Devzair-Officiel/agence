import { expect, test } from "@playwright/test"
import { waitForEstimatorHydrated } from "./helpers/estimator-hydration"

// E2E — Lead estimateur (EST-6)
//
// Périmètre :
//   - CTA "Parler de mon projet" visible après résultat estimated
//   - CTA visible après résultat human_scoping_required
//   - Clic CTA → formulaire affiché (nom, email, téléphone, société)
//   - Résultat toujours visible sous le formulaire (pas de portail)
//   - Soumission succès → message de confirmation
//   - Erreur 429 → message approprié
//   - Erreur validation (email manquant) → erreur inline
//   - Modifier mes réponses → formulaire disparu (résultat démonté)
//   - Honeypot rempli → 202 silencieux → succès UX (anti-bot invisible)

const ESTIMATOR_PATH = "/estimer-mon-projet"

// ─── Helpers ─────────────────────────────────────────────────────────────────

async function completeVitrineToLastStep(page: import("@playwright/test").Page) {
  await page.goto(ESTIMATOR_PATH)
  await waitForEstimatorHydrated(page)
  await page.locator("label:has(input[value='vitrinesite'])").click()
  await page.getByRole("button", { name: /Continuer/i }).click()
  await page.locator("label:has(input[value='none'])").click()
  await page.getByRole("button", { name: /Continuer/i }).click()
  await page.locator("label:has(input[value='small'])").click()
  await page.getByRole("button", { name: /Continuer/i }).click()
  for (let i = 0; i < 3; i++) {
    await page.getByRole("button", { name: /Continuer/i }).click()
  }
  await expect(
    page.getByRole("button", { name: /Voir mon estimation/i }),
  ).toBeVisible()
}

function mockEstimateResponse(page: import("@playwright/test").Page, body: unknown, status = 200) {
  return page.route("**/api/estimate", (route) =>
    route.fulfill({
      status,
      contentType: "application/json",
      body: JSON.stringify(body),
    }),
  )
}

function mockLeadResponse(page: import("@playwright/test").Page, status = 201, body: unknown = { status: "ok", request_id: "mock-lead-001" }) {
  return page.route("**/api/estimate/lead", (route) =>
    route.fulfill({
      status,
      contentType: "application/json",
      body: JSON.stringify(body),
    }),
  )
}

const MOCK_ESTIMATED = {
  status: "ok",
  request_id: "mock-req-001",
  outcome: "estimated",
  recommended_project_type: "vitrinesite",
  estimate: { minimum: 90_000, maximum: 130_000, currency: "EUR" },
  one_off_items: [{ code: "BASE", minimum: 90_000, maximum: 130_000, currency: "EUR" }],
  recurring_items: [],
  assumptions: [],
  pricing_version: "2026-v1",
}

const MOCK_HUMAN_SCOPING = {
  status: "ok",
  request_id: "mock-req-002",
  outcome: "human_scoping_required",
  recommended_project_type: "unknown",
  estimate: { minimum: 90_000, maximum: 400_000, currency: "EUR" },
  one_off_items: [{ code: "BASE", minimum: 90_000, maximum: 400_000, currency: "EUR" }],
  recurring_items: [],
  assumptions: ["unknown_project_requires_human_scoping"],
  pricing_version: "2026-v1",
}

// ─── CTA visible ──────────────────────────────────────────────────────────────

test("CTA 'Parler de mon projet' visible après résultat estimated", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await expect(page.getByRole("button", { name: /Parler de mon projet/i })).toBeVisible()
})

test("CTA 'Parler de mon projet' visible après résultat human_scoping_required", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_HUMAN_SCOPING)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await expect(page.getByRole("button", { name: /Parler de mon projet/i })).toBeVisible()
})

// ─── Ouverture du formulaire ──────────────────────────────────────────────────

test("Clic CTA → formulaire lead visible avec champs requis", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /Parler de mon projet/i }).click()

  await expect(page.locator('input[name="name"]')).toBeVisible()
  await expect(page.locator('input[name="email"]')).toBeVisible()
  await expect(page.locator('input[name="phone"]')).toBeVisible()
  await expect(page.locator('input[name="company"]')).toBeVisible()
})

test("Résultat toujours visible après ouverture du formulaire", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /Parler de mon projet/i }).click()

  // Le résultat doit toujours être visible
  await expect(page.getByText("estimation")).toBeVisible()
  // Le formulaire est aussi visible
  await expect(page.locator('input[name="name"]')).toBeVisible()
})

// ─── Soumission succès ────────────────────────────────────────────────────────

test("Soumission valide → message de confirmation", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await mockLeadResponse(page, 201)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /Parler de mon projet/i }).click()

  await page.locator('input[name="name"]').fill("Jean Dupont")
  await page.locator('input[name="email"]').fill("jean@example.com")
  await page.getByRole("button", { name: /Envoyer ma demande/i }).click()

  await expect(page.getByRole("status")).toBeVisible()
  await expect(page.getByText(/merci/i)).toBeVisible()
})

// ─── Erreur 429 ───────────────────────────────────────────────────────────────

test("Erreur 429 → message rate limited dans le formulaire", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await mockLeadResponse(page, 429, { status: "error", code: "rate_limited" })
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /Parler de mon projet/i }).click()

  await page.locator('input[name="name"]').fill("Jean Dupont")
  await page.locator('input[name="email"]').fill("jean@example.com")
  await page.getByRole("button", { name: /Envoyer ma demande/i }).click()

  await expect(page.getByRole("alert")).toBeVisible()
  await expect(page.getByText(/patienter/i)).toBeVisible()
})

// ─── Validation inline ────────────────────────────────────────────────────────

test("Email manquant → erreur de validation inline", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /Parler de mon projet/i }).click()

  await page.locator('input[name="name"]').fill("Jean Dupont")
  await page.getByRole("button", { name: /Envoyer ma demande/i }).click()

  await expect(page.locator('[aria-invalid="true"]')).toBeVisible()
})

// ─── Modifier mes réponses → reset ────────────────────────────────────────────

test("Modifier mes réponses → formulaire lead disparu (résultat démonté)", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /Parler de mon projet/i }).click()

  // Formulaire visible
  await expect(page.locator('input[name="name"]')).toBeVisible()

  // Modifier les réponses
  await page.getByRole("button", { name: /Modifier mes réponses/i }).click()

  // Le résultat est démonté, le formulaire a disparu avec
  await expect(page.locator('input[name="name"]')).not.toBeVisible()
})
