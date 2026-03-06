import { chromium } from 'playwright';

(async () => {
    try {
        const browser = await chromium.launch();
        const page = await browser.newPage();
        
        console.log('Navigating to test-toast...');
        await page.goto('http://farmacorp.test/test-toast');
        
        console.log('Triggering toast...');
        await page.click('#trigger-toast');
        
        // Wait for Alpine's transition to complete
        await page.waitForTimeout(500);
        
        const element = await page.$('.fixed.z-50');
        if (element) {
            const box = await element.boundingBox();
            console.log('Toast Bounding Box:', box);
            
            const viewport = page.viewportSize();
            console.log('Viewport Size:', viewport);
            
            const expectedCenterX = viewport.width / 2;
            const actualCenterX = box.x + (box.width / 2);
            
            console.log('Expected Center X:', expectedCenterX);
            console.log('Actual Center X:', actualCenterX);
            
            if (Math.abs(expectedCenterX - actualCenterX) > 10) {
                console.log('TEST RESULT: FAILED. The toast is NOT centered. It snapped to X:', box.x, 'which is aligned to the left side!');
            } else {
                console.log('TEST RESULT: PASSED. The toast is properly centered.');
            }
        } else {
            console.log('Toast element not found.');
        }
        
        await browser.close();
    } catch (error) {
        console.error('Test script error:', error);
        process.exit(1);
    }
})();
