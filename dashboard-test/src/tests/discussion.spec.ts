import { test, expect, type APIRequestContext, type Page } from '@playwright/test';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { collect } from '../lib/collect.js';
import { BASE, DISCUSSION_SENTENCE, shotDir } from '../lib/env.js';

/**
 * 讨论话题页 /discussion/topic/:topicId（「复制链接」拷出的地址）
 *
 * 数据：测试前用 v3 API 在测试账号自己的句子上建一个 discussion 话题 + 一条回复
 * （notify=false，不发通知），测试后把剩下的回复和话题删掉。删除是软删，
 * 表里会留两三行带 deleted_at 的记录，对外不可见。
 * 句子换环境时用 E2E_DISCUSSION_SENTENCE 覆盖（与 api-test 的 discussion_sentence 同一条）。
 */
const SHOT = shotDir('discussion');
const SENTENCE = DISCUSSION_SENTENCE;
/** 走 dev server 的 /api 代理 */
const API = new URL('/api/v3', BASE).toString();

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const AUTH_FILE = path.join(__dirname, '../../playwright/.auth/user.json');

/** setup 项目登录后存下的 token（v6 放在 localStorage 的 token 里） */
function token(): string {
  const state = JSON.parse(fs.readFileSync(AUTH_FILE, 'utf8')) as {
    origins: { localStorage: { name: string; value: string }[] }[];
  };
  const t = state.origins.flatMap((o) => o.localStorage).find((i) => i.name === 'token')?.value;
  if (!t) throw new Error('playwright/.auth/user.json 里没有 token，setup 是否跑过？');
  return t;
}

async function call(request: APIRequestContext, method: string, url: string, data?: object) {
  const res = await request.fetch(`${API}${url}`, {
    method,
    data,
    headers: { Authorization: `Bearer ${token()}`, Accept: 'application/json' },
  });
  return { status: res.status(), body: await res.json().catch(() => null) };
}

const shot = (page: Page, name: string) =>
  page.screenshot({ path: `${SHOT}/${name}.png`, fullPage: true });
const items = (page: Page) => page.locator('[id^=answer-]');
const editors = (page: Page) => page.locator('.w-md-editor');

/** 打开某条（话题或回复）右上角的 ⋯ 菜单，点其中一项 */
async function menu(page: Page, id: string, label: string) {
  await page.locator(`#answer-${id} .anticon-more`).click();
  await page
    .locator('.ant-dropdown:not(.ant-dropdown-hidden) .ant-dropdown-menu-item')
    .filter({ hasText: new RegExp(`^${label}$`) })
    .click();
}

test.describe('话题页 /discussion/topic/:id', () => {
  const stamp = `e2e-tmp-${Date.now()}`;
  let topicId = '';
  let replyId = '';

  test.beforeAll(async ({ request }) => {
    const topic = await call(request, 'POST', '/discussions', {
      target_type: 'sentence',
      target_id: SENTENCE,
      type: 'discussion',
      title: stamp,
      content: '由 dashboard-test 建立，测完即删',
      notify: false,
    });
    expect(topic.status, JSON.stringify(topic.body)).toBe(201);
    topicId = topic.body.data.id;

    const reply = await call(request, 'POST', `/discussions/${topicId}/replies`, {
      content: `${stamp} 第一条回复`,
      notify: false,
    });
    expect(reply.status).toBe(201);
    replyId = reply.body.data.id;
  });

  test.afterAll(async ({ request }) => {
    if (!topicId) return;
    const left = await call(request, 'GET', `/discussions/${topicId}/replies`);
    for (const r of left.body?.data ?? []) {
      await call(request, 'DELETE', `/discussions/${r.id}`);
    }
    await call(request, 'DELETE', `/discussions/${topicId}`);
  });

  test('登录用户：打开、回复、关闭 / 重开、删回复', async ({ page }) => {
    const log = collect(page);

    await test.step('1. 打开话题页', async () => {
      await page.goto(`discussion/topic/${topicId}`, { waitUntil: 'networkidle' });
      await expect(page.getByText(stamp, { exact: true })).toBeVisible();
      await expect(items(page)).toHaveCount(2); // 话题 + 一条回复
      await expect(editors(page)).toHaveCount(1); // 回复框
      await shot(page, '01-topic');
      const problems = [...log.all];
      log.dump('1. 打开话题页');
      expect(problems).toEqual([]);
    });

    let uiReplyId = '';
    await test.step('2. 发一条回复', async () => {
      await editors(page).locator('textarea').fill(`${stamp} 页面上发的回复`);
      const posted = page.waitForResponse(
        (r) => r.url().endsWith(`/discussions/${topicId}/replies`) && r.request().method() === 'POST',
      );
      await page.getByRole('button', { name: /提 *交/ }).click();
      const res = await posted;
      expect(res.status()).toBe(201);
      uiReplyId = (await res.json()).data.id;
      await expect(page.locator(`#answer-${uiReplyId}`)).toContainText('页面上发的回复');
      await shot(page, '02-replied');
      log.dump('2. 发一条回复');
    });

    await test.step('3. 关闭话题 → 回复框消失；重新打开 → 回复框回来', async () => {
      await menu(page, topicId, '关闭');
      await expect(editors(page)).toHaveCount(0);
      await shot(page, '03-closed');

      await menu(page, topicId, '打开');
      await expect(editors(page)).toHaveCount(1);
      log.dump('3. 关闭 / 重开');
    });

    await test.step('4. 删掉页面上发的回复', async () => {
      await menu(page, uiReplyId, '删除');
      const deleted = page.waitForResponse(
        (r) => r.url().endsWith(`/discussions/${uiReplyId}`) && r.request().method() === 'DELETE',
      );
      await page.locator('.ant-modal-confirm-btns .ant-btn-dangerous').click();
      expect((await deleted).status()).toBe(200);
      await expect(page.locator(`#answer-${uiReplyId}`)).toHaveCount(0);
      await shot(page, '04-reply-deleted');
      log.dump('4. 删回复');
    });
  });

  test('回复链接带 #锚点：定位到那条回复', async ({ page }) => {
    await page.goto(`discussion/topic/${topicId}#${replyId}`, { waitUntil: 'networkidle' });
    await expect(page.locator(`#answer-${replyId}`)).toBeInViewport();
  });

  test('匿名访问 discussion 类话题 → 无权查看', async ({ browser }) => {
    // discussion / issue 是协作类，匿名不可见（后端 404）
    const context = await browser.newContext({
      baseURL: BASE,
      storageState: { cookies: [], origins: [] },
    });
    const page = await context.newPage();
    await page.goto(`discussion/topic/${topicId}`, { waitUntil: 'networkidle' });
    await expect(page.getByText('话题不存在或无权查看')).toBeVisible();
    await expect(page.getByText(stamp)).toHaveCount(0);
    await shot(page, '05-anonymous');
    await context.close();
  });

  test('不存在的话题 → 404 页，不崩', async ({ page }) => {
    const log = collect(page);
    await page.goto('discussion/topic/00000000-0000-4000-8000-000000000000', {
      waitUntil: 'networkidle',
    });
    await expect(page.getByText('话题不存在或无权查看')).toBeVisible();
    // 只允许那一条预期的 404
    expect(log.all.filter((l) => !l.includes('[HTTP 404]') && !l.includes('404 (Not Found)'))).toEqual([]);
  });
});
