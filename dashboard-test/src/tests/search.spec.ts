import { test, expect, type Page } from '@playwright/test';
import { collect } from '../lib/collect.js';

/**
 * workspace 搜索（取代 v4 的 /search 页）：主导航「搜索」弹窗 + 整页 /workspace/search。
 * 全文走 /v3/search（category 分面），标题走 /v3/tipitaka-titles?q=；定义走 /v3/tipitaka-word-paragraphs，页码走 /v3/tipitaka-page-numbers（输入页码时全文不查）。
 */
const CHANNEL = '4a356b64-8e6b-414a-ae81-7dfa2d5b35b6';
const READING = `workspace/tipitaka/chapter/83-150?channel=${CHANNEL}`;

const searchResponse = (page: Page, test: (u: URL) => boolean = () => true) =>
  page.waitForResponse((r) => {
    const u = new URL(r.url());
    return u.pathname === '/api/v3/search' && test(u);
  });

const openModal = async (page: Page) => {
  await page.goto(READING, { waitUntil: 'networkidle' });
  await page.getByRole('menuitem', { name: '搜索' }).click();
  const dialog = page.getByRole('dialog');
  await expect(dialog).toBeVisible();
  return dialog;
};

test('主导航打开搜索弹窗：全文与标题同时查询，按层级筛选后分面重算', async ({ page }) => {
  const log = collect(page);
  const dialog = await openModal(page);

  const fts = searchResponse(page);
  const chapters = page.waitForResponse((r) => new URL(r.url()).pathname === '/api/v3/tipitaka-titles');
  await dialog.getByRole('combobox').fill('vitakka');
  await dialog.getByRole('combobox').press('Enter');

  const ftsRes = await fts;
  expect(ftsRes.status()).toBe(200);
  const q = new URL(ftsRes.url()).searchParams;
  expect(q.get('resource_type')).toBe('tipitaka');
  expect(q.get('granularity')).toBe('paragraph');
  expect(q.get('facets')).toBe('true');
  const body = await ftsRes.json();
  expect(body.meta.total).toBeGreaterThan(0);
  expect((await chapters).status()).toBe(200);
  console.log(`>>> 全文 vitakka：${body.meta.total} 段；分类分面 ${body.meta.facets.category.length} 个`);

  // 标签上显示命中数
  await expect(dialog.getByRole('tab', { name: /全文/ })).toContainText(body.meta.total.toLocaleString());
  // 第一条结果的坐标
  await expect(dialog.getByText(body.data[0].related_id, { exact: true }).first()).toBeVisible();

  // 选「义注」→ 带 category 重查，结果全是义注
  const filtered = searchResponse(page, (u) => u.searchParams.get('category') === 'aṭṭhakathā');
  await dialog.getByRole('checkbox', { name: /义注/ }).check();
  const fBody = await (await filtered).json();
  expect(fBody.meta.total).toBeGreaterThan(0);
  expect(fBody.meta.total).toBeLessThan(body.meta.total);
  for (const hit of fBody.data) expect(hit.category).toContain('aṭṭhakathā');
  console.log(`>>> 筛选义注后：${fBody.meta.total} 段`);

  // 同组单选：再选「复注」替换掉「义注」
  const replaced = searchResponse(page, (u) => u.searchParams.get('category') === 'ṭīkā');
  await dialog.getByRole('checkbox', { name: /复注/ }).check();
  await replaced;
  await expect(dialog.getByRole('checkbox', { name: /义注/ })).not.toBeChecked();

  // 跨组叠加：再选「论」→ category=ṭīkā,abhidhamma
  const both = searchResponse(page, (u) => u.searchParams.get('category') === 'ṭīkā,abhidhamma');
  await dialog.getByRole('checkbox', { name: /论/ }).check();
  await both;

  log.dump('全文分面');
});

