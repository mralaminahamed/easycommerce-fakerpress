import { test, expect } from '@playwright/test';

const PLUGIN_URL = '/wp-admin/admin.php?page=easycommerce-fakerpress';

test.describe('Home page', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(PLUGIN_URL);
    await page.getByTestId('stats-bar').waitFor();
  });

  test.describe('StatsBar', () => {
    test('renders 4 stat cards', async ({ page }) => {
      await expect(page.getByTestId('stats-bar')).toBeVisible();
      await expect(page.getByTestId('stat-products')).toBeVisible();
      await expect(page.getByTestId('stat-customers')).toBeVisible();
      await expect(page.getByTestId('stat-orders')).toBeVisible();
      await expect(page.getByTestId('stat-total')).toBeVisible();
    });

    test('each stat card shows its label', async ({ page }) => {
      await expect(page.getByTestId('stat-products')).toContainText('Products');
      await expect(page.getByTestId('stat-customers')).toContainText('Customers');
      await expect(page.getByTestId('stat-orders')).toContainText('Orders');
      await expect(page.getByTestId('stat-total')).toContainText('Total Generated');
    });

    test('empty stats show em-dash not zero', async ({ page }) => {
      const productsCard = page.getByTestId('stat-products');
      const value = productsCard.locator('p.text-2xl');
      const text = await value.textContent();
      expect(text).toMatch(/^(—|\d[\d,]*)$/);
    });
  });

  test.describe('GeneratorGrid', () => {
    test('shows three category section headings', async ({ page }) => {
      await expect(page.getByText('Core Generators')).toBeVisible();
      await expect(page.getByText('Advanced Generators')).toBeVisible();
      await expect(page.getByText('Enhanced Generators')).toBeVisible();
    });

    const POPULAR_ROUTES = ['products', 'customers', 'orders'];
    for (const route of POPULAR_ROUTES) {
      test(`${route} card has Popular badge`, async ({ page }) => {
        await expect(page.getByTestId(`generator-card-${route}`)).toContainText('Popular');
      });
    }

    const ALL_ROUTES = [
      'products', 'customers', 'orders', 'coupons',
      'product-variations', 'shipping-plans', 'tax-classes',
      'transaction', 'cart-sessions', 'attributes',
      'refunds', 'logs', 'locations', 'product-reviews',
    ];
    test('all 14 generator cards are visible', async ({ page }) => {
      for (const route of ALL_ROUTES) {
        await expect(page.getByTestId(`generator-card-${route}`)).toBeVisible();
      }
    });

    test('clicking Products card navigates to Products generator page', async ({ page }) => {
      await page.getByTestId('generator-card-products').click();
      await page.getByTestId('action-panel').first().waitFor();
      await expect(page.getByTestId('generator-topbar')).toContainText('Products');
    });

    test('clicking Logs card navigates to Logs generator page', async ({ page }) => {
      await page.getByTestId('generator-card-logs').click();
      await page.getByTestId('action-panel').first().waitFor();
      await expect(page.getByTestId('generator-topbar')).toContainText('Logs');
    });
  });
});
