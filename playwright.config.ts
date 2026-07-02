import { defineConfig, devices } from "@playwright/test";

const baseURL = "http://localhost:8766";

export default defineConfig({
  testDir: "./tests/e2e",
  outputDir: "./tests/e2e/results",
  reporter: [
    ["list"],
    ["json", { outputFile: "tests/e2e/results/report.json" }],
  ],
  use: {
    baseURL,
  },
  projects: [
    {
      name: "chromium",
      use: { ...devices["Desktop Chrome"] },
    },
  ],
  webServer: {
    // `composer fixture` runs pkill via `sh -c "pkill -f 'php -S localhost:8766' || true"`,
    // which on Linux matches itself (the string appears in argv) and self-terminates with SIGTERM
    // before `|| true` can suppress it, causing exit code 143. Run PHP directly instead.
    command: "php -S localhost:8766 -t tests/e2e/fixtures/",
    url: baseURL,
    reuseExistingServer: !process.env.CI,
    stdout: "ignore",
    stderr: "ignore",
  },
});
