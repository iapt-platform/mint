import { test, expect, type Page, type APIRequestContext } from '@playwright/test';
import { call } from '../lib/api.js';
import { collect } from '../lib/collect.js';
import { DESTRUCTIVE, shotDir } from '../lib/env.js';

/**
 * 团队空间里的 channel（v3 /studios/{团队 uuid}/channels）：
 * 「本工作室」列团队名下的，「协作」列共享给团队的；新建建在团队名下。
 *
 * 用测试账号名下的「e2e 团队空间」（teamspace.spec 也用它）。团队名下固定放一个 channel
 * 「e2e 团队 channel」，只建一次、保留；新建用例的 e2e-tmp-* 测完软删。
 */
const SHOT = shotDir('channel-teamspace');
const TEAM = 'e2e 团队空间';
const TEAM_CHANNEL = 'e2e 团队 channel';

const shot = (page: Page, name: string) =>
  page.screenshot({ path: `${SHOT}/${name}.png`, fullPage: true });
const row = (page: Page, name: string) => page.locator('.ant-table-row').filter({ hasText: name });

async function ensureTeam(request: APIRequestContext): Promise<string> {
  const mine = await call(request, 'GET', '/v3/me/groups?owned=true&per_page=200');
  const found = (mine.body.data as { id: string; name: string }[]).find((g) => g.name === TEAM);
  if (found) return found.id;
  const created = await call(request, 'POST', '/v3/groups', { name: TEAM });
  expect(created.status).toBe(201);
  return created.body.data.id;
}

async function ensureTeamChannel(request: APIRequestContext, team: string): Promise<string> {
  const list = await call(request, 'GET', `/v3/studios/${team}/channels?relation=own&per_page=200`);
  const found = (list.body.data as { id: string; name: string }[]).find((c) => c.name === TEAM_CHANNEL);
  if (found) return found.id;
  const created = await call(request, 'POST', `/v3/studios/${team}/channels`, {
    name: TEAM_CHANNEL, type: 'translation', lang: 'zh-Hans',
  });
  expect(created.status, JSON.stringify(created.body)).toBe(201);
  return created.body.data.id;
}

/** 直接把团队空间写进 localStorage（切换流程本身由 teamspace.spec 测） */
async function enterTeamspace(page: Page, team: string) {
  await page.goto('workspace', { waitUntil: 'networkidle' });
  await page.evaluate(
    ([id, name]) => localStorage.setItem('teamspace', JSON.stringify({ id, nickName: name, realName: id })),
    [team, TEAM],
  );
}

test.describe('团队空间里的 channel', () => {
  test('列表只有团队名下的，新建建在团队名下', async ({ page, request }) => {
    test.skip(!DESTRUCTIVE, 'E2E_DESTRUCTIVE=0');
    const log = collect(page);
    const team = await ensureTeam(request);
    await ensureTeamChannel(request, team);
    const mine = await call(request, 'GET', '/v3/me');

    await test.step('1. 本工作室页签：团队名下的 channel', async () => {
      await enterTeamspace(page, team);
      const listed = page.waitForResponse(
        (r) => r.url().includes(`/api/v3/studios/${team}/channels`) && r.url().includes('relation=own'),
      );
      await page.goto('workspace/channel', { waitUntil: 'networkidle' });
      expect((await listed).status()).toBe(200);
      await expect(page.getByTestId('active-account')).toHaveText(new RegExp(`${TEAM}$`));
      await expect(row(page, TEAM_CHANNEL)).toBeVisible();
      // 个人名下的 channel 不出现：随便取一个个人 channel 名核对
      const personal = await call(request, 'GET', `/v3/studios/${mine.body.data.id}/channels?relation=own&per_page=1`);
      const personalName = personal.body.data?.[0]?.name as string | undefined;
      if (personalName && personalName !== TEAM_CHANNEL) {
        await expect(row(page, personalName)).toHaveCount(0);
      }
      await shot(page, '01-team-own');
      log.dump('1. 团队名下');
    });

    await test.step('2. 新建：建在团队名下', async () => {
      const name = `e2e-tmp-${Date.now()}`;
      await page.getByRole('button', { name: /新建/ }).click();
      const pop = page.locator('.ant-popover:visible');
      await pop.getByRole('textbox').first().fill(name);
      // 类型有默认值，语言必填（与 channel.spec 的新建写法一致）
      await pop.locator('.ant-select').last().click();
      await page.locator('.ant-select-dropdown:visible .ant-select-item-option').first().click();
      const created = page.waitForResponse(
        (r) => r.url().includes(`/api/v3/studios/${team}/channels`) && r.request().method() === 'POST',
      );
      await pop.getByRole('button', { name: /提 *交/ }).click();
      const res = await created;
      expect(res.status()).toBe(201);
      const channel = (await res.json()).data;
      expect(channel.owner).toMatchObject({ kind: 'team', id: team });
      await expect(row(page, name)).toBeVisible();
      await shot(page, '02-created');

      // 清理：软删
      expect((await call(request, 'DELETE', `/v3/channels/${channel.id}`)).status).toBe(200);
    });

    await test.step('3. 原地切回个人空间：表格按本人重新取，团队的 channel 不见了', async () => {
      const relisted = page.waitForRequest(
        (r) =>
          r.url().includes(`/api/v3/studios/${mine.body.data.id}/channels`) &&
          new URL(r.url()).searchParams.get('per_page') !== '1',
      );
      await page.getByTestId('active-account').hover();
      const popover = page.locator('.ant-popover:visible');
      if (!(await popover.getByTestId('account-personal').isVisible())) {
        await popover.getByText('切换账号').click();
      }
      await popover.getByTestId('account-personal').click();
      await relisted;
      await expect(row(page, TEAM_CHANNEL)).toHaveCount(0);
      await shot(page, '03-back-to-personal');
    });
  });
});
