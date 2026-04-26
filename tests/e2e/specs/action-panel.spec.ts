import { test, expect } from '@playwright/test';

const PLUGIN_URL = '/wp-admin/admin.php?page=easycommerce-fakerpress';

test.describe('ActionPanel', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/products`);
    await page.getByTestId('action-panel').waitFor();
  });

  test.describe('Count stepper', () => {
    test('starts at 10', async ({ page }) => {
      await expect(page.getByTestId('count-input')).toHaveValue('10');
    });

    test('decrement button reduces count by 1', async ({ page }) => {
      await page.getByTestId('count-decrement').click();
      await expect(page.getByTestId('count-input')).toHaveValue('9');
    });

    test('increment button increases count by 1', async ({ page }) => {
      await page.getByTestId('count-increment').click();
      await expect(page.getByTestId('count-input')).toHaveValue('11');
    });

    test('decrement button is disabled at count 1', async ({ page }) => {
      for (let i = 0; i < 9; i++) {
        await page.getByTestId('count-decrement').click();
      }
      await expect(page.getByTestId('count-input')).toHaveValue('1');
      await expect(page.getByTestId('count-decrement')).toBeDisabled();
    });

    test('increment button is disabled at count 100', async ({ page }) => {
      await page.getByTestId('count-input').fill('100');
      await page.getByTestId('count-input').press('Tab');
      await expect(page.getByTestId('count-increment')).toBeDisabled();
    });

    test('manual input of 0 clamps to 1', async ({ page }) => {
      await page.getByTestId('count-input').fill('0');
      await page.getByTestId('count-input').press('Tab');
      await expect(page.getByTestId('count-input')).toHaveValue('1');
    });

    test('multiple increments work correctly', async ({ page }) => {
      await page.getByTestId('count-increment').click();
      await page.getByTestId('count-increment').click();
      await page.getByTestId('count-increment').click();
      await expect(page.getByTestId('count-input')).toHaveValue('13');
    });
  });

  test.describe('Locale select', () => {
    test('locale select is visible and has a value', async ({ page }) => {
      const panel = page.getByTestId('action-panel');
      await expect(panel).toContainText(/en|English|US/i);
    });
  });

  test.describe('Seed input', () => {
    test('seed input is empty by default', async ({ page }) => {
      const seed = page.getByTestId('action-panel').getByPlaceholder('random (leave blank)');
      await expect(seed).toHaveValue('');
    });

    test('seed input accepts a number', async ({ page }) => {
      const seed = page.getByTestId('action-panel').getByPlaceholder('random (leave blank)');
      await seed.fill('42');
      await expect(seed).toHaveValue('42');
    });
  });

  test.describe('Include metadata toggle', () => {
    test('is checked by default', async ({ page }) => {
      const toggle = page.locator('#action-include-meta');
      await expect(toggle).toHaveAttribute('aria-checked', 'true');
    });

    test('can be toggled off by clicking the label', async ({ page }) => {
      await page.getByLabel('Include metadata').click();
      const toggle = page.locator('#action-include-meta');
      await expect(toggle).toHaveAttribute('aria-checked', 'false');
    });

    test('toggling off then on restores checked state', async ({ page }) => {
      const label = page.getByLabel('Include metadata');
      await label.click();
      await label.click();
      await expect(page.locator('#action-include-meta')).toHaveAttribute('aria-checked', 'true');
    });
  });

  test.describe('Generate button', () => {
    test('generate button is visible and enabled by default', async ({ page }) => {
      await expect(page.getByTestId('generate-btn')).toBeVisible();
      await expect(page.getByTestId('generate-btn')).toBeEnabled();
    });

    test('generate button shows Generate text', async ({ page }) => {
      await expect(page.getByTestId('generate-btn')).toContainText('Generate');
    });

    test('clicking generate triggers progress or result', async ({ page }) => {
      await page.getByTestId('generate-btn').click();
      // After click: progress shimmer OR success OR error appears
      await expect(
        page.locator('.animate-progress-shimmer')
          .or(page.getByTestId('result-success'))
          .or(page.getByTestId('result-error')),
      ).toBeVisible({ timeout: 20_000 });
    });

    test('generation completes with success or error', async ({ page }) => {
      await page.getByTestId('generate-btn').click();
      await expect(
        page.getByTestId('result-success').or(page.getByTestId('result-error')),
      ).toBeVisible({ timeout: 20_000 });
    });
  });
});
