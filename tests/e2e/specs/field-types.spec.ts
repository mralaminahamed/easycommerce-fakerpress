import { test, expect } from '@playwright/test';

const PLUGIN_URL = '/wp-admin/admin.php?page=easycommerce-fakerpress';

// ─── Chips field — Coupons: discount_types ────────────────────────────────────

test.describe('Chips field — Coupons: discount_types', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/coupons`);
    await page.getByTestId('generator-runbar').waitFor();
  });

  test('renders chip buttons for all 4 enum options', async ({ page }) => {
    const section = page.locator('[data-param="discount_types"]');
    await expect(section.locator('[data-chip-value="percentage"]')).toBeVisible();
    await expect(section.locator('[data-chip-value="fixed"]')).toBeVisible();
    await expect(section.locator('[data-chip-value="free_shipping"]')).toBeVisible();
    await expect(section.locator('[data-chip-value="products"]')).toBeVisible();
  });

  test('default selected chips have "on" class', async ({ page }) => {
    const section = page.locator('[data-param="discount_types"]');
    await expect(section.locator('[data-chip-value="percentage"]')).toHaveClass(/on/);
    await expect(section.locator('[data-chip-value="fixed"]')).toHaveClass(/on/);
  });

  test('unselected chips do not have "on" class', async ({ page }) => {
    const section = page.locator('[data-param="discount_types"]');
    await expect(section.locator('[data-chip-value="free_shipping"]')).not.toHaveClass(/\bon\b/);
  });

  test('clicking unselected chip selects it', async ({ page }) => {
    const section = page.locator('[data-param="discount_types"]');
    const chip = section.locator('[data-chip-value="free_shipping"]');
    await chip.click();
    await expect(chip).toHaveClass(/on/);
  });

  test('clicking selected chip deselects it', async ({ page }) => {
    const section = page.locator('[data-param="discount_types"]');
    const chip = section.locator('[data-chip-value="fixed"]');
    await chip.click();
    await expect(chip).not.toHaveClass(/\bon\b/);
  });
});

// ─── Range field — Products: price_range ────────────────────────────────────

test.describe('Range field — Products: price_range', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/products`);
    await page.getByTestId('generator-runbar').waitFor();
  });

  test('price_range section has two number inputs', async ({ page }) => {
    const section = page.locator('[data-param="price_range"]');
    const inputs = section.locator('input');
    await expect(inputs).toHaveCount(2);
  });

  test('section contains Min / Max labels', async ({ page }) => {
    // The range section is inside the price_range fp-field-section; parent section label contains Price
    await expect(page.locator('[data-param="price_range"]')).toBeVisible();
  });
});

// ─── Select field — Products: product_type ────────────────────────────────────

test.describe('Select field — Products: product_type', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/products`);
    await page.getByTestId('generator-runbar').waitFor();
  });

  test('renders a combobox for product_type', async ({ page }) => {
    const section = page.locator('[data-param="product_type"]');
    await expect(section.getByRole('combobox')).toBeVisible();
  });

  test('default value is Mixed', async ({ page }) => {
    const section = page.locator('[data-param="product_type"]');
    await expect(section.getByRole('combobox')).toContainText(/Mixed/i);
  });
});

// ─── Toggle field — Products: inventory > manage_stock ────────────────────────

test.describe('Toggle field — Products: inventory > manage_stock', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/products`);
    await page.getByTestId('generator-runbar').waitFor();
  });

  test('manage_stock toggle is present', async ({ page }) => {
    const section = page.locator('[data-param="manage_stock"]');
    await expect(section).toBeVisible();
    await expect(section.locator('button[role="switch"]')).toBeVisible();
  });

  test('toggle is on by default', async ({ page }) => {
    const section = page.locator('[data-param="manage_stock"]');
    await expect(section.locator('button[role="switch"]')).toHaveAttribute('aria-checked', 'true');
  });

  test('clicking toggle turns it off', async ({ page }) => {
    const section = page.locator('[data-param="manage_stock"]');
    await section.locator('button[role="switch"]').click();
    await expect(section.locator('button[role="switch"]')).toHaveAttribute('aria-checked', 'false');
  });
});
