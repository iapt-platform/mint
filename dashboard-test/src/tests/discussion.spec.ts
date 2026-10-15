import { test, expect, type APIRequestContext, type Page } from '@playwright/test';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { collect } from '../lib/collect.js';
import { ARTICLE, BASE, DISCUSSION_SENTENCE, shotDir } from '../lib/env.js';

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

/**
 * 只看讨论自己闯的祸：页面异常、请求失败、讨论接口报错。
 *
 * 讨论挂靠内容（句子 / 文章）是用现成的阅读组件渲染的，那里有几处与讨论无关的老问题
 * （重复 key "0"、<p> 嵌套、nav-article 拼出 `_undefined`），记在测试日志的待修里，
 * 不在这里掩盖成「讨论有错」。
 */
const discussionProblems = (all: string[]) =>
  all.filter(
    (l) =>
      l.startsWith('[PAGEERROR]') ||
      l.startsWith('[REQFAIL]') ||
      (l.startsWith('[HTTP') && /discussion/.test(l)),
  );

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
      const problems = discussionProblems(log.all);
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

  test('话题页上方显示讨论挂在哪一句，并链到这里的全部讨论', async ({ page }) => {
    await page.goto(`discussion/topic/${topicId}`, { waitUntil: 'networkidle' });
    await expect(page.locator('.ant-card').first()).toBeVisible();
    await page.getByRole('link', { name: '这里的全部讨论' }).click();
    await expect(page).toHaveURL(new RegExp(`discussion/show/sentence/${SENTENCE}$`));
  });

  test('对象的全部讨论 /discussion/show/:type/:id：列表里有这个话题，点开在原地看', async ({ page }) => {
    const log = collect(page);
    await page.goto(`discussion/show/sentence/${SENTENCE}`, { waitUntil: 'networkidle' });
    await page.getByText(stamp, { exact: true }).click();
    await expect(items(page).filter({ hasText: '第一条回复' })).toHaveCount(1);
    await shot(page, '06-show');
    const problems = discussionProblems(log.all);
    log.dump('show 页');
    expect(problems).toEqual([]);
  });

  test('讨论角标：挂靠句子的角标数据走 v3 batch-count，数到这个话题', async ({ page }) => {
    const counted = page.waitForResponse(
      async (r) =>
        r.url().includes('/api/v3/discussions/batch-count') &&
        ((await r.json()).data ?? []).some(
          (b: { target_id: string; total: number }) => b.target_id === SENTENCE && b.total >= 1,
        ),
    );
    await page.goto(`discussion/show/sentence/${SENTENCE}`);
    await counted;
  });

  test('不支持的对象类型 → 提示页', async ({ page }) => {
    await page.goto(`discussion/show/channel/${SENTENCE}`, { waitUntil: 'networkidle' });
    await expect(page.getByText('不支持的讨论对象')).toBeVisible();
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

test.describe('v4 漏搬的讨论入口', () => {
  test('文章底部有问答 / 求助 / 讨论区；角标（v3）与标签（v2）各只请求一次（防抖）', async ({ page }) => {
    const log = collect(page);
    const badges: number[] = [];
    const tagsReq: number[] = [];
    page.on('request', (r) => {
      if (r.method() !== 'POST') return;
      if (r.url().includes('/api/v3/discussions/batch-count')) badges.push(Date.now());
      if (r.url().includes('/api/v2/discussion-count')) tagsReq.push(Date.now());
    });
    await page.goto(`workspace/article/${ARTICLE}`, { waitUntil: 'networkidle' });
    await page.waitForTimeout(1500);

    // 文章作者：qa / help 能发、discussion 登录即可发，三个页签都在
    const tabs = page.locator('.ant-tabs-tab');
    await expect(tabs.filter({ hasText: '问答' })).toHaveCount(1);
    await expect(tabs.filter({ hasText: '求助' })).toHaveCount(1);
    await expect(tabs.filter({ hasText: '讨论' })).toHaveCount(1);
    await shot(page, '07-article-interactive');

    // 有句子才会请求；有就只该一两次，而不是每句一次
    console.log('batch-count 请求次数:', badges.length, ' discussion-count（标签）请求次数:', tagsReq.length);
    expect(badges.length).toBeLessThanOrEqual(2);
    expect(tagsReq.length).toBeLessThanOrEqual(2);
    const problems = discussionProblems(log.all);
    log.dump('文章页');
    expect(problems).toEqual([]);
  });

  test('术语的讨论组件能正常加载（不再是 DiscussionMock 占位）', async ({ page }) => {
    await page.goto('', { waitUntil: 'networkidle' });
    // 直接在 dev server 上加载模块：循环引用之类的问题会在求值时抛出
    const loaded = await page.evaluate(async (base) => {
      const mod = await import(/* @vite-ignore */ `${new URL(base).pathname}src/components/term/TermItem.tsx`);
      return typeof mod.default;
    }, BASE);
    expect(loaded).toBe('function');
  });
});
