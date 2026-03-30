import type { Page } from '@playwright/test'

export const ADMIN = {
  email: 'admin@venom.pl',
  password: 'Admin123!',
}

/** URL backendu: przez Vite proxy w dev, bezpośrednio w CI */
const BASE = process.env.PLAYWRIGHT_BASE_URL ?? 'http://localhost:5173'

/** Loguje użytkownika przez UI i czeka na dashboard */
export async function login(page: Page, email = ADMIN.email, password = ADMIN.password) {
  await page.goto('/login')
  await page.getByPlaceholder('admin@venom.pl').fill(email)
  await page.getByPlaceholder('••••••••').fill(password)
  await page.getByRole('button', { name: /zaloguj się/i }).click()
  await page.waitForURL('**/dashboard', { timeout: 15_000 })
}

/**
 * Loguje się przez API (szybciej niż przez UI) i zapisuje token w localStorage.
 * Używa Vite proxy (`/api/*` → backend:8000), więc backend musi działać.
 */
export async function loginViaApi(page: Page, email = ADMIN.email, password = ADMIN.password) {
  await page.goto('/login')

  // 1. Uzyskaj token
  const loginRes = await page.request.post(`${BASE}/api/auth/login`, {
    data: { email, password },
  })

  if (!loginRes.ok()) {
    throw new Error(
      `Login API failed (${loginRes.status()}): ${await loginRes.text()}. ` +
      'Upewnij się, że backend (symfony server:start) działa na porcie 8000.',
    )
  }

  const loginBody = await loginRes.json()
  const token: string = loginBody.token ?? loginBody.data?.token

  // 2. Pobierz dane zalogowanego użytkownika (/api/auth/me)
  //    Wymagane, żeby authStore.initials (firstName[0] + lastName[0]) nie crashowało
  const meRes = await page.request.get(`${BASE}/api/auth/me`, {
    headers: { Authorization: `Bearer ${token}` },
  })
  const meBody = meRes.ok() ? await meRes.json() : {}
  const userData = meBody.data ?? meBody ?? null

  // 3. Zapisz w localStorage
  await page.evaluate(
    ([t, u]: [string, unknown]) => {
      localStorage.setItem('venom_token', t as string)
      localStorage.setItem('venom_user', JSON.stringify(u))
    },
    [token, userData],
  )

  await page.goto('/dashboard')
  await page.waitForURL('**/dashboard', { timeout: 10_000 })
}

/** Wylogowuje użytkownika */
export async function logout(page: Page) {
  // Przycisk z title="Wyloguj" w górnym pasku (v-btn icon)
  await page.locator('[title="Wyloguj"]').click()
  await page.waitForURL('**/login')
}

/** Pomocnik: token z localStorage aktualnej strony */
export async function getToken(page: Page): Promise<string> {
  const token = await page.evaluate(() => localStorage.getItem('venom_token'))
  if (!token) throw new Error('Brak tokenu — wywołaj loginViaApi() przed tym!')
  return token
}
