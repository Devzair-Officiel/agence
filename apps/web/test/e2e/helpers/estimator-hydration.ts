import type { Page } from "@playwright/test"

export async function waitForEstimatorHydrated(page: Page): Promise<void> {
  await page.waitForSelector('[data-estimator-hydrated="true"]', { timeout: 20_000 })
}
