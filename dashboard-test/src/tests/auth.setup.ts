import { test as setup, expect } from '@playwright/test';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
export const AUTH_FILE = path.join(__dirname, '../../playwright/.auth/user.json');

const USER = process.env.E2E_USER ?? 'test161';
const PASS = process.env.E2E_PASS ?? '12345';

setup('登录并保存会话', async ({ page }) => {
  await page.goto('anonymous/sign-in');

  // ProForm 会渲染隐藏 input，必须用 :visible 过滤
  const inputs = page.locator('form input:visible');
  await inputs.nth(0).fill(USER);
  await inputs.nth(1).fill(PASS);
  await page.getByRole('button', { name: /提 *交/ }).click();

  // 登录成功会跳离 sign-in
  await expect(page).not.toHaveURL(/sign-in/, { timeout: 20_000 });

  await page.context().storageState({ path: AUTH_FILE });
});
