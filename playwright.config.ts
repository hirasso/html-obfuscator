import { defineConfig, devices } from "@playwright/test";

const baseURL = "http://localhost:8765";

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
    command: "composer demo",
    url: baseURL,
    reuseExistingServer: true,
  },
});
