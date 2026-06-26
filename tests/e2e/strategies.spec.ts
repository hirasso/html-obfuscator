import { test, expect } from '@playwright/test';

const PLAINTEXT = [
  'mail@example.com',
  'contact@example.com',
  '+1 555 123 4567',
  '+1 555 123-4567',
];

async function assertObfuscatedInSource(url: string, { request }: { request: import('@playwright/test').APIRequestContext }) {
  const response = await request.get(url);
  const body = await response.text();
  for (const value of PLAINTEXT) {
    expect(body, `"${value}" should not appear in source`).not.toContain(value);
  }
}

async function assertRevealed(page: import('@playwright/test').Page) {
  for (const value of PLAINTEXT) {
    await expect(page.getByText(value, { exact: false }).first()).toBeVisible();
  }
}

async function assertNotRevealed(page: import('@playwright/test').Page) {
  for (const value of PLAINTEXT) {
    await expect(page.getByText(value, { exact: false }).first()).not.toBeVisible();
  }
}

test.describe('strategy: onload', () => {
  test('source is obfuscated', async ({ request }) => {
    await assertObfuscatedInSource('/strategy-onload.php', { request });
  });

  test('content is revealed on load', async ({ page }) => {
    await page.goto('/strategy-onload.php');
    await assertRevealed(page);
  });
});

test.describe('strategy: oninteraction', () => {
  test('source is obfuscated', async ({ request }) => {
    await assertObfuscatedInSource('/strategy-oninteraction.php', { request });
  });

  test('content is not revealed before interaction', async ({ page }) => {
    await page.goto('/strategy-oninteraction.php');
    await assertNotRevealed(page);
  });

  test('content is revealed after interaction', async ({ page }) => {
    await page.goto('/strategy-oninteraction.php');
    await page.mouse.move(100, 100);
    await assertRevealed(page);
  });
});

test.describe('strategy: none', () => {
  test('source is obfuscated', async ({ request }) => {
    await assertObfuscatedInSource('/strategy-none.php', { request });
  });

  test('content is never revealed', async ({ page }) => {
    await page.goto('/strategy-none.php');
    await assertNotRevealed(page);
  });
});
