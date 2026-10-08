import { test, expect, type Page, type Request } from '@playwright/test';
import { collect } from '../lib/collect.js';

/**
 * 匿名账号页面：/anonymous/sign-in、/anonymous/sign-up、/anonymous/sign-up/:token、
 * /anonymous/forgot-password、/anonymous/reset-password/:token
 *
 * 后端是 v3：sessions / me / email-certifications / invites / users / password-resets。
 * 登录用 E2E_USER / E2E_PASS 走真实后端（只读）。
 *
 * 真实后端 vs 拦截：
 * - 只读、或不产生副作用的请求走真实后端：无效邀请、无效重置链接、错误验证码、
 *   用未注册的 `.invalid` 邮箱申请重置（服务端不发信）。
 * - **会发信或建账号的请求一律用 page.route 拦截**：开发机的 MAIL_MAILER 是真实 SMTP，
 *   收不到验证码；建出来的账号也没有删除接口，清不干净。这些步骤验证的是前端流程、
 *   错误展示和**前端发出的请求体**，后端行为由 api-v13 的 Pest 测试覆盖
 *   （SignUpV3Test、PasswordResetV3Test）。
 *
 * 截图输出目录由 E2E_SHOT 指定，默认 documents/testlog/assets/users_latest。
 */
const SHOT = process.env.E2E_SHOT ?? 'documents/testlog/assets/users_latest';

const shot = (page: Page, name: string) =>
  page.screenshot({ path: `${SHOT}/${name}.png`, fullPage: true });

/** 一个格式合法、但不可能存在的 v4 uuid */
const UNKNOWN_UUID = '00000000-0000-4000-8000-000000000000';
/** 格式合法（64 位字母数字）、但不可能存在的重置 token */
const UNKNOWN_RESET_TOKEN = 'e2e'.padEnd(64, '0');
/** RFC 2606 保留域：服务端查无此人，不会发信 */
const unregisteredEmail = () => `e2e-${Date.now()}@example.invalid`;

/** 拦截一个 v3 端点，记录请求体，按给定状态码与 body 回应 */
async function stub(page: Page, path: string, status: number, body?: unknown) {
  const seen: Request[] = [];
  await page.route(`**/api${path}`, (route) => {
    seen.push(route.request());
    return route.fulfill({
      status,
      contentType: status >= 400 ? 'application/problem+json' : 'application/json',
      body: body === undefined ? '' : JSON.stringify(body),
    });
  });
  return seen;
}

/** 422 Problem Details，与后端 bootstrap/app.php 的渲染形状一致 */
const problem422 = (errors: Record<string, string[]>) => ({
  type: 'urn:problem:validation-error',
  title: 'Unprocessable Content',
  status: 422,
  detail: Object.values(errors)[0][0],
  instance: '/api/v3/…',
  errors,
});

/**
 * 断言某个表单项下面出现了指定的校验错误。
 *
 * 按文字过滤而不是取「唯一一条」：antd 换错误时旧的那条有淡出动画，
 * 同一时刻 DOM 里会并存新旧两条。
 */
const expectFieldError = (page: Page, label: string, text: string) =>
  expect(
    page
      .locator('.ant-form-item')
      .filter({ has: page.locator('label').getByText(label, { exact: true }) })
      .locator('.ant-form-item-explain-error', { hasText: text })
  ).toBeVisible();

/* 全部是匿名页面：不复用 setup 存下的登录态 */
test.use({ storageState: { cookies: [], origins: [] } });

