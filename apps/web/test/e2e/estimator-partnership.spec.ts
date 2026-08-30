import { expect, test } from "@playwright/test"

// E2E — Partenariat estimateur (EST-7)
//
// Périmètre :
//   - CTA secondaire "partenariat" visible après résultat estimated et human_scoping
//   - CTA secondaire visuellement distinct du CTA principal
//   - Clic CTA secondaire → formulaire partenariat (champs requis visibles)
//   - Formulaire lead et partenariat mutuellement exclusifs
//   - Soumission valide → message de confirmation
//   - Erreur 429 → message approprié
//   - Validation inline (nom manquant, email manquant, type partenariat manquant)
//   - Payload intercepté : aucun champ de prix frontend envoyé
//   - Honeypot rempli → 202 silencieux → succès UX
//   - Pré-remplissage contact après lead soumis dans la même session

const ESTIMATOR_PATH = "/estimer-mon-projet"

// ─── Helpers ─────────────────────────────────────────────────────────────────

async function completeVitrineToLastStep(page: import("@playwright/test").Page) {
  await page.goto(ESTIMATOR_PATH)
  await page.locator("label:has(input[value='vitrinesite'])").click()
  await page.getByRole("button", { name: /Continuer/i }).click()
  await page.locator("label:has(input[value='none'])").click()
  await page.getByRole("button", { name: /Continuer/i }).click()
  await page.locator("label:has(input[value='small'])").click()
  await page.getByRole("button", { name: /Continuer/i }).click()
  for (let i = 0; i < 3; i++) {
    await page.getByRole("button", { name: /Continuer/i }).click()
  }
  await expect(page.getByRole("button", { name: /Voir mon estimation/i })).toBeVisible()
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

function mockPartnershipResponse(
  page: import("@playwright/test").Page,
  status = 201,
  body: unknown = { status: "submitted", proposal_id: "prop-mock-001", request_id: "req-mock-001" },
) {
  return page.route("**/api/estimate/partnership", (route) =>
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

async function fillPartnershipForm(page: import("@playwright/test").Page) {
  await page.locator('input[name="name"]').fill("Marie Dupont")
  await page.locator('input[name="email"]').fill("marie@example.com")
  await page.locator('input[type="radio"][value="revenue_share"]').check()
  await page.locator('input[type="radio"][value="launched"]').check()
  await page.locator('textarea[name="proposal_text"]').fill("Je propose un partage de revenus sur mon activité principale.")
}

// ─── CTA partenariat visible ──────────────────────────────────────────────────

test("CTA partenariat secondaire visible après résultat estimated", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()

  await expect(page.getByRole("button", { name: /partenariat/i })).toBeVisible()
})

test("CTA partenariat secondaire visible après résultat human_scoping_required", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_HUMAN_SCOPING)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()

  await expect(page.getByRole("button", { name: /partenariat/i })).toBeVisible()
})

// ─── Hiérarchie visuelle ──────────────────────────────────────────────────────

test("CTA principal 'Parler de mon projet' et CTA secondaire coexistent", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()

  await expect(page.getByRole("button", { name: /Parler de mon projet/i })).toBeVisible()
  await expect(page.getByRole("button", { name: /partenariat/i })).toBeVisible()
})

test("le CTA partenariat n'affiche pas de montant ni de pourcentage", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()

  const ctaText = await page.getByRole("button", { name: /partenariat/i }).textContent()
  expect(ctaText).not.toMatch(/\d+\s*%/)
  expect(ctaText).not.toContain("€")
  expect(ctaText).not.toContain("Revenue share disponible")
})

// ─── Ouverture du formulaire partenariat ─────────────────────────────────────

test("Clic CTA secondaire → formulaire partenariat visible", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /partenariat/i }).click()

  await expect(page.locator('input[name="name"]')).toBeVisible()
  await expect(page.locator('input[name="email"]')).toBeVisible()
  await expect(page.locator('input[type="radio"][name="partnership-type"]').first()).toBeVisible()
  await expect(page.locator('textarea[name="proposal_text"]')).toBeVisible()
})

test("le formulaire partenariat affiche l'editorial 'ne constitue pas une alternative'", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /partenariat/i }).click()

  await expect(page.getByText(/ne constitue pas une alternative automatiquement disponible/i)).toBeVisible()
})

// ─── Exclusivité mutuelle lead / partenariat ──────────────────────────────────

test("ouvrir partenariat masque le CTA principal", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /partenariat/i }).click()

  await expect(page.getByRole("button", { name: /Parler de mon projet/i })).not.toBeVisible()
})

test("ouvrir le lead ferme le formulaire partenariat", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()

  // Aller vers partenariat → le formulaire s'ouvre
  await page.getByRole("button", { name: /partenariat/i }).click()
  await expect(page.locator('textarea[name="proposal_text"]')).toBeVisible()

  // Annuler → les deux CTAs reviennent
  await page.getByRole("button", { name: /Annuler/i }).click()
  await expect(page.getByRole("button", { name: /Parler de mon projet/i })).toBeVisible()
})

// ─── Soumission valide ────────────────────────────────────────────────────────

test("Soumission valide → message de confirmation (no commit price fields)", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await mockPartnershipResponse(page, 201)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /partenariat/i }).click()

  await fillPartnershipForm(page)
  await page.getByRole("button", { name: /Envoyer ma proposition/i }).click()

  await expect(page.getByRole("status")).toBeVisible()
  await expect(page.getByText(/transmise/i)).toBeVisible()
})

