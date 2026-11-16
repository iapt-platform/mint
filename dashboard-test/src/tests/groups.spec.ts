import { test, expect, type Page, type APIRequestContext } from '@playwright/test';
import { call } from '../lib/api.js';
import { collect } from '../lib/collect.js';
import { DESTRUCTIVE, USER, shotDir } from '../lib/env.js';

/**
 * 团队页（/workspace/team，v3 /groups 与 /me/groups）：建、改、删，
 * 名下还有资源时拒删，团队文档里把资源移出团队。
 *
 * 临时团队叫 e2e-tmp-<时间戳>，用例末尾删掉。「名下有资源」用固定团队 HAS_CHANNEL：
 * 团队名下的 channel 在 v2 里谁都删不掉（v2 只认 owner 本人），channel 迁 v3 之前无法清理，
 * 所以只建一次、反复用。
 */
const HAS_CHANNEL = 'e2e 团队（名下有资源）';
const SHOT = shotDir('groups');

const shot = (page: Page, name: string) =>
  page.screenshot({ path: `${SHOT}/${name}.png`, fullPage: true });
const row = (page: Page, name: string) => page.locator('.ant-table-row').filter({ hasText: name });
const isGroupCall = (method: string, tail = '') => (r: { url(): string; request(): { method(): string } }) =>
  new RegExp(`/api/v3/groups(/[^/?]+)?${tail}(\\?|$)`).test(r.url()) && r.request().method() === method;

async function createTeam(request: APIRequestContext, name: string): Promise<string> {
  const r = await call(request, 'POST', '/v3/groups', { name });
  expect(r.status, JSON.stringify(r.body)).toBe(201);
  return r.body.data.id;
}

/** 测试账号名下的固定团队，没有就建；建好后经 v2 在它名下建一个 channel */
async function teamWithChannel(request: APIRequestContext): Promise<string> {
  const mine = await call(request, 'GET', '/v3/me/groups?owned=true&per_page=200');
  const found = (mine.body.data as { id: string; name: string }[]).find((g) => g.name === HAS_CHANNEL);
  if (found) return found.id;
  const id = await createTeam(request, HAS_CHANNEL);
  const ch = await call(request, 'POST', '/v2/channel', {
    studio: id, name: `${HAS_CHANNEL}-channel`, type: 'translation', lang: 'zh-Hans',
  });
  expect(ch.body?.ok, JSON.stringify(ch.body)).toBe(true);
  return id;
}

async function teamExists(request: APIRequestContext, id: string) {
  return (await call(request, 'GET', `/v3/groups/${id}`)).status === 200;
}

test.describe('团队页', () => {
  test.skip(!DESTRUCTIVE, 'E2E_DESTRUCTIVE=0');

  test('建团队 → 改名与可见性 → 删除', async ({ page, request }) => {
    const log = collect(page);
    const name = `e2e-tmp-${Date.now()}`;
    let id = '';

    await test.step('1. 新建', async () => {
      await page.goto('workspace/team', { waitUntil: 'networkidle' });
      await page.getByRole('button', { name: /新建/ }).click();
      const pop = page.locator('.ant-popover:visible');
      await pop.getByRole('textbox', { name: /名称/ }).fill(name);
      const created = page.waitForResponse(isGroupCall('POST'));
      await pop.getByRole('button', { name: /提 *交/ }).click();
      const res = await created;
      expect(res.status()).toBe(201);
      id = (await res.json()).data.id;

      await expect(row(page, name)).toBeVisible();
      await expect(row(page, name)).toContainText('拥有者');
      await shot(page, '01-created');
      log.dump('1. 新建');
    });

    await test.step('2. 设置页改名、改为私有', async () => {
      await row(page, name).getByRole('button', { name: '设置' }).first().click();
      await expect(page).toHaveURL(new RegExp(`workspace/team/${id}/setting$`));
      await page.locator('input#name').fill(`${name}-renamed`);
      await page.getByText('私有（只有成员看得到）').click();
      const patched = page.waitForResponse(isGroupCall('PATCH'));
      await page.getByRole('button', { name: /提 *交/ }).click();
      expect((await patched).status()).toBe(200);

      const r = await call(request, 'GET', `/v3/groups/${id}`);
      expect(r.body.data).toMatchObject({ name: `${name}-renamed`, status: 10 });
      await shot(page, '02-edited');
    });

    await test.step('3. 列表里删除', async () => {
      await page.goto('workspace/team', { waitUntil: 'networkidle' });
      const target = row(page, `${name}-renamed`);
      await target.locator('.ant-dropdown-trigger').click();
      await page.locator('.ant-dropdown:visible').getByText('删除').click();
      const deleted = page.waitForResponse(isGroupCall('DELETE'));
      await page.locator('.ant-modal-confirm').getByRole('button', { name: /删 *除/ }).click();
      expect((await deleted).status()).toBe(204);
      await expect(target).toHaveCount(0);
      expect(await teamExists(request, id)).toBe(false);
      await shot(page, '03-deleted');
    });
  });

  test('名下还有 channel 时拒删，提示原因', async ({ page, request }) => {
    const id = await teamWithChannel(request);

    await page.goto('workspace/team', { waitUntil: 'networkidle' });
    await row(page, HAS_CHANNEL).locator('.ant-dropdown-trigger').click();
    await page.locator('.ant-dropdown:visible').getByText('删除').click();
    const deleted = page.waitForResponse(isGroupCall('DELETE'));
    await page.locator('.ant-modal-confirm').getByRole('button', { name: /删 *除/ }).click();
    expect((await deleted).status()).toBe(409);
    await expect(page.locator('.ant-message, .ant-notification')).toContainText('团队名下还有资源');
    expect(await teamExists(request, id)).toBe(true);
    await shot(page, '04-conflict');
  });

  test('团队文档：共享进来的资源可以移出团队', async ({ page, request }) => {
    const name = `e2e-tmp-${Date.now()}`;
    const id = await createTeam(request, name);
    // 测试账号自己名下的第一个 channel（channel 还没迁 v3，经 v2 查），共享给临时团队
    const mine = await call(request, 'GET', `/v2/channel?view=studio&name=${USER}&limit=1`);
    const channel = mine.body?.data?.rows?.[0]?.uid;
    expect(channel, '测试账号名下至少要有一个 channel').toBeTruthy();
    const shared = await call(request, 'POST', '/v2/share', {
      res_id: channel, res_type: 2, user_id: [id], user_type: 'group', role: 'editor',
    });
    expect(shared.body?.ok, JSON.stringify(shared.body)).toBe(true);

    await page.goto(`workspace/team/${id}`, { waitUntil: 'networkidle' });
    const item = page.locator('.ant-pro-list-item').filter({ hasText: '可编辑' });
    await expect(item).toHaveCount(1);
    await shot(page, '05-shared');

    const removed = page.waitForResponse(isGroupCall('DELETE', '/shares/[^/?]+'));
    await item.getByRole('button', { name: /移/ }).click();
    await page.locator('.ant-popconfirm:visible').getByRole('button', { name: /确 *定/ }).click();
    expect((await removed).status()).toBe(204);
    await expect(item).toHaveCount(0);

    expect((await call(request, 'DELETE', `/v3/groups/${id}`)).status).toBe(204);
  });
});
