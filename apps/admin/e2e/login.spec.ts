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

    // Since we don't have a real backend running in a simple Playwright setup without more config,
    // this might fail or timeout if the proxy doesn't work.
    // However, for GH Actions, it should work if we start the whole stack or mock the API.
    
    // For now, let's just check if it stays on the login page or shows an error if mocked.
    await expect(page.getByText('Invalid credentials provided')).toBeVisible();
  });

  test('should login successfully with admin credentials', async ({ page }) => {
    await page.goto('/login');

    await page.getByLabel('Email Address').fill('admin@example.com');
    await page.getByLabel('Password').fill('password123');
    await page.getByRole('button', { name: 'Sign In' }).click();

    // After login, it should redirect to /admin/sites
    await expect(page).toHaveURL(/\/admin\/sites/);
    await expect(page.getByText('Site Management')).toBeVisible();
  });
});