test.describe('自助注册 /anonymous/sign-up', () => {
  test('版本说明 → 邮箱验证 → 账号信息 → 完成', async ({ page }) => {
    const log = collect(page);
    const email = unregisteredEmail();

    await test.step('1. 版本说明：勾选「已了解」前不能下一步', async () => {
      await page.goto('anonymous/sign-up', { waitUntil: 'networkidle' });
      const next = page.getByRole('button', { name: '下一步' });
      await expect(next).toBeDisabled();
      // 图标替代了 emoji，不依赖系统 emoji 字体
      await expect(page.locator('.anticon-check').first()).toBeVisible();
      await shot(page, '01-signup-welcome');

      await page.getByText('我已经了解基础版的功能限制').click();
      await expect(next).toBeEnabled();
      await next.click();
      await expect(page.getByPlaceholder('电子邮箱')).toBeVisible();
      log.dump('1. 版本说明');
    });

    await test.step('2. 获取验证码（拦截：真发会寄出邮件）', async () => {
      const sent = await stub(page, '/v3/email-certifications', 204);

      await page.getByPlaceholder('电子邮箱').fill(email);
      await page.getByRole('button', { name: '获取验证码' }).click();
      await expect(page.locator('.ant-message')).toContainText('验证码已发送');
      await expect(page.getByRole('button', { name: /秒后重新获取/ })).toBeVisible();

      expect(sent).toHaveLength(1);
      expect(sent[0].postDataJSON()).toEqual({ email, lang: 'zh-Hans' });
      await shot(page, '02-signup-code-sent');
      log.dump('2. 获取验证码');
    });

    await test.step('3. 错误验证码：真实后端 422，错误挂在验证码输入框下', async () => {
      // 没有真的发过码，服务端 Cache 里查不到 → 必然 422 errors.code，不写库
      await page.getByPlaceholder('请输入邮件里的 6 位验证码').fill('000000');
      await page.getByRole('button', { name: '下一步' }).click();
      // 文案语言不在这里断言：v3 的错误文案目前总是英文，见下面 BUG-5
      await expect(
        page.locator('.ant-form-item-explain-error', {
          hasText: /验证码不正确或已过期|verification code is incorrect/,
        })
      ).toBeVisible();
      await shot(page, '03-signup-wrong-code');
      log.dump('3. 错误验证码（预期 HTTP 422）');
    });

    await test.step('4. 正确验证码（拦截）→ 进入账号信息，邮箱只读展示', async () => {
      const exchanged = await stub(page, '/v3/invites', 201, {
        data: { id: UNKNOWN_UUID, email, status: 'invited', created_at: null },
      });
      await page.getByPlaceholder('请输入邮件里的 6 位验证码').fill('123456');
      await page.getByRole('button', { name: '下一步' }).click();

      await expect(page.getByText(email, { exact: true })).toBeVisible();
      expect(exchanged[0].postDataJSON()).toEqual({ email, code: '123456' });
      await shot(page, '04-signup-account');
      log.dump('4. 换 invite');
    });

    await test.step('5. 账号信息：前端校验（用户名字符、两次密码）', async () => {
      await page.getByLabel('用户名(登录名)').fill('abc中文def');
      await page.getByLabel('密码', { exact: true }).fill('secret-pw');
      await page.getByLabel('确认密码').fill('secret-PW');
      await page.getByRole('button', { name: '下一步' }).click();

      await expectFieldError(page, '用户名(登录名)', '只允许字母、数字、下划线');
      await expectFieldError(page, '确认密码', '两次密码不一致');
      await shot(page, '05-signup-client-validation');
      log.dump('5. 前端校验');
    });

    await test.step('6. 后端 422（拦截）→ 字段错误挂到用户名下', async () => {
      await stub(page, '/v3/users', 422, problem422({ username: ['用户名已经存在。'] }));
      await page.getByLabel('用户名(登录名)').fill('e2e_taken_name');
      await page.getByLabel('确认密码').fill('secret-pw');
      await page.getByRole('button', { name: '下一步' }).click();

      await expectFieldError(page, '用户名(登录名)', '用户名已经存在');
      await shot(page, '06-signup-server-422');
      await page.unroute('**/api/v3/users');
      log.dump('6. 后端 422（预期 HTTP 422）');
    });

    await test.step('7. 建号成功（拦截）→ 完成页 → 去登录', async () => {
      const created = await stub(page, '/v3/users', 201, {
        data: { id: UNKNOWN_UUID, username: 'e2e_new_user', nickname: 'e2e_new_user', email, created_at: null },
      });
      await page.getByLabel('用户名(登录名)').fill('e2e_new_user');
      await page.getByRole('button', { name: '下一步' }).click();

      await expect(page.getByText('注册成功')).toBeVisible();
      // 请求体：带 invite，不带邮箱（邮箱来自 invite，客户端不该提交）
      const body = created[0].postDataJSON();
      expect(body).toMatchObject({
        invite: UNKNOWN_UUID,
        username: 'e2e_new_user',
        password: 'secret-pw',
        password_confirmation: 'secret-pw',
        lang: 'zh-Hans',
      });
      expect(body).not.toHaveProperty('email');
      await shot(page, '07-signup-done');

      await page.getByRole('button', { name: /登 *录/ }).click();
      await expect(page).toHaveURL(/anonymous\/sign-in/);
      log.dump('7. 完成');
    });
  });
});

