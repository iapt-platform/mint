import { test, expect, type Browser, type Page, type APIRequestContext } from '@playwright/test';
import { authFile, call, type Account } from '../lib/api.js';
import { collect } from '../lib/collect.js';
import { BASE, HAS_STUDENT, USER, shotDir } from '../lib/env.js';

/**
 * 团队成员管理（/workspace/team/:teamId，v3 /v3/groups/{group}/members）。
 *
 * E2E_USER 是团队 owner；E2E_STUDENT_USER 充当被拉进团队的成员（没配就跳过相关用例）。
 * 团队沿用 teamspace.spec 的「e2e 团队空间」，没有就建一个，跑完保留。
 */
const SHOT = shotDir('group-members');
const TEAM = 'e2e 团队空间';

interface Me {
  id: string;
  userName: string;
  nickName: string;
}

const shot = (page: Page, name: string) =>
  page.screenshot({ path: `${SHOT}/${name}.png`, fullPage: true });
const memberRow = (page: Page, nick: string) =>
  page.locator('.ant-pro-list-item').filter({ hasText: nick });
const isMemberCall = (method: string) => (r: { url(): string; request(): { method(): string } }) =>
  /\/api\/v3\/groups\/[^/]+\/members/.test(r.url()) && r.request().method() === method;

async function me(request: APIRequestContext, who: Account): Promise<Me> {
  const r = await call(request, 'GET', '/v3/me', undefined, who);
  expect(r.status).toBe(200);
  return r.body.data;
}

async function teamId(request: APIRequestContext): Promise<string> {
  const find = async () => {
    const r = await call(request, 'GET', '/v3/me/groups?per_page=200');
    expect(r.status).toBe(200);
    return (r.body.data as { id: string; name: string; role: string }[]).find(
      (g) => g.name === TEAM && g.role === 'owner',
    )?.id;
  };
  let id = await find();
  if (!id) {
    await call(request, 'POST', '/v2/group', { name: TEAM, studio_name: USER });
    id = await find();
  }
  expect(id, '测试账号应当拥有测试团队').toBeTruthy();
  return id!;
}

/** 把某人移出团队（存在才移），返回是否移过 */
async function kick(request: APIRequestContext, team: string, userId: string) {
  const r = await call(request, 'GET', `/v3/groups/${team}/members?per_page=200`);
  const row = (r.body.data as { id: string; user?: { id: string } }[]).find((m) => m.user?.id === userId);
  if (row) await call(request, 'DELETE', `/v3/groups/${team}/members/${row.id}`);
}

async function roleOf(request: APIRequestContext, team: string, userId: string) {
  const r = await call(request, 'GET', `/v3/groups/${team}/members?per_page=200`);
  return (r.body.data as { role: string; user?: { id: string } }[]).find((m) => m.user?.id === userId)?.role;
}

async function open(browser: Browser, who: Account) {
  const ctx = await browser.newContext({ baseURL: BASE, storageState: authFile(who) });
  return { ctx, page: await ctx.newPage() };
}

test.describe('团队成员管理', () => {
  test.skip(!HAS_STUDENT, '需要第二个账号 E2E_STUDENT_USER 当成员');

  test('owner 加人、改角色；成员看不到管理入口、能自己退出', async ({ page, request, browser }) => {
    const log = collect(page);
    const team = await teamId(request);
    const student = await me(request, 'student');
    await kick(request, team, student.id);

    await test.step('1. 成员列表：owner 排第一，有「加人」', async () => {
      await page.goto(`workspace/team/${team}`, { waitUntil: 'networkidle' });
      await expect(page.locator('.ant-pro-list-item').first()).toContainText('拥有者');
      await expect(page.getByRole('button', { name: /加人/ })).toBeVisible();
      await shot(page, '01-list');
    });

    await test.step('2. 加人：默认只读成员', async () => {
      await page.getByRole('button', { name: /加人/ }).click();
      const pop = page.locator('.ant-popover:visible');
      await pop.locator('.ant-select').first().click();
      await pop.locator('.ant-select').first().locator('input').pressSequentially(student.userName, { delay: 30 });
      await page
        .locator('.ant-select-dropdown:not(.ant-select-dropdown-hidden) .ant-select-item-option')
        .filter({ hasText: `${student.userName}-${student.nickName}` })
        .first()
        .click();
      const added = page.waitForResponse(isMemberCall('POST'));
      await pop.getByRole('button', { name: /提 *交/ }).click();
      expect((await added).status()).toBe(201);

      await expect(memberRow(page, student.nickName)).toContainText('只读成员');
      expect(await roleOf(request, team, student.id)).toBe('reader');
      await shot(page, '02-added');
      log.dump('2. 加人');
    });

    await test.step('3. 改角色为编辑', async () => {
      await memberRow(page, student.nickName).locator('.ant-select').click();
      const patched = page.waitForResponse(isMemberCall('PATCH'));
      await page
        .locator('.ant-select-dropdown:not(.ant-select-dropdown-hidden) .ant-select-item-option')
        .filter({ hasText: /^编辑$/ })
        .click();
      expect((await patched).status()).toBe(200);
      await expect(memberRow(page, student.nickName)).toContainText('编辑');
      expect(await roleOf(request, team, student.id)).toBe('editor');
      await shot(page, '03-role');
    });

    await test.step('4. 成员视角：没有管理入口，能退出团队', async () => {
      const { ctx, page: other } = await open(browser, 'student');
      await other.goto(`workspace/team/${team}`, { waitUntil: 'networkidle' });
      await expect(memberRow(other, student.nickName)).toContainText('编辑');
      await expect(other.getByRole('button', { name: /加人/ })).toHaveCount(0);
      await expect(memberRow(other, student.nickName).locator('.ant-select')).toHaveCount(0);

      await other.getByRole('button', { name: '退出团队' }).click();
      const left = other.waitForResponse(isMemberCall('DELETE'));
      await other.locator('.ant-popconfirm:visible').getByRole('button', { name: /确 *定/ }).click();
      expect((await left).status()).toBe(204);
      await expect(other).toHaveURL(/workspace\/team$/);
      await shot(other, '04-left');
      await ctx.close();

      expect(await roleOf(request, team, student.id)).toBeUndefined();
    });
  });

  test('owner 移除成员', async ({ page, request }) => {
    const team = await teamId(request);
    const student = await me(request, 'student');
    await kick(request, team, student.id);
    const r = await call(request, 'POST', `/v3/groups/${team}/members`, { user_id: student.id });
    expect(r.status).toBe(201);

    await page.goto(`workspace/team/${team}`, { waitUntil: 'networkidle' });
    const row = memberRow(page, student.nickName);
    await row.getByRole('button', { name: /移/ }).click();
    const removed = page.waitForResponse(isMemberCall('DELETE'));
    await page.locator('.ant-popconfirm:visible').getByRole('button', { name: /确 *定/ }).click();
    expect((await removed).status()).toBe(204);
    await expect(row).toHaveCount(0);
    expect(await roleOf(request, team, student.id)).toBeUndefined();
    await shot(page, '05-removed');
  });
});
