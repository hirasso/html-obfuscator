import { defineConfig } from "vitest/config";

export default defineConfig({
  test: {
    environment: "node",
    include: ["tests/Unit/**/*.test.ts"],
    globalSetup: ["tests/globalSetup.ts"],
  },
});
