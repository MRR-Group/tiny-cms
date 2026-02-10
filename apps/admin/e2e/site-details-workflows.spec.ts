import { test, expect, Page, Route } from '@playwright/test';

type Editor = {
  id: string;
  email: string;
  role: string;
};

type Site = {
  id: string;
  name: string;
  url: string;
  type: 'static' | 'dynamic';
  editorCount: number;
  createdAt: string;
  editors: Editor[];
};

function makeAdminToken(): string {
  const payload = 'eyJyb2xlIjoiYWRtaW4ifQ==';
  return `header.${payload}.signature`;
}

async function setAdminAuth(page: Page) {
  const token = makeAdminToken();
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

test.describe('Site Details Workflows', () => {
  test('supports assign, edit, unassign, and delete flows', async ({ page }) => {
    await setAdminAuth(page);

    const site: Site = {
      id: 'site-1',
      name: 'Docs Site',
      url: 'https://docs.example.com',
      type: 'static',
      editorCount: 0,
      createdAt: '2026-01-01T00:00:00+00:00',
      editors: [],
    };

    const users: Editor[] = [
      { id: 'user-1', email: 'editor1@example.com', role: 'editor' },
      { id: 'user-2', email: 'editor2@example.com', role: 'editor' },
    ];

    await page.route('**/api/admin/sites/site-1', async (route) => {
      const method = route.request().method();

      if (method === 'GET') {
        await fulfillJson(route, 200, site);
        return;
      }

      if (method === 'PUT') {
        const body = route.request().postDataJSON() as {
          name: string;
          url: string;
          type: 'static' | 'dynamic';
        };

        site.name = body.name;
        site.url = body.url;
        site.type = body.type;

        await fulfillJson(route, 204, {});
        return;
      }

      if (method === 'DELETE') {
        await fulfillJson(route, 204, {});
        return;
      }

      await route.fallback();
    });

    await page.route('**/api/admin/sites/site-1/users/*', async (route) => {
      const userId = route.request().url().split('/').pop();
      site.editors = site.editors.filter((editor) => editor.id !== userId);
      site.editorCount = site.editors.length;

      await fulfillJson(route, 204, {});
    });

    await page.route('**/api/admin/sites/assign', async (route) => {
      const body = route.request().postDataJSON() as { userId: string; siteId: string };

      if (body.siteId === site.id) {
        const user = users.find((candidate) => candidate.id === body.userId);
        if (user && !site.editors.some((editor) => editor.id === user.id)) {
          site.editors.push(user);
          site.editorCount = site.editors.length;
        }
      }

      await fulfillJson(route, 204, {});
    });

    await page.route('**/api/admin/sites', async (route) => {
      await fulfillJson(route, 200, []);
    });

    await page.route('**/api/admin/users', async (route) => {
      await fulfillJson(route, 200, users);
    });

    await page.goto('/admin/sites/site-1');

    await expect(page.getByRole('heading', { name: 'Docs Site' })).toBeVisible();
    await expect(page.getByText('No editors assigned.')).toBeVisible();

    await page.getByRole('button', { name: 'Assign User' }).click();
    await page.locator('select').selectOption('user-1');
    await page.getByRole('button', { name: 'Assign', exact: true }).click();

    await expect(page.getByText('editor1@example.com')).toBeVisible();

    await page.getByRole('button', { name: 'Edit Site' }).click();
    await page.getByLabel('Name').fill('Docs Site Updated');
    await page.getByLabel('URL').fill('docs-updated.example.com');
    await page.getByLabel('Type').selectOption('dynamic');
    await page.getByRole('button', { name: 'Save Changes' }).click();

    await expect(page.getByRole('heading', { name: 'Docs Site Updated' })).toBeVisible();
    await expect(page.getByRole('link', { name: /docs-updated\.example\.com/ })).toBeVisible();

    await page.getByRole('button', { name: 'Remove' }).click();
    await page.getByRole('button', { name: 'Remove' }).nth(1).click();

    await expect(page.getByText('No editors assigned.')).toBeVisible();

    await page.getByRole('button', { name: 'Delete Site' }).click();
    await page.getByRole('button', { name: 'Delete Site' }).nth(1).click();

    await expect(page).toHaveURL('/admin/sites');
    await expect(page.getByText('Site Management')).toBeVisible();
  });
});
