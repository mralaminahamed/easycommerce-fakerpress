import { test as setup } from '@playwright/test';
import { mkdirSync } from 'fs';

const AUTH_FILE = 'tests/e2e/.auth/admin.json';

setup('authenticate as WP admin', async ({ page, baseURL }) => {
  mkdirSync('tests/e2e/.auth', { recursive: true });

  const user = process.env.WP_ADMIN_USER ?? 'admin';
  const pass = process.env.WP_ADMIN_PASS ?? 'Test@12345';

  await page.goto(`${baseURL}/wp-login.php`);
  await page.locator('#user_login').fill(user);
  await page.locator('#user_pass').fill(pass);
  await page.locator('#wp-submit').click();
  await page.waitForURL('**/wp-admin/**');

  await page.context().storageState({ path: AUTH_FILE });
});