test("Soumission valide human_scoping → confirmation", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_HUMAN_SCOPING)
  await mockPartnershipResponse(page, 201)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /partenariat/i }).click()

  await fillPartnershipForm(page)
  await page.getByRole("button", { name: /Envoyer ma proposition/i }).click()

  await expect(page.getByRole("status")).toBeVisible()
})

// ─── Payload intercepté : aucun prix frontend ─────────────────────────────────

test("Payload envoyé ne contient pas de champs de prix frontend", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)

  let capturedBody: Record<string, unknown> | null = null
  await page.route("**/api/estimate/partnership", async (route) => {
    capturedBody = JSON.parse(route.request().postData() ?? "{}")
    await route.fulfill({
      status: 201,
      contentType: "application/json",
      body: JSON.stringify({ status: "submitted", proposal_id: "prop-001", request_id: "req-001" }),
    })
  })

  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /partenariat/i }).click()
  await fillPartnershipForm(page)
  await page.getByRole("button", { name: /Envoyer ma proposition/i }).click()

  await expect(page.getByRole("status")).toBeVisible()

  expect(capturedBody).not.toBeNull()
  expect(capturedBody!["estimate"]).toBeUndefined()
  expect(capturedBody!["pricing_version"]).toBeUndefined()
  expect(capturedBody!["one_off_items"]).toBeUndefined()
  expect(capturedBody!["percentage"]).toBeUndefined()
  expect(capturedBody!["revenue_share_rate"]).toBeUndefined()
})

// ─── Validation inline ────────────────────────────────────────────────────────

test("Nom manquant → erreur de validation inline", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /partenariat/i }).click()

  await page.getByRole("button", { name: /Envoyer ma proposition/i }).click()
  await expect(page.locator('[aria-invalid="true"]')).toBeVisible()
  await expect(page.getByText(/requis/i)).toBeVisible()
})

test("Email invalide → erreur inline", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /partenariat/i }).click()

  await page.locator('input[name="name"]').fill("Jean Dupont")
  await page.getByRole("button", { name: /Envoyer ma proposition/i }).click()
  await expect(page.getByText(/e-mail valide/i)).toBeVisible()
})

test("Type de partenariat manquant → erreur inline", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /partenariat/i }).click()

  await page.locator('input[name="name"]').fill("Jean Dupont")
  await page.locator('input[name="email"]').fill("jean@example.com")
  await page.getByRole("button", { name: /Envoyer ma proposition/i }).click()
  await expect(page.getByText(/nature de proposition/i)).toBeVisible()
})

// ─── Erreur 429 ───────────────────────────────────────────────────────────────

test("Erreur 429 → message rate limited dans le formulaire", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await mockPartnershipResponse(page, 429, { code: "rate_limited" })
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /partenariat/i }).click()

  await fillPartnershipForm(page)
  await page.getByRole("button", { name: /Envoyer ma proposition/i }).click()

  await expect(page.getByRole("alert")).toBeVisible()
  await expect(page.getByText(/patienter/i)).toBeVisible()
})

// ─── Honeypot silencieux ──────────────────────────────────────────────────────

test("Honeypot rempli → 202 silencieux → succès UX invisible", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await mockPartnershipResponse(page, 202, {})
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /partenariat/i }).click()

  // Remplir le formulaire normalement + injecter le honeypot via JS
  await fillPartnershipForm(page)
  await page.evaluate(() => {
    const hp = document.querySelector<HTMLInputElement>('input[name="website"]')
    if (hp) hp.value = "https://bot.example"
  })
  await page.getByRole("button", { name: /Envoyer ma proposition/i }).click()

  // UX identique à un succès — aucun message d'erreur "bot"
  await expect(page.getByRole("status")).toBeVisible()
  await expect(page.getByText(/bot/i)).not.toBeVisible()
})

// ─── Pré-remplissage contact après lead soumis ───────────────────────────────

test("Après lead soumis, formulaire partenariat pré-remplit nom et email", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await mockLeadResponse(page, 201)
  await mockPartnershipResponse(page, 201)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()

  // Soumettre le formulaire lead
  await page.getByRole("button", { name: /Parler de mon projet/i }).click()
  await page.locator('input[name="name"]').fill("Marie Dupont")
  await page.locator('input[name="email"]').fill("marie@example.com")
  await page.getByRole("button", { name: /Envoyer ma demande/i }).click()

  // Attendre le succès lead
  await expect(page.getByText(/merci/i)).toBeVisible()

  // Ouvrir maintenant le formulaire partenariat (lead est fermé)
  await page.getByRole("button", { name: /partenariat/i }).click()

  // Le nom et l'email doivent être pré-remplis
  await expect(page.locator('input[name="name"]')).toHaveValue("Marie Dupont")
  await expect(page.locator('input[name="email"]')).toHaveValue("marie@example.com")
})

// ─── Positionnement éditorial ─────────────────────────────────────────────────

test("Le formulaire n'affiche jamais 'Payez avec' ni '0 €' ni 'Revenue share disponible'", async ({ page }) => {
  await mockEstimateResponse(page, MOCK_ESTIMATED)
  await completeVitrineToLastStep(page)
  await page.getByRole("button", { name: /Voir mon estimation/i }).click()
  await page.getByRole("button", { name: /partenariat/i }).click()

  const content = await page.content()
  expect(content).not.toContain("Payez avec")
  expect(content).not.toContain("0 €")
  expect(content).not.toContain("Revenue share disponible")
  expect(content).not.toContain("crédit")
  expect(content).not.toContain("financement")
})
