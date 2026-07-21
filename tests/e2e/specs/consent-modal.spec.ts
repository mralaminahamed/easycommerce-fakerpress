import { test, expect } from '@playwright/test';

const PLUGIN_URL = '/wp-admin/admin.php?page=easycommerce-fakerpress';

test.describe('Sample-data consent modal', () => {
  test('shows the modal when consent is undecided', async ({ page }) => {
    await page.route('**/download-sample', async (route) => {
      if (route.request().method() === 'GET') {
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({
            exists: false,
            last_synced: null,
            repo_url: '',
            consent: null,
          }),
        });
      } else {
        await route.continue();
      }
    });

    await page.goto(PLUGIN_URL);
    await expect(page.getByTestId('consent-modal')).toBeVisible();
  });

  test('declining records consent and hides the modal', async ({ page }) => {
    await page.route('**/download-sample', async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          exists: false,
          last_synced: null,
          repo_url: '',
          consent: null,
        }),
      });
    });
    let declined = false;
    await page.route('**/download-sample/consent', async (route) => {
      declined = true;
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ consent: 'declined' }),
      });
    });

    await page.goto(PLUGIN_URL);
    await page.getByTestId('consent-decline').click();
    await expect(page.getByTestId('consent-modal')).toBeHidden();
    expect(declined).toBe(true);
  });
});
