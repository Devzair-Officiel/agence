import { defineConfig, devices } from '@playwright/test'

// Configuration Playwright — Devzair, apps/web
//
// Deux modes :
//   - local : Nuxt dev suffit (reuseExistingServer si déjà démarré).
//   - CI : Nuxt buildé puis servi via `.output/server/index.mjs` — plus
//     rapide et plus proche du comportement de production que `nuxt dev`.
//
// `baseURL` peut être surchargé par `PLAYWRIGHT_BASE_URL` (utile pour pointer
// sur un service Docker déjà en écoute sur un autre port).

const isCI = !!process.env.CI
const baseURL = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:3000'

export default defineConfig({
  testDir: './test/e2e',
  fullyParallel: true,
  forbidOnly: isCI,
  retries: isCI ? 1 : 0,
  workers: isCI ? 1 : undefined,
  timeout: 30_000,
  expect: {
    timeout: 5_000,
  },
  reporter: isCI ? [['github'], ['html', { open: 'never' }]] : 'list',
  use: {
    baseURL,
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'off',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
  webServer: process.env.PLAYWRIGHT_BASE_URL
    ? undefined
    : {
        command: isCI
          ? 'npm run build && node .output/server/index.mjs'
          : 'npm run dev',
        url: baseURL,
        reuseExistingServer: !isCI,
        timeout: 180_000,
        env: {
          NUXT_PORT: '3000',
          HOST: '0.0.0.0',
          PORT: '3000',
        },
      },
})
