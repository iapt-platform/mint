import { test, expect, type APIRequestContext } from '@playwright/test';
import { call } from '../lib/api.js';
import { collect } from '../lib/collect.js';
import { BASE, HAS_STUDENT, STUDENT_PASS, STUDENT_USER, shotDir } from '../lib/env.js';

/**
 * 团队邀请链接：成员页「获取邀请链接」→ 别人打开 /team-invite/:token →（登录）→ 同意 → 以只读成员加入。
 * v3：POST /v3/groups/{group}/invite-links、GET /v3/group-invites/{token}、POST /v3/me/groups。
 *
 * E2E_USER 是团队 owner；E2E_STUDENT_USER 充当被邀请的人（没配就跳过）。
 * 团队沿用「e2e 团队空间」。开跑前先把被邀请人移出团队，跑完留着（看结果用）。
 */
const SHOT = shotDir('group-invite');
const TEAM = 'e2e 团队空间';

async function teamId(request: APIRequestContext): Promise<string> {
  const find = async () => {
    const r = await call(request, 'GET', '/v3/me/groups?owned=true&per_page=200');
    expect(r.status).toBe(200);
    return (r.body.data as { id: string; name: string }[]).find((g) => g.name === TEAM)?.id;
  };
  let id = await find();
  if (!id) {
    const created = await call(request, 'POST', '/v3/groups', { name: TEAM });
    expect(created.status).toBe(201);
    id = created.body.data.id;
  }
  return id!;
}

async function memberOf(request: APIRequestContext, team: string, userId: string) {
  const r = await call(request, 'GET', `/v3/groups/${team}/members?per_page=200`);
  return (r.body.data as { id: string; role: string; user?: { id: string } }[]).find(
    (m) => m.user?.id === userId,
  );
}

test.describe('团队邀请链接', () => {
  test.skip(!HAS_STUDENT, '需要第二个账号 E2E_STUDENT_USER 当被邀请人');

  test('owner 取链接；未登录打开 → 登录 → 同意，以只读成员加入', async ({ page, request, browser }) => {
    const log = collect(page);
    const team = await teamId(request);
    const student = (await call(request, 'GET', '/v3/me', undefined, 'student')).body.data as {
      id: string;
    };
    const stale = await memberOf(request, team, student.id);
    if (stale) await call(request, 'DELETE', `/v3/groups/${team}/members/${stale.id}`);

    let link = '';
    await test.step('1. 成员页：「获取邀请链接」弹出链接', async () => {
      await page.goto(`workspace/team/${team}`, { waitUntil: 'networkidle' });
      const issued = page.waitForResponse(
        (r) => r.url().includes(`/api/v3/groups/${team}/invite-links`) && r.request().method() === 'POST',
      );
      await page.getByRole('button', { name: /获取邀请链接/ }).click();
      expect((await issued).status()).toBe(201);
      const modal = page.locator('.ant-modal:visible');
      link = await modal.locator('input').inputValue();
      expect(link).toMatch(/\/team-invite\/[\w-]+\.[\w-]+\.[\w-]+$/);
      await expect(modal).toContainText('只读成员');
      await page.screenshot({ path: `${SHOT}/01-link.png`, fullPage: true });
      log.dump('1. 取链接');
    });

    // 被邀请人：全新的浏览器上下文，没有登录态
    const ctx = await browser.newContext({ baseURL: BASE, storageState: { cookies: [], origins: [] } });
    const guest = await ctx.newPage();
    const guestLog = collect(guest);

    await test.step('2. 未登录打开：看到邀请与「登录后加入」', async () => {
      await guest.goto(link, { waitUntil: 'networkidle' });
      await expect(guest.getByText(new RegExp(`邀请你加入团队空间「${TEAM}」`))).toBeVisible();
      await expect(guest.getByText('加入后的角色：只读成员')).toBeVisible();
      await guest.screenshot({ path: `${SHOT}/02-guest.png`, fullPage: true });
      await guest.getByRole('button', { name: /登录后加入/ }).click();
      await expect(guest).toHaveURL(/anonymous\/sign-in\?url=/);
    });

    await test.step('3. 登录后回到邀请页，点同意加入', async () => {
      // ProForm 会渲染隐藏 input，必须用 :visible 过滤（同 auth.setup）
      const inputs = guest.locator('form input:visible');
      await inputs.nth(0).fill(STUDENT_USER);
      await inputs.nth(1).fill(STUDENT_PASS);
      await guest.getByRole('button', { name: /提 *交/ }).click();
      await expect(guest).toHaveURL(/\/team-invite\//, { timeout: 20_000 });
      const accepted = guest.waitForResponse(
        (r) => r.url().endsWith('/api/v3/me/groups') && r.request().method() === 'POST',
      );
      await guest.getByRole('button', { name: /同意加入/ }).click();
      expect((await accepted).status()).toBe(201);
      await expect(guest).toHaveURL(new RegExp(`workspace/team/${team}$`));
      expect((await memberOf(request, team, student.id))?.role).toBe('reader');
      await guest.screenshot({ path: `${SHOT}/03-joined.png`, fullPage: true });
      guestLog.dump('3. 同意加入');
    });

    await test.step('4. 再打开链接：提示已是成员', async () => {
      await guest.goto(link, { waitUntil: 'networkidle' });
      await expect(guest.getByText('你已经是这个团队的成员（只读成员）。')).toBeVisible();
      await expect(guest.getByRole('button', { name: /进入团队空间/ })).toBeVisible();
      await guest.screenshot({ path: `${SHOT}/04-member.png`, fullPage: true });
    });
    // 被邀请人留在团队里（本地测试数据不删，用户要看结果）；下次跑时第一步会先移出
    await ctx.close();
  });

  test('无效链接：提示无效或已过期', async ({ page }) => {
    const log = collect(page);
    await page.goto('team-invite/a.b.c', { waitUntil: 'networkidle' });
    await expect(page.getByText(/邀请链接无效或已过期/)).toBeVisible();
    await page.screenshot({ path: `${SHOT}/05-invalid.png`, fullPage: true });
    log.dump('无效链接');
  });
});
