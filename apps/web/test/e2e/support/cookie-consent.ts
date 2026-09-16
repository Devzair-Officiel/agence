import type { Page } from '@playwright/test'
import { expect } from '@playwright/test'

// Ouvre le gestionnaire de préférences de cookies de manière déterministe.
//
// Même race condition que pour la navigation mobile (DEV-045) : Playwright peut
// dispatcher le clic avant que Vue n'ait attaché le listener @click sur le
// bouton footer. `data-hydrated="true"` posé par SiteFooter.vue dans `onMounted`
// garantit que l'hydratation est terminée avant le clic. La boucle de retry
// est une ceinture supplémentaire en cas de rendu différé.
export async function openCookieConsentManager(page: Page): Promise<void> {
  const button = page.locator('footer button', { hasText: 'Gérer mes cookies' })
  await button.waitFor({ state: 'visible' })
  await expect(button).toHaveAttribute('data-hydrated', 'true', { timeout: 30_000 })
  await expect(async () => {
    await button.click()
    await expect(page.locator('[role="dialog"]')).toBeVisible({ timeout: 500 })
  }).toPass({ timeout: 10_000, intervals: [100, 200, 500] })
}
