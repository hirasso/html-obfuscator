import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  outputDir: './tests/e2e/results',
  reporter: [['list'], ['json', { outputFile: 'tests/e2e/results/report.json' }]],
  use: {
    baseURL: 'http://localhost:8080',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
  webServer: {
    command: 'php -S localhost:8765 -t examples/',
    url: 'http://localhost:8765',
    reuseExistingServer: true,
  },
});