test.describe('邀请注册 /anonymous/sign-up/:token', () => {
  test('无效邀请、旧路径重定向、有效邀请建号', async ({ page }) => {
    const log = collect(page);

    await test.step('8. 无效邀请（真实后端 404）→ 提示页', async () => {
      await page.goto(`anonymous/sign-up/${UNKNOWN_UUID}`, { waitUntil: 'networkidle' });
      await expect(page.getByText('邀请无效或已被使用')).toBeVisible();
      await shot(page, '08-invite-invalid');
      log.dump('8. 无效邀请（预期 HTTP 404）');
    });

    await test.step('9. v2 邀请邮件的旧路径 /anonymous/users/sign-up/:token 重定向', async () => {
      await page.goto(`anonymous/users/sign-up/${UNKNOWN_UUID}`, { waitUntil: 'networkidle' });
      await expect(page).toHaveURL(new RegExp(`/anonymous/sign-up/${UNKNOWN_UUID}$`));
      log.dump('9. 旧路径重定向（预期 HTTP 404）');
    });

    await test.step('10. 有效邀请（拦截）→ 显示邮箱 → 建号成功', async () => {
      const email = unregisteredEmail();
      await stub(page, `/v3/invites/${UNKNOWN_UUID}`, 200, {
        data: { id: UNKNOWN_UUID, email, status: 'invited', created_at: null },
      });
      const created = await stub(page, '/v3/users', 201, {
        data: { id: UNKNOWN_UUID, username: 'e2e_invited', nickname: '觉音', email, created_at: null },
      });

      await page.goto(`anonymous/sign-up/${UNKNOWN_UUID}`, { waitUntil: 'networkidle' });
      await expect(page.getByText(email, { exact: true })).toBeVisible();

      await page.getByLabel('用户名(登录名)').fill('e2e_invited');
      await page.getByLabel('密码', { exact: true }).fill('secret-pw');
      await page.getByLabel('确认密码').fill('secret-pw');
      await page.getByLabel('昵称').fill('觉音');
      await page.getByRole('button', { name: /提 *交/ }).click();

      await expect(page.getByText('注册成功')).toBeVisible();
      expect(created[0].postDataJSON()).toMatchObject({
        invite: UNKNOWN_UUID,
        username: 'e2e_invited',
        nickname: '觉音',
      });
      await shot(page, '10-invite-done');
      log.dump('10. 邀请建号');
    });
  });
});

test.describe('找回密码 /anonymous/forgot-password', () => {
  test('前端校验、提交后提示（真实后端，不发信）', async ({ page }) => {
    const log = collect(page);

    await test.step('11. 邮箱格式错：前端拦下，不发请求', async () => {
      await page.goto('anonymous/forgot-password', { waitUntil: 'networkidle' });
      let calls = 0;
      page.on('request', (r) => r.url().includes('/api/v3/password-resets') && calls++);

      await page.getByLabel('电子邮箱').fill('not-an-email');
      await page.getByRole('button', { name: /提 *交/ }).click();
      await expect(page.locator('.ant-form-item-explain-error')).toBeVisible();
      expect(calls).toBe(0);
      log.dump('11. 前端校验');
    });

    await test.step('12. 未注册邮箱 → 204 → 不泄露是否注册的成功提示', async () => {
      const response = page.waitForResponse('**/api/v3/password-resets');
      await page.getByLabel('电子邮箱').fill(unregisteredEmail());
      await page.getByRole('button', { name: /提 *交/ }).click();
      expect((await response).status()).toBe(204);

      await expect(page.locator('.ant-alert-success')).toContainText('如果该邮箱已注册');
      await shot(page, '12-forgot-sent');
      log.dump('12. 提交');
    });
  });
});

