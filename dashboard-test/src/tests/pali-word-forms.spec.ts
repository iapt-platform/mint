import { test, expect } from '@playwright/test';
import { collect } from '../lib/collect.js';

/**
 * 词形展开（v3 /pali-word-forms，取代 v2 case）：字典页右栏的变格列表。
 */
test('字典页的变格列表走 v3 pali-word-forms，不再请求 v2 case', async ({ page }) => {
  const log = collect(page);
  const v2: string[] = [];
  page.on('request', (r) => r.url().includes('/api/v2/case/') && v2.push(r.url()));
  const forms = page.waitForResponse((r) => r.url().includes('/api/v3/pali-word-forms/vitakka'));

  await page.goto('dictionary/vitakka', { waitUntil: 'networkidle' });

  const res = await forms;
  expect(res.status()).toBe(200);
  const body = await res.json();
  expect(body.data[0].base).toBe('vitakka');
  // 默认选中词形最多的那组，组内全部勾选
  type Group = { base: string; forms: { word: string }[] };
  const group = (body.data as Group[]).reduce((a, b) => (b.forms.length > a.forms.length ? b : a));
  const top = group.forms[0].word;
  console.log(`>>> vitakka：${body.meta.total} 组；默认选中 ${group.base}，最常见词形 ${top}`);

  await expect(page.getByRole('checkbox', { name: new RegExp(`^${top}[\\s\\d,]*$`) })).toBeChecked();
  expect(v2).toEqual([]);
  log.dump('字典变格列表');
});
