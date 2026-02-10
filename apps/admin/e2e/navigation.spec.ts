import { test, expect, Page } from '@playwright/test';

async function loginAsAdmin(page: Page) {
  await page.goto('/login');
  await page.getByLabel('Email Address').fill('admin@example.com');
  await page.getByLabel('Password').fill('password123');
  await page.getByRole('button', { name: 'Sign In' }).click();
  await expect(page).toHaveURL(/\/admin\/sites/);
}

test.describe('Navigation & Layout', () => {
  test.beforeEach(async ({ page }) => {
    // Set viewport wide enough to see expanded sidebar
    await page.setViewportSize({ width: 1280, height: 720 });
    await loginAsAdmin(page);
  });

  test('should display sidebar with navigation items', async ({ page }) => {
    // Expand sidebar if collapsed
    const expandButton = page.getByRole('button', { name: /expand sidebar/i });
    if (await expandButton.isVisible()) {
      await expandButton.click();
    }

    await expect(page.getByRole('link', { name: 'Sites' })).toBeVisible();
    await expect(page.getByText('Add User')).toBeVisible();
    await expect(page.getByText('Logout')).toBeVisible();
  });

  test('should navigate to create user page from sidebar', async ({ page }) => {
    // Expand sidebar if collapsed
    const expandButton = page.getByRole('button', { name: /expand sidebar/i });
    if (await expandButton.isVisible()) {
      await expandButton.click();
    }

    await page.getByText('Add User').click();

    await expect(page).toHaveURL(/\/users\/create/);
    await expect(page.getByRole('heading', { name: 'Create User' })).toBeVisible();
  });

  test('should logout and redirect to login', async ({ page }) => {
    // Expand sidebar if collapsed
    const expandButton = page.getByRole('button', { name: /expand sidebar/i });
    if (await expandButton.isVisible()) {
      await expandButton.click();
    }

    await page.getByText('Logout').click();

    await expect(page).toHaveURL(/\/login/);
  });

  test('should stay logged in after page refresh', async ({ page }) => {
    await page.reload();

    // Should still be on sites page (not redirected to login)
    await expect(page).toHaveURL(/\/admin\/sites/);
    await expect(page.getByText('Site Management')).toBeVisible();
  });

  test('should not be able to access protected routes after logout', async ({ page }) => {
    // Expand sidebar if collapsed
    const expandButton = page.getByRole('button', { name: /expand sidebar/i });
    if (await expandButton.isVisible()) {
      await expandButton.click();
    }

    // Logout
    await page.getByText('Logout').click();
    await expect(page).toHaveURL(/\/login/);

    // Try to navigate to protected route
    await page.goto('/admin/sites');
    await expect(page).toHaveURL(/\/login/);
  });

  test('should toggle sidebar collapse', async ({ page }) => {
    const expandButton = page.getByRole('button', { name: /expand sidebar/i });
    const collapseButton = page.getByRole('button', { name: /collapse sidebar/i });

    if (await expandButton.isVisible()) {
      await expandButton.click();
      await expect(collapseButton).toBeVisible();
      await collapseButton.click();
      await expect(expandButton).toBeVisible();
    } else if (await collapseButton.isVisible()) {
      await collapseButton.click();
      await expect(expandButton).toBeVisible();
      await expandButton.click();
      await expect(collapseButton).toBeVisible();
    }
  });
});

test.describe('Create User Page', () => {
  test.beforeEach(async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 720 });
    await loginAsAdmin(page);
    // Navigate via URL directly
    await page.goto('/users/create');
    await expect(page).toHaveURL(/\/users\/create/);
  });

  test('should display create user form', async ({ page }) => {
    await expect(page.getByRole('heading', { name: 'Create User' })).toBeVisible();
    await expect(page.getByLabel('Email Address')).toBeVisible();
    await expect(page.getByLabel('Temporary Password')).toBeVisible();
    await expect(page.getByLabel('Account Role')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Create User' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Cancel' })).toBeVisible();
  });

  test('should have editor as default role', async ({ page }) => {
    const roleSelect = page.getByLabel('Account Role');
    await expect(roleSelect).toHaveValue('editor');
  });

  test('should show password strength indicator', async ({ page }) => {
    await page.getByLabel('Temporary Password').fill('ab');
    await expect(page.getByText(/weak/i)).toBeVisible();

    await page.getByLabel('Temporary Password').fill('StrongP@ss123!');
    await expect(page.getByText(/strong/i)).toBeVisible();
  });

  test('should navigate back when clicking cancel', async ({ page }) => {
    await page.getByRole('button', { name: 'Cancel' }).click();

    // Admin goes back to sites
    await expect(page).toHaveURL(/\/admin\/sites/);
  });
});
