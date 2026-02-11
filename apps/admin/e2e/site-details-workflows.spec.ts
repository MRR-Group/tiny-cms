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

type SiteSection = {
  id: string;
  type: string;
  title: string;
  data: Record<string, unknown>;
  position: number;
  createdAt: string;
};

function makeAdminToken(): string {
  const payload = Buffer.from(
    JSON.stringify({ role: 'admin', sub: '00000000-0000-0000-0000-000000000001' })
  ).toString('base64');
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
      editorCount: 1,
      createdAt: '2026-01-01T00:00:00+00:00',
      editors: [{ id: '00000000-0000-0000-0000-000000000001', email: 'admin@example.com', role: 'admin' }],
    };

    const users: Editor[] = [
      { id: 'user-1', email: 'editor1@example.com', role: 'editor' },
      { id: 'user-2', email: 'editor2@example.com', role: 'editor' },
    ];

    const sections: SiteSection[] = [];

    await page.route('**/api/admin/sites/site-1', async (route) => {
      const method = route.request().method();

      if (method === 'GET') {
        await fulfillJson(route, 200, {
          ...site,
          sections,
        });
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

    await page.route('**/api/sites/site-1/sections', async (route) => {
      const method = route.request().method();

      if (method === 'GET') {
        await fulfillJson(route, 200, sections);
        return;
      }

      if (method === 'POST') {
        const body = route.request().postDataJSON() as {
          type: string;
          title: string;
        };

        const section: SiteSection = {
          id: `sec-${sections.length + 1}`,
          type: body.type,
          title: body.title,
          data: {},
          position: sections.length,
          createdAt: '2026-01-01T00:00:00+00:00',
        };

        sections.push(section);
        await fulfillJson(route, 201, section);
        return;
      }

      await route.fallback();
    });

    await page.route('**/api/admin/sites/site-1/sections', async (route) => {
      const method = route.request().method();

      if (method === 'POST') {
        const body = route.request().postDataJSON() as { type: string; title: string };
        const section: SiteSection = {
          id: `sec-${sections.length + 1}`,
          type: body.type,
          title: body.title,
          data: {},
          position: sections.length,
          createdAt: '2026-01-01T00:00:00+00:00',
        };
        sections.push(section);
        await fulfillJson(route, 201, section);
        return;
      }

      await route.fallback();
    });

    await page.route('**/api/admin/sites/site-1/sections/*', async (route) => {
      const method = route.request().method();
      const sectionId = route.request().url().split('/').pop() as string;

      if (method === 'DELETE') {
        const remaining = sections.filter((item) => item.id !== sectionId);
        sections.splice(0, sections.length, ...remaining);
        await fulfillJson(route, 204, {});
        return;
      }

      await route.fallback();
    });

    await page.route('**/api/sites/site-1/sections/*', async (route) => {
      const method = route.request().method();
      const sectionId = route.request().url().split('/').pop() as string;

      if (method === 'PUT') {
        const body = route.request().postDataJSON() as { title: string; data: Record<string, unknown> };
        const section = sections.find((item) => item.id === sectionId);
        if (section) {
          section.title = body.title;
          section.data = body.data;
          await fulfillJson(route, 200, section);
          return;
        }
      }

      if (method === 'DELETE') {
        const remaining = sections.filter((item) => item.id !== sectionId);
        sections.splice(0, sections.length, ...remaining);
        await fulfillJson(route, 204, {});
        return;
      }

      await route.fallback();
    });

    await page.route('**/api/sites/site-1/sections/sec-1/items', async (route) => {
      const method = route.request().method();

      if (method === 'GET') {
        const section = sections.find((item) => item.id === 'sec-1');
        const sectionData = (section?.data ?? {}) as { items?: Array<Record<string, unknown>> };
        await fulfillJson(route, 200, Array.isArray(sectionData.items) ? sectionData.items : []);
        return;
      }

      if (method === 'POST') {
        const body = route.request().postDataJSON() as { data: Record<string, unknown> };
        const section = sections.find((item) => item.id === 'sec-1');
        if (section) {
          const data = (section.data ?? {}) as { items?: Array<Record<string, unknown>> };
          const items = Array.isArray(data.items) ? data.items : [];
          const created = { id: `item-${items.length + 1}`, ...body.data };
          section.data = { ...data, items: [...items, created] };
          await fulfillJson(route, 201, created);
          return;
        }
      }

      await route.fallback();
    });

    await page.route('**/api/sites/site-1/sections/order', async (route) => {
      await fulfillJson(route, 204, {});
    });

    await page.route('**/api/admin/users', async (route) => {
      await fulfillJson(route, 200, users);
    });

    await page.goto('/admin/sites/site-1');

    await expect(page.getByRole('heading', { name: 'Docs Site' })).toBeVisible();
    await expect(page.getByText('admin@example.com')).toBeVisible();
    await expect(page.getByText('No sections yet. Add the first section.')).toBeVisible();

    await page.getByLabel('Section Type').selectOption('image');
    await page.getByLabel('Title').fill('Hero Banner');
    await page.getByRole('button', { name: 'Create Section' }).click();

    await expect(page.getByText('Hero Banner')).toBeVisible();

    await page
      .locator('.border.border-slate-200.rounded-xl.p-4.bg-white')
      .filter({ hasText: 'Hero Banner' })
      .getByRole('button', { name: 'Edit', exact: true })
      .click();
    await expect(page).toHaveURL('/admin/sites/site-1/sections/sec-1');
    await expect(page.getByRole('button', { name: '+ Add Item' })).toBeVisible();
    await page.getByLabel('Title').fill('Hero Banner Updated');
    await page.getByRole('button', { name: 'Save Section' }).click();
    await page.getByRole('button', { name: 'Understand' }).click();
    await page.getByRole('button', { name: 'Back to Site' }).click();
    await expect(page).toHaveURL('/admin/sites/site-1');

    await page.getByLabel('Section Type').selectOption('news');
    await page.getByLabel('Title').fill('About Section');
    await page.getByRole('button', { name: 'Create Section' }).click();

    await expect(page.getByText('About Section')).toBeVisible();
    await expect(page.locator('.border.border-slate-200.rounded-xl.p-4.bg-white')).toHaveCount(2);
    await page.getByRole('button', { name: 'Delete' }).nth(1).click();
    await page
      .locator('div.fixed.inset-0')
      .filter({ hasText: 'Delete Section' })
      .getByRole('button', { name: 'Delete Section' })
      .click();
    await expect(page.locator('.border.border-slate-200.rounded-xl.p-4.bg-white')).toHaveCount(1);

    await page.getByRole('button', { name: 'Assign User' }).click();
    await page.locator('form').filter({ hasText: 'Select User' }).locator('select').selectOption('user-1');
    await page.getByRole('button', { name: 'Assign', exact: true }).click();

    await expect(page.getByText('editor1@example.com')).toBeVisible();

    await page.getByRole('button', { name: 'Edit Site' }).click();
    const editForm = page.locator('form').filter({ has: page.getByRole('button', { name: 'Save Changes' }) });
    await editForm.locator('#name').fill('Docs Site Updated');
    await editForm.locator('#url').fill('docs-updated.example.com');
    await editForm.locator('#type').selectOption('dynamic');
    await page.getByRole('button', { name: 'Save Changes' }).click();

    await expect(page.getByRole('heading', { name: 'Docs Site Updated' })).toBeVisible();
    await expect(page.getByRole('link', { name: /docs-updated\.example\.com/ })).toBeVisible();

    await page
      .locator('div.py-3.flex.justify-between.items-center')
      .filter({ hasText: 'editor1@example.com' })
      .getByRole('button', { name: 'Remove' })
      .click();
    await page
      .locator('div.fixed.inset-0')
      .filter({ hasText: 'Remove Editor' })
      .getByRole('button', { name: 'Remove' })
      .click();

    await expect(page.getByText('editor1@example.com')).not.toBeVisible();

    await page.getByRole('button', { name: 'Delete Site' }).click();
    await page.getByRole('button', { name: 'Delete Site' }).nth(1).click();

    await expect(page).toHaveURL('/admin/sites');
    await expect(page.getByText('Site Management')).toBeVisible();
  });
});
