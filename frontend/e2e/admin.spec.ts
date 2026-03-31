import { test, expect } from '@playwright/test'
import { loginViaApi, getToken } from './helpers/auth'

const unique = (suffix: string) => `E2E-${suffix}-${Date.now()}`
const API = (path: string) => `${process.env.PLAYWRIGHT_BASE_URL ?? 'http://127.0.0.1:5173'}${path}`

test.describe('Użytkownicy systemu CRM', () => {
  let adminRoleId: number

  test.beforeEach(async ({ page }) => {
    await loginViaApi(page)

    // Pobierz roleId "Administratora" przez API (potrzebne do tworzenia usera)
    const token = await getToken(page)
    const res = await page.request.get(API('/api/access/roles'), {
      headers: { Authorization: `Bearer ${token}` },
    })
    const roles: Array<{ id: number; name: string }> = (await res.json()).data ?? []
    adminRoleId = roles.find(r => r.name === 'Administrator')?.id ?? roles[0]?.id

    await page.goto('/admin', { waitUntil: 'domcontentloaded' })
    await page.waitForURL('**/admin')
  })

  test('strona użytkowników jest dostępna z sidebar', async ({ page }) => {
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' })
    // Sidebar: v-list-item z :to="/admin" renderuje jako <a href="/admin">
    await page.locator('a[href="/admin"]').first().click()
    await page.waitForURL('**/admin')
    await page.waitForLoadState('networkidle')
    await expect(page.locator('h2.page-heading')).toContainText('Użytkownicy', { timeout: 8_000 })
  })

  test('tabela zawiera co najmniej konto admina', async ({ page }) => {
    await expect(page.getByText('admin@venom.pl')).toBeVisible({ timeout: 8_000 })
  })

  test('dodanie nowego użytkownika CRM', async ({ page }) => {
    const email     = `e2e.user.${Date.now()}@test.pl`
    const firstName = unique('Jan')
    const lastName  = 'Testowy'

    await page.getByRole('button', { name: /nowy użytkownik/i }).click()

    const dialog = page.locator('.v-dialog:visible')
    await expect(dialog).toBeVisible({ timeout: 5_000 })

    // Imię, Nazwisko, E-mail — kolejne input[type!=password]
    const inputs = dialog.locator('input[type="text"], input:not([type])')
    await inputs.nth(0).fill(firstName)
    await inputs.nth(1).fill(lastName)

    const emailInput = dialog.locator('input[type="email"]')
    await emailInput.fill(email)

    // Hasło
    const pwdInput = dialog.locator('input[type="password"]')
    await pwdInput.fill('SecurePass123!')

    // Wybierz rolę przez kliknięcie v-select
    await dialog.locator('.v-select').click()
    const roleOption = page.locator('.v-overlay--active .v-list-item').first()
    await expect(roleOption).toBeVisible()
    await roleOption.click()

    await dialog.getByRole('button', { name: /zapisz/i }).click()
    await expect(dialog).not.toBeVisible({ timeout: 5_000 })
    await expect(page.getByText(email)).toBeVisible({ timeout: 8_000 })
  })

  test('anulowanie formularza użytkownika', async ({ page }) => {
    await page.getByRole('button', { name: /nowy użytkownik/i }).click()
    const dialog = page.locator('.v-dialog:visible')
    await expect(dialog).toBeVisible()
    await dialog.getByRole('button', { name: /anuluj/i }).click()
    await expect(dialog).not.toBeVisible({ timeout: 5_000 })
  })

  test('usunięcie użytkownika CRM', async ({ page }) => {
    const email = `e2e.delete.${Date.now()}@test.pl`
    const token = await getToken(page)

    // Utwórz użytkownika przez API
    await page.request.post(API('/api/admin/users'), {
      data: {
        email,
        password:  'TmpPass123!',
        firstName: 'DoUsuniecia',
        lastName:  'Test',
        roleId:    adminRoleId,
        isActive:  true,
      },
      headers: { Authorization: `Bearer ${token}` },
    })

    await page.reload()
    await page.waitForLoadState('networkidle')

    const row = page.locator('tr', { hasText: email })
    await expect(row).toBeVisible({ timeout: 8_000 })

    // Kliknij przycisk usuwania (ostatni przycisk w wierszu)
    await row.getByRole('button').last().click()

    const confirmDialog = page.locator('.v-dialog:visible')
    await expect(confirmDialog).toBeVisible()
    await confirmDialog.getByRole('button', { name: /usuń|tak|potwierdź/i }).click()

    await expect(confirmDialog).not.toBeVisible({ timeout: 5_000 })
    await expect(page.getByText(email)).not.toBeVisible({ timeout: 8_000 })
  })
})