test('标题标签页按层级筛选；结果链接带上当前 channel，在新标签页打开、弹窗留着', async ({ page }) => {
  const dialog = await openModal(page);
  await dialog.getByRole('combobox').fill('vitakka');
  await dialog.getByRole('combobox').press('Enter');
  await dialog.getByRole('tab', { name: /标题/ }).click();

  const mula = page.waitForResponse(
    (r) => new URL(r.url()).pathname === '/api/v3/tipitaka-titles' && new URL(r.url()).searchParams.get('layer') === 'mula',
  );
  await dialog.getByTitle('根本', { exact: true }).click();
  const body = await (await mula).json();
  expect(body.data.length).toBeGreaterThan(0);
  for (const c of body.data) expect(c.layer).toBe('mula');

  const first = body.data[0];
  // 浮动容器里的链接一律在新标签页打开
  const opened = page.context().waitForEvent('page');
  await dialog.getByRole('link', { name: first.title }).first().click();
  const tab = await opened;
  await expect(tab).toHaveURL(new RegExp(`workspace/tipitaka/chapter/${first.book}-${first.para}\\?channel=${CHANNEL}`));
  await tab.close();
  await expect(dialog).toBeVisible();
  await expect(page).toHaveURL(/workspace\/tipitaka\/chapter\/83-150/);
});

test('在新标签页打开整页搜索：条件进地址栏，刷新后仍在', async ({ page }) => {
  const dialog = await openModal(page);
  await dialog.getByRole('combobox').fill('vitakka');
  await dialog.getByRole('combobox').press('Enter');
  const filtered = searchResponse(page, (u) => u.searchParams.get('category') === 'mūla');
  await dialog.getByRole('checkbox', { name: /根本/ }).check();
  await filtered;

  const opened = page.context().waitForEvent('page');
  await dialog.getByRole('button', { name: 'export' }).click();
  const full = await opened;
  await expect(full).toHaveURL(/\/search\?q=vitakka&category=m%C5%ABla/);
  // 整页是顶层的 /search，不在 workspace 下；原页面的弹窗还在
  expect(new URL(full.url()).pathname).toMatch(/^\/[^/]+\/search$/);
  await expect(dialog).toBeVisible();

  const again = searchResponse(full, (u) => u.searchParams.get('category') === 'mūla');
  await full.reload();
  await again;
  await expect(full.getByRole('checkbox', { name: /根本/ })).toBeChecked();
  await expect(full.getByRole('combobox')).toHaveValue('vitakka');
});

test('输入数字自动切到页码标签页，全文不查', async ({ page }) => {
  const dialog = await openModal(page);
  const fts: string[] = [];
  page.on('request', (r) => new URL(r.url()).pathname === '/api/v3/search' && fts.push(r.url()));
  await dialog.getByRole('combobox').fill('2.100');
  await dialog.getByRole('combobox').press('Enter');
  await expect(dialog.getByRole('tab', { name: /页码/ })).toHaveAttribute('aria-selected', 'true');
  await expect(dialog.getByRole('tab', { name: /全文/ })).toHaveText('全文');
  await dialog.getByRole('tab', { name: /全文/ }).click();
  await expect(dialog.getByText('这是页码，全文不检索数字')).toBeVisible();
  expect(fts).toEqual([]);
});

const wordParagraphs = (page: Page, test: (u: URL) => boolean = () => true) =>
  page.waitForResponse((r) => {
    const u = new URL(r.url());
    return u.pathname === '/api/v3/tipitaka-word-paragraphs' && test(u);
  });

