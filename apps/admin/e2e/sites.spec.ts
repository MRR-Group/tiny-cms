import { test, expect, Page } from '@playwright/test';

async function loginAsAdmin(page: Page) {
  await page.goto('/login');
  await page.getByLabel('Email Address').fill('admin@example.com');
  await page.getByLabel('Password').fill('password123');
  await page.getByRole('button', { name: 'Sign In' }).click();
  await expect(page).toHaveURL(/\/admin\/sites/);

  // Wait for the site management page to fully load
  await expect(page.getByText('Site Management')).toBeVisible();
  await expect(page.getByText('Existing Sites')).toBeVisible();
}

async function createSite(page: Page, name: string, url: string) {
  await page.getByLabel('Name').fill(name);
  await page.getByLabel('URL').fill(url);

  // Click and wait for the POST response
  const responsePromise = page.waitForResponse(
    resp => resp.url().includes('/api/admin/sites') && resp.request().method() === 'POST',
    { timeout: 10000 }
  );
  await page.getByRole('button', { name: 'Create Site' }).click();
  const response = await responsePromise;
  expect(response.status()).toBe(201);

  // Wait for the site to appear in the list after re-fetch
  await expect(page.getByText(name)).toBeVisible({ timeout: 10000 });
}

function uniqueSiteUrl(prefix: string): string {
  const uniqueId = `${Date.now()}-${Math.floor(Math.random() * 100000)}`;
  return `https://${prefix}-${uniqueId}.example.com`;
}

test.describe('Site Management', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('should display site management page with form and list', async ({ page }) => {
    await expect(page.getByText('Site Management')).toBeVisible();
    await expect(page.getByText('Create New Site')).toBeVisible();
    await expect(page.getByText('Existing Sites')).toBeVisible();
  });

  test('should display create site form with required fields', async ({ page }) => {
    await expect(page.getByLabel('Name')).toBeVisible();
    await expect(page.getByLabel('URL')).toBeVisible();
    await expect(page.getByLabel('Type')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Create Site' })).toBeVisible();
  });

  test('should create a new site', async ({ page }) => {
    const siteName = `Test Site ${Date.now()}`;
    await createSite(page, siteName, uniqueSiteUrl('test-site'));
  });

  test('should navigate to site details when clicking a site', async ({ page }) => {
    const siteName = `Details Site ${Date.now()}`;
    await createSite(page, siteName, uniqueSiteUrl('details-site'));

    await page.getByRole('link', { name: siteName }).click();

    await expect(page).toHaveURL(/\/admin\/sites\/.+/);
    await expect(page.getByRole('heading', { name: siteName })).toBeVisible();
  });

  test('should show site details page with actions', async ({ page }) => {
    const siteName = `Action Site ${Date.now()}`;
    await createSite(page, siteName, uniqueSiteUrl('action-site'));

    await page.getByRole('link', { name: siteName }).click();
    await expect(page).toHaveURL(/\/admin\/sites\/.+/);

    await expect(page.getByRole('button', { name: 'Edit Site' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Assign User' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Delete Site' })).toBeVisible();
    await expect(page.getByRole('button', { name: /back to sites/i })).toBeVisible();
  });

  test('should navigate back to sites list from details', async ({ page }) => {
    const siteName = `Back Nav Site ${Date.now()}`;
    await createSite(page, siteName, uniqueSiteUrl('backnav-site'));

    await page.getByRole('link', { name: siteName }).click();
    await expect(page).toHaveURL(/\/admin\/sites\/.+/);

    await page.getByRole('button', { name: /back to sites/i }).click();
    await expect(page).toHaveURL(/\/admin\/sites$/);
  });

  test('should delete a site from details page', async ({ page }) => {
    const siteName = `Delete Me ${Date.now()}`;
    await createSite(page, siteName, uniqueSiteUrl('delete-me'));

    await page.getByRole('link', { name: siteName }).click();
    await expect(page).toHaveURL(/\/admin\/sites\/.+/);

    // Click delete button
    await page.getByRole('button', { name: 'Delete Site' }).first().click();

    // Confirm in modal
    await expect(page.getByText(/are you sure you want to delete/i)).toBeVisible();
    await page.getByRole('button', { name: 'Delete Site' }).nth(1).click();

    // Should redirect back
    await expect(page).toHaveURL(/\/admin\/sites$/, { timeout: 10000 });
    await expect(page.getByText(siteName)).not.toBeVisible();
  });
});
