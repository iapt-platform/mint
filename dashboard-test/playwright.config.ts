import { defineConfig, devices } from '@playwright/test';

/**
 * dashboard-v6 端到端测试配置
 * 文档: https://playwright.dev/docs/test-configuration
 *
 * 前置：dev server 需已启动
 *   cd ../api-v13     && php artisan serve
 *   cd ../dashboard-v6 && npm run dev
 */
export default defineConfig({
  testDir: './src/tests',
  /* 本项目是对开发环境的手工测试替代，串行执行更贴近真人操作，日志也更好读 */
  fullyParallel: false,
  workers: 1,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  timeout: 90_000,
  expect: { timeout: 15_000 },
  reporter: [['list'], ['html', { outputFolder: 'playwright-report', open: 'never' }]],

  use: {
    baseURL: process.env.E2E_BASE ?? 'http://127.0.0.1:4000/pcd-v2026/',
    viewport: { width: 1600, height: 1000 },
    trace: 'retain-on-failure',
    video: 'retain-on-failure',
    actionTimeout: 30_000,
  },

  projects: [
    /* 登录一次，把会话存到 playwright/.auth/user.json */
    { name: 'setup', testMatch: /.*\.setup\.ts/ },
    {
      name: 'chromium',
      use: {
        ...devices['Desktop Chrome'],
        storageState: 'playwright/.auth/user.json',
      },
      dependencies: ['setup'],
    },
  ],
});