test.describe('重置密码 /anonymous/reset-password/:token', () => {
  test('无效链接、前端校验、重置成功', async ({ page }) => {
    const log = collect(page);

    await test.step('13. 无效 token（真实后端 404）→ 提示页 + 重新申请入口', async () => {
      await page.goto(`anonymous/reset-password/${UNKNOWN_RESET_TOKEN}`, { waitUntil: 'networkidle' });
      await expect(page.getByText('链接无效或已过期')).toBeVisible();
      await page.locator('.ant-result').getByRole('link', { name: '忘记密码' }).click();
      await expect(page).toHaveURL(/anonymous\/forgot-password$/);
      await shot(page, '13-reset-invalid');
      log.dump('13. 无效 token（预期 HTTP 404）');
    });

    await test.step('14. 有效 token（拦截）→ 显示账号 → 两次密码不一致被拦', async () => {
      await stub(page, `/v3/password-resets/${UNKNOWN_RESET_TOKEN}`, 200, {
        data: { username: 'e2e_reset_user', expires_at: '2099-01-01T00:00:00Z' },
      });
      await page.goto(`anonymous/reset-password/${UNKNOWN_RESET_TOKEN}`, { waitUntil: 'networkidle' });
      await expect(page.getByText('e2e_reset_user')).toBeVisible();

      await page.getByLabel('密码', { exact: true }).fill('new-secret');
      await page.getByLabel('确认密码').fill('new-secreT');
      await page.getByRole('button', { name: /提 *交/ }).click();
      await expectFieldError(page, '确认密码', '两次密码不一致');
      log.dump('14. 前端校验');
    });

    await test.step('15. 提交（拦截 PATCH 204）→ 成功页 → 去登录', async () => {
      // 同一 URL 的 GET 已被上一步拦截；这里按方法分流，PATCH 回 204
      const patched: Request[] = [];
      await page.route(`**/api/v3/password-resets/${UNKNOWN_RESET_TOKEN}`, (route) => {
        if (route.request().method() !== 'PATCH') return route.fallback();
        patched.push(route.request());
        return route.fulfill({ status: 204, body: '' });
      });

      await page.getByLabel('确认密码').fill('new-secret');
      await page.getByRole('button', { name: /提 *交/ }).click();

      await expect(page.getByText('重置密码成功')).toBeVisible();
      expect(patched[0].postDataJSON()).toEqual({
        password: 'new-secret',
        password_confirmation: 'new-secret',
      });
      await shot(page, '15-reset-done');

      await page.getByRole('button', { name: /登 *录/ }).click();
      await expect(page).toHaveURL(/anonymous\/sign-in/);
      log.dump('15. 重置成功');
    });
  });
});

const USER = process.env.E2E_USER ?? 'test161';
const PASS = process.env.E2E_PASS ?? '12345';

/**
 * 打开登录页。不等 networkidle：登录过的页面上有轮询请求，网络可能一直不空闲，
 * 等的是登录表单本身出现。
 */
async function openSignIn(page: Page, query = '') {
  await page.goto(`anonymous/sign-in${query}`);
  await expect(page.locator('form input:visible').first()).toBeVisible();
}

/** 在登录页填表并提交 */
async function submitSignIn(page: Page, login: string, password: string) {
  const inputs = page.locator('form input:visible');
  await inputs.nth(0).fill(login);
  await inputs.nth(1).fill(password);
  await page.getByRole('button', { name: /提 *交/ }).click();
}

