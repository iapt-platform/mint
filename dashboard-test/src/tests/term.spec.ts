import { test, expect, type Page } from '@playwright/test';
import { call } from '../lib/api.js';
import { collect } from '../lib/collect.js';
import { DESTRUCTIVE, shotDir } from '../lib/env.js';

/**
 * 术语（v3）：channel 详情页的 term 页签——新建、编辑（词头不可改、质量字段）、
 * 并发修改的 412 提示、删除；以及术语页列表。
 * 用 e2e-tmp-* 临时 channel，测完软删（术语也是软删）。
 */
const SHOT = shotDir('term');
const shot = (page: Page, name: string) =>
  page.screenshot({ path: `${SHOT}/${name}.png`, fullPage: true });

const modal = (page: Page) => page.locator('.ant-modal:visible');
/** channel 详情页有多个页签，各有表格；只看 term 页签 */
const pane = (page: Page) => page.getByRole('tabpanel', { name: 'term' });
const field = (page: Page, label: string) =>
  modal(page).locator('.ant-form-item').filter({ has: page.locator('label', { hasText: new RegExp(`^${label}$`) }) });

const submit = async (page: Page, method: 'POST' | 'PATCH') => {
  const saved = page.waitForResponse(
    (r) => /\/api\/v3\/(channels\/[^/]+\/terms|terms\/[^/?]+)$/.test(new URL(r.url()).pathname) && r.request().method() === method,
  );
  await modal(page).getByRole('button', { name: /^保\s*存/ }).click();
  return saved;
};

