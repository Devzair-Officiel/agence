import { defineConfig, devices } from '@playwright/test'

// Configuration Playwright — Devzair, apps/web
//
// Périmètre volontairement minimal en Phase 2 : un unique smoke test qui
// ouvre `/design-preview` et vérifie qu'aucun avertissement vue-router ou
// erreur console n'apparaît. On étendra à d'autres pages quand elles
// existeront réellement (roadmap Phase 3+).
//
// Le webServer démarre `npm run dev` sur le port 3000 (celui du conteneur).
// En local hors Docker, cela suffit ; en Docker Compose, il faut lancer les
// tests depuis le service `web` (cf. README).

export default defineConfig({
  testDir: './test/e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: process.env.CI ? [['github'], ['html', { open: 'never' }]] : 'list',
  use: {
    baseURL: 'http://localhost:3000',
    trace: 'on-first-retry',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
  webServer: {
    command: 'npm run dev',
    url: 'http://localhost:3000',
    reuseExistingServer: !process.env.CI,
    timeout: 120_000,
  },
})
