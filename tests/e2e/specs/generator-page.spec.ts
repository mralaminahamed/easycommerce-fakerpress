import { test, expect } from '@playwright/test';

const PLUGIN_URL = '/wp-admin/admin.php?page=easycommerce-fakerpress';

test.describe('Generator page layout', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/products`);
    await page.getByTestId('action-panel').waitFor();
  });

  test.describe('Top bar', () => {
    test('is visible', async ({ page }) => {
      await expect(page.getByTestId('generator-topbar')).toBeVisible();
    });

    test('shows generator name', async ({ page }) => {
      await expect(page.getByTestId('generator-topbar')).toContainText('Products');
    });

    test('back link navigates to home page', async ({ page }) => {
      await page.getByTestId('generator-topbar').getByRole('link', { name: 'Back' }).click();
      await page.getByTestId('generator-grid').waitFor();
      await expect(page.getByTestId('generator-grid')).toBeVisible();
    });

    test('shows locale in top bar', async ({ page }) => {
      const text = await page.getByTestId('generator-topbar').textContent();
      expect(text).toMatch(/en_US|English/i);
    });
  });

  test.describe('Sidebar', () => {
    test('generator sidebar is visible', async ({ page }) => {
      await expect(page.getByTestId('generator-sidebar')).toBeVisible();
    });

    test('current generator highlighted with blue border', async ({ page }) => {
      const current = page.getByTestId('generator-sidebar').locator('.border-l-2.border-blue-600');
      await expect(current).toBeVisible();
      await expect(current).toContainText('Products');
    });

    test('sidebar lists other generators', async ({ page }) => {
      const sidebar = page.getByTestId('generator-sidebar');
      await expect(sidebar).toContainText('Orders');
      await expect(sidebar).toContainText('Logs');
    });

    test('sidebar has category group labels', async ({ page }) => {
      const sidebar = page.getByTestId('generator-sidebar');
      await expect(sidebar).toContainText('Core');
      await expect(sidebar).toContainText('Advanced');
    });

    test('clicking sidebar link navigates to that generator', async ({ page }) => {
      await page.getByTestId('generator-sidebar').getByRole('link', { name: 'Orders' }).click();
      await page.getByTestId('action-panel').waitFor();
      await expect(page.getByTestId('generator-topbar')).toContainText('Orders');
    });
  });

  test.describe('Panels', () => {
    test('params panel is visible', async ({ page }) => {
      await expect(page.getByTestId('params-panel')).toBeVisible();
    });

    test('action panel is visible', async ({ page }) => {
      await expect(page.getByTestId('action-panel')).toBeVisible();
    });

    test('products params panel shows section headers', async ({ page }) => {
      const params = page.getByTestId('params-panel');
      await expect(params).toContainText('Product Type');
      await expect(params).toContainText('Price Range');
      await expect(params).toContainText('Inventory');
    });

    test('product-reviews generator renders panels', async ({ page }) => {
      await page.goto(`${PLUGIN_URL}#/generator/product-reviews`);
      await page.getByTestId('action-panel').waitFor();
      await expect(page.getByTestId('params-panel')).toBeVisible();
    });
  });
});
