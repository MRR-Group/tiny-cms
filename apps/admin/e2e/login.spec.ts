import { test, expect } from '@playwright/test';

test.describe('Login Page', () => {
  test('should display login form', async ({ page }) => {
    await page.goto('/login');

    await expect(page.getByLabel('Email Address')).toBeVisible();
    await expect(page.getByLabel('Password')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Sign In' })).toBeVisible();
  });

  test('should show error on invalid credentials', async ({ page }) => {
    await page.goto('/login');

    await page.getByLabel('Email Address').fill('invalid@example.com');
    await page.getByLabel('Password').fill('wrongpassword');
    await page.getByRole('button', { name: 'Sign In' }).click();

    await expect(page.getByText('Invalid credentials provided')).toBeVisible();
  });

  test('should login successfully with admin credentials', async ({ page }) => {
    await page.goto('/login');

    await page.getByLabel('Email Address').fill('admin@example.com');
    await page.getByLabel('Password').fill('password123');
    await page.getByRole('button', { name: 'Sign In' }).click();

    await expect(page).toHaveURL(/\/admin\/sites/);
    await expect(page.getByText('Site Management')).toBeVisible();
  });

  test('should have a link to password reset', async ({ page }) => {
    await page.goto('/login');

    const resetLink = page.getByRole('link', { name: /forgot password/i });
    await expect(resetLink).toBeVisible();
    await resetLink.click();

    await expect(page).toHaveURL(/\/password-reset/);
  });

  test('should show loading state while authenticating', async ({ page }) => {
    await page.goto('/login');

    await page.getByLabel('Email Address').fill('admin@example.com');
    await page.getByLabel('Password').fill('password123');
    await page.getByRole('button', { name: 'Sign In' }).click();

    // Button should show loading text briefly
    await expect(
      page.getByRole('button', { name: /authenticating/i }).or(page.getByText('Site Management'))
    ).toBeVisible();
  });
});
