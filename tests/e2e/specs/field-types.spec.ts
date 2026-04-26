import { test, expect } from '@playwright/test';

const PLUGIN_URL = '/wp-admin/admin.php?page=easycommerce-fakerpress';

// ─── ChipField ───────────────────────────────────────────────────────────────

test.describe('ChipField — Coupons: discount_types', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/coupons`);
    await page.getByTestId('params-panel').waitFor();
  });

  test('renders pill buttons for all 4 enum options', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="discount_types"]');
    await expect(section.locator('[data-chip-value="percentage"]')).toBeVisible();
    await expect(section.locator('[data-chip-value="fixed"]')).toBeVisible();
    await expect(section.locator('[data-chip-value="free_shipping"]')).toBeVisible();
    await expect(section.locator('[data-chip-value="products"]')).toBeVisible();
  });

  test('default selected chips (percentage, fixed) have blue fill', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="discount_types"]');
    await expect(section.locator('[data-chip-value="percentage"]')).toHaveClass(/bg-blue-600/);
    await expect(section.locator('[data-chip-value="fixed"]')).toHaveClass(/bg-blue-600/);
  });

  test('unselected chips do not have blue fill', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="discount_types"]');
    await expect(section.locator('[data-chip-value="free_shipping"]')).not.toHaveClass(/bg-blue-600/);
  });

  test('clicking unselected chip selects it', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="discount_types"]');
    const chip = section.locator('[data-chip-value="free_shipping"]');
    await chip.click();
    await expect(chip).toHaveClass(/bg-blue-600/);
  });

  test('clicking selected chip deselects it when others remain', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="discount_types"]');
    const chip = section.locator('[data-chip-value="fixed"]');
    await chip.click();
    await expect(chip).not.toHaveClass(/bg-blue-600/);
  });

  test('cannot deselect the last remaining chip', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="discount_types"]');
    await section.locator('[data-chip-value="fixed"]').click();
    // Only percentage remains — try to deselect it
    const last = section.locator('[data-chip-value="percentage"]');
    await last.click();
    // Should stay selected
    await expect(last).toHaveClass(/bg-blue-600/);
  });
});

// ─── RangeField ───────────────────────────────────────────────────────────────

test.describe('RangeField — Products: price_range', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/products`);
    await page.getByTestId('params-panel').waitFor();
  });

  test('renders Min and Max inputs', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="price_range"]');
    await expect(section.getByTestId('range-min')).toBeVisible();
    await expect(section.getByTestId('range-max')).toBeVisible();
    await expect(section).toContainText('Min');
    await expect(section).toContainText('Max');
  });

  test('default min is 10 and max is 500', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="price_range"]');
    await expect(section.getByTestId('range-min')).toHaveValue('10');
    await expect(section.getByTestId('range-max')).toHaveValue('500');
  });

  test('shows validation error when min exceeds max', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="price_range"]');
    await section.getByTestId('range-min').fill('999');
    await section.getByTestId('range-max').fill('1');
    await section.getByTestId('range-max').press('Tab');
    await expect(section).toContainText('Min must be ≤ Max');
  });

  test('no error when min equals max', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="price_range"]');
    await section.getByTestId('range-min').fill('50');
    await section.getByTestId('range-max').fill('50');
    await section.getByTestId('range-max').press('Tab');
    await expect(section).not.toContainText('Min must be ≤ Max');
  });
});

// ─── SelectField ─────────────────────────────────────────────────────────────

test.describe('SelectField — Products: product_type', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/products`);
    await page.getByTestId('params-panel').waitFor();
  });

  test('renders a combobox for product_type', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="product_type"]');
    await expect(section.getByRole('combobox')).toBeVisible();
  });

  test('default value is Mixed', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="product_type"]');
    await expect(section.getByRole('combobox')).toContainText(/Mixed/i);
  });

  test('dropdown shows Physical, Digital, Mixed options', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="product_type"]');
    await section.getByRole('combobox').click();
    await expect(page.getByRole('option', { name: 'Physical' })).toBeVisible();
    await expect(page.getByRole('option', { name: 'Digital' })).toBeVisible();
    await expect(page.getByRole('option', { name: 'Mixed' })).toBeVisible();
  });

  test('selecting Physical updates the value', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="product_type"]');
    await section.getByRole('combobox').click();
    await page.getByRole('option', { name: 'Physical' }).click();
    await expect(section.getByRole('combobox')).toContainText('Physical');
  });
});

// ─── BooleanField ─────────────────────────────────────────────────────────────

test.describe('BooleanField — Products: inventory > manage_stock', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/products`);
    await page.getByTestId('params-panel').waitFor();
  });

  test('inventory section has a manage stock toggle', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="inventory"]');
    await expect(section).toContainText('Manage Stock');
    await expect(section.locator('button[role="switch"]').first()).toBeVisible();
  });

  test('manage stock switch is on by default', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="inventory"]');
    await expect(section.locator('button[role="switch"]').first()).toHaveAttribute('aria-checked', 'true');
  });

  test('clicking toggle turns it off', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="inventory"]');
    await section.locator('button[role="switch"]').first().click();
    await expect(section.locator('button[role="switch"]').first()).toHaveAttribute('aria-checked', 'false');
  });
});

// ─── Conditional field ────────────────────────────────────────────────────────

test.describe('Conditional field — Orders: specific_customer_id', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto(`${PLUGIN_URL}#/generator/orders`);
    await page.getByTestId('params-panel').waitFor();
  });

  test('specific_customer_id is hidden by default (customer_type is mixed)', async ({ page }) => {
    await expect(
      page.getByTestId('params-panel').locator('[data-param="specific_customer_id"]'),
    ).not.toBeVisible();
  });

  test('specific_customer_id appears when customer_type set to specific', async ({ page }) => {
    const customerTypeSection = page.getByTestId('params-panel').locator('[data-param="customer_type"]');
    await customerTypeSection.getByRole('combobox').click();
    await page.getByRole('option', { name: 'Specific' }).click();
    await expect(
      page.getByTestId('params-panel').locator('[data-param="specific_customer_id"]'),
    ).toBeVisible();
  });

  test('specific_customer_id hides when customer_type changed back to mixed', async ({ page }) => {
    const section = page.getByTestId('params-panel').locator('[data-param="customer_type"]');
    await section.getByRole('combobox').click();
    await page.getByRole('option', { name: 'Specific' }).click();
    await section.getByRole('combobox').click();
    await page.getByRole('option', { name: 'Mixed' }).click();
    await expect(
      page.getByTestId('params-panel').locator('[data-param="specific_customer_id"]'),
    ).not.toBeVisible();
  });
});
