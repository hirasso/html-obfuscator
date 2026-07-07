import { test, expect } from "@playwright/test";
import { strategyOrder } from "../../resources/src/generated/strategies.js";

const PLAINTEXT = [
  "mail@example.com",
  "contact@example.com",
  "+1 555 123 4567",
  "+1 555 123-4567",
];

for (const strategy of strategyOrder) {
  const url = `/?strategy=${strategy}`;

  test.describe(`[${strategy}]`, () => {
    test("source is obfuscated", async ({ request }) => {
      const response = await request.get(url);
      const body = await response.text();
      for (const value of PLAINTEXT) {
        expect(body, `"${value}" should not appear in source`).not.toContain(
          value
        );
      }
    });

    test("content is hidden before interaction", async ({ page }) => {
      await page.goto(url);
      for (const value of PLAINTEXT) {
        await expect(
          page.getByText(value, { exact: false }).first()
        ).not.toBeVisible();
      }
    });

    test("content is revealed after interaction", async ({ page }) => {
      await page.goto(url);
      await page.mouse.move(100, 100);
      for (const value of PLAINTEXT) {
        await expect(
          page.getByText(value, { exact: false }).first()
        ).toBeVisible();
      }
    });

    test("mailto href is obfuscated in source", async ({ request }) => {
      const response = await request.get(url);
      const body = await response.text();
      expect(body).not.toContain('href="mailto:mail@');
    });

    test("mailto href is revealed after interaction", async ({ page }) => {
      await page.goto(url);
      await page.mouse.move(100, 100);
      await expect(
        page.locator('a[href="mailto:mail@example.com"]')
      ).toBeVisible();
    });

    test("obfuscated elements have aria-label", async ({ page }) => {
      await page.goto(url);
      const elements = page.locator("ob-fus-ca-ted:not([attr])");
      const count = await elements.count();
      expect(count).toBeGreaterThan(0);
      for (let i = 0; i < count; i++) {
        await expect(elements.nth(i)).toHaveAttribute(
          "aria-label",
          "Interact with the page to reveal"
        );
      }
    });

    test("obfuscated elements contain noscript with fallback text", async ({
      page,
    }) => {
      await page.goto(url);
      const noscripts = page.locator("ob-fus-ca-ted noscript");
      const count = await noscripts.count();
      expect(count).toBeGreaterThan(0);
      for (let i = 0; i < count; i++) {
        const text = await noscripts.nth(i).innerHTML();
        expect(text.trim()).toBe("Please activate JavaScript");
      }
    });
  });
}
