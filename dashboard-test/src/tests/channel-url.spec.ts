import { test, expect, type Page, type Request } from '@playwright/test';
import { collect } from '../lib/collect.js';
import { shotDir } from '../lib/env.js';

/**
 * /workspace/channel 的列表状态在 URL 里：页签、页码、每页条数、关键字、排序都能 F5 重入。
 */
const SHOT = shotDir('channel-url');
const TAB = '.ant-pro-table-list-toolbar-inline-menu-item';

const shot = (page: Page, name: string) =>
  page.screenshot({ path: `${SHOT}/${name}.png`, fullPage: true });
const isList = (r: Request) =>
  /\/api\/v3\/(channels|studios\/[^/]+\/channels)(\?|$)/.test(r.url()) && r.method() === 'GET';
/** 下一次列表请求的查询参数（忽略数量徽标的 per_page=1 请求） */
const nextList = (page: Page) =>
  page
    .waitForRequest((r) => isList(r) && new URL(r.url()).searchParams.get('per_page') !== '1')
    .then((r) => ({ path: new URL(r.url()).pathname, query: new URL(r.url()).searchParams }));
const activeTab = (page: Page) => page.locator(`${TAB}-active`);

test.describe('channel 列表 URL 可重入', () => {
  test('页签、搜索、页码、排序写进 URL，刷新后原样恢复', async ({ page }) => {
    const log = collect(page);

    await test.step('1. 带参数直接打开：按 URL 请求并显示', async () => {
      const listed = nextList(page);
      await page.goto('workspace/channel?tab=collaboration&q=test&pagesize=10', { waitUntil: 'networkidle' });
      const { path, query } = await listed;
      expect(path).toMatch(/\/studios\/[^/]+\/channels$/);
      expect(query.get('relation')).toBe('shared');
      expect(query.get('search')).toBe('test');
      expect(query.get('per_page')).toBe('10');
      await expect(activeTab(page)).toContainText('协作');
      await expect(page.getByPlaceholder('请输入').first()).toHaveValue('test');
      await shot(page, '01-from-url');
    });

    await test.step('2. 切页签：URL 跟着变，页码与协作者重置', async () => {
      const listed = nextList(page);
      await page.locator(TAB).filter({ hasText: '社区' }).click();
      const { path } = await listed;
      expect(path).toMatch(/\/v3\/channels$/);
      await expect(page).toHaveURL(/tab=community/);
      await expect(page).not.toHaveURL(/page=/);
    });

    await test.step('3. 排序：点创建时间表头，URL 记下排序', async () => {
      // ProTable 点表头时会先按旧参数自己请求一次，URL 更新后才带上排序再请求——等后一次
      const sorted = page.waitForRequest(
        (r) => isList(r) && new URL(r.url()).searchParams.get('order') === 'created_at',
      );
      await page.locator('.ant-table-thead th').filter({ hasText: '创建' }).last().click();
      await sorted;
      await expect(page).toHaveURL(/order=created_at/);
    });

    await test.step('4. F5：页签、关键字、排序都还在', async () => {
      const url = page.url();
      const listed = nextList(page);
      await page.reload({ waitUntil: 'networkidle' });
      const { path, query } = await listed;
      expect(page.url()).toBe(url);
      expect(path).toMatch(/\/v3\/channels$/);
      expect(query.get('search')).toBe('test');
      expect(query.get('order')).toBe('created_at');
      await expect(activeTab(page)).toContainText('社区');
      await shot(page, '04-after-f5');
    });

    await test.step('4b. 改每页条数：记进 localStorage，不会来回请求（曾死循环闪烁）', async () => {
      await page.goto('workspace/channel', { waitUntil: 'networkidle' });
      await page.evaluate(() => localStorage.removeItem('pagesize/channel'));
      await page.reload({ waitUntil: 'networkidle' });
      const requests: string[] = [];
      const onRequest = (r: Request) => {
        if (isList(r) && new URL(r.url()).searchParams.get('per_page') !== '1') requests.push(r.url());
      };
      page.on('request', onRequest);
      await page.locator('.ant-pagination-options .ant-select').click();
      await page.locator('.ant-select-dropdown:visible .ant-select-item-option').filter({ hasText: '10' }).first().click();
      await page.waitForTimeout(3000);
      page.off('request', onRequest);
      // 换每页条数最多两次请求（ProTable 自己一次 + 换 key 重新挂载一次），死循环时会持续增加
      expect(requests.length, requests.join('\n')).toBeLessThanOrEqual(2);
      expect(new URL(requests[requests.length - 1]).searchParams.get('per_page')).toBe('10');
      expect(await page.evaluate(() => localStorage.getItem('pagesize/channel'))).toBe('10');
      // 等于记下的默认值，不写进 URL
      await expect(page).not.toHaveURL(/pagesize=/);
    });

    await test.step('4c. 不带 pagesize 重新打开：沿用记下的每页条数', async () => {
      const listed = nextList(page);
      await page.goto('workspace/channel', { waitUntil: 'networkidle' });
      expect((await listed).query.get('per_page')).toBe('10');
      await expect(page.locator('.ant-pagination-options')).toContainText('10');
      // URL 显式带的优先
      const explicit = nextList(page);
      await page.goto('workspace/channel?pagesize=50', { waitUntil: 'networkidle' });
      expect((await explicit).query.get('per_page')).toBe('50');
    });

    await test.step('4d. 每个页签的角标 = 切过去后表格的总条数', async () => {
      for (const [tab, label] of [['my', '此工作室'], ['collaboration', '协作'], ['community', '社区']] as const) {
        await page.goto(`workspace/channel${tab === 'my' ? '' : `?tab=${tab}`}`, { waitUntil: 'networkidle' });
        const badgeText = (await page.locator(TAB).filter({ hasText: label }).innerText()).match(/\d+\+?/)?.[0] ?? '0';
        const badge = Number(badgeText.replace('+', ''));
        const totalText = await page.locator('.ant-pagination-total-text').innerText().catch(() => '');
        const total = Number(totalText.match(/总共\s*(\d+)/)?.[1] ?? 0);
        // 角标超过 99 显示「99+」（antd Badge 的封顶）
        if (badgeText.endsWith('+')) {
          expect(total, `${label}：角标 ${badgeText}，表格「${totalText}」`).toBeGreaterThan(badge);
        } else {
          expect(badge, `${label}：角标 ${badgeText}，表格「${totalText}」`).toBe(total);
        }
      }
    });

    await test.step('5. 页码：直接带 page=2 打开就请求第 2 页', async () => {
      const listed = nextList(page);
      await page.goto('workspace/channel?tab=community&page=2&pagesize=5', { waitUntil: 'networkidle' });
      const { query } = await listed;
      expect(query.get('page')).toBe('2');
      expect(query.get('per_page')).toBe('5');
      await expect(page.locator('.ant-pagination-item-active')).toHaveText('2');
      log.dump('5. 页码');
    });
  });
});
