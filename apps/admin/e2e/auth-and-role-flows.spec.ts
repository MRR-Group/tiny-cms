import { test, expect, Page, Route } from '@playwright/test';

function makeToken(role: 'admin' | 'editor'): string {
  const payload = role === 'admin' ? 'eyJyb2xlIjoiYWRtaW4ifQ==' : 'eyJyb2xlIjoiZWRpdG9yIn0=';
  return `header.${payload}.signature`;
}

async function setAuthToken(page: Page, role: 'admin' | 'editor') {
  const token = makeToken(role);
  await page.addInitScript((storedToken) => {
    window.localStorage.setItem('authToken', storedToken);
  }, token);
}

async function fulfillJson(route: Route, status: number, body: unknown) {
  await route.fulfill({
    status,
    contentType: 'application/json',
    body: JSON.stringify(body),
  });
}

test.describe('Auth, Role, and User Flows', () => {
  test('redirects to force password change when backend requires it', async ({ page }) => {
    await page.route('**/api/auth/login', async (route) => {
      await fulfillJson(route, 200, {
        token: makeToken('admin'),
        requirePasswordChange: true,
      });
    });

    await page.goto('/login');
    await page.getByLabel('Email Address').fill('admin@example.com');
    await page.getByLabel('Password').fill('temporary-password');
    await page.getByRole('button', { name: 'Sign In' }).click();

    await expect(page).toHaveURL(/\/change-password$/);
    await expect(page.getByRole('button', { name: 'Update Password' })).toBeVisible();
  });

  test('validates force-change-password form before submit', async ({ page }) => {
    await setAuthToken(page, 'admin');
    await page.goto('/change-password');

    await page.getByLabel('Current Password').fill('old-password');
    await page.getByLabel(/^New Password$/).fill('new-password-123');
    await page.getByLabel('Confirm New Password').fill('different-password-123');
    await page.getByRole('button', { name: 'Update Password' }).click();

    await expect(page.getByText('Passwords do not match')).toBeVisible();

    await page.getByLabel(/^New Password$/).fill('short');
    await page.getByLabel('Confirm New Password').fill('short');
    await page.getByRole('button', { name: 'Update Password' }).click();

    await expect(page.getByText('Password must be at least 8 characters long')).toBeVisible();
  });

  test('submits force-change-password and returns to app', async ({ page }) => {
    await setAuthToken(page, 'admin');

    await page.route('**/api/auth/change-password', async (route) => {
      await fulfillJson(route, 204, {});
    });
    await page.route('**/api/admin/sites', async (route) => {
      await fulfillJson(route, 200, []);
    });

    await page.goto('/change-password');
    await page.getByLabel('Current Password').fill('old-password-123');
    await page.getByLabel(/^New Password$/).fill('StrongerPassword123!');
    await page.getByLabel('Confirm New Password').fill('StrongerPassword123!');
    await page.getByRole('button', { name: 'Update Password' }).click();

    await expect(page).toHaveURL(/\/admin\/sites$/);
    await expect(page.getByText('Site Management')).toBeVisible();
  });

  test('submits create-user form and shows success message', async ({ page }) => {
    await setAuthToken(page, 'admin');

    await page.route('**/api/admin/users', async (route) => {
      if (route.request().method() === 'POST') {
        await fulfillJson(route, 201, {});
        return;
      }
      await fulfillJson(route, 200, []);
    });

    await page.goto('/users/create');
    await page.getByLabel('Email Address').fill(`new-user-${Date.now()}@example.com`);
    await page.getByLabel('Temporary Password').fill('StrongPass123!');
    await page.getByLabel('Account Role').selectOption('editor');
    await page.getByRole('button', { name: 'Create User' }).click();

    await expect(page.getByText('User created successfully! Redirecting...')).toBeVisible();
  });

  test('shows API error when create-user request fails', async ({ page }) => {
    await setAuthToken(page, 'admin');
    await page.route('**/api/admin/users', async (route) => {
      await fulfillJson(route, 400, { error: { message: 'User already exists' } });
    });

    await page.goto('/users/create');
    await page.getByLabel('Email Address').fill('admin@example.com');
    await page.getByLabel('Temporary Password').fill('StrongPass123!');
    await page.getByRole('button', { name: 'Create User' }).click();

    await expect(page.getByText('User already exists')).toBeVisible();
  });

  test('renders editor dashboard on default route with assigned sites', async ({ page }) => {
    await setAuthToken(page, 'editor');

    await page.route('**/api/sites', async (route) => {
      await fulfillJson(route, 200, [
        {
          id: 'site-1',
          name: 'Editor Site',
          url: 'https://editor.example.com',
          type: 'static',
          editorCount: 1,
          createdAt: '2026-01-01T00:00:00+00:00',
        },
      ]);
    });

    await page.goto('/');

    await expect(page).toHaveURL('/');
    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
    await expect(page.getByText('Editor Site')).toBeVisible();
    await expect(page.getByText('Add User')).not.toBeVisible();
  });
});