test('定义标签页：展开词形 → 只看黑体检索，可按书与黑体范围筛选，不再请求 v2', async ({ page }) => {
  const log = collect(page);
  const v2: string[] = [];
  page.on('request', (r) => /\/api\/v2\/(case|search-pali-wbw)/.test(r.url()) && v2.push(r.url()));
  const dialog = await openModal(page);

  const forms = page.waitForResponse((r) => r.url().includes('/api/v3/pali-word-forms/vitakka'));
  const paras = wordParagraphs(page, (u) => u.searchParams.get('bold') === 'only');
  const books = page.waitForResponse((r) => new URL(r.url()).pathname === '/api/v3/tipitaka-word-paragraphs/aggregate');
  await dialog.getByRole('combobox').fill('vitakka');
  await dialog.getByRole('combobox').press('Enter');

  const formBody = await (await forms).json();
  const defaultForms = formBody.data[0].forms.map((f: { word: string }) => f.word);
  const parasRes = await paras;
  expect(new URL(parasRes.url()).searchParams.get('words')?.split(',')).toEqual(defaultForms);
  const parasBody = await parasRes.json();
  expect(parasBody.meta.total).toBeGreaterThan(0);
  const booksBody = await (await books).json();
  console.log(`>>> 定义 vitakka：${defaultForms.length} 个词形，只看黑体 ${parasBody.meta.total} 段，分布在 ${booksBody.meta.total} 本书`);

  await dialog.getByRole('tab', { name: /定义/ }).click();
  await expect(dialog.getByRole('tab', { name: /定义/ })).toContainText(parasBody.meta.total.toLocaleString());
  // 正文里黑体与命中词都标出来了
  await expect(dialog.locator('.search-definition-text mark').first()).toBeVisible();
  await expect(dialog.getByText(parasBody.data[0].id, { exact: true }).first()).toBeVisible();

  // 勾选一本书 → 带 book_id 重查
  const first = booksBody.data[0];
  const byBook = wordParagraphs(page, (u) => u.searchParams.get('book_id') === String(first.book_id));
  await dialog.getByRole('checkbox', { name: new RegExp(`^${first.title.replace(/[()]/g, '\\$&')}`) }).check();
  const byBookBody = await (await byBook).json();
  expect(byBookBody.meta.total).toBe(first.paragraphs);

  // 改成「全部出现」→ 不带 bold，命中更多
  const all = wordParagraphs(page, (u) => !u.searchParams.has('bold') && !u.searchParams.has('book_id'));
  await dialog.getByText('全部出现', { exact: true }).click();
  const allBody = await (await all).json();
  expect(allBody.meta.total).toBeGreaterThan(parasBody.meta.total);

  // 取消一个词形 → words 少一个
  const fewer = wordParagraphs(page, (u) => u.searchParams.get('words')?.split(',').length === defaultForms.length - 1);
  await dialog.getByRole('checkbox', { name: new RegExp(`^${defaultForms[0]}[\\s\\d,]*$`) }).first().uncheck();
  await fewer;

  expect(v2).toEqual([]);
  log.dump('定义标签页');
});

test('定义标签页：多个词提示改用全文', async ({ page }) => {
  const dialog = await openModal(page);
  await dialog.getByRole('combobox').fill('vitakka vicāra');
  await dialog.getByRole('combobox').press('Enter');
  await dialog.getByRole('tab', { name: /定义/ }).click();
  await expect(dialog.getByText('定义检索只支持单个词')).toBeVisible();
});

test('定义标签页：目录树按书的 tag 限定范围，条件进地址栏', async ({ page }) => {
  const books = page.waitForResponse((r) => new URL(r.url()).pathname === '/api/v3/tipitaka-word-paragraphs/aggregate');
  await page.goto('search?q=vitakka&tab=definition', { waitUntil: 'networkidle' });
  const booksBody = await books.then((r) => r.json());
  type Book = { book_id: number; tags: string[]; paragraphs: number };
  const sutta = (booksBody.data as Book[]).filter((b) => b.tags.includes('sutta'));
  const expected = sutta.reduce((n, b) => n + b.paragraphs, 0);

  // 点「suttapiṭaka」→ 带上经藏全部命中书的 book_id 重查，总数等于这些书的段落数之和
  const scoped = wordParagraphs(page, (u) => (u.searchParams.get('book_id') ?? '').split(',').length === sutta.length);
  await page.getByText('suttapiṭaka', { exact: true }).first().click();
  const scopedBody = await (await scoped).json();
  expect(scopedBody.meta.total).toBe(expected);
  await expect(page).toHaveURL(/scope=sutta/);
  console.log(`>>> 定义 vitakka 只看黑体，限经藏：${sutta.length} 本书 ${expected} 段`);

  // 刷新后范围还在
  const again = wordParagraphs(page, (u) => u.searchParams.has('book_id'));
  await page.reload();
  expect((await (await again).json()).meta.total).toBe(expected);

  // 清除选择 → 不再限定书
  const cleared = wordParagraphs(page, (u) => !u.searchParams.has('book_id'));
  await page.getByRole('button', { name: '清除选择' }).click();
  await cleared;
});

const pageNumbers = (page: Page, test: (u: URL) => boolean = () => true) =>
  page.waitForResponse((r) => {
    const u = new URL(r.url());
    return u.pathname.startsWith('/api/v3/tipitaka-page-numbers/') && test(u);
  });

