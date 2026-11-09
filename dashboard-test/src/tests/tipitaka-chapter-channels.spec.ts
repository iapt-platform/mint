import { test, expect, type Page } from '@playwright/test';
import { collect } from '../lib/collect.js';
import { shotDir } from '../lib/env.js';

/**
 * 三藏书目页 → 点开一章 → 抽屉里的「资源」页签（GET /v3/tipitaka-chapter-channels/{book}/{para}）
 *
 * 只读，不建数据。断言页面上列出的 channel 与接口返回的一致（名字、完成度），
 * 且请求走的是 v3、不再调 v2 的 progress?view=chapter_channels。
 */
const SHOT = shotDir('tipitaka-chapter-channels');
const shot = (page: Page, name: string) => page.screenshot({ path: `${SHOT}/${name}.png` });

interface Row {
  progress: number;
  channel: { id: string; name: string; type: string };
}

test.describe('三藏书目 · 章节资源列表', () => {
  test('点开一章，资源页签按完成度列出看得见的 channel', async ({ page }) => {
    const log = collect(page);
    const v2Calls: string[] = [];
    page.on('request', (r) => {
      if (r.url().includes('view=chapter_channels')) v2Calls.push(r.url());
    });

    await test.step('1. 打开书目页，选长部，点开戒蕴品', async () => {
      await page.goto('workspace/tipitaka/lib/default', { waitUntil: 'networkidle' });
      // 书目页初始没有章节，选一个分类（长部）才列出章节卡片
      await page.locator('.ant-tree-title', { hasText: /^长部$/ }).first().click();
      await page.waitForURL(/\/lib\/default\/.+/);
      // 长部戒蕴品：本机与线上都有多个译本
      const firstChapter = page.getByRole('heading', { level: 5, name: '(DN) Sīlakkhandhavaggapāḷi' });
      await expect(firstChapter).toBeVisible({ timeout: 20_000 });
      await firstChapter.click();
      await expect(page.locator('.ant-drawer-open')).toBeVisible();
      await shot(page, '01-drawer');
    });

    let rows: Row[] = [];
    await test.step('2. 切到「资源」页签：请求 v3，列表与接口一致', async () => {
      const listed = page.waitForResponse((r) => r.url().includes('/api/v3/tipitaka-chapter-channels/'));
      await page.locator('.ant-drawer-open').getByRole('tab', { name: '资源' }).click();
      const res = await listed;
      expect(res.status()).toBe(200);
      rows = ((await res.json()) as { data: Row[] }).data;

      // 按完成度降序
      const progress = rows.map((r) => r.progress);
      expect(progress).toEqual([...progress].sort((a, b) => b - a));

      if (rows.length === 0) {
        test.info().annotations.push({ type: 'note', description: '这一章没有任何 channel，只验证了请求' });
        return;
      }
      // 默认停在「译文」页签，每页 5 条：按接口顺序核对第一页的链接与完成度
      const translations = rows.filter((r) => r.channel.type === 'translation').slice(0, 5);
      const items = page.getByRole('tabpanel', { name: '译文' }).locator('.ant-list-item');
      await expect(items).toHaveCount(translations.length);
      for (const [i, row] of translations.entries()) {
        const item = items.nth(i);
        await expect(item.locator(`a[href*="channel=${row.channel.id}"]`)).toBeVisible();
        await expect(item).toContainText(row.channel.name);
        await expect(item).toContainText(`${Math.ceil(row.progress * 100)}%`);
      }
      await shot(page, '02-resources');
    });

    await test.step('3. 没有调 v2 的 chapter_channels，也没有页面异常与 v3 报错', async () => {
      expect(v2Calls).toEqual([]);
      const bad = log.all.filter((l) => l.startsWith('[PAGEERROR]') || l.includes('/api/v3/tipitaka-chapter-channels/'));
      log.dump('章节资源列表');
      expect(bad).toEqual([]);
    });
  });
});
