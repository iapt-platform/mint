import { test, expect, type Page } from '@playwright/test';
import { call } from '../lib/api.js';
import { collect } from '../lib/collect.js';
import { DESTRUCTIVE, shotDir } from '../lib/env.js';

/**
 * channel 设置页「关联」页签：继承自、捆绑的逐词、降级版本、术语路径（/v3/channels/{id}/links）。
 * 服务端只记录；这里只测设置界面。用五个 e2e-tmp-* 临时 channel（三个 translation、两个 nissaya），测完软删。
 */
const SHOT = shotDir('channel-links');
const shot = (page: Page, name: string) =>
  page.screenshot({ path: `${SHOT}/${name}.png`, fullPage: true });

const item = (page: Page, label: string) =>
  page.locator('.ant-form-item').filter({ has: page.locator('label', { hasText: label }) });

/** 在某个表单项里的下拉框中搜索并选中一个 channel */
const pick = async (page: Page, label: string, name: string) => {
  const select = item(page, label).locator('.ant-select').last();
  await select.click();
  await select.locator('input').fill(name);
  const option = page.locator('.ant-select-dropdown:visible .ant-select-item-option').filter({ hasText: name });
  await expect(option).toBeVisible({ timeout: 10_000 });
  await option.click();
};

const save = async (page: Page, id: string) => {
  const saved = page.waitForResponse(
    (r) => r.url().endsWith(`/api/v3/channels/${id}/links`) && r.request().method() === 'PATCH',
  );
  await page.getByRole('button', { name: /保 *存/ }).click();
  const res = await saved;
  expect(res.status()).toBe(200);
  return (await res.json()).data;
};

test('关联页签：设置继承自与术语路径，刷新后仍在，移除后清空；按类型过滤候选', async ({ page, request }) => {
  test.skip(!DESTRUCTIVE, 'E2E_DESTRUCTIVE=0');
  const log = collect(page);
  const me = (await call(request, 'GET', '/v3/me')).body.data;
  const stamp = Date.now();
  const make = async (suffix: string, type = 'translation') => {
    const res = await call(request, 'POST', `/v3/studios/${me.id}/channels`, {
      name: `e2e-tmp-${stamp}-${suffix}`, type, lang: 'en',
    });
    expect(res.status).toBe(201);
    return { id: res.body.data.id as string, name: res.body.data.name as string };
  };
  const a = await make('a');
  const b = await make('b');
  const c = await make('c');
  const nissaya = await make('nissaya', 'nissaya');
  const nissaya2 = await make('nissaya2', 'nissaya');

  try {
    await test.step('1. 打开关联页签，初始都未设置', async () => {
      await page.goto(`workspace/channel/${a.id}/setting/links`, { waitUntil: 'networkidle' });
      await expect(item(page, '继承自')).toBeVisible();
      await expect(item(page, '降级版本')).toBeVisible();
      await expect(item(page, '捆绑的逐词')).toBeVisible();
      await expect(item(page, '术语路径')).toBeVisible();
      await shot(page, '01-empty');
      log.dump('1. 打开关联页签');
    });

    await test.step('1b. 术语路径的候选只有 translation 类型', async () => {
      const select = item(page, '术语路径').locator('.ant-select').last();
      const listed = page.waitForResponse((r) => /\/api\/v3\/channels\/editable\?/.test(r.url()) && r.url().includes(String(stamp)));
      await select.click();
      await select.locator('input').fill(String(stamp));
      expect(new URL((await listed).url()).searchParams.get('type')).toBe('translation');
      const options = page.locator('.ant-select-dropdown:visible .ant-select-item-option');
      await expect(options.filter({ hasText: b.name })).toBeVisible({ timeout: 10_000 });
      await expect(options.filter({ hasText: nissaya.name })).toHaveCount(0);
      await page.keyboard.press('Escape');
      // 等收起动画结束，否则下一步会点到这个正在消失的下拉框里的同名选项
      await expect(page.locator('.ant-select-dropdown:visible')).toHaveCount(0);
    });

    await test.step('2. 选继承自 b、术语路径 c → b，保存', async () => {
      await pick(page, '继承自', b.name);
      await pick(page, '术语路径', c.name);
      await pick(page, '术语路径', b.name);
      const data = await save(page, a.id);
      expect(data.derived_from.id).toBe(b.id);
      expect(data.term_source.map((r: { id: string }) => r.id)).toEqual([c.id, b.id]);
      await shot(page, '02-saved');
      log.dump('2. 保存');
    });

    await test.step('3. 下移第一项，顺序变为 b → c', async () => {
      await item(page, '术语路径').locator('.ant-list-item').first().getByRole('button').nth(1).click();
      const data = await save(page, a.id);
      expect(data.term_source.map((r: { id: string }) => r.id)).toEqual([b.id, c.id]);
    });

    await test.step('4. 刷新后仍在', async () => {
      await page.reload({ waitUntil: 'networkidle' });
      await expect(item(page, '继承自')).toContainText(b.name);
      await expect(item(page, '术语路径').locator('.ant-list-item')).toHaveCount(2);
      await expect(item(page, '术语路径').locator('.ant-list-item').first()).toContainText(b.name);
      await shot(page, '04-reloaded');
    });

    await test.step('5. 清掉继承自、移除术语路径，保存后为空', async () => {
      await item(page, '继承自').locator('.ant-select').hover();
      await item(page, '继承自').locator('.ant-select-clear').click();
      const list = item(page, '术语路径').locator('.ant-list-item');
      await list.first().locator('button.ant-btn-dangerous').click();
      await list.first().locator('button.ant-btn-dangerous').click();
      const data = await save(page, a.id);
      expect(data.derived_from).toBeNull();
      expect(data.term_source).toEqual([]);
      await shot(page, '05-cleared');
      log.dump('5. 清空');
    });

    await test.step('6. 降级版本的候选只有与当前版本同类型的（nissaya）', async () => {
      await page.goto(`workspace/channel/${nissaya.id}/setting/links`, { waitUntil: 'networkidle' });
      const select = item(page, '降级版本').locator('.ant-select').last();
      const listed = page.waitForResponse((r) => /\/api\/v3\/channels\/editable\?/.test(r.url()) && r.url().includes(String(stamp)));
      await select.click();
      await select.locator('input').fill(String(stamp));
      expect(new URL((await listed).url()).searchParams.get('type')).toBe('nissaya');
      const options = page.locator('.ant-select-dropdown:visible .ant-select-item-option');
      await expect(options.filter({ hasText: nissaya2.name })).toBeVisible({ timeout: 10_000 });
      await expect(options.filter({ hasText: b.name })).toHaveCount(0);
      await shot(page, '06-fallback-same-type');
      // 术语路径只对 translation channel 有效，nissaya 的设置页里没有这一项
      await expect(item(page, '术语路径')).toHaveCount(0);
      await page.keyboard.press('Escape');
    });
  } finally {
    for (const ch of [a, b, c, nissaya, nissaya2]) {
      await call(request, 'DELETE', `/v3/channels/${ch.id}`);
    }
  }
});
