import { expect, test } from "@playwright/test"
import { openCookieConsentManager } from "./support/cookie-consent"

// E2E — Gestionnaire de préférences de cookies (DEV-089)
//
// Périmètre :
//   - présence de "Gérer mes cookies" dans le footer ;
//   - ouverture de la modale au clic ;
//   - présence des trois catégories ;
//   - "Nécessaires" toujours actif et non modifiable ;
//   - modification des catégories optionnelles ;
//   - sauvegarde et persistance après navigation ;
//   - réouverture après sauvegarde ;
//   - "Tout refuser" désactive les catégories optionnelles ;
//   - aucun bandeau automatique (hasConsentRequiredServices === false).

const STORAGE_KEY = "dz_cookie_prefs"

async function clearConsent(page: Parameters<typeof test.beforeEach>[0]["page"]) {
  await page.evaluate((key) => localStorage.removeItem(key), STORAGE_KEY)
}

// ─── Footer ──────────────────────────────────────────────────────────────────

test.describe("Cookie consent — footer", () => {
  test("le footer contient un bouton Gérer mes cookies", async ({ page }) => {
    await page.goto("/")
    const btn = page.locator("footer button", { hasText: "Gérer mes cookies" })
    await expect(btn).toBeVisible()
  })

  test("Gérer mes cookies est accessible depuis /politique-de-confidentialite", async ({ page }) => {
    await page.goto("/politique-de-confidentialite")
    const btn = page.locator("footer button", { hasText: "Gérer mes cookies" })
    await expect(btn).toBeVisible()
  })
})

// ─── Ouverture de la modale ───────────────────────────────────────────────────

test.describe("Cookie consent — ouverture modale", () => {
  test("le clic sur Gérer mes cookies ouvre la modale", async ({ page }) => {
    await page.goto("/")
    await openCookieConsentManager(page)
    await expect(page.locator('[role="dialog"]')).toBeVisible()
  })

  test("la modale a un titre accessible", async ({ page }) => {
    await page.goto("/")
    await openCookieConsentManager(page)
    await expect(page.locator('[role="dialog"] h2')).toContainText("Préférences de cookies")
  })

  test("la modale affiche les trois catégories", async ({ page }) => {
    await page.goto("/")
    await openCookieConsentManager(page)
    const dialog = page.locator('[role="dialog"]')
    await expect(dialog).toContainText("Nécessaires")
    await expect(dialog).toContainText("Mesure d'audience")
    await expect(dialog).toContainText("Marketing")
  })

  test("la modale se ferme via le bouton ×", async ({ page }) => {
    await page.goto("/")
    await openCookieConsentManager(page)
    await page.locator('[aria-label="Fermer les préférences de cookies"]').click()
    await expect(page.locator('[role="dialog"]')).not.toBeVisible()
  })

  test("la modale se ferme via la touche Échap", async ({ page }) => {
    await page.goto("/")
    await openCookieConsentManager(page)
    await page.keyboard.press("Escape")
    await expect(page.locator('[role="dialog"]')).not.toBeVisible()
  })
})

// ─── Catégorie Nécessaires ────────────────────────────────────────────────────

test.describe("Cookie consent — catégorie Nécessaires", () => {
  test("le toggle Nécessaires est toujours activé", async ({ page }) => {
    await page.goto("/")
    await openCookieConsentManager(page)
    const dialog = page.locator('[role="dialog"]')
    // Le toggle nécessaires a checked=true et disabled
    const necessaryToggle = dialog.locator('.consent-toggle--locked input[type="checkbox"]')
    await expect(necessaryToggle).toBeChecked()
    await expect(necessaryToggle).toBeDisabled()
  })
})

// ─── Actions ─────────────────────────────────────────────────────────────────

test.describe("Cookie consent — Tout refuser", () => {
  test("Tout refuser désactive analytics et marketing", async ({ page }) => {
    await page.goto("/")
    await clearConsent(page)

    await openCookieConsentManager(page)
    await page.locator('[role="dialog"] button', { hasText: "Tout refuser" }).click()

    // La modale se ferme
    await expect(page.locator('[role="dialog"]')).not.toBeVisible()

    // Vérifie le localStorage
    const stored = await page.evaluate((key) => {
      const raw = localStorage.getItem(key)
      return raw ? JSON.parse(raw) : null
    }, STORAGE_KEY)
    expect(stored).not.toBeNull()
    expect(stored.analytics).toBe(false)
    expect(stored.marketing).toBe(false)
    expect(stored.necessary).toBe(true)
  })
})

