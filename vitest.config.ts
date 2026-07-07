import { defineConfig } from "vitest/config";

export default defineConfig({
  test: {
    environment: "node",
    include: ["tests/vitest/**/*.test.ts"],
    globalSetup: ["tests/vitest/globalSetup.ts"],
  },
});
