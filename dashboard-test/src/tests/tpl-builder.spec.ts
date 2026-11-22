import { test, expect } from '@playwright/test';
import { collect } from '../lib/collect.js';

/**
 * 模板生成器（components/tpl-builder，后端 Markup/Templates 的全部可手写模板）：
 * 从文章阅读工具栏的「获取引用模版」打开，article 页签按这篇文章预填；切到其它模板填参数，代码随之变化，
 * 预览走 v3 /markup-previews（后端渲染，与阅读页一致）。
 */
test('文章「获取引用模版」：预填 article 模板，切换模板生成代码并预览', async ({ page }) => {
  const log = collect(page);
  const list = page.waitForResponse((r) => /\/api\/v2\/article\?/.test(r.url()));
  await page.goto('workspace/article', { waitUntil: 'networkidle' });
  const rows = (await (await list).json()).data?.rows ?? [];
  if (rows.length === 0) test.skip(true, '测试账号没有文章');
  // 打开第一篇文章，阅读工具栏「更多」→「获取引用模版」
  await page.goto(`workspace/article/${rows[0].uid}`, { waitUntil: 'networkidle' });
  await page.getByRole('img', { name: 'more' }).first().click();
  const preview1 = page.waitForResponse((r) => r.url().includes('/api/v3/markup-previews'));
  await page.getByRole('menuitem', { name: '获取引用模版' }).click();

  const dialog = page.getByRole('dialog').filter({ hasText: '模板生成器' });
  await expect(dialog).toBeVisible();
  const code = dialog.locator('textarea[readonly]');

  // 弹窗不超出浏览器；换模板时修改区高度不变（内容多的 article 与内容少的 quality 同高）
  const viewport = page.viewportSize()!.height;
  const panelHeight = async () => (await dialog.locator('.tpl-builder-panel').boundingBox())!.height;

  // article：按入口预填 type 与 id
  await expect(code).toHaveValue(/^\{\{article\|type=article\|id=[0-9a-f-]{36}/);
  expect((await preview1).status()).toBe(200);
  await page.waitForTimeout(500); // 等弹窗的打开动画结束再量
  const box = (await dialog.boundingBox())!;
  expect(box.y + box.height).toBeLessThanOrEqual(viewport);
  const articleHeight = await panelHeight();

  // quality：缺省 pending，改成 featured，预览出标签
  await dialog.getByRole('menuitem', { name: /quality/ }).click();
  await expect(code).toHaveValue('{{quality|pending}}');
  expect(await panelHeight()).toBe(articleHeight);
  await dialog.locator('.ant-select').first().click();
  const featured = page.waitForResponse(
    (r) => r.url().includes('/api/v3/markup-previews') && (r.request().postData() ?? '').includes('featured'),
  );
  await page.locator('.ant-select-item-option', { hasText: 'featured' }).click();
  await expect(code).toHaveValue('{{quality|featured}}');
  const res = await featured;
  expect(res.status()).toBe(200);
  const body = (await res.json()).data.body;
  expect(JSON.stringify(body)).toContain('"name":"quality"');
  await expect(dialog.locator('.ant-tag').last()).toBeVisible();

  // note：正文放第一个位置参数，其余写名字
  await dialog.getByRole('menuitem', { name: /note/ }).click();
  await dialog.locator('textarea:not([readonly])').first().fill('脚注正文 a=b');
  await dialog.getByRole('textbox').nth(1).fill('*');
  await expect(code).toHaveValue('{{note|脚注正文 a=b|trigger=*}}');

  // mermaid：生成围栏
  await dialog.getByRole('menuitem', { name: /mermaid/ }).click();
  const mermaid = page.waitForResponse(
    (r) => r.url().includes('/api/v3/markup-previews') && (r.request().postData() ?? '').includes('mermaid'),
  );
  await dialog.locator('textarea:not([readonly])').first().fill('graph LR\n  A --> B');
  await expect(code).toHaveValue('```mermaid\ngraph LR\n  A --> B\n```');
  expect(JSON.stringify((await (await mermaid).json()).data.body)).toContain('"name":"mermaid"');
  // mermaid 异步渲染出的图（不是图标的 svg）
  await expect(dialog.locator('svg[aria-roledescription]')).toBeVisible();

  await page.screenshot({ path: 'testlog/assets/tpl-builder.png', fullPage: true });
  log.dump('模板生成器');
});