test.describe('Uprawnienia / Role', () => {
  test.beforeEach(async ({ page }) => {
    await loginViaApi(page)
    await page.goto('/access', { waitUntil: 'domcontentloaded' })
    await page.waitForURL('**/access')
  })

  test('strona ról jest dostępna z sidebar', async ({ page }) => {
    await page.goto('/dashboard', { waitUntil: 'domcontentloaded' })
    // Sidebar: v-list-item z :to="/access" renderuje jako <a href="/access">
    await page.locator('a[href="/access"]').first().click()
    await page.waitForURL('**/access')
    await page.waitForLoadState('networkidle')
    await expect(page.locator('h2.page-heading')).toContainText('Uprawnienia', { timeout: 8_000 })
  })

  test('domyślne role systemowe są widoczne', async ({ page }) => {
    await expect(page.getByText('Administrator')).toBeVisible({ timeout: 8_000 })
    await expect(page.getByText('Handlowiec')).toBeVisible()
  })

  test('dodanie nowej roli', async ({ page }) => {
    const roleName = unique('Rola')

    await page.getByRole('button', { name: /nowa rola/i }).click()

    const dialog = page.locator('.v-dialog:visible')
    await expect(dialog).toBeVisible({ timeout: 5_000 })

    // Nazwa roli — pierwszy input
    await dialog.locator('input').first().fill(roleName)
    // Opcjonalny opis — textarea
    await dialog.locator('textarea').fill('Rola stworzona przez test E2E')

    await dialog.getByRole('button', { name: /zapisz/i }).click()
    await expect(dialog).not.toBeVisible({ timeout: 5_000 })
    await expect(page.getByText(roleName)).toBeVisible({ timeout: 8_000 })
  })

  test('edycja istniejącej roli', async ({ page }) => {
    const originalName = unique('RolaEdit')
    const updatedName  = unique('RolaEdited')
    const token = await getToken(page)

    // Utwórz rolę przez API
    await page.request.post(API('/api/access/roles'), {
      data: { name: originalName, description: 'Opis przed edycją' },
      headers: { Authorization: `Bearer ${token}` },
    })

    await page.reload()
    await page.waitForLoadState('networkidle')

    const card = page.locator('.v-card', { hasText: originalName })
    await expect(card).toBeVisible({ timeout: 8_000 })

    // Kliknij przycisk edycji (pierwszy icon-button w karcie = ołówek)
    await card.getByRole('button').nth(0).click()

    const dialog = page.locator('.v-dialog:visible')
    await expect(dialog).toBeVisible()

    const nameInput = dialog.locator('input').first()
    await nameInput.clear()
    await nameInput.fill(updatedName)
    await dialog.getByRole('button', { name: /zapisz/i }).click()

    await expect(dialog).not.toBeVisible({ timeout: 5_000 })
    await expect(page.getByText(updatedName)).toBeVisible({ timeout: 8_000 })
  })

  test('usunięcie roli bez przypisanych użytkowników', async ({ page }) => {
    const roleName = unique('RolaDel')
    const token = await getToken(page)

    await page.request.post(API('/api/access/roles'), {
      data: { name: roleName },
      headers: { Authorization: `Bearer ${token}` },
    })

    await page.reload()
    await page.waitForLoadState('networkidle')

    const card = page.locator('.v-card', { hasText: roleName })
    await expect(card).toBeVisible({ timeout: 8_000 })

    await card.getByRole('button').last().click()

    const confirmDialog = page.locator('.v-dialog:visible')
    await expect(confirmDialog).toBeVisible()
    await confirmDialog.getByRole('button', { name: /usuń|tak|potwierdź/i }).click()

    await expect(confirmDialog).not.toBeVisible({ timeout: 5_000 })
    await expect(page.getByText(roleName)).not.toBeVisible({ timeout: 8_000 })
  })
})
