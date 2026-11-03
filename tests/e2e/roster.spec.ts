import { expect, test } from '@playwright/test';
import fs from 'node:fs';

// Screenshots land in test-results/screenshots unless PLAYWRIGHT_SHOTS points elsewhere.
// The README images in docs/screenshots are curated by hand and are not overwritten.
const shot = process.env.PLAYWRIGHT_SHOTS ?? 'test-results/screenshots';

test.beforeAll(() => {
    fs.mkdirSync(shot, { recursive: true });
});

async function login(page: import('@playwright/test').Page, email: string) {
    await page.goto('/login');
    await page.getByLabel('Email', { exact: true }).fill(email);
    await page.getByLabel('Password', { exact: true }).fill('password');
    await page.getByRole('button', { name: 'Log in' }).click();
    await expect(page).toHaveURL(/dashboard/);
}

test('a doctor applies, a scheduler approves, and the calendar shows the department', async ({ page }) => {
    await page.goto('/');
    await expect(page.getByRole('heading', { name: /Doctor rosters/ })).toBeVisible();
    await page.screenshot({ path: `${shot}/landing.png`, fullPage: true });

    await page.goto('/login');
    await expect(page.getByRole('heading', { name: 'Sign in to HSS' })).toBeVisible();
    await page.screenshot({ path: `${shot}/login.png`, fullPage: true });

    await login(page, 'doctor@hss.test');
    await expect(page.getByRole('heading', { name: 'Dashboard', level: 1 })).toBeVisible();
    await page.screenshot({ path: `${shot}/dashboard-doctor.png`, fullPage: true });

    await page.goto('/leave');
    const start = new Date();
    start.setDate(start.getDate() + 20);
    const end = new Date(start);
    end.setDate(start.getDate() + 1);
    const iso = (date: Date) => date.toISOString().slice(0, 10);
    await page.getByLabel('Start date').fill(iso(start));
    await page.getByLabel('End date').fill(iso(end));
    await page.getByLabel('Reason').fill('Postgraduate teaching at UTM');
    await page.getByRole('button', { name: 'Submit for review' }).click();
    await expect(page.getByText('Postgraduate teaching at UTM').first()).toBeVisible();
    await page.screenshot({ path: `${shot}/history.png`, fullPage: true });

    await page.goto('/your-schedule');
    await expect(page.getByRole('heading', { name: 'Your Schedule', level: 1 })).toBeVisible();
    await page.screenshot({ path: `${shot}/your-schedule.png`, fullPage: true });

    await page.getByRole('button', { name: 'Log out' }).click();

    await login(page, 'scheduler@hss.test');
    await page.goto('/department/on_call');
    await expect(page.locator('.fc')).toBeVisible();
    await page.screenshot({ path: `${shot}/department-calendar.png`, fullPage: true });

    await page.goto('/generate/on_call');
    await expect(page.getByRole('heading', { name: /On Call/, level: 1 })).toBeVisible();
    await page.screenshot({ path: `${shot}/generator-preview.png`, fullPage: true });

    await page.goto('/review');
    const card = page.locator('article', { hasText: 'Postgraduate teaching at UTM' }).first();
    await expect(card).toBeVisible();
    await page.screenshot({ path: `${shot}/review.png`, fullPage: true });
    await card.getByRole('button', { name: 'Approve' }).click();
    await expect(page.getByText('Request approved.')).toBeVisible();

    await page.goto('/staff');
    await page.screenshot({ path: `${shot}/unauthorized-or-staff.png`, fullPage: true });
});

test('each role can open its home', async ({ page }) => {
    await login(page, 'admin@hss.test');
    await page.goto('/staff');
    await expect(page.getByRole('heading', { name: 'Staff', level: 1 })).toBeVisible();
    await page.screenshot({ path: `${shot}/staff.png`, fullPage: true });
    await page.getByRole('button', { name: 'Log out' }).click();

    await login(page, 'doctor@hss.test');
    await page.goto('/staff');
    await expect(page.getByRole('heading', { name: /another role/ })).toBeVisible();
});