test('页码标签页：输入页码自动切换、按版本分组，点进去在新标签页打开印本页阅读页', async ({ page }) => {
  const log = collect(page);
  const v2: string[] = [];
  page.on('request', (r) => r.url().includes('/api/v2/search-page-number') && v2.push(r.url()));
  const dialog = await openModal(page);

  const res = pageNumbers(page, (u) => u.pathname.endsWith('/100') && !u.searchParams.has('volume'));
  await dialog.getByRole('combobox').fill('100');
  await dialog.getByRole('combobox').press('Enter');
  const body = await (await res).json();
  expect(body.meta.total).toBeGreaterThan(0);
  await expect(dialog.getByRole('tab', { name: /页码/ })).toHaveAttribute('aria-selected', 'true');
  await expect(dialog.getByRole('tab', { name: /页码/ })).toContainText(body.meta.total.toLocaleString());
  const pts = (body.data as { edition: string }[]).filter((h) => h.edition === 'PTS').length;
  console.log(`>>> 页码 100：共 ${body.meta.total} 处，PTS ${pts} 处`);

  // 默认 PTS；点一条 → 新标签页打开印本页阅读页，能取到这一页
  const opened = page.context().waitForEvent('page');
  await dialog.locator('a[href*="/workspace/tipitaka/page/P_"]').first().click();
  const tab = await opened;
  const nav = await tab.waitForResponse((r) => r.url().includes('/api/v2/nav-page/'));
  expect(nav.status()).toBe(200);
  expect((await nav.json()).ok).toBe(true);
  await tab.close();
  await expect(dialog).toBeVisible();

  // 切到 CS 段落号 → 链接指向 cs-para 阅读页
  await dialog.getByText('CS 段落号').click();
  const csOpened = page.context().waitForEvent('page');
  await dialog.locator('a[href*="/workspace/tipitaka/cs-para/"]').first().click();
  const csTab = await csOpened;
  const csNav = await csTab.waitForResponse((r) => r.url().includes('/api/v2/nav-cs-para/'));
  expect((await csNav.json()).ok).toBe(true);
  await csTab.close();

  expect(v2).toEqual([]);
  log.dump('页码标签页');
});

test('页码标签页：「卷.页」只查这一卷；非页码时给出提示', async ({ page }) => {
  const res = pageNumbers(page, (u) => u.pathname.endsWith('/100') && u.searchParams.get('volume') === '2');
  await page.goto('search?q=2.100&tab=page', { waitUntil: 'networkidle' });
  const body = await (await res).json();
  for (const h of body.data) expect(h.volume).toBe(2);
  await expect(page.getByText(`第 2 卷第 100 页共 ${body.meta.total} 处`)).toBeVisible();

  await page.goto('search?q=vitakka&tab=page', { waitUntil: 'networkidle' });
  await expect(page.getByText('输入页码数字')).toBeVisible();
});

test('页码标签页：目录树按书的 tag 过滤，版本计数跟着变，条件进地址栏', async ({ page }) => {
  const res = pageNumbers(page, (u) => u.pathname.endsWith('/100'));
  await page.goto('search?q=100&tab=page', { waitUntil: 'networkidle' });
  await expect(page).toHaveTitle('100 - 页码 - 搜索');
  type Hit = { edition: string; tags: string[]; book_title: string };
  const hits = (await (await res).json()).data as Hit[];
  const pts = hits.filter((h) => h.edition === 'PTS');
  const ptsSutta = pts.filter((h) => h.tags.includes('sutta'));
  expect(ptsSutta.length).toBeGreaterThan(0);
  expect(ptsSutta.length).toBeLessThan(pts.length);
  const ptsOption = page.locator('.ant-segmented-item').filter({ hasText: 'PTS' });
  await expect(ptsOption).toContainText(String(pts.length));

  // 点「suttapiṭaka」→ 只剩经藏的书，不重新请求
  let refetched = false;
  page.on('request', (r) => r.url().includes('/api/v3/tipitaka-page-numbers') && (refetched = true));
  await page.getByText('suttapiṭaka', { exact: true }).first().click();
  await expect(page).toHaveURL(/page_scope=sutta/);
  await expect(ptsOption).toContainText(String(ptsSutta.length));
  const outside = pts.find((h) => !h.tags.includes('sutta'))!;
  await expect(page.getByText(outside.book_title, { exact: true })).toHaveCount(0);
  expect(refetched).toBe(false);
  console.log(`>>> 页码 100 PTS：${pts.length} 处，限经藏 ${ptsSutta.length} 处`);

  // 刷新后范围还在
  await page.reload({ waitUntil: 'networkidle' });
  await expect(ptsOption).toContainText(String(ptsSutta.length));
  expect(refetched).toBe(true); // 刷新才会重新请求
});

