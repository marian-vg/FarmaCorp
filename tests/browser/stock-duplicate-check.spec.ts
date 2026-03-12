import { test, expect } from '@playwright/test';

test.describe('Stock Ingreso and Kardex E2E flow', () => {
    test('creates batches for the same medicine and verifies no duplication in Kardex', async ({ page }) => {
        // 1. Iniciar sesión como administrador (asumiendo que admin@farma.corp existe y su password es password)
        await page.goto('/login');
        await page.fill('input[name="email"]', 'admin@farma.corp'); // Replace with actual default admin
        await page.fill('input[name="password"]', 'password');
        await page.click('button[type="submit"]');
        
        // Wait for dashboard to load
        await expect(page).toHaveURL(/.*dashboard/);

        // 2. Navegar a modulo de Ingreso de Stock
        await page.goto('/admin/stock/ingreso');

        // Buscar un medicamento (e.g. Omeprazol)
        // O simplemente clickeamos el botón "Registrar Ingreso" del primer medicamento de la lista
        const registerButton = page.getByRole('button', { name: /Registrar Ingreso/i }).first();
        await expect(registerButton).toBeVisible();
        await registerButton.click();

        // Llenar formulario modal de Ingreso 1
        await page.getByLabel('Número de Lote').fill('BATCH-TEST-001');
        await page.getByLabel('Fecha de Vencimiento').fill('31-12-2027');
        await page.getByLabel('Cantidad Recibida').fill('10');
        await page.getByLabel('Precio de Compra').fill('5.00');
        
        // Confirmar ingreso
        await page.getByRole('button', { name: 'Registrar Entrada' }).click();

        // Esperar la confirmación y que el modal desaparezca
        await expect(page.getByText('Stock registrado exitosamente')).toBeVisible();

        // Repetir proceso para el MISMO medicamento, segundo lote
        await registerButton.click();
        await page.getByLabel('Número de Lote').fill('BATCH-TEST-002');
        await page.getByLabel('Fecha de Vencimiento').fill('31-12-2028');
        await page.getByLabel('Cantidad Recibida').fill('20');
        await page.getByLabel('Precio de Compra').fill('5.50');
        await page.getByRole('button', { name: 'Registrar Entrada' }).click();
        await expect(page.getByText('Stock registrado exitosamente')).toBeVisible();

        // 3. Navegar a Kardex (Historial de Movimientos)
        await page.goto('/admin/stock/historial');

        // 4. Validar que las transacciones BATCH-TEST-001 y BATCH-TEST-002 aparecen exactamente 1 vez
        // y no están duplicadas debido al error del leftJoin
        await page.fill('input[placeholder*="Buscar movimientos"]', 'BATCH-TEST');
        
        // Playwright debounce delay for wire:model.live.debounce.300ms
        await page.waitForTimeout(1000);

        // Contar el número de filas que contienen la palabra BATCH-TEST
        const rows = page.locator('table tbody tr', { hasText: 'BATCH-TEST' });
        
        // El conteo estricto debe ser 2 (un ingreso por cada lote)
        const rowCount = await rows.count();
        expect(rowCount).toBe(2);

        // Validar explícitamente contenido de cada fila
        await expect(rows.filter({ hasText: 'BATCH-TEST-001' })).toHaveCount(1);
        await expect(rows.filter({ hasText: 'BATCH-TEST-002' })).toHaveCount(1);
    });
});
