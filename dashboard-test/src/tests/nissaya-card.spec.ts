import { test, expect } from '@playwright/test';
import { collect } from '../lib/collect.js';

/**
 * 缅文词尾卡片（v3 /nissaya-cards，取代 v2 nissaya-card）：词尾页显示语法关系表格。
 */
test('词尾页：取 v3 卡片，显示关系表格，不再请求 v2 nissaya-card', async ({ page }) => {
  const log = collect(page);
  const v2: string[] = [];
  page.on('request', (r) => r.url().includes('/api/v2/nissaya-card') && v2.push(r.url()));
  const card = page.waitForResponse((r) => r.url().includes('/api/v3/nissaya-cards/'));

  await page.goto(`workspace/nissaya/ending/${encodeURIComponent('သည်')}`, { waitUntil: 'networkidle' });
  const res = await card;
  expect(res.status()).toBe(200);
  const data = (await res.json()).data;
  console.log(`>>> 词尾 ${data.ending}：${data.relations.length} 条关系（${data.lang}）`);

  await expect(page.locator('.ant-table-row').first()).toBeVisible();
  expect(await page.locator('.ant-table-row').count()).toBeGreaterThan(0);
  expect(v2).toEqual([]);
  await page.screenshot({ path: 'testlog/assets/nissaya-card.png', fullPage: true });
  log.dump('词尾卡片');
});
