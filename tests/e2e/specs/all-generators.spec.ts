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
      // Wait for React to mount and render the generator component
      // Look for either the action panel or the generator topbar as indicators
      const actionPanel = page.getByTestId('action-panel');
      const topbar = page.getByTestId('generator-topbar');
      await Promise.race([
        actionPanel.waitFor({ timeout: 15_000 }).catch(() => null),
        topbar.waitFor({ timeout: 15_000 }).catch(() => null),
      ]);
    });

    test('page loads without JS errors', async ({ page }) => {
      await expect(page.getByText('Something went wrong')).not.toBeVisible();
    });

    test('top bar shows generator name', async ({ page }) => {
      await expect(page.getByTestId('generator-topbar')).toContainText(name);
    });

    test('params panel renders', async ({ page }) => {
      await expect(page.getByTestId('params-panel')).toBeVisible();
    });

    test('action panel has generate button', async ({ page }) => {
      // Use first() since the button appears multiple times in the DOM
      const generateBtn = page.getByTestId('generate-btn').first();
      await expect(generateBtn).toBeVisible();
      await expect(generateBtn).toBeEnabled();
    });

    test('sidebar highlights current generator', async ({ page }) => {
      const highlight = page.getByTestId('generator-sidebar').locator('.border-l-2.border-blue-600');
      await expect(highlight).toContainText(name);
    });
  });
}
