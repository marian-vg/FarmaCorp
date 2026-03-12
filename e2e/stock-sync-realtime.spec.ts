import { test, expect, type Page, type BrowserContext } from '@playwright/test';

/**
 * E2E Test: Sincronización de Stock en Tiempo Real (WebSocket)
 *
 * Verifica que cuando un admin registra un ingreso de stock,
 * el POS de un empleado se actualiza automáticamente SIN recargar.
 *
 * REQUISITOS PREVIOS:
 *   - php artisan reverb:start
 *   - php artisan queue:work
 *   - http://farmacorp.test accesible (Laravel Herd)
 */

async function loginAs(page: Page, email: string, password: string): Promise<void> {
  await page.goto('/login');
  await page.locator('input[name="email"]').fill(email);
  await page.locator('input[name="password"]').fill(password);
  await page.locator('[data-test="login-button"]').click();
  await page.waitForURL(/dashboard/, { timeout: 15000 });
}

test.describe('Stock Sync en Tiempo Real via WebSocket', () => {
  test.describe.configure({ mode: 'serial' });
  test.setTimeout(90000);

  let adminContext: BrowserContext;
  let employeeContext: BrowserContext;
  let adminPage: Page;
  let posPage: Page;

  test.beforeAll(async ({ browser }) => {
    adminContext = await browser.newContext();
    employeeContext = await browser.newContext();
    adminPage = await adminContext.newPage();
    posPage = await employeeContext.newPage();
  });

  test.afterAll(async () => {
    await adminContext.close();
    await employeeContext.close();
  });

  test('el POS actualiza el stock en tiempo real cuando admin registra un ingreso', async () => {
    // PASO 1: Login de ambos usuarios
    await loginAs(adminPage, 'admin@admin.com', 'password');
    await loginAs(posPage, 'test@test.com', 'password');

    // PASO 2: Admin va a Stock Ingresos PRIMERO — capturar el nombre del primer medicamento
    await adminPage.goto('/admin/stock/ingresos');
    await adminPage.waitForLoadState('networkidle');

    // Capturar el nombre del primer medicamento en la tabla del admin
    const firstMedicineCell = adminPage.locator('table tbody tr').first().locator('td').first();
    await expect(firstMedicineCell).toBeVisible({ timeout: 10000 });
    const adminMedicineName = (await firstMedicineCell.textContent())?.trim() ?? '';
    console.log(`💊 Medicamento en admin: "${adminMedicineName}"`);

    // PASO 3: Empleado abre el POS y espera WebSocket
    await posPage.goto('/user/ventas');
    await posPage.waitForLoadState('networkidle');
    await posPage.waitForTimeout(4000); // Esperar conexión WebSocket

    // Buscar el mismo medicamento en el POS y leer su stock
    // Primero verificar que hay badges de stock
    const allBadges = posPage.locator('text=/\\d+ disponibles/');
    const badgeCount = await allBadges.count();

    if (badgeCount === 0) {
      test.skip(true, 'No hay medicamentos con stock visible en el POS');
      return;
    }

    const firstBadge = allBadges.first();
    const textBefore = await firstBadge.textContent();
    const stockBefore = parseInt(textBefore?.match(/(\d+)/)?.[1] ?? '0', 10);
    console.log(`📊 Stock ANTES: ${stockBefore}`);

    // PASO 4: Admin registra el ingreso
    const ingressButton = adminPage.getByRole('button', { name: 'Ingresar Lote' }).first();
    await ingressButton.click();
    await adminPage.waitForTimeout(1500);

    const qtyToAdd = 25;
    const futureDate = new Date();
    futureDate.setFullYear(futureDate.getFullYear() + 1);
    const formattedDate = futureDate.toISOString().split('T')[0];

    await adminPage.locator('input[wire\\:model="batch_number"]').fill(`E2E-${Date.now()}`);
    await adminPage.locator('input[wire\\:model="expiration_date"]').fill(formattedDate);
    await adminPage.locator('input[wire\\:model="quantity_received"]').fill(String(qtyToAdd));
    await adminPage.locator('input[wire\\:model="minimum_stock"]').fill('5');

    await adminPage.getByRole('button', { name: 'Confirmar Ingreso' }).click();

    // Esperar a que el admin vea la confirmación (el toast Livewire)
    await expect(
      adminPage.locator('text=/registrado con éxito/i').first()
    ).toBeVisible({ timeout: 15000 });
    console.log(`✅ Admin confirmó el ingreso de +${qtyToAdd}`);

    // PASO 5: Verificar que el POS actualiza SIN recarga
    const expectedStock = stockBefore + qtyToAdd;
    console.log(`🎯 Stock ESPERADO: ${expectedStock}`);

    await expect(async () => {
      const currentText = await posPage.locator('text=/\\d+ disponibles/').first().textContent();
      const currentStock = parseInt(currentText?.match(/(\d+)/)?.[1] ?? '0', 10);
      console.log(`   🔄 Stock POS: ${currentStock}`);
      expect(currentStock).toBe(expectedStock);
    }).toPass({ timeout: 30000, intervals: [2000, 3000, 5000] });

    console.log(`✅ WebSocket sync OK: ${expectedStock} disponibles`);
  });
});
