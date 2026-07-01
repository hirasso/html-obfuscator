import { test, expect } from "@playwright/test";

const PLAINTEXT = [
  "mail@example.com",
  "contact@example.com",
  "+1 555 123 4567",
  "+1 555 123-4567",
];

test("source is obfuscated", async ({ request }) => {
  const response = await request.get("/");
  const body = await response.text();
  for (const value of PLAINTEXT) {
    expect(body, `"${value}" should not appear in source`).not.toContain(value);
  }
});

test("content is hidden before interaction", async ({ page }) => {
  await page.goto("/");
  for (const value of PLAINTEXT) {
    await expect(page.getByText(value, { exact: false }).first()).not.toBeVisible();
  }
});

test("content is revealed after interaction", async ({ page }) => {
  await page.goto("/");
  await page.mouse.move(100, 100);
  for (const value of PLAINTEXT) {
    await expect(page.getByText(value, { exact: false }).first()).toBeVisible();
  }
});

test("mailto href is obfuscated in source", async ({ request }) => {
  const response = await request.get("/");
  const body = await response.text();
  expect(body).not.toContain('href="mailto:');
});

test("mailto href is revealed after interaction", async ({ page }) => {
  await page.goto("/");
  await page.mouse.move(100, 100);
  await expect(page.locator('a[href="mailto:mail@example.com"]')).toBeVisible();
});

test("script tag is removed from DOM after execution", async ({ page }) => {
  await page.goto("/");
  await expect(page.locator("script[data-settings]")).toHaveCount(0);
});
