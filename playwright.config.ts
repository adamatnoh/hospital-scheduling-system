import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './tests/e2e',
    timeout: 60_000,
    use: {
        baseURL: process.env.APP_URL ?? 'http://127.0.0.1:8000',
        trace: 'retain-on-failure',
    },
    projects: [
        { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    ],
    webServer: {
        command: 'env -u DB_CONNECTION -u DB_DATABASE -u DB_URL php artisan serve --host=127.0.0.1 --port=8000',
        url: 'http://127.0.0.1:8000/login',
        reuseExistingServer: !process.env.CI,
        timeout: 120_000,
    },
});