test.describe('登录 /anonymous/sign-in', () => {
  test('密码错误、一次请求登录、刷新恢复登录态', async ({ page }) => {
    const log = collect(page);

    await test.step('16. 密码错误（真实后端 422）→ 表单上方提示', async () => {
      await openSignIn(page);
      await submitSignIn(page, USER, 'definitely-wrong-password');
      await expect(page.locator('.ant-alert-error')).toContainText('用户名或密码错误');
      await expect(page).toHaveURL(/anonymous\/sign-in/);
      await shot(page, '16-signin-wrong');
      log.dump('16. 密码错误（预期 HTTP 422）');
    });

    await test.step('17. 登录只发一次 v3 请求，不再调 v2', async () => {
      const calls: string[] = [];
      page.on('request', (r) => {
        const u = r.url();
        if (/\/api\/v[23]\/(sign-in|auth\/current|sessions|me)(\?|$)/.test(u)) {
          calls.push(`${r.method()} ${new URL(u).pathname}`);
        }
      });
      await submitSignIn(page, USER, PASS);
      await expect(page).toHaveURL(/\/workspace/);
      expect(calls).toEqual(['POST /api/v3/sessions']);
      log.dump('17. 登录');
    });

    await test.step('18. 刷新页面 → GET /v3/me 恢复登录态', async () => {
      const me = page.waitForResponse('**/api/v3/me');
      await page.reload({ waitUntil: 'networkidle' });
      expect((await me).status()).toBe(200);
      await expect(page).toHaveURL(/\/workspace/);
      await shot(page, '18-signin-restored');
      log.dump('18. 恢复登录态');
    });
  });

  test('?url= 只接受同源地址', async ({ page }) => {
    const log = collect(page);
    const base = new URL(test.info().project.use.baseURL ?? 'http://127.0.0.1:4000/pcd-v2026/');
    const b64 = (s: string) => Buffer.from(s).toString('base64');

    // 登录接口按「账号 + IP」每分钟限 5 次。这里测的是前端跳转逻辑，只真实登录一次，
    // 之后把这份真实结果（有效 token）回放给登录表单，后续页面的接口照常可用。
    const real = await page.request.post(new URL('/api/v3/sessions', base).href, {
      data: { login: USER, password: PASS },
    });
    expect(real.status()).toBe(201);
    const session = await real.json();
    await page.route('**/api/v3/sessions', (route) =>
      route.fulfill({ status: 201, contentType: 'application/json', body: JSON.stringify(session) })
    );

    await test.step('19. 同源地址 → 登录后跳回', async () => {
      const back = new URL('workspace/channel', base).href;
      await openSignIn(page, `?url=${b64(back)}`);
      await submitSignIn(page, USER, PASS);
      await expect(page).toHaveURL(back);
      log.dump('19. 同源跳回');
    });

    for (const [n, label, target] of [
      ['20', '外站', 'https://example.com/phish'],
      ['21', 'javascript:', 'javascript:document.title="pwned"'],
    ] as const) {
      await test.step(`${n}. ${label} → 不跳，进 workspace`, async () => {
        await page.context().clearCookies();
        await page.evaluate(() => {
          localStorage.clear();
          sessionStorage.clear();
        });
        await openSignIn(page, `?url=${b64(target)}`);
        await submitSignIn(page, USER, PASS);
        await expect(page).toHaveURL(/\/workspace$/);
        expect(await page.title()).not.toBe('pwned');
        log.dump(`${n}. ${label}`);
      });
    }
  });
});

test.describe('已知缺陷', () => {
  /**
   * BUG-5 v3 接口的错误文案没有本地化：中文界面里显示英文。
   *
   * 后端 `SetLocale` 中间件只挂在 web 组，api 组没有语言协商，`__()` 永远取 en
   * （v3-resource skill 第 11 条已记录）。前端 `src/api/client.ts` 也没有把界面语言
   * 告诉后端。修复需要两边一起：v3 路由组挂语言协商 + client 中间件带上界面语言。
   * 修复前保持红灯。
   */
  test('BUG-5 中文界面下，v3 的字段错误应为中文', async ({ page }) => {
    await page.goto('anonymous/sign-up', { waitUntil: 'networkidle' });
    await page.getByText('我已经了解基础版的功能限制').click();
    await page.getByRole('button', { name: '下一步' }).click();

    // 错误验证码走真实后端：不写库、不发信
    await page.getByPlaceholder('电子邮箱').fill(unregisteredEmail());
    await page.getByPlaceholder('请输入邮件里的 6 位验证码').fill('000000');
    await page.getByRole('button', { name: '下一步' }).click();

    const error = page.locator('.ant-form-item-explain-error').first();
    await expect(error).toBeVisible();
    await expect(error).toHaveText('验证码不正确或已过期。');
  });
});
