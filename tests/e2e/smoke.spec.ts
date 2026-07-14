import { expect, test } from '@playwright/test';

test.describe('storefront and authentication', () => {
  test('renders the storefront catalogue', async ({ page }) => {
    await page.goto('/catalogue');

    await expect(page.locator('body')).toContainText(/catalogue|produits|boutique/i);
  });

  test('renders the cart and checkout entry points', async ({ page }) => {
    await page.goto('/cart');
    await expect(page.locator('body')).toContainText(/panier|cart/i);

    await page.goto('/checkout');
    await expect(page.locator('body')).toContainText(/commande|checkout|paiement|panier/i);
  });

  test('shows an error for invalid credentials', async ({ page }) => {
    await page.goto('/auth/login');
    await page.getByLabel('Email professionnel').fill('invalid@example.test');
    await page.locator('#password').fill('invalid-password');
    await page.getByRole('button', { name: 'Se connecter' }).click();

    await expect(page.locator('.lovable-auth__error')).toBeVisible();
  });

  test('navigates categories and filters catalogue products', async ({ page }) => {
    await page.goto('/catalogue');
    await expect(page.getByRole('heading', { name: 'Catalogue' })).toBeVisible();
    await expect(page.getByText(/produit\(s\)/)).toBeVisible();

    const categoryLink = page.getByRole('link', { name: 'Mode' }).first();
    await expect(categoryLink).toHaveAttribute('href', /categories\/mode/);
    await categoryLink.click();
    await expect(page.getByRole('heading', { name: 'Mode' })).toBeVisible();
    await expect(page.getByText('2 produit(s)')).toBeVisible();

    await page.locator('input[placeholder="Min"]').first().fill('500');
    await expect(page.getByText('1 produit(s)')).toBeVisible();
    await expect(page.getByRole('link', { name: 'Sac Medina cuir', exact: true })).toBeVisible();
  });
});

test.describe('backoffice authentication', () => {
  test.skip(
    !process.env.E2E_ADMIN_EMAIL || !process.env.E2E_ADMIN_PASSWORD,
    'Set E2E_ADMIN_EMAIL and E2E_ADMIN_PASSWORD for the authenticated journey',
  );

  test('admin can open the dashboard', async ({ page }) => {
    await page.goto('/auth/login');
    await page.getByLabel('Email professionnel').fill(process.env.E2E_ADMIN_EMAIL!);
    await page.locator('#password').fill(process.env.E2E_ADMIN_PASSWORD!);
    const loginResponse = page.waitForResponse((response) => response.url().endsWith('/api/auth/login'));
    await page.getByRole('button', { name: 'Se connecter' }).click();
    await expect((await loginResponse).status()).toBe(200);

    await page.goto('/admin/dashboard');
    await expect(page).toHaveURL(/\/admin\//);
    await expect(page.locator('body')).toContainText(/tableau de bord|dashboard/i);
  });
});

test.describe('API catalogue CRUD and boutique isolation', () => {
  test.skip(
    !process.env.E2E_ADMIN_EMAIL || !process.env.E2E_ADMIN_PASSWORD,
    'Set E2E_ADMIN_EMAIL and E2E_ADMIN_PASSWORD for authenticated API journeys',
  );

  test('admin can create, update and delete a category', async ({ request }) => {
    const host = new URL(process.env.BASE_URL ?? 'http://demo-hanooti.localhost:8082').host;
    const login = await request.post('/api/auth/login', {
      headers: { Host: host },
      data: { email: process.env.E2E_ADMIN_EMAIL, password: process.env.E2E_ADMIN_PASSWORD },
    });
    expect(login.ok()).toBeTruthy();
    const { accessToken } = await login.json();
    const headers = { Authorization: `Bearer ${accessToken}`, Host: host };

    const created = await request.post('/api/categories', {
      headers,
      data: { name: `E2E Category ${Date.now()}` },
    });
    expect(created.status()).toBe(201);
    const category = await created.json();

    try {
      const updated = await request.patch(`/api/categories/${category.id}`, {
        headers: { ...headers, 'Content-Type': 'application/merge-patch+json' },
        data: { name: `E2E Category Updated ${Date.now()}` },
      });
      expect(updated.ok()).toBeTruthy();
    } finally {
      const deleted = await request.delete(`/api/categories/${category.id}`, { headers });
      expect([200, 204]).toContain(deleted.status());
    }
  });
});
