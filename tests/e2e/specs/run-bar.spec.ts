import { test, expect } from '@playwright/test';

const PLUGIN_URL = '/wp-admin/admin.php?page=easycommerce-fakerpress';

test.describe('RunBar', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/products`);
    await page.getByTestId('generator-runbar').waitFor();
  });

  test.describe('Count stepper', () => {
    test('value input exists and has a numeric value', async ({ page }) => {
      const input = page.getByTestId('count-input');
      await expect(input).toBeVisible();
      const val = await input.inputValue();
      expect(Number(val)).toBeGreaterThanOrEqual(1);
    });

    test('decrement button decreases count by 1', async ({ page }) => {
      const input = page.getByTestId('count-input');
      const before = Number(await input.inputValue());
      await page.getByTestId('count-dec').click();
      const after = Number(await input.inputValue());
      expect(after).toBe(Math.max(1, before - 1));
    });

    test('increment button increases count by 1', async ({ page }) => {
      const input = page.getByTestId('count-input');
      const before = Number(await input.inputValue());
      await page.getByTestId('count-inc').click();
      const after = Number(await input.inputValue());
      expect(after).toBe(before + 1);
    });

    test('manual input of value 1 is accepted', async ({ page }) => {
      const input = page.getByTestId('count-input');
      await input.fill('1');
      await input.press('Tab');
      await expect(input).toHaveValue('1');
    });
  });

  test.describe('Generate button', () => {
    test('generate-btn is visible and enabled', async ({ page }) => {
      await expect(page.getByTestId('generate-btn')).toBeVisible();
      await expect(page.getByTestId('generate-btn')).toBeEnabled();
    });

    test('generate-btn contains "Generate" text', async ({ page }) => {
      await expect(page.getByTestId('generate-btn')).toContainText('Generate');
    });
  });

  test.describe('Add to batch button', () => {
    test('add-to-batch is visible and enabled', async ({ page }) => {
      await expect(page.getByTestId('add-to-batch')).toBeVisible();
      await expect(page.getByTestId('add-to-batch')).toBeEnabled();
    });

    test('add-to-batch contains "batch" text', async ({ page }) => {
      await expect(page.getByTestId('add-to-batch')).toContainText('batch');
    });
  });

  test.describe('Metadata toggle', () => {
    test('metadata toggle label is present in the run bar', async ({ page }) => {
      await expect(page.getByTestId('generator-runbar')).toContainText('Metadata');
    });
  });
});
