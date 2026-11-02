import { defineConfig, devices } from '@playwright/test';

// Point the suite at any running copy of HSS, e.g.
//   PLAYWRIGHT_BASE_URL=http://127.0.0.1:8001 npx playwright test
// If nothing is listening there, Playwright starts `php artisan serve` on that host and port.
const baseURL = (process.env.PLAYWRIGHT_BASE_URL ?? 'http://127.0.0.1:8001').replace(/\/$/, '');
const { hostname, port } = new URL(baseURL);

export default defineConfig({
    testDir: './tests/e2e',
    timeout: 60_000,
    use: {
        baseURL,
        trace: 'retain-on-failure',
    },
    projects: [
        { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    ],
    webServer: {
        command: `env -u DB_CONNECTION -u DB_DATABASE -u DB_URL php artisan serve --host=${hostname} --port=${port || 80}`,
        url: `${baseURL}/login`,
        reuseExistingServer: !process.env.CI,
        timeout: 120_000,
    },
});
