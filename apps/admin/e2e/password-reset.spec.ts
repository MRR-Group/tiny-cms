import { test, expect } from '@playwright/test';

test.describe('Password Reset Page', () => {
  test('should display password reset form', async ({ page }) => {
    await page.goto('/password-reset');

    await expect(page.getByText('Password Recovery')).toBeVisible();
    await expect(page.getByLabel('Registered Email Address')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Send Recovery Link' })).toBeVisible();
  });

  test('should have a back to login link', async ({ page }) => {
    await page.goto('/password-reset');

    const backLink = page.getByRole('link', { name: /back to login/i });
    await expect(backLink).toBeVisible();
    await backLink.click();

    await expect(page).toHaveURL(/\/login/);
  });

  test('should submit password reset request', async ({ page }) => {
    await page.goto('/password-reset');

    await page.getByLabel('Registered Email Address').fill('admin@example.com');
    await page.getByRole('button', { name: 'Send Recovery Link' }).click();

    // Should show success message
    await expect(page.getByText(/instructions to reset your password/i)).toBeVisible();
    await expect(page.getByText('admin@example.com')).toBeVisible();
  });

  test('should show return to login after successful reset request', async ({ page }) => {
    await page.goto('/password-reset');

    await page.getByLabel('Registered Email Address').fill('admin@example.com');
    await page.getByRole('button', { name: 'Send Recovery Link' }).click();

    await expect(page.getByText(/instructions to reset your password/i)).toBeVisible();

    const returnLink = page.getByRole('link', { name: /return to login/i });
    await expect(returnLink).toBeVisible();
    await returnLink.click();

    await expect(page).toHaveURL(/\/login/);
  });
});

test.describe('Set New Password Page', () => {
  test('should display set new password form', async ({ page }) => {
    await page.goto('/password-reset/confirm?token=test-token');

    await expect(page.getByText('Secure Credentials Update')).toBeVisible();
    await expect(page.getByLabel('New Secure Password')).toBeVisible();
    await expect(page.getByLabel('Verify New Password')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Set New Password' })).toBeVisible();
  });

  test('should show error when passwords do not match', async ({ page }) => {
    await page.goto('/password-reset/confirm?token=test-token');

    await page.getByLabel('New Secure Password').fill('password123');
    await page.getByLabel('Verify New Password').fill('differentpassword');
    await page.getByRole('button', { name: 'Set New Password' }).click();

    await expect(page.getByText('Passwords do not match')).toBeVisible();
  });

  test('should show error when password is too short', async ({ page }) => {
    await page.goto('/password-reset/confirm?token=test-token');

    await page.getByLabel('New Secure Password').fill('short');
    await page.getByLabel('Verify New Password').fill('short');
    await page.getByRole('button', { name: 'Set New Password' }).click();

    await expect(page.getByText('Password must be at least 8 characters long')).toBeVisible();
  });

  test('should have a return to login link', async ({ page }) => {
    await page.goto('/password-reset/confirm?token=test-token');

    const returnLink = page.getByRole('link', { name: /return to login/i });
    await expect(returnLink).toBeVisible();
    await returnLink.click();

    await expect(page).toHaveURL(/\/login/);
  });

  test('should show invalid-link error when token is missing', async ({ page }) => {
    await page.goto('/password-reset/confirm');

    await page.getByLabel('New Secure Password').fill('StrongPassword123!');
    await page.getByLabel('Verify New Password').fill('StrongPassword123!');
    await page.getByRole('button', { name: 'Set New Password' }).click();

    await expect(page.getByText('Invalid password reset link')).toBeVisible();
  });

  test('should support reset-password alias route', async ({ page }) => {
    await page.goto('/auth/reset-password?token=test-token');

    await expect(page.getByText('Secure Credentials Update')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Set New Password' })).toBeVisible();
  });
});
