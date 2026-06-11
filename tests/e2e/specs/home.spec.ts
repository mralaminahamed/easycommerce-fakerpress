import { test, expect } from '@playwright/test';

const PLUGIN_URL = '/wp-admin/admin.php?page=easycommerce-fakerpress';

test.describe('Home / Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(PLUGIN_URL);
    await page.getByTestId('app-shell').waitFor();
  });

  test('app-shell, sidebar, topbar, generator-grid all render', async ({ page }) => {
    await expect(page.getByTestId('app-shell')).toBeVisible();
    await expect(page.getByTestId('sidebar')).toBeVisible();
    await expect(page.getByTestId('topbar')).toBeVisible();
    await expect(page.getByTestId('generator-grid')).toBeVisible();
  });

  test.describe('Stat cards', () => {
    test('four stat cards are visible with correct labels', async ({ page }) => {
      await expect(page.getByTestId('stat-products')).toBeVisible();
      await expect(page.getByTestId('stat-customers')).toBeVisible();
      await expect(page.getByTestId('stat-orders')).toBeVisible();
      await expect(page.getByTestId('stat-total')).toBeVisible();
    });

    test('each stat card shows its label text', async ({ page }) => {
      await expect(page.getByTestId('stat-products')).toContainText('Products');
      await expect(page.getByTestId('stat-customers')).toContainText('Customers');
      await expect(page.getByTestId('stat-orders')).toContainText('Orders');
      await expect(page.getByTestId('stat-total')).toContainText('Total Generated');
    });

    test('empty stat shows em-dash or a number', async ({ page }) => {
      const text = await page.getByTestId('stat-products').textContent();
      expect(text).toMatch(/—|\d/);
    });
  });

  test.describe('GeneratorGrid', () => {
    test('category section headings are present', async ({ page }) => {
      const grid = page.getByTestId('generator-grid');
      await expect(grid).toContainText('Core');
      await expect(grid).toContainText('Advanced');
      await expect(grid).toContainText('Enhanced');
    });

    const ALL_ROUTES = [
      'products', 'customers', 'orders', 'coupons',
      'product-variations', 'shipping-plans', 'tax-classes',
      'transaction', 'cart-sessions', 'attributes',
      'refunds', 'logs', 'locations', 'product-reviews',
    ];

    test('all 14 generator cards are visible', async ({ page }) => {
      for (const route of ALL_ROUTES) {
        await expect(page.getByTestId(`gen-card-${route}`)).toBeVisible();
      }
    });

    test('clicking a gen-card navigates to its generator', async ({ page }) => {
      await page.getByTestId('gen-card-products').click();
      await page.getByTestId('generator-runbar').waitFor();
      await expect(page.getByTestId('topbar')).toContainText('Products');
    });
  });
});
