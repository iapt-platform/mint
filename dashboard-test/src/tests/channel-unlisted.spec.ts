import { test, expect, type Page } from '@playwright/test';
import { call } from '../lib/api.js';
import { DESTRUCTIVE, shotDir } from '../lib/env.js';

/**
 * channel 公开性「公开不列出」（status 20）：拿到链接谁都能看，但不出现在社区列表里。
 * 用 e2e-tmp-* 临时 channel，测完软删。
 */
const SHOT = shotDir('channel-unlisted');
const shot = (page: Page, name: string) =>
  page.screenshot({ path: `${SHOT}/${name}.png`, fullPage: true });

test('设置为「公开不列出」：社区里没有，直接打开可以', async ({ page, request, playwright }) => {
  test.skip(!DESTRUCTIVE, 'E2E_DESTRUCTIVE=0');
  const me = (await call(request, 'GET', '/v3/me')).body.data;
  const name = `e2e-tmp-${Date.now()}`;
  const created = await call(request, 'POST', `/v3/studios/${me.id}/channels`, {
    name, type: 'translation', lang: 'zh-Hans', status: 30,
  });
  expect(created.status).toBe(201);
  const id = created.body.data.id as string;

  await test.step('1. 设置页选「公开不列出」并保存', async () => {
    await page.goto(`workspace/channel/${id}/setting`, { waitUntil: 'networkidle' });
    await page.locator('.ant-form-item').filter({ hasText: '公开性' }).locator('.ant-select').click();
    await page.locator('.ant-select-dropdown:visible .ant-select-item-option').filter({ hasText: '公开不列出' }).click();
    const saved = page.waitForResponse((r) => r.url().endsWith(`/api/v3/channels/${id}`) && r.request().method() === 'PATCH');
    await page.getByRole('button', { name: /提 *交/ }).click();
    const res = await saved;
    expect(res.status()).toBe(200);
    expect((await res.json()).data.status).toBe(20);
    await shot(page, '01-saved');
  });

  await test.step('2. 社区页签搜不到它', async () => {
    const listed = page.waitForResponse((r) => /\/api\/v3\/channels\?/.test(r.url()));
    await page.goto(`workspace/channel?tab=community&q=${name}`, { waitUntil: 'networkidle' });
    const body = await (await listed).json();
    expect((body.data as { id: string }[]).map((c) => c.id)).not.toContain(id);
    await expect(page.locator('.ant-table-row').filter({ hasText: name })).toHaveCount(0);
  });

  await test.step('3. 不登录也能直接打开它', async () => {
    const anon = await playwright.request.newContext();
    const res = await anon.get(new URL(`/api/v3/channels/${id}`, page.url()).toString());
    expect(res.status()).toBe(200);
    expect((await res.json()).data.abilities.read).toBe(true);
    await anon.dispose();
  });

  expect((await call(request, 'DELETE', `/v3/channels/${id}`)).status).toBe(200);
});