test.describe("Cookie consent — Tout accepter", () => {
  test("Tout accepter active analytics et marketing", async ({ page }) => {
    await page.goto("/")
    await clearConsent(page)

    await openCookieConsentManager(page)
    await page.locator('[role="dialog"] button', { hasText: "Tout accepter" }).click()

    await expect(page.locator('[role="dialog"]')).not.toBeVisible()

    const stored = await page.evaluate((key) => {
      const raw = localStorage.getItem(key)
      return raw ? JSON.parse(raw) : null
    }, STORAGE_KEY)
    expect(stored.analytics).toBe(true)
    expect(stored.marketing).toBe(true)
  })
})

test.describe("Cookie consent — Enregistrer mes choix", () => {
  test("peut enregistrer analytics=true, marketing=false", async ({ page }) => {
    await page.goto("/")
    await clearConsent(page)

    await openCookieConsentManager(page)

    const dialog = page.locator('[role="dialog"]')
    // Active le toggle analytics (deuxième toggle non verrouillé)
    const analyticsToggle = dialog.locator('.consent-category:nth-child(2) input[type="checkbox"]')
    await analyticsToggle.check()

    await dialog.locator('button', { hasText: "Enregistrer mes choix" }).click()

    const stored = await page.evaluate((key) => {
      const raw = localStorage.getItem(key)
      return raw ? JSON.parse(raw) : null
    }, STORAGE_KEY)
    expect(stored.analytics).toBe(true)
    expect(stored.marketing).toBe(false)
    expect(stored.necessary).toBe(true)
  })
})

// ─── Persistance ─────────────────────────────────────────────────────────────

test.describe("Cookie consent — persistance après navigation", () => {
  test("les préférences restent après navigation vers une autre page", async ({ page }) => {
    await page.goto("/")
    await clearConsent(page)

    await openCookieConsentManager(page)
    await page.locator('[role="dialog"] button', { hasText: "Tout accepter" }).click()

    // Navigation vers une autre page
    await page.goto("/contact")

    const stored = await page.evaluate((key) => {
      const raw = localStorage.getItem(key)
      return raw ? JSON.parse(raw) : null
    }, STORAGE_KEY)
    expect(stored).not.toBeNull()
    expect(stored.analytics).toBe(true)
  })

  test("Gérer mes cookies permet de rouvrir la modale après sauvegarde", async ({ page }) => {
    await page.goto("/")
    await clearConsent(page)

    // Premier choix
    await openCookieConsentManager(page)
    await page.locator('[role="dialog"] button', { hasText: "Tout refuser" }).click()

    // Réouverture
    await openCookieConsentManager(page)
    await expect(page.locator('[role="dialog"]')).toBeVisible()
    // Les préférences courantes sont reflétées dans les toggles
    const analyticsToggle = page.locator('[role="dialog"] .consent-category:nth-child(2) input[type="checkbox"]')
    await expect(analyticsToggle).not.toBeChecked()
  })
})

// ─── Pas de bandeau automatique ───────────────────────────────────────────────

test.describe("Cookie consent — aucun bandeau automatique", () => {
  test("aucun bandeau ne s'affiche automatiquement au premier chargement", async ({ page }) => {
    await page.goto("/")
    await clearConsent(page)
    await page.reload()

    // Le bandeau ne doit pas être présent (hasConsentRequiredServices === false)
    await expect(page.locator('[role="region"][aria-label="Préférences de cookies"]')).not.toBeVisible()
  })
})

// ─── Accessibilité focus ──────────────────────────────────────────────────────

test.describe("Cookie consent — focus et accessibilité", () => {
  test("le focus revient sur le bouton d'ouverture après fermeture", async ({ page }) => {
    await page.goto("/")
    await openCookieConsentManager(page)
    await page.locator('[aria-label="Fermer les préférences de cookies"]').click()
    // Le focus doit être revenu sur le bouton qui a ouvert la modale
    const openBtn = page.locator("footer button", { hasText: "Gérer mes cookies" })
    await expect(openBtn).toBeFocused()
  })
})
