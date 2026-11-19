import { test, expect } from '@playwright/test';
import { collect } from '../lib/collect.js';

/**
 * 词典词卡里的语法点悬停说明（GrammarPop，v3，取代 v2 grammar-guide）：
 * 语法术语表里有的词 → /v3/terms/{id}；没有的 → 退回按词查 /v3/terms?word=，都找不到显示「暂无…的语法说明」。
 */
test('词典词卡的语法点：悬停显示 v3 的语法说明，不再请求 v2 grammar-guide', async ({ page }) => {
  const log = collect(page);
  const v2: string[] = [];
  const v3: string[] = [];
  page.on('request', (r) => {
    if (r.url().includes('/api/v2/grammar-guide')) v2.push(r.url());
    if (/\/api\/v3\/terms(\/|\?)/.test(r.url())) v3.push(decodeURIComponent(new URL(r.url()).pathname + new URL(r.url()).search));
  });

  // 词典释义正文里嵌的 grammar-pop 模板（如【阳】→ m）渲染成 GrammarPop
  await page.goto('dictionary/buddha', { waitUntil: 'networkidle' });
  const grammarLinks = page.locator('a.ant-typography').filter({ hasText: /^【.+】$/ });
  await expect(grammarLinks.first()).toBeVisible({ timeout: 15_000 });
  const count = await grammarLinks.count();

  for (let i = 0; i < Math.min(count, 3); i++) {
    const link = grammarLinks.nth(i);
    await link.hover();
    const popover = page.locator('.ant-popover:visible').last();
    await expect(popover).toBeVisible();
    await expect(popover.locator('.ant-skeleton')).toHaveCount(0, { timeout: 10_000 });
    console.log(`>>> 悬停「${(await link.innerText()).trim()}」→ ${(await popover.innerText()).replace(/\s+/g, " ").trim().slice(0, 60)}`);
    await page.mouse.move(0, 0);
    await page.waitForTimeout(300);
  }
  console.log('>>> v3 请求：', v3.join(' | '));
  expect(v2).toEqual([]);
  expect(v3.length).toBeGreaterThan(0);
  log.dump('语法点悬停');
});
