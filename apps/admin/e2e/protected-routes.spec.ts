import { test, expect } from '@playwright/test';

test.describe('Protected Routes', () => {
  test('should redirect to login when accessing / without auth', async ({ page }) => {
    await page.goto('/');

    await expect(page).toHaveURL(/\/login/);
  });

  test('should redirect to login when accessing /admin/sites without auth', async ({ page }) => {
    await page.goto('/admin/sites');

    await expect(page).toHaveURL(/\/login/);
  });

  test('should redirect to login when accessing /users/create without auth', async ({ page }) => {
    await page.goto('/users/create');

    await expect(page).toHaveURL(/\/login/);
  });
});
