import { test, expect, type Page } from '@playwright/test';
import { type APIRequestContext } from '@playwright/test';
import { call } from '../lib/api.js';
import { collect } from '../lib/collect.js';
import { shotDir } from '../lib/env.js';

/**
 * 团队空间（P0）：头像弹窗里切到团队空间、顶栏标签、菜单置灰、直接访问被拦、
 * 刷新后保持、切回个人空间、localStorage 里的过期团队被清掉。
 *
 * 团队空间 = 以某个 group 为 owner 浏览资源。P0 阶段所有可团队化的模块都还没迁 v3，
 * 所以切过去之后它们一律置灰；随各资源迁完再逐个改这里的断言。
 */
const SHOT = shotDir('teamspace');
const TEAM = 'e2e 团队空间';
const TEAM2 = 'e2e 团队空间 2';

const shot = (page: Page, name: string) =>
  page.screenshot({ path: `${SHOT}/${name}.png`, fullPage: true });
const badge = (page: Page) => page.getByTestId('teamspace-badge');
const menuItem = (page: Page, label: string) =>
  page.locator('.ant-layout-sider .ant-menu-item').filter({ hasText: label });
/** 展开主导航的「协作」子菜单（inline 子菜单收起时子项不渲染） */
async function openCollaboration(page: Page) {
  const title = page.locator('.ant-layout-sider .ant-menu-submenu').filter({ hasText: '协作' });
  if (!(await title.getAttribute('class'))?.includes('ant-menu-submenu-open')) {
    await title.locator('.ant-menu-submenu-title').click();
  }
}
const stored = (page: Page) => page.evaluate(() => localStorage.getItem('teamspace'));

/** 测试账号名下的团队，没有就建（本地测试数据保留不删） */
async function ensureTeam(request: APIRequestContext, name: string): Promise<string> {
  const mine = await call(request, 'GET', '/v3/me/groups?owned=true&per_page=200');
  const found = (mine.body.data as { id: string; name: string }[]).find((g) => g.name === name);
  if (found) return found.id;
  const created = await call(request, 'POST', '/v3/groups', { name });
  expect(created.status, JSON.stringify(created.body)).toBe(201);
  return created.body.data.id;
}

/** 头像弹窗里选一个团队空间 */
async function switchTo(page: Page, name: string) {
  await page.locator('.ant-layout-sider .ant-avatar').first().hover();
  const popover = page.locator('.ant-popover:visible');
  const title = popover.getByText('切换到团队空间');
  if (!(await popover.getByText(name, { exact: true }).isVisible())) {
    await title.click();
  }
  await popover.getByText(name, { exact: true }).click();
}

let teamId = '';
let team2Id = '';

test.beforeAll(async ({ request }) => {
  teamId = await ensureTeam(request, TEAM);
  team2Id = await ensureTeam(request, TEAM2);
});

