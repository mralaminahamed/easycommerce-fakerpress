import { defineConfig, devices } from '@playwright/test';
import { readFileSync, existsSync } from 'fs';

// Load tests/e2e/.env.test if present (no dotenv dep needed)
const envPath = 'tests/e2e/.env.test';
if (existsSync(envPath)) {
  for (const line of readFileSync(envPath, 'utf-8').split('\n')) {
    const trimmed = line.trim();
    if (!trimmed || trimmed.startsWith('#')) continue;
    const idx = trimmed.indexOf('=');
    if (idx === -1) continue;
    const key = trimmed.slice(0, idx).trim();
    const val = trimmed.slice(idx + 1).trim();
    if (key && !(key in process.env)) process.env[key] = val;
  }
}

const baseURL = process.env.WP_BASE_URL ?? 'https://easycommerce-development.test';

export default defineConfig({
  testDir: 'tests/e2e',
  fullyParallel: false,
  retries: process.env.CI ? 2 : 0,
  timeout: 30_000,
  use: {
    baseURL,
    ignoreHTTPSErrors: true,
    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
  },
  projects: [
    {
      name: 'setup',
      testMatch: 'auth.setup.ts',
    },
    {
      name: 'chromium',
      use: {
        ...devices['Desktop Chrome'],
        storageState: 'tests/e2e/.auth/admin.json',
      },
      dependencies: ['setup'],
      testMatch: 'specs/**/*.spec.ts',
    },
  ],
  reporter: [['html', { outputFolder: 'tests/e2e/report' }], ['list']],
});