test('channel 的术语：新建、编辑、并发修改提示、删除', async ({ page, request }) => {
  test.skip(!DESTRUCTIVE, 'E2E_DESTRUCTIVE=0');
  const log = collect(page);
  const me = (await call(request, 'GET', '/v3/me')).body.data;
  const stamp = Date.now();
  const created = await call(request, 'POST', `/v3/studios/${me.id}/channels`, {
    name: `e2e-tmp-term-${stamp}`, type: 'translation', lang: 'zh-Hans',
  });
  expect(created.status).toBe(201);
  const channel = created.body.data.id as string;
  const word = `e2e${stamp}`;
  let termId = '';

  try {
    await test.step('1. 打开 channel 的 term 页签，列表为空', async () => {
      await page.goto(`workspace/channel/${channel}`, { waitUntil: 'networkidle' });
      const listed = page.waitForResponse((r) => r.url().includes(`/api/v3/channels/${channel}/terms`));
      await page.getByRole('tab', { name: 'term' }).click();
      expect((await listed).status()).toBe(200);
      await expect(pane(page).locator('.ant-table-placeholder')).toBeVisible();
      log.dump('1. term 页签');
    });

    await test.step('2. 新建术语（质量：草稿，正文带分类）', async () => {
      await pane(page).getByRole('button', { name: /新\s*建|创\s*建/ }).first().click();
      await expect(modal(page)).toBeVisible();
      await field(page, '词头').locator('input').fill(word);
      await field(page, '意思').locator('input').fill('测试释义');
      await field(page, '注解').locator('textarea').fill('第一段。\n\n{{category|e2e分类}}');
      // 版本、语言、质量、正文格式、重定向在「设置」页签
      await modal(page).getByRole('tab', { name: '设置' }).click();
      await expect(field(page, '所属版本')).toBeVisible(); // 版本下拉已预选本版本
      await field(page, '质量').locator('.ant-select').click();
      await page.locator('.ant-select-dropdown:visible .ant-select-item-option').filter({ hasText: '草稿' }).click();
      await expect(modal(page).getByRole('button', { name: /^提\s*交$|^重\s*置$/ })).toHaveCount(0);
      const res = await submit(page, 'POST');
      expect(res.status()).toBe(201);
      const data = (await res.json()).data;
      termId = data.id;
      expect(data.channel_id).toBe(channel);
      expect(data.quality).toBe('draft');
      expect(data.content_type).toBe('markdown-e2');
      expect(data.categories).toEqual(['e2e分类']);
      await expect(pane(page).locator('.ant-table-row').filter({ hasText: word })).toBeVisible();
      await shot(page, '02-created');
      log.dump('2. 新建');
    });

    await test.step('3. 编辑：词头不可改，改释义后保存', async () => {
      await pane(page).locator('.ant-table-row').filter({ hasText: word }).getByText('编辑').click();
      await expect(field(page, '词头').locator('input')).toBeDisabled();
      await expect(field(page, '意思').locator('input')).toHaveValue('测试释义');
      await field(page, '意思').locator('input').fill('改过的释义');
      const res = await submit(page, 'PATCH');
      expect(res.status()).toBe(200);
      expect(res.request().headers()['if-match']).toBeTruthy();
      await expect(pane(page).locator('.ant-table-row').filter({ hasText: '改过的释义' })).toBeVisible();
      await shot(page, '03-edited');
    });

    await test.step('3b. 编辑页：右上角取消 / 保存，脏文档提示，另存为弹出版本选择', async () => {
      await page.goto(`workspace/term/${termId}/edit`, { waitUntil: 'networkidle' });
      const meaning = page.locator('.ant-form-item').filter({ has: page.locator('label', { hasText: /^意思$/ }) }).locator('input');
      await expect(meaning).toHaveValue('改过的释义');
      // 没改就取消：直接回预览
      await page.getByRole('button', { name: /^取\s*消$/ }).click();
      await expect(page).toHaveURL(new RegExp(`/workspace/term/${termId}$`));

      // 从预览页点「编辑」站内进入，这样后退是路由跳转而不是整页离开
      await page.getByText(/^编\s*辑$/).first().click();
      await expect(page).toHaveURL(/\/edit$/);
      await expect(meaning).toHaveValue('改过的释义');
      await meaning.fill('在页面上改的');
      // 改了再取消：提示保存，选「继续编辑」留在原页
      await page.getByRole('button', { name: /^取\s*消$/ }).click();
      const confirm = page.locator('.ant-modal-confirm:not(.ant-zoom-leave)');
      await expect(confirm).toContainText('有未保存的修改');
      await confirm.getByRole('button', { name: '继续编辑' }).click();
      await expect(page).toHaveURL(/\/edit$/);
      // 浏览器后退也被拦下
      await page.goBack();
      await expect(confirm).toContainText('有未保存的修改');
      await confirm.getByRole('button', { name: '继续编辑' }).click();
      await expect(page).toHaveURL(/\/edit$/);

      // 另存为：下拉里点「另存为」弹出版本选择，取消不保存
      await page.locator('.ant-space-compact .ant-btn-icon-only').click();
      await page.locator('.ant-dropdown:visible .ant-dropdown-menu-item').filter({ hasText: '另存为' }).click();
      const saveAs = page.locator('.ant-modal:visible').filter({ hasText: '另存为' });
      await expect(saveAs.locator('label', { hasText: '所属版本' })).toBeVisible();
      await shot(page, '03b-save-as');
      await saveAs.getByRole('button', { name: /^取\s*消$/ }).click();
      await expect(saveAs).toHaveCount(0);

      // 保存：回到预览
      const saved = page.waitForResponse((r) => r.url().endsWith(`/api/v3/terms/${termId}`) && r.request().method() === 'PATCH');
      await page.getByRole('button', { name: /^保\s*存/ }).click();
      expect((await saved).status()).toBe(200);
      await expect(page).toHaveURL(new RegExp(`/workspace/term/${termId}$`));
      await page.goto(`workspace/channel/${channel}`, { waitUntil: 'networkidle' });
      await page.getByRole('tab', { name: 'term' }).click();
      await expect(pane(page).locator('.ant-table-row').filter({ hasText: '在页面上改的' })).toBeVisible();
    });

    await test.step('4. 编辑期间被别人改过：提示 412，不覆盖', async () => {
      await pane(page).locator('.ant-table-row').filter({ hasText: word }).getByText('编辑').click();
      await expect(field(page, '意思').locator('input')).toHaveValue('在页面上改的');
      // 别人（这里用同一账号的另一个请求）先改了一次
      const fresh = (await call(request, 'GET', `/v3/terms/${termId}`)).body.data;
      const other = await call(request, 'PATCH', `/v3/terms/${termId}`, { meaning: '别人改的' }, 'user', { 'If-Match': fresh.etag });
      expect(other.status).toBe(200);

      await field(page, '意思').locator('input').fill('我的修改');
      const res = await submit(page, 'PATCH');
      expect(res.status()).toBe(412);
      await expect(page.getByText(/被别人改过/).first()).toBeVisible();
      expect((await call(request, 'GET', `/v3/terms/${termId}`)).body.data.meaning).toBe('别人改的');
      await shot(page, '04-conflict');
      // 没保存成功、表单还是脏的：Esc（同 ×）先提示，选「不保存」才关
      await page.keyboard.press('Escape');
      const confirm = page.locator('.ant-modal-confirm:not(.ant-zoom-leave)');
      await expect(confirm).toContainText('有未保存的修改');
      await confirm.getByRole('button', { name: '不保存' }).click();
      await expect(modal(page)).toHaveCount(0);
    });

    await test.step('4b. 导出 xlsx，再原样导入：全部「未变」', async () => {
      await page.reload({ waitUntil: 'networkidle' });
      await page.getByRole('tab', { name: 'term' }).click();
      await expect(pane(page).locator('.ant-table-row').filter({ hasText: word })).toBeVisible();
      const download = page.waitForEvent('download');
      const exported = page.waitForResponse((r) => r.url().includes(`/api/v3/channels/${channel}/terms`) && r.request().headers()['accept']?.includes('spreadsheetml'));
      await pane(page).getByRole('button', { name: /导\s*出/ }).click();
      expect((await exported).status()).toBe(200);
      const file = await (await download).path();

      const imported = page.waitForResponse((r) => r.url().endsWith(`/api/v3/channels/${channel}/terms/imports`));
      await pane(page).locator('input[type=file]').setInputFiles({ name: 'terms.xlsx', mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', buffer: (await import('node:fs')).readFileSync(file) });
      const res = await imported;
      expect(res.status()).toBe(200);
      const data = (await res.json()).data;
      expect(data).toMatchObject({ created: 0, updated: 0, unchanged: 1, errors: [] });
      await expect(page.locator('.ant-modal:visible')).toContainText('未变 1 条');
      await shot(page, '04b-import-result');
      await page.keyboard.press('Escape');
      log.dump('4b. 导出导入');
    });

    await test.step('5. 删除', async () => {
      await page.reload({ waitUntil: 'networkidle' });
      await page.getByRole('tab', { name: 'term' }).click();
      const row = pane(page).locator('.ant-table-row').filter({ hasText: word });
      await row.locator('.ant-dropdown-trigger').click();
      await page.locator('.ant-dropdown:visible .ant-dropdown-menu-item').filter({ hasText: '删除' }).click();
      const deleted = page.waitForResponse((r) => r.url().endsWith(`/api/v3/terms/${termId}`) && r.request().method() === 'DELETE');
      await page.locator('.ant-modal-confirm .ant-btn-dangerous').click();
      expect((await deleted).status()).toBe(200);
      await expect(row).toHaveCount(0);
      log.dump('5. 删除');
    });
  } finally {
    await call(request, 'DELETE', `/v3/channels/${channel}`);
  }
});

test('术语页：列出当前身份名下的术语', async ({ page }) => {
  const log = collect(page);
  const listed = page.waitForResponse((r) => /\/api\/v3\/studios\/[^/]+\/terms/.test(r.url()));
  await page.goto('workspace/term', { waitUntil: 'networkidle' });
  expect((await listed).status()).toBe(200);
  await expect(page.locator('.ant-table')).toBeVisible();
  await shot(page, '06-studio-list');
  log.dump('6. 术语页');
});

test('术语详情页：显示摘要与正文（v3 渲染）', async ({ page }) => {
  const log = collect(page);
  const id = 'b7ede292-dc78-4138-a13c-3cf1a26cad62';
  const loaded = page.waitForResponse((r) => r.url().includes(`/api/v3/terms/${id}`));
  await page.goto(`workspace/term/${id}`, { waitUntil: 'networkidle' });
  const term = (await (await loaded).json()).data;
  test.skip(!term?.summary, '开发库里这条术语没有摘要');
  await expect(page.locator('.pcd_article').first()).toContainText('是律学“十七特征”的第五项');
  await expect(page.locator('.pcd_article').first()).toContainText('词源与定义');
  // 质量标签（空按 pending，显示本地化文字）
  const quality = (term.quality ?? 'pending') as string;
  const label = { featured: '特色', standard: '标准', draft: '草稿', pending: '待审' }[quality];
  await expect(page.locator('.ant-tag').filter({ hasText: label })).toBeVisible();
  await shot(page, '07-term-show');
  log.dump('7. 术语详情页');
});

test('术语详情页：点正文里的术语，在当前页打开那条术语', async ({ page }) => {
  const log = collect(page);
  const id = 'b7ede292-dc78-4138-a13c-3cf1a26cad62';
  await page.goto(`workspace/term/${id}`, { waitUntil: 'networkidle' });
  await expect(page.locator('.pcd_article').first()).toBeVisible();
  const link = page.locator('.pcd_article .term + a').first();
  test.skip((await link.count()) === 0, '这条术语的正文里没有术语链接');
  const text = (await link.innerText()).trim();

  const next = page.waitForResponse((r) => /\/api\/v3\/terms\/[0-9a-f-]{36}\?/.test(r.url()) && !r.url().includes(id));
  await link.click();
  const loaded = (await (await next).json()).data;
  await expect(page).toHaveURL(new RegExp(`/workspace/term/${loaded.id}`));
  expect(loaded.id).not.toBe(id);
  // 标题换成新术语的释义（新术语可能没有正文，不能拿正文判断）
  await expect(page.getByRole('heading', { name: loaded.meaning, level: 4 }).first()).toBeVisible();
  console.log(`>>> 点「${text}」→ ${loaded.word}（${loaded.meaning}）`);
  await shot(page, '08-term-navigate');
  log.dump('8. 点术语跳转');
});