test.describe('团队空间切换', () => {
  test('切过去、菜单置灰、直接访问被拦、刷新保持、切回个人', async ({ page }) => {
    const log = collect(page);

    await test.step('1. 头像弹窗里选团队', async () => {
      await page.goto('workspace', { waitUntil: 'networkidle' });
      await page.evaluate(() => localStorage.removeItem('teamspace'));
      await page.reload({ waitUntil: 'networkidle' });
      await expect(badge(page)).toHaveCount(0);

      await switchTo(page, TEAM);

      await expect(badge(page)).toContainText(TEAM);
      expect(JSON.parse((await stored(page)) ?? '{}').id).toBe(teamId);
      await shot(page, '01-switched');
      log.dump('1. 切到团队空间');
    });

    await test.step('2. 主导航：可团队化与个人专属的模块置灰，全局模块照常', async () => {
      await expect(menuItem(page, '版本风格')).toHaveClass(/ant-menu-item-disabled/);
      await expect(menuItem(page, '术语')).toHaveClass(/ant-menu-item-disabled/);
      await expect(menuItem(page, '最近')).toHaveClass(/ant-menu-item-disabled/);
      await expect(menuItem(page, '主页')).not.toHaveClass(/ant-menu-item-disabled/);
      // 我加入的团队列表属于个人身份；管理本团队走「成员管理」
      await openCollaboration(page);
      await expect(menuItem(page, '团队空间')).toHaveClass(/ant-menu-item-disabled/);
      await expect(menuItem(page, '成员管理')).not.toHaveClass(/ant-menu-item-disabled/);
      await shot(page, '02-menu');
    });

    await test.step('2b. 成员管理：列出当前团队的成员', async () => {
      await menuItem(page, '成员管理').click();
      await expect(page).toHaveURL(/workspace\/members$/);
      await expect(page.locator('.ant-pro-list-item').first()).toContainText('拥有者');
      await page.goto('workspace/team', { waitUntil: 'networkidle' });
      await expect(page.getByTestId('teamspace-blocked')).toContainText('个人专属功能');
      await shot(page, '02b-members');
    });

    await test.step('2c. 在成员管理页切换团队空间，成员列表跟着换', async () => {
      await page.goto('workspace/members', { waitUntil: 'networkidle' });
      await expect(badge(page)).toContainText(TEAM);

      const refetched = page.waitForResponse(
        (r) => r.url().includes(`/api/v3/groups/${team2Id}/members`) && r.request().method() === 'GET',
      );
      await switchTo(page, TEAM2);
      expect((await refetched).status()).toBe(200);
      await expect(badge(page)).toContainText(TEAM2);
      await expect(page).toHaveURL(/workspace\/members$/);
      await shot(page, '02c-switched-members');

      // 切回第一个团队，后面的步骤以它为准
      const back = page.waitForResponse((r) => r.url().includes(`/api/v3/groups/${teamId}/members`));
      await switchTo(page, TEAM);
      await back;
      await expect(badge(page)).toContainText(TEAM);
    });

    await test.step('3. 直接输入地址也进不去', async () => {
      await page.goto('workspace/channel', { waitUntil: 'networkidle' });
      await expect(page.getByTestId('teamspace-blocked')).toContainText('该模块暂不支持团队空间');
      await expect(page.locator('.ant-table')).toHaveCount(0);
      await shot(page, '03-blocked');
    });

    await test.step('4. 刷新后仍在团队空间', async () => {
      await page.reload({ waitUntil: 'networkidle' });
      await expect(badge(page)).toContainText(TEAM);
      await expect(page.getByTestId('teamspace-blocked')).toBeVisible();
    });

    await test.step('5. 拦截页上切回个人空间', async () => {
      await page.getByTestId('teamspace-blocked').getByRole('button', { name: '切回个人空间' }).click();
      await expect(badge(page)).toHaveCount(0);
      await expect(page.locator('.ant-table')).toBeVisible();
      expect(await stored(page)).toBeNull();
      await shot(page, '05-personal');
      log.dump('5. 切回个人空间');
    });

    await test.step('5b. 个人空间：成员管理置灰，直接访问被拦', async () => {
      await openCollaboration(page);
      await expect(menuItem(page, '成员管理')).toHaveClass(/ant-menu-item-disabled/);
      await expect(menuItem(page, '团队空间')).not.toHaveClass(/ant-menu-item-disabled/);
      await page.goto('workspace/members', { waitUntil: 'networkidle' });
      await expect(page.getByTestId('teamspace-blocked')).toContainText('仅在团队空间中可用');
      await expect(page.getByTestId('teamspace-blocked').getByRole('button')).toHaveCount(0);
    });

    await test.step('6. 顶栏标签关掉也能切回个人空间', async () => {
      await page.evaluate(
        ([id, name]) =>
          localStorage.setItem('teamspace', JSON.stringify({ id, nickName: name, realName: id })),
        [teamId, TEAM],
      );
      await page.goto('workspace', { waitUntil: 'networkidle' });
      await expect(badge(page)).toBeVisible();
      await badge(page).locator('.ant-tag-close-icon').click();
      await expect(badge(page)).toHaveCount(0);
      expect(await stored(page)).toBeNull();
    });
  });

  test('localStorage 里已不是成员的团队会被清掉', async ({ page }) => {
    await page.goto('workspace', { waitUntil: 'networkidle' });
    await page.evaluate(() =>
      localStorage.setItem(
        'teamspace',
        JSON.stringify({
          id: '00000000-0000-4000-8000-000000000000',
          nickName: 'ghost',
          realName: '00000000-0000-4000-8000-000000000000',
        }),
      ),
    );
    await page.reload({ waitUntil: 'networkidle' });

    // group 列表回来后校验成员资格，不是成员就回到个人空间
    await expect(badge(page)).toHaveCount(0);
    await expect.poll(() => stored(page)).toBeNull();
  });
});
