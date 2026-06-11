import { test, expect } from '@playwright/test';

const PLUGIN_URL = '/wp-admin/admin.php?page=easycommerce-fakerpress';

const GENERATORS = [
  { route: 'products',           name: 'Products' },
  { route: 'customers',          name: 'Customers' },
  { route: 'orders',             name: 'Orders' },
  { route: 'coupons',            name: 'Coupons' },
  { route: 'product-variations', name: 'Product Variations' },
  { route: 'shipping-plans',     name: 'Shipping Plans' },
  { route: 'tax-classes',        name: 'Tax Classes' },
  { route: 'transaction',        name: 'Transactions' },
  { route: 'cart-sessions',      name: 'Cart Sessions' },
  { route: 'attributes',         name: 'Attributes' },
  { route: 'refunds',            name: 'Refunds' },
  { route: 'logs',               name: 'Logs' },
  { route: 'locations',          name: 'Locations' },
  { route: 'product-reviews',    name: 'Product Reviews' },
] as const;

for (const { route, name } of GENERATORS) {
  test.describe(`${name} generator`, () => {
    test.beforeEach(async ({ page }) => {
      await page.goto(`${PLUGIN_URL}#/generator/${route}`, { waitUntil: 'domcontentloaded' });
      await page.getByTestId('generator-runbar').waitFor({ timeout: 15_000 });
    });

    test('page loads without error boundary', async ({ page }) => {
      await expect(page.getByText('Something went wrong')).not.toBeVisible();
    });

    test('topbar shows generator name', async ({ page }) => {
      await expect(page.getByTestId('topbar')).toContainText(name);
    });

    test('config column shows generator name', async ({ page }) => {
      await expect(page.locator('.fp-config-col')).toContainText(name);
    });

    test('generator runbar is visible', async ({ page }) => {
      await expect(page.getByTestId('generator-runbar')).toBeVisible();
    });

    test('preview table renders', async ({ page }) => {
      await expect(page.getByTestId('preview-table')).toBeVisible();
    });

    test('generate-btn is present and enabled', async ({ page }) => {
      await expect(page.getByTestId('generate-btn')).toBeVisible();
      await expect(page.getByTestId('generate-btn')).toBeEnabled();
    });
  });
}
