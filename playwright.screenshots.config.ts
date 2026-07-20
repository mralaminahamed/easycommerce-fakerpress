import base from './playwright.config';
import { defineConfig } from '@playwright/test';

/**
 * Config for regenerating the WordPress.org listing screenshots.
 *
 *   npx playwright test --config=playwright.screenshots.config.ts --project=shots
 *
 * Uses `channel: 'chrome'` so it drives an already-installed Chrome rather than
 * requiring `playwright install`, and pins the viewport to 1440x900 so every
 * screenshot in .wordpress-org/ comes out the same size.
 */
export default defineConfig({
  ...base,
  projects: [
    { name: 'setup', testMatch: 'auth.setup.ts', use: { channel: 'chrome' } },
    {
      name: 'shots',
      testMatch: 'screenshots.spec.ts',
      dependencies: ['setup'],
      use: {
        channel: 'chrome',
        viewport: { width: 1440, height: 900 },
        storageState: 'tests/e2e/.auth/admin.json',
      },
    },
  ],
});
