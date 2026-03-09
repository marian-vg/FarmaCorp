import { test, expect } from '@playwright/test';

test('Verify Toast Bounding Box and Position', async ({ page }) => {
    // Navigate to our isolated test route
    await page.goto('http://farmacorp.test/test-toast');
    
    // Trigger the toast event
    await page.click('#trigger-toast');
    
    // Wait for the toast to become visible (Alpine transition)
    const toast = page.locator('div[x-show="show"]').first();
    await expect(toast).toBeVisible();
    
    // Allow Alpine transition to finish (approx 300ms)
    await page.waitForTimeout(500);
    
    // Capture bounding box
    const box = await toast.boundingBox();
    console.log('Toast Bounding Box:', box);
    
    const viewport = page.viewportSize();
    console.log('Viewport Size:', viewport);
    
    // Math checks
    const viewportCenterX = viewport.width / 2;
    const toastCenterX = box.x + (box.width / 2);
    
    console.log('Expected Center X:', viewportCenterX);
    console.log('Actual Toast Center X:', toastCenterX);
    
    // Assert centering
    expect(Math.abs(viewportCenterX - toastCenterX)).toBeLessThan(10);
});
