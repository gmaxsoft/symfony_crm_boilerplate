import { defineConfig, devices } from '@playwright/test'

/**
 * Playwright E2E — VENOM CRM
 *
 * Wymagania przed uruchomieniem:
 *   - backend: symfony server:start (port 8000)
 *   - frontend: uruchamiany automatycznie przez webServer
 *
 * Uruchomienie:
 *   npx playwright test
 *   npx playwright test --ui           (tryb interaktywny)
 *   npx playwright test --headed       (widoczna przeglądarka)
 */
export default defineConfig({
  testDir: './e2e',
  fullyParallel: false,          // testy E2E są sekwencyjne (wspólna baza)
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: 1,                    // jeden worker = brak konfliktów na bazie
  reporter: process.env.CI
    ? [['github'], ['html', { open: 'never' }]]
    : [['list'], ['html', { open: 'on-failure' }]],

  use: {
    baseURL: 'http://localhost:5173',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'off',
    // Vuetify potrzebuje chwili na animacje
    actionTimeout: 10_000,
    navigationTimeout: 15_000,
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],

  // Uruchamia Vite dev server przed testami (backend musi działać osobno)
  webServer: {
    command: 'npm run dev',
    url: 'http://localhost:5173',
    reuseExistingServer: !process.env.CI,
    timeout: 30_000,
    env: {
      VITE_API_URL: '',           // proxy do backendu na :8000
    },
  },
})
