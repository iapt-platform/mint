import { test, expect } from '@playwright/test';
import { collect } from '../lib/collect.js';

/**
 * 术语词汇表（v3 /glossaries，取代 v2 term-vocabulary）：应用启动时整表拉取语法术语表与社区译名表。
 */
test('启动时拉取两张 v3 词汇表，不再请求 v2 term-vocabulary', async ({ page }) => {
  const log = collect(page);
  const v2: string[] = [];
  page.on('request', (r) => r.url().includes('/api/v2/term-vocabulary') && v2.push(r.url()));
  const grammar = page.waitForResponse((r) => r.url().includes('/api/v3/glossaries/grammar'));
  const community = page.waitForResponse((r) => r.url().includes('/api/v3/glossaries/community'));

  await page.goto('workspace/channel', { waitUntil: 'networkidle' });

  for (const res of [await grammar, await community]) {
    expect(res.status()).toBe(200);
    const body = await res.json();
    expect(Array.isArray(body.data)).toBe(true);
    expect(body.meta).toHaveProperty('lang');
    console.log(`>>> ${new URL(res.url()).pathname}${new URL(res.url()).search}：${body.meta.total} 条（${body.meta.lang}）`);
  }
  expect(v2).toEqual([]);
  log.dump('启动加载词汇表');
});
