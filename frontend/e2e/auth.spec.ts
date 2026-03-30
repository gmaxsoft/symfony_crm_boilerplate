import { test, expect } from '@playwright/test'
import { ADMIN, login, loginViaApi, logout } from './helpers/auth'

test.describe('Autoryzacja', () => {
  test('przekierowanie z / na /login gdy brak tokenu', async ({ page }) => {
    await page.goto('/')
    await page.waitForURL('**/login')
    await expect(page).toHaveURL(/\/login/)
  })

  test('formularz logowania jest widoczny', async ({ page }) => {
    await page.goto('/login')
    await expect(page.getByPlaceholder('admin@venom.pl')).toBeVisible()
    await expect(page.getByPlaceholder('••••••••')).toBeVisible()
    await expect(page.getByRole('button', { name: /zaloguj się/i })).toBeVisible()
  })

  test('błąd przy nieprawidłowych danych', async ({ page }) => {
    await page.goto('/login')
    await page.getByPlaceholder('admin@venom.pl').fill('wrong@user.pl')
    await page.getByPlaceholder('••••••••').fill('WrongPassword!')
    await page.getByRole('button', { name: /zaloguj się/i }).click()

    // Alert z błędem powinien się pojawić (nie następuje przekierowanie)
    await expect(page.locator('.v-alert')).toBeVisible({ timeout: 8_000 })
    await expect(page).toHaveURL(/\/login/)
  })

  test('poprawne logowanie przekierowuje na dashboard', async ({ page }) => {
    await login(page)
    await expect(page).toHaveURL(/\/dashboard/)
    // Sidebar z nazwą systemu
    await expect(page.getByText('VENOM CRM')).toBeVisible()
  })

  test('po zalogowaniu token jest w localStorage', async ({ page }) => {
    await login(page)
    const token = await page.evaluate(() => localStorage.getItem('venom_token'))
    expect(token).not.toBeNull()
    expect(token!.length).toBeGreaterThan(10)
  })

  test('wylogowanie usuwa token i przekierowuje na /login', async ({ page }) => {
    await login(page)
    await logout(page)

    await expect(page).toHaveURL(/\/login/)
    const token = await page.evaluate(() => localStorage.getItem('venom_token'))
    expect(token).toBeNull()
  })

  test('zalogowany użytkownik jest przekierowywany z /login na /dashboard', async ({ page }) => {
    await loginViaApi(page)
    await page.goto('/login')
    await page.waitForURL('**/dashboard')
    await expect(page).toHaveURL(/\/dashboard/)
  })

  test('wygaśnięty/usunięty token przekierowuje na /login', async ({ page }) => {
    await loginViaApi(page)
    // Symulujemy wygaśnięcie sesji przez usunięcie tokenu
    await page.evaluate(() => localStorage.removeItem('venom_token'))
    await page.goto('/customers')
    await page.waitForURL('**/login')
    await expect(page).toHaveURL(/\/login/)
  })
})
