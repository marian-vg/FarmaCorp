import { test, expect, type Page } from '@playwright/test';

async function loginAs(page: Page, email: string, password: string): Promise<void> {
  await page.goto('/login');
  await page.locator('input[name="email"]').fill(email);
  await page.locator('input[name="password"]').fill(password);
  await page.locator('[data-test="login-button"]').click();
  await page.waitForURL(/dashboard/, { timeout: 15000 });
}

test.describe('Chat Interno - Widget Flotante', () => {
  test('el usuario puede abrir el chat widget e interactuar', async ({ page }) => {
    // PASO 1: Login
    await loginAs(page, 'test@test.com', 'password');

    // PASO 2: Verificar la existencia del botón flotante (toggle button)
    const toggleBtn = page.locator('button[x-on\\:click*="open = !open"]');
    
    try {
        await expect(toggleBtn).toBeVisible({ timeout: 10000 });
    } catch (error) {
        await page.screenshot({ path: 'playwright-debug-dashboard.png' });
        console.log("Tomada captura de pantalla en playwright-debug-dashboard.png");
        throw error;
    }

    // PASO 3: Click en el botón para abrir el chat
    await toggleBtn.click();

    // PASO 4: Verificar que el popup se abre y muestra "Chat Interno"
    const chatWindow = page.locator('text=Chat Interno');
    await expect(chatWindow).toBeVisible({ timeout: 5000 });

    // PASO 5: Verificar que se puede interactuar. 
    // Si hay chats, intentamos seleccionar uno.
    const emptyStateText = page.locator('text=Selecciona un chat para comenzar a conversar.');
    const hasChats = await emptyStateText.isVisible();

    if (hasChats) {
      // Intentar clickear el primer chat disponible en el sidebar si hay alguno
      const firstChatBtn = page.locator('button[wire\\:click^="selectConversation"]').first();
      const count = await firstChatBtn.count();
      if (count > 0) {
        await firstChatBtn.click();
        
        // Esperar que aparezca el input
        const chatInput = page.locator('input[wire\\:model="body"]');
        await expect(chatInput).toBeVisible({ timeout: 5000 });
        
        // Interactuar con el chat
        await chatInput.fill('Prueba E2E Playwright');
        
        // Botón enviar
        const sendBtn = page.locator('button[type="submit"][wire\\:loading\\.attr="disabled"]');
        await sendBtn.click();
        
        // Verificar que el mensaje enviado aparezca (Optimistic UI o recarga vía backend)
        await expect(page.locator('text=Prueba E2E Playwright').last()).toBeVisible({ timeout: 5000 });
        console.log('✅ Chat interactuado correctamente y mensaje enviado.');
      } else {
        console.log('⚠️ No hay chats en el sidebar para interactuar, pero el widget se abre.');
      }
    } else {
      // Si el input ya está visible porque seleccionó uno por defecto o algo
      const chatInput = page.locator('input[wire\\:model="body"]');
      if (await chatInput.isVisible()) {
         await chatInput.fill('Prueba E2E Playwright');
         const sendBtn = page.locator('button[type="submit"][wire\\:loading\\.attr="disabled"]');
         await sendBtn.click();
         await expect(page.locator('text=Prueba E2E Playwright').last()).toBeVisible({ timeout: 5000 });
         console.log('✅ Chat interactuado correctamente y mensaje enviado.');
      }
    }
    
    // Cerrar el popup (usando el botón superior con icon=x-mark)
    // El x-mark cierra el widget
    const closeBtn = page.locator('button[x-on\\:click="open = false"]');
    if (await closeBtn.isVisible()) {
        await closeBtn.click();
        await expect(chatWindow).not.toBeVisible({ timeout: 5000 });
        console.log('✅ Chat cerrado correctamente.');
    }
  });
});
