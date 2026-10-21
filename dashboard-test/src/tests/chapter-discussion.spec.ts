import { test, expect, type Page } from '@playwright/test';
import { collect } from '../lib/collect.js';
import { call } from '../lib/api.js';
import { CHAPTER, CHAPTER_CHANNEL, shotDir } from '../lib/env.js';

/**
 * 章节页右侧「讨论」面板的「本章」视图（GET /v3/tipitaka-discussions/{channel}）
 *
 * 数据：章节与版本由 E2E_CHAPTER / E2E_CHAPTER_CHANNEL 指定（默认本机开发库的数据）。
 * 测试前用 v3 API 在这一章起始段的第一句上建一条 issue（notify=false；issue 登录即可发、
 * 对所有人公开，测试账号不需要该版本的编辑权），测试后删掉（软删，对外不可见）。
 */
const SHOT = shotDir('chapter-discussion');
const shot = (page: Page, name: string) => page.screenshot({ path: `${SHOT}/${name}.png` });
const panel = (page: Page) => page.locator('.ant-pro-list');
const scopeSelected = (page: Page) => page.locator('.ant-segmented-item-selected').first();

test.describe('章节页 讨论面板 · 本章', () => {
  const stamp = `e2e-tmp-${Date.now()}`;
  let topicId = '';
  let sentenceId = '';
  const chapterUrl = `workspace/tipitaka/chapter/${CHAPTER}?channel=${CHAPTER_CHANNEL}`;

  test.beforeAll(async ({ request }) => {
    const [book, para] = CHAPTER.split('-');
    const sents = await call(
      request,
      'GET',
      `/v2/sentence?view=paragraph&book=${book}&para=${para}&channels=${CHAPTER_CHANNEL}&limit=1`,
    );
    sentenceId = sents.body?.data?.rows?.[0]?.id;
    expect(sentenceId, `${CHAPTER} 起始段在 ${CHAPTER_CHANNEL} 里没有句子`).toBeTruthy();

    const topic = await call(request, 'POST', '/v3/discussions', {
      target_type: 'sentence',
      target_id: sentenceId,
      type: 'issue',
      severity: 'warning',
      title: stamp,
      content: '由 dashboard-test 建立，测完即删',
      notify: false,
    });
    expect(topic.status, JSON.stringify(topic.body)).toBe(201);
    topicId = topic.body.data.id;
  });

  test.afterAll(async ({ request }) => {
    if (topicId) await call(request, 'DELETE', `/v3/discussions/${topicId}`);
  });

  test('列出本章讨论、按类型过滤、选中行高亮句子、点开切到本句、返回', async ({ page }) => {
    const log = collect(page);

    await test.step('1. 打开章节页的讨论面板：默认「本章」，列出刚建的 issue', async () => {
      await page.goto(chapterUrl, { waitUntil: 'networkidle' });
      const listed = page.waitForResponse((r) => r.url().includes('/api/v3/tipitaka-discussions/'));
      await page.getByRole('button', { name: '讨论' }).first().click();
      expect((await listed).status()).toBe(200);
      await expect(scopeSelected(page)).toHaveText('本章');
      await expect(panel(page).getByText(stamp, { exact: true })).toBeVisible();
      await shot(page, '01-chapter');
      log.dump('1. 本章列表');
    });

    await test.step('2. 类型只选 glossary → 看不到这条 issue；清掉过滤 → 又出现', async () => {
      await page.locator('.ant-select').filter({ hasText: '类型' }).click();
      const filtered = page.waitForResponse((r) => r.url().includes('type=glossary'));
      await page.locator('.ant-select-item-option').filter({ hasText: /^glossary$/ }).click();
      await page.keyboard.press('Escape');
      await filtered;
      await expect(panel(page).getByText(stamp, { exact: true })).toHaveCount(0);

      const cleared = page.waitForResponse(
        (r) => r.url().includes('/api/v3/tipitaka-discussions/') && !r.url().includes('type='),
      );
      await page.locator('.ant-select').filter({ hasText: 'glossary' }).locator('.ant-select-clear').click();
      await cleared;
      await expect(panel(page).getByText(stamp, { exact: true })).toBeVisible();
      log.dump('2. 类型过滤');
    });

    await test.step('3. 点一行 → 正文里那句高亮；再点一次 → 取消', async () => {
      // 章节页默认阅读模式：译文块带 data-sent-uid
      const sent = page.locator(`[data-sent-uid="${sentenceId}"]`);
      const row = panel(page).locator('.ant-pro-list-row').filter({ hasText: stamp });
      await row.locator('.ant-pro-list-row-description').click();
      await expect(sent).toHaveCSS('background-color', 'rgba(24, 144, 255, 0.2)');
      await expect(sent).toBeInViewport();
      await shot(page, '02-selected');

      await row.locator('.ant-pro-list-row-description').click();
      await expect(sent).toHaveCSS('background-color', 'rgba(0, 0, 0, 0)');
      log.dump('3. 选中行');
    });

    await test.step('4. 点标题 → 切到「本句」并打开话题；返回 → 这一句的讨论列表', async () => {
      await panel(page).getByRole('button', { name: stamp }).click();
      await expect(scopeSelected(page)).toHaveText('本句');
      await expect(page.locator(`#answer-${topicId}`)).toBeVisible();
      await shot(page, '03-topic');

      await page.locator('button:has(.anticon-arrow-left)').first().click();
      await expect(panel(page).getByRole('button', { name: stamp })).toBeVisible();
      await shot(page, '04-sentence-list');
      log.dump('4. 点开与返回');
    });

    const problems = log.all.filter(
      (l) => l.startsWith('[PAGEERROR]') || (l.startsWith('[HTTP') && /discussion/.test(l)),
    );
    expect(problems).toEqual([]);
  });
});
