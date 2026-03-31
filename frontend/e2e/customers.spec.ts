import { test, expect } from '@playwright/test'
import { loginViaApi, getToken } from './helpers/auth'

/** Unikalna nazwa kontrahenta dla każdego uruchomienia testów */
const unique = (suffix: string) => `E2E-Test-${suffix}-${Date.now()}`

/** URL przez Vite proxy */
const API = (path: string) => `${process.env.PLAYWRIGHT_BASE_URL ?? 'http://127.0.0.1:5173'}${path}`

test.describe('Kontrahenci', () => {
  // Logujemy raz przed wszystkimi testami w tym describe
  test.beforeEach(async ({ page }) => {
    await loginViaApi(page)
    await page.goto('/customers')
    await page.waitForURL('**/customers')
  })

  test('strona kontrahentów jest dostępna z sidebar', async ({ page }) => {
    await page.goto('/dashboard')
    // Sidebar: v-list-item z :to="/customers" renderuje jako <a href="/customers">
    await page.locator('a[href="/customers"]').first().click()
    await page.waitForURL('**/customers')
    await page.waitForLoadState('networkidle')
    await expect(page.locator('h2.page-heading')).toContainText('Kontrahenci', { timeout: 8_000 })
    await expect(page.getByRole('button', { name: /nowy kontrahent/i })).toBeVisible()
  })

  test('dodanie nowego kontrahenta (wymagane pola)', async ({ page }) => {
    const name = unique('ACME')

    await page.getByRole('button', { name: /nowy kontrahent/i }).click()

    // Dialog powinien się otworzyć
    const dialog = page.locator('.v-dialog:visible')
    await expect(dialog).toBeVisible({ timeout: 5_000 })

    // Wypełnij nazwę (jedyne wymagane pole)
    await dialog.locator('input').first().fill(name)
    await dialog.getByRole('button', { name: /zapisz/i }).click()

    // Dialog powinien się zamknąć i kontrahent pojawić w tabeli
    await expect(dialog).not.toBeVisible({ timeout: 5_000 })
    await expect(page.getByText(name)).toBeVisible({ timeout: 8_000 })
  })

  test('dodanie kontrahenta z pełnymi danymi', async ({ page }) => {
    const name = unique('FullData')

    await page.getByRole('button', { name: /nowy kontrahent/i }).click()
    const dialog = page.locator('.v-dialog:visible')
    await expect(dialog).toBeVisible()

    // Wypełnij wszystkie dostępne pola tekstowe w kolejności
    const inputs = dialog.locator('input[type="text"], input:not([type]), input[type="email"]')

    await inputs.nth(0).fill(name)           // Nazwa
    await inputs.nth(1).fill('test@e2e.pl')  // E-mail
    await inputs.nth(2).fill('+48 100 200 300') // Telefon
    await inputs.nth(3).fill('1234567890')   // NIP

    await dialog.getByRole('button', { name: /zapisz/i }).click()
    await expect(dialog).not.toBeVisible({ timeout: 5_000 })
    await expect(page.getByText(name)).toBeVisible({ timeout: 8_000 })
  })

  test('anulowanie formularza nie dodaje kontrahenta', async ({ page }) => {
    const name = unique('Cancelled')

    await page.getByRole('button', { name: /nowy kontrahent/i }).click()
    const dialog = page.locator('.v-dialog:visible')
    await expect(dialog).toBeVisible()

    await dialog.locator('input').first().fill(name)
    await dialog.getByRole('button', { name: /anuluj/i }).click()

    await expect(dialog).not.toBeVisible({ timeout: 5_000 })
    await expect(page.getByText(name)).not.toBeVisible()
  })

  test('edycja istniejącego kontrahenta', async ({ page }) => {
    const originalName = unique('EditBefore')
    const updatedName  = unique('EditAfter')

    // Stwórz kontrahenta przez API (szybciej niż przez UI)
    const token = await getToken(page)
    await page.request.post(API('/api/customers'), {
      data: { name: originalName },
      headers: { Authorization: `Bearer ${token}` },
    })

    await page.reload()
    await page.waitForLoadState('networkidle')

    // Znajdź wiersz z kontrahentem
    const row = page.locator('tr', { hasText: originalName })
    await expect(row).toBeVisible({ timeout: 8_000 })

    // Kliknij przycisk edycji (pierwszy icon-button w wierszu = ołówek)
    await row.getByRole('button').nth(0).click()

    const dialog = page.locator('.v-dialog:visible')
    await expect(dialog).toBeVisible()

    const nameInput = dialog.locator('input').first()
    await nameInput.clear()
    await nameInput.fill(updatedName)
    await dialog.getByRole('button', { name: /zapisz/i }).click()

    await expect(dialog).not.toBeVisible({ timeout: 5_000 })
    await expect(page.getByText(updatedName)).toBeVisible({ timeout: 8_000 })
    await expect(page.getByText(originalName)).not.toBeVisible()
  })

  test('usunięcie kontrahenta', async ({ page }) => {
    const name = unique('ToDelete')

    // Utwórz kontrahenta przez API
    const token = await getToken(page)
    await page.request.post(API('/api/customers'), {
      data: { name },
      headers: { Authorization: `Bearer ${token}` },
    })

    await page.reload()
    await page.waitForLoadState('networkidle')

    const row = page.locator('tr', { hasText: name })
    await expect(row).toBeVisible({ timeout: 8_000 })

    // Kliknij przycisk usuwania
    await row.getByRole('button').last().click()

    // Dialog potwierdzenia
    const confirmDialog = page.locator('.v-dialog:visible')
    await expect(confirmDialog).toBeVisible()
    await confirmDialog.getByRole('button', { name: /usuń|tak|potwierdź/i }).click()

    await expect(confirmDialog).not.toBeVisible({ timeout: 5_000 })
    await expect(page.getByText(name)).not.toBeVisible({ timeout: 8_000 })
  })

  test('wyszukiwanie kontrahenta', async ({ page }) => {
    const name = unique('SearchTarget')

    // Utwórz kontrahenta przez API
    const token = await getToken(page)
    await page.request.post(API('/api/customers'), {
      data: { name },
      headers: { Authorization: `Bearer ${token}` },
    })

    await page.reload()
    await page.waitForLoadState('networkidle')

    const searchBox = page.getByPlaceholder(/szukaj/i)
    await searchBox.fill('SearchTarget')

    await expect(page.getByText(name)).toBeVisible({ timeout: 8_000 })
  })
})
