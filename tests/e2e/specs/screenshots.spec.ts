import { test } from '@playwright/test';
import { join } from 'path';

const PLUGIN_URL = '/wp-admin/admin.php?page=easycommerce-fakerpress';
const SCREENSHOT_DIR = join(__dirname, '..', '..', '..', '.wordpress-org');

/**
 * Hide WordPress admin chrome (admin bar + sidebar menu) so only the
 * plugin UI is visible in the screenshots.
 */
async function hideWpChrome(page: import('@playwright/test').Page) {
  await page.addStyleTag({
    content: `
      #adminmenuwrap, #adminmenuback, #adminmenu,
      #wpadminbar, .wp-toolbar,
      #wpbody-content { margin-left: 0 !important; }
      #adminmenumain { display: none !important; }
      #wpadminbar { display: none !important; }
      html.wp-toolbar { padding-top: 0 !important; }
      #wpcontent, #wpfooter { margin-left: 0 !important; }
    `,
  });
  // Allow layout to settle after injection
  await page.waitForTimeout(500);
}

test.describe('Screenshots', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
  });

  test('1. Dashboard', async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/`);
    await page.getByTestId('app-shell').waitFor();
    await page.getByTestId('generator-grid').waitFor();
    await hideWpChrome(page);
    await page.waitForTimeout(1000);
    await page.screenshot({
      path: join(SCREENSHOT_DIR, 'screenshot-1.png'),
      fullPage: true,
    });
  });

  test('2. Generator page - Products', async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/products`);
    await page.getByTestId('generator-runbar').waitFor();
    await page.getByTestId('preview-table').waitFor();
    await hideWpChrome(page);
    await page.waitForTimeout(1000);
    await page.screenshot({
      path: join(SCREENSHOT_DIR, 'screenshot-2.png'),
      fullPage: true,
    });
  });

  test('3. Live preview with shuffle', async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/products`);
    await page.getByTestId('generator-runbar').waitFor();
    await page.getByTestId('preview-table').waitFor();
    await hideWpChrome(page);
    const shuffleBtn = page.getByTestId('shuffle-btn');
    if (await shuffleBtn.isVisible()) {
      await shuffleBtn.click();
      await page.waitForTimeout(500);
    }
    await page.screenshot({
      path: join(SCREENSHOT_DIR, 'screenshot-3.png'),
      fullPage: true,
    });
  });

  test('4. Command palette', async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/`);
    await page.getByTestId('app-shell').waitFor();
    await hideWpChrome(page);
    await page.keyboard.press('Meta+k');
    await page.getByTestId('command-palette').waitFor();
    await page.waitForTimeout(500);
    await page.screenshot({
      path: join(SCREENSHOT_DIR, 'screenshot-4.png'),
      fullPage: false,
    });
    await page.keyboard.press('Escape');
  });

  test('5. Batch queue', async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/products`);
    await page.getByTestId('generator-runbar').waitFor();

    await page.getByTestId('add-to-batch').click();
    await page.getByTestId('batch-chip').waitFor({ timeout: 5000 });

    await page.goto(`${PLUGIN_URL}#/generator/customers`);
    await page.getByTestId('generator-runbar').waitFor();
    await page.getByTestId('add-to-batch').click();

    await page.goto(`${PLUGIN_URL}#/generator/orders`);
    await page.getByTestId('generator-runbar').waitFor();
    await page.getByTestId('add-to-batch').click();

    await hideWpChrome(page);
    await page.getByTestId('batch-chip').waitFor({ timeout: 5000 });
    await page.getByTestId('batch-chip').click();
    await page.getByTestId('batch-tray').waitFor();
    await page.waitForTimeout(500);
    await page.screenshot({
      path: join(SCREENSHOT_DIR, 'screenshot-5.png'),
      fullPage: false,
    });
  });

  test('6. Tweaks panel', async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/`);
    await page.getByTestId('app-shell').waitFor();
    await hideWpChrome(page);
    await page.getByTestId('tweaks-button').click();
    await page.getByTestId('tweaks-panel').waitFor();
    await page.waitForTimeout(500);
    await page.screenshot({
      path: join(SCREENSHOT_DIR, 'screenshot-6.png'),
      fullPage: false,
    });
  });

  test('7. Dark mode', async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/`);
    await page.getByTestId('app-shell').waitFor();
    await hideWpChrome(page);
    await page.getByTestId('tweaks-button').click();
    await page.getByTestId('tweaks-panel').waitFor();
    await page.getByTestId('tweaks-panel').getByRole('button', { name: /Dark/i }).click();
    await page.waitForTimeout(500);
    await page.getByTestId('tweaks-panel').getByRole('button', { name: /Close/i }).click();
    await page.waitForTimeout(500);
    await page.screenshot({
      path: join(SCREENSHOT_DIR, 'screenshot-7.png'),
      fullPage: true,
    });
    // Reset
    await page.getByTestId('tweaks-button').click();
    await page.getByTestId('tweaks-panel').waitFor();
    await page.getByTestId('tweaks-panel').getByRole('button', { name: /Light/i }).click();
    await page.getByTestId('tweaks-panel').getByRole('button', { name: /Close/i }).click();
  });

  test('8. Settings page', async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/settings`);
    await page.getByTestId('app-shell').waitFor();
    await hideWpChrome(page);
    await page.waitForTimeout(1000);
    await page.screenshot({
      path: join(SCREENSHOT_DIR, 'screenshot-8.png'),
      fullPage: true,
    });
  });

  test('9. Our Plugins page', async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/plugins`);
    await page.getByTestId('app-shell').waitFor();
    await hideWpChrome(page);
    await page.waitForTimeout(3000);
    await page.screenshot({
      path: join(SCREENSHOT_DIR, 'screenshot-9.png'),
      fullPage: true,
    });
  });

  test('10. Product Reviews Generator', async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/product-reviews`);
    await page.getByTestId('generator-runbar').waitFor();
    await page.getByTestId('preview-table').waitFor();
    await hideWpChrome(page);
    await page.waitForTimeout(1000);
    await page.screenshot({
      path: join(SCREENSHOT_DIR, 'screenshot-10.png'),
      fullPage: true,
    });
  });

  test('11. Locations Generator', async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/locations`);
    await page.getByTestId('generator-runbar').waitFor();
    await page.getByTestId('preview-table').waitFor();
    await hideWpChrome(page);
    await page.waitForTimeout(1000);
    await page.screenshot({
      path: join(SCREENSHOT_DIR, 'screenshot-11.png'),
      fullPage: true,
    });
  });
});
