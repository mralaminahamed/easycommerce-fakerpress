import { test, expect } from '@playwright/test';

const PLUGIN_URL = '/wp-admin/admin.php?page=easycommerce-fakerpress';

test.describe('Overlays', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(PLUGIN_URL);
    await page.getByTestId('app-shell').waitFor();
  });

  test.describe('CommandPalette', () => {
    test('cmd-button opens command-palette', async ({ page }) => {
      await page.getByTestId('cmd-button').click();
      await expect(page.getByTestId('command-palette')).toBeVisible();
    });

    test('⌘K keyboard shortcut opens command-palette', async ({ page }) => {
      await page.keyboard.press('Meta+k');
      await expect(page.getByTestId('command-palette')).toBeVisible();
    });

    test('typing in command-palette and pressing Enter navigates', async ({ page }) => {
      await page.getByTestId('cmd-button').click();
      await page.getByTestId('command-palette').waitFor();
      // Type to filter to the Orders generator
      await page.keyboard.type('Orders');
      // Wait for the first result to be highlighted, then press Enter
      await page.keyboard.press('Enter');
      // Should navigate to orders generator
      await page.getByTestId('generator-runbar').waitFor({ timeout: 10_000 });
      await expect(page.getByTestId('topbar')).toContainText('Orders');
    });

    test('Escape closes command-palette', async ({ page }) => {
      await page.getByTestId('cmd-button').click();
      await page.getByTestId('command-palette').waitFor();
      await page.keyboard.press('Escape');
      await expect(page.getByTestId('command-palette')).not.toBeVisible();
    });
  });

  test.describe('TweaksPanel', () => {
    test('tweaks-button opens tweaks-panel', async ({ page }) => {
      await page.getByTestId('tweaks-button').click();
      await expect(page.getByTestId('tweaks-panel')).toBeVisible();
    });

    test('selecting dark appearance sets data-theme="dark" on .fp-root', async ({ page }) => {
      await page.getByTestId('tweaks-button').click();
      await page.getByTestId('tweaks-panel').waitFor();
      // Click the Dark segment button
      await page.getByTestId('tweaks-panel').getByRole('button', { name: /Dark/i }).click();
      await expect(page.locator('.fp-root')).toHaveAttribute('data-theme', 'dark');
    });

    test('closing tweaks-panel removes it', async ({ page }) => {
      await page.getByTestId('tweaks-button').click();
      await page.getByTestId('tweaks-panel').waitFor();
      await page.getByTestId('tweaks-panel').getByRole('button', { name: /Close/i }).click();
      await expect(page.getByTestId('tweaks-panel')).not.toBeVisible();
    });
  });

  test.describe('BatchTray', () => {
    test('add-to-batch then batch-chip opens batch-tray', async ({ page }) => {
      // Navigate to a generator page first
      await page.goto(`${PLUGIN_URL}#/generator/products`);
      await page.getByTestId('generator-runbar').waitFor();

      // Add to batch
      await page.getByTestId('add-to-batch').click();

      // The batch chip should now appear in the topbar
      await page.getByTestId('batch-chip').waitFor({ timeout: 5_000 });
      await page.getByTestId('batch-chip').click();

      // Batch tray should open
      await expect(page.getByTestId('batch-tray')).toBeVisible();
    });
  });
});
