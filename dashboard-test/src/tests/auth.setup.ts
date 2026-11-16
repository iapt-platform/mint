import { test as setup, expect, type Page } from "@playwright/test";
import { authFile, type Account } from "../lib/api.js";
import {
  USER,
  PASS,
  STUDENT_USER,
  STUDENT_PASS,
  ASSISTANT_USER,
  ASSISTANT_PASS,
} from "../lib/env.js";

export const AUTH_FILE = authFile("user");

async function signIn(page: Page, user: string, pass: string, who: Account) {
  await page.goto("anonymous/sign-in");

  // ProForm 会渲染隐藏 input，必须用 :visible 过滤
  const inputs = page.locator("form input:visible");
  await inputs.nth(0).fill(user);
  await inputs.nth(1).fill(pass);
  await page.getByRole("button", { name: /提 *交/ }).click();

  // 登录成功会跳离 sign-in
  await expect(page).not.toHaveURL(/sign-in/, { timeout: 20_000 });

  await page.context().storageState({ path: authFile(who) });
}

setup("登录并保存会话", async ({ page }) => {
  await signIn(page, USER, PASS, "user");
});

/* 课程测试（course.spec）用的学生、助教账号：可选，没配就不登录，相关用例会跳过 */
setup("登录学生账号（E2E_STUDENT_USER）", async ({ page }) => {
  setup.skip(!STUDENT_USER || !STUDENT_PASS, "未配置 E2E_STUDENT_USER / E2E_STUDENT_PASS");
  await signIn(page, STUDENT_USER, STUDENT_PASS, "student");
});

setup("登录助教账号（E2E_ASSISTANT_USER）", async ({ page }) => {
  setup.skip(!ASSISTANT_USER || !ASSISTANT_PASS, "未配置 E2E_ASSISTANT_USER / E2E_ASSISTANT_PASS");
  await signIn(page, ASSISTANT_USER, ASSISTANT_PASS, "assistant");
});
