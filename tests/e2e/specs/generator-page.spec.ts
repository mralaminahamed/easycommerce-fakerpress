import { test, expect } from '@playwright/test';

const PLUGIN_URL = '/wp-admin/admin.php?page=easycommerce-fakerpress';

test.describe('Generator page layout', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/products`);
    await page.getByTestId('generator-runbar').waitFor();
  });

  test.describe('Topbar', () => {
    test('topbar is visible', async ({ page }) => {
      await expect(page.getByTestId('topbar')).toBeVisible();
    });

    test('topbar shows generator name', async ({ page }) => {
      await expect(page.getByTestId('topbar')).toContainText('Products');
    });

    test('breadcrumb FakerPress button navigates back to dashboard', async ({ page }) => {
      await page.getByTestId('topbar').getByRole('button', { name: 'FakerPress' }).click();
      await page.getByTestId('generator-grid').waitFor();
      await expect(page.getByTestId('generator-grid')).toBeVisible();
    });
  });

  test.describe('Sidebar', () => {
    test('sidebar is visible', async ({ page }) => {
      await expect(page.getByTestId('sidebar')).toBeVisible();
    });

    test('sidebar nav item for products is marked active', async ({ page }) => {
      const item = page.getByTestId('nav-products');
      await expect(item).toBeVisible();
      await expect(item).toHaveClass(/active/);
    });

    test('sidebar lists other generator nav items', async ({ page }) => {
      await expect(page.getByTestId('nav-orders')).toBeVisible();
      await expect(page.getByTestId('nav-logs')).toBeVisible();
    });

    test('clicking sidebar nav item navigates to that generator', async ({ page }) => {
      await page.getByTestId('nav-orders').click();
      await page.getByTestId('generator-runbar').waitFor();
      await expect(page.getByTestId('topbar')).toContainText('Orders');
    });
  });

  test.describe('2-col generator layout', () => {
    test('config column shows generator name', async ({ page }) => {
      await expect(page.locator('.fp-config-col')).toContainText('Products');
    });

    test('preview table renders', async ({ page }) => {
      await expect(page.getByTestId('preview-table')).toBeVisible();
    });

    test('generator runbar is visible', async ({ page }) => {
      await expect(page.getByTestId('generator-runbar')).toBeVisible();
    });

    test('product-reviews generator renders correctly', async ({ page }) => {
      await page.goto(`${PLUGIN_URL}#/generator/product-reviews`);
      await page.getByTestId('generator-runbar').waitFor();
      await expect(page.getByTestId('preview-table')).toBeVisible();
    });
  });
});