test('目录树默认单选；多选时选了子目录父目录取消、选了父目录子目录取消', async ({ page }) => {
  await page.goto('search?q=100&tab=page', { waitUntil: 'networkidle' });
  const scope = () => new URL(page.url()).searchParams.get('page_scope');
  // 节点按 title 属性（巴利原名）找：显示的文字会按界面语言译成「律藏」「长部」
  const node = (selector: string) => page.locator(`.ant-tree-node-content-wrapper${selector}`).first();

  // 单选：点了别的就换成别的
  await node('[title^="suttapi"]').click();
  await expect.poll(scope).toBe('sutta');
  await node('[title^="vinayapi"]').click();
  await expect.poll(scope).toBe('vinaya');

  // 打开多选：经藏与律藏可以并存；再选经藏下的长部，经藏取消
  await page.getByRole('switch').click();
  await node('[title^="suttapi"]').click();
  await expect.poll(scope).toBe('vinaya;sutta');
  await node('[title^="d"][title*="ghanik"]').click();
  await expect.poll(scope).toBe('vinaya;sutta,dīghanikāya');
  // 再选回经藏，长部取消
  await node('[title^="suttapi"]').click();
  await expect.poll(scope).toBe('vinaya;sutta');
  // 地址栏先变、树后渲染：等树上的选中状态跟上再切换
  await expect(node('[title^="d"][title*="ghanik"]')).not.toHaveClass(/ant-tree-node-selected/);
  await expect(node('[title^="suttapi"]')).toHaveClass(/ant-tree-node-selected/);

  // 切回单选只留最后选的
  await page.getByRole('switch').click();
  await expect.poll(scope).toBe('sutta');
});

test('搜索框补全巴利词：不打变音也能补全，回车搜原文，选候选替换最后一个词并搜索', async ({ page }) => {
  const v2: string[] = [];
  page.on('request', (r) => r.url().includes('/api/v2/pali-word-index') && v2.push(r.url()));
  const dialog = await openModal(page);
  const box = dialog.getByRole('combobox');

  const suggest = page.waitForResponse((r) => {
    const u = new URL(r.url());
    return u.pathname === '/api/v3/pali-word-suggestions' && u.searchParams.get('q') === 'dhamma';
  });
  await box.fill('dhamma');
  const body = await (await suggest).json();
  const words = (body.data as { word: string }[]).map((w) => w.word);
  expect(words.length).toBeGreaterThan(1);
  const options = page.locator('.ant-select-dropdown:visible .ant-select-item-option');
  await expect(options.first()).toContainText(words[0]);
  console.log(`>>> 补全 dhamma：${words.slice(0, 5).join(', ')}`);

  // 直接回车：搜的是输入的原文，不是第一个候选
  const plain = searchResponse(page, (u) => u.searchParams.get('q') === 'dhamma');
  await box.press('Enter');
  await plain;
  await expect(options).toHaveCount(0); // 搜索之后候选收起，不挡标签页

  // 多个词时补全最后一个；方向键选第二个候选回车 → 替换最后一个词并搜索
  const second = page.waitForResponse((r) => new URL(r.url()).searchParams.get('q') === 'vitakk');
  await box.fill('sati vitakk');
  const secondBody = await (await second).json();
  const pick = (secondBody.data as { word: string }[])[1].word;
  await expect(options.nth(1)).toContainText(pick);
  const picked = searchResponse(page, (u) => u.searchParams.get('q') === `sati ${pick}`);
  const queries: string[] = [];
  page.on('request', (r) => {
    const u = new URL(r.url());
    if (u.pathname === '/api/v3/search') queries.push(u.searchParams.get('q') ?? '');
  });
  await box.press('ArrowDown');
  await box.press('ArrowDown');
  await box.press('Enter');
  await picked;
  await page.waitForTimeout(500);
  await expect(box).toHaveValue(`sati ${pick}`);
  expect(queries).toEqual([`sati ${pick}`]); // 回车只搜选中的候选，不再拿原文多搜一次

  // 页码不补全
  let pageSuggest = false;
  page.on('request', (r) => r.url().includes('/api/v3/pali-word-suggestions?q=100') && (pageSuggest = true));
  await box.fill('100');
  await page.waitForTimeout(600);
  expect(pageSuggest).toBe(false);
  expect(v2).toEqual([]);
});
