import { test, expect, type Page } from '@playwright/test';
import { collect } from '../lib/collect.js';

/**
 * 主导航「工具」：每个子项都打开弹窗，右上角「在新标签页打开」开对应页面。
 * 字典、语法手册的独立页面挂在根下（/dictionary、/grammar-book），其余仍是 /workspace/*。
 */
const openTool = async (page: Page, name: string) => {
  const tools = page.getByRole('menuitem', { name: '工具' });
  if ((await page.getByRole('menuitem', { name, exact: true }).count()) === 0) await tools.click();
  await page.getByRole('menuitem', { name, exact: true }).click();
  const dialog = page.getByRole('dialog').filter({ hasText: name });
  await expect(dialog).toBeVisible();
  return dialog;
};

const openInNewTab = async (page: Page, dialog: ReturnType<Page['getByRole']>) => {
  const popup = page.waitForEvent('popup');
  await dialog.getByRole('button', { name: '在新标签页打开' }).click();
  const tab = await popup;
  await tab.waitForLoadState('domcontentloaded');
  return tab;
};

test('字典弹窗：查词后在新标签页打开 /dictionary/:word', async ({ page }) => {
  const log = collect(page);
  await page.goto('workspace', { waitUntil: 'networkidle' });
  const before = page.url();

  const dialog = await openTool(page, '字典');
  // 主页面不跳转
  expect(page.url()).toBe(before);
  const input = dialog.getByRole('combobox', { name: 'search here' });
  await input.fill('buddha');
  // 候选词还在加载时回车不查（字典页同样如此），等候选出来
  await page.waitForTimeout(1500);
  await input.press('Enter');

  const tab = await openInNewTab(page, dialog);
  expect(new URL(tab.url()).pathname).toBe('/pcd-v2026/dictionary/buddha');
  await tab.close();
  log.dump('字典弹窗');
});

test('语法手册弹窗：检索与选中的术语带进新标签页 /grammar-book', async ({ page }) => {
  await page.goto('workspace', { waitUntil: 'networkidle' });
  const dialog = await openTool(page, '语法手册');

  // 只检索：新标签页恢复检索词与结果列表
  await dialog.getByPlaceholder('input search text').fill('a');
  const first = dialog.locator('.ant-list-item').first();
  await expect(first).toBeVisible();
  const title = (await first.locator('.ant-list-item-meta-title').innerText()).trim();
  let tab = await openInNewTab(page, dialog);
  expect(new URL(tab.url()).pathname).toBe('/pcd-v2026/grammar-book');
  expect(new URL(tab.url()).searchParams.get('q')).toBe('a');
  await expect(tab.getByPlaceholder('input search text')).toHaveValue('a');
  await expect(tab.locator('.ant-list-item-meta-title').first()).toHaveText(title);
  await tab.close();

  // 选中一条：新标签页打开的是这条术语
  await first.click();
  // 术语详情的标题是指向 /term/show/{id} 的链接
  const termLink = dialog.locator('a[href*="/term/show/"]').first();
  await expect(termLink).toBeVisible();
  const termTitle = (await termLink.innerText()).trim();
  tab = await openInNewTab(page, dialog);
  const id = new URL(tab.url()).searchParams.get('id');
  expect(id).toBeTruthy();
  await expect(tab.locator(`a[href*="/term/show/${id}"]`).first()).toHaveText(termTitle);
  console.log(`>>> 语法手册：q=a，选中 ${title} → ${termTitle}（${id}）`);
  await tab.close();
});

for (const [name, path] of [
  ['用户字典', '/pcd-v2026/workspace/dict'],
  ['标签', '/pcd-v2026/workspace/tag'],
  ['网盘', '/pcd-v2026/workspace/driver'],
] as const) {
  test(`${name}弹窗 → ${path}`, async ({ page }) => {
    await page.goto('workspace', { waitUntil: 'networkidle' });
    const before = page.url();
    const dialog = await openTool(page, name);
    expect(page.url()).toBe(before);
    const tab = await openInNewTab(page, dialog);
    expect(new URL(tab.url()).pathname).toBe(path);
    await tab.close();
  });
}
