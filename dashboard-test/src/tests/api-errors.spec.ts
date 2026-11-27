import { test, expect, type Page, type Route } from '@playwright/test';
import { call } from '../lib/api.js';
import { CHAPTER, CHAPTER_CHANNEL, DISCUSSION_SENTENCE, shotDir } from '../lib/env.js';

/**
 * v3 请求失败的统一处理（dashboard-v6 src/api/client.ts）：超时、网络不通、网关 504 都变成 ApiError；
 * 页面级的加载失败显示错误页（ApiErrorResult / RouteError），不渲染表单、空列表或骨架屏，并可「重试」。
 *
 * 用 page.route 拦截真实请求制造失败；「重试」前关掉拦截，验证能恢复。
 * 只读：用测试账号名下已有的 channel / 术语 / 团队，不写库。
 */
const SHOT = shotDir('api-errors');
const shot = (page: Page, name: string) =>
  page.screenshot({ path: `${SHOT}/${name}.png`, fullPage: true });

const NETWORK = '无法连接服务器，请检查网络';
const TIMEOUT = '请求超时，服务器没有在规定时间内响应';
const refused = (route: Route) => route.abort('connectionrefused');

const retryButton = (page: Page) => page.getByRole('button', { name: /^重\s*试$/ });

/** 错误页（antd Result）里的标题；全局提示里的同一句话不算 */
const errorPage = (scope: Page | ReturnType<Page['locator']>, text: string) =>
  scope.locator('.ant-result-title').filter({ hasText: text });

/**
 * 拦截路径满足 `match` 的请求：跳过前 `skip` 次，之后交给 `fail`；`off()` 之后全部放行。
 * 开发模式 StrictMode 下 effect / 首次请求可能发两次，所以是「从第几次起」而不是「第几次」。
 */
const intercept = async (
  page: Page,
  match: (path: string) => boolean,
  fail: (route: Route) => Promise<void> | void,
  { skip = 0, enabled = true } = {},
) => {
  let count = 0;
  let on = enabled;
  await page.route(
    (url) => match(url.pathname),
    async (route) => {
      count += 1;
      if (on && count > skip) {
        await fail(route);
        return;
      }
      await route.continue();
    },
  );
  return {
    on: () => {
      on = true;
    },
    off: () => {
      on = false;
    },
  };
};

/**
 * 错误页已经说明了原因，unwrap() 为同一错误弹的提示应当被收掉。
 * antd 提示 3 秒后才自己消失，所以 1.5 秒内看不到才算是被收掉的。
 */
const expectNoToast = (page: Page) =>
  expect(page.locator('.ant-message-notice')).toHaveCount(0, { timeout: 1500 });

/** 表单任何字段都不该出现 */
const expectNoForm = async (page: Page) => {
  await expectNoToast(page);
  await expect(page.locator('.ant-form-item')).toHaveCount(0);
  await expect(page.getByRole('button', { name: /^保\s*存|^提\s*交/ })).toHaveCount(0);
};

/** 不该出现空列表占位（「暂无数据」）或表格 */
const expectNoList = async (page: Page) => {
  await expectNoToast(page);
  await expect(page.locator('.ant-empty')).toHaveCount(0);
  await expect(page.locator('.ant-table, .ant-pro-list .ant-list')).toHaveCount(0);
};

let me = '';
let termId = '';
let channelId = '';
let groupId = '';

test.beforeAll(async ({ request }) => {
  me = (await call(request, 'GET', '/v3/me')).body.data.id;
  termId = (await call(request, 'GET', `/v3/studios/${me}/terms?per_page=1`)).body.data?.[0]?.id ?? '';
  channelId =
    (await call(request, 'GET', `/v3/studios/${me}/channels?relation=own&per_page=1`)).body.data?.[0]?.id ?? '';
  const groups = (await call(request, 'GET', '/v3/me/groups?per_page=1')).body.data ?? [];
  groupId = groups[0]?.group?.id ?? groups[0]?.id ?? '';
});

test.describe('术语编辑页（TermEdit + 路由 loader）', () => {
  // 页面先由路由 loader 取一次（第 1 次），再由 TermEdit 取（第 2、3 次：StrictMode 跑两遍）
  const term = () => (p: string) => p === `/api/v3/terms/${termId}`;
  test.beforeEach(() => test.skip(!termId, '测试账号名下没有术语'));

  test('网络不通：错误页、无表单、不弹提示；重试后恢复', async ({ page }) => {
    const ctl = await intercept(page, term(), refused, { skip: 1 });
    await page.goto(`workspace/term/${termId}/edit`);
    await expect(errorPage(page, NETWORK)).toBeVisible();
    await expectNoForm(page);
    await shot(page, '01-term-edit-network');

    ctl.off();
    await retryButton(page).click();
    await expect(page.getByRole('button', { name: /^保\s*存/ })).toBeVisible();
    await expect(page.locator('.ant-form-item:not(.ant-form-item-hidden)').first()).toBeVisible();
    await shot(page, '01b-term-edit-recovered');
  });

  test('网关 504（HTML 响应体）：按超时显示', async ({ page }) => {
    await intercept(
      page,
      term(),
      (route) => route.fulfill({ status: 504, contentType: 'text/html', body: '<html>504 Gateway Time-out</html>' }),
      { skip: 1 },
    );
    await page.goto(`workspace/term/${termId}/edit`);
    await expect(errorPage(page, TIMEOUT)).toBeVisible();
    await expectNoForm(page);
    await shot(page, '02-term-edit-gateway-504');
  });

  test('服务器一直不响应：等待期间只转圈，30 秒后显示超时', async ({ page }) => {
    test.setTimeout(90_000);
    // 不 fulfill、不 continue：请求一直挂着，等前端自己的超时
    await intercept(page, term(), () => {}, { skip: 1 });
    await page.goto(`workspace/term/${termId}/edit`);
    await expect(page.locator('.ant-spin')).toBeVisible();
    await expectNoForm(page);
    await shot(page, '03a-term-edit-waiting');
    await expect(errorPage(page, TIMEOUT)).toBeVisible({ timeout: 45_000 });
    await expectNoForm(page);
    await shot(page, '03b-term-edit-timeout');
  });

  test('路由 loader 失败：路由错误页；重试后恢复', async ({ page }) => {
    const ctl = await intercept(page, term(), refused);
    await page.goto(`workspace/term/${termId}/edit`);
    await expect(errorPage(page, NETWORK)).toBeVisible();
    await expect(page.getByRole('button', { name: /^返\s*回$/ })).toBeVisible();
    await expectNoForm(page);
    await shot(page, '04-term-loader-network');

    ctl.off();
    await retryButton(page).click();
    await expect(page.getByRole('button', { name: /^保\s*存/ })).toBeVisible();
  });
});

test.describe('channel', () => {
  test.beforeEach(() => test.skip(!channelId, '测试账号名下没有 channel'));
  const one = () => (p: string) => p === `/api/v3/channels/${channelId}`;

  test('channel 设置页（Edit）：取不到 channel 显示错误页；重试后恢复', async ({ page }) => {
    // 第 1 次是路由 loader，之后是设置表单
    const ctl = await intercept(page, one(), refused, { skip: 1 });
    await page.goto(`workspace/channel/${channelId}/setting`);
    await expect(errorPage(page, NETWORK)).toBeVisible();
    await expectNoForm(page);
    await shot(page, '05-channel-setting-network');

    ctl.off();
    await retryButton(page).click();
    await expect(page.locator('.ant-form-item').first()).toBeVisible();
  });

  test('channel 路由 loader：超时等失败不再说成「不存在」', async ({ page }) => {
    const ctl = await intercept(page, one(), refused);
    await page.goto(`workspace/channel/${channelId}`);
    await expect(errorPage(page, NETWORK)).toBeVisible();
    await expect(page.getByText('要找的内容不存在')).toHaveCount(0);
    await shot(page, '06-channel-loader-network');

    ctl.off();
    await retryButton(page).click();
    await expect(errorPage(page, NETWORK)).toHaveCount(0);
  });

  test('channel 关联页（ChannelLinks）：不再停在骨架屏', async ({ page }) => {
    const ctl = await intercept(page, (p) => p === `/api/v3/channels/${channelId}/links`, refused);
    await page.goto(`workspace/channel/${channelId}/setting/links`);
    await expect(errorPage(page, NETWORK)).toBeVisible();
    await expect(page.locator('.ant-skeleton')).toHaveCount(0);
    await shot(page, '07-channel-links-network');

    ctl.off();
    await retryButton(page).click();
    await expect(errorPage(page, NETWORK)).toHaveCount(0);
  });

  test('channel 列表（ChannelTable）：错误页代替空表格', async ({ page }) => {
    const ctl = await intercept(page, (p) => p === `/api/v3/studios/${me}/channels`, refused);
    await page.goto('workspace/channel');
    await expect(errorPage(page, NETWORK)).toBeVisible();
    await expectNoList(page);
    await shot(page, '08-channel-list-network');

    ctl.off();
    await retryButton(page).click();
    await expect(page.locator('.ant-table')).toBeVisible();
  });
});

test.describe('术语列表与团队', () => {
  test('术语列表（TermList）：错误页代替空表格', async ({ page }) => {
    const ctl = await intercept(page, (p) => p === `/api/v3/studios/${me}/terms`, refused);
    await page.goto('workspace/term');
    await expect(errorPage(page, NETWORK)).toBeVisible();
    await expectNoList(page);
    await shot(page, '09-term-list-network');

    ctl.off();
    await retryButton(page).click();
    await expect(page.locator('.ant-table')).toBeVisible();
  });

  test('团队列表（GroupList）：错误页代替空表格', async ({ page }) => {
    const ctl = await intercept(page, (p) => p === '/api/v3/me/groups', refused);
    await page.goto('workspace/team');
    await expect(errorPage(page, NETWORK)).toBeVisible();
    await expectNoList(page);
    await shot(page, '10-team-list-network');

    ctl.off();
    await retryButton(page).first().click();
    await expect(page.locator('.ant-table')).toBeVisible();
  });

  test('团队详情（GroupFile、GroupMember）：两块各自显示错误页', async ({ page }) => {
    test.skip(!groupId, '测试账号不在任何团队里');
    const ctl = await intercept(
      page,
      (p) => p === `/api/v3/groups/${groupId}/shares` || p === `/api/v3/groups/${groupId}/members`,
      refused,
    );
    await page.goto(`workspace/team/${groupId}`);
    await expect(errorPage(page, NETWORK)).toHaveCount(2);
    await expectNoList(page);
    await shot(page, '11-team-show-network');

    ctl.off();
    await retryButton(page).first().click();
    await retryButton(page).first().click();
    await expect(errorPage(page, NETWORK)).toHaveCount(0);
  });

  test('团队设置（GroupEdit）：错误页代替空表单', async ({ page }) => {
    test.skip(!groupId, '测试账号不在任何团队里');
    // 第 1 次是路由 loader
    const ctl = await intercept(page, (p) => p === `/api/v3/groups/${groupId}`, refused, { skip: 1 });
    await page.goto(`workspace/team/${groupId}/setting`);
    await expect(errorPage(page, NETWORK)).toBeVisible();
    await expectNoForm(page);
    await shot(page, '12-team-setting-network');

    ctl.off();
    await retryButton(page).click();
    await expect(page.locator('.ant-form-item').first()).toBeVisible();
  });

  test('团队路由 loader：失败显示原因，可重试', async ({ page }) => {
    test.skip(!groupId, '测试账号不在任何团队里');
    const ctl = await intercept(page, (p) => p === `/api/v3/groups/${groupId}`, refused);
    await page.goto(`workspace/team/${groupId}`);
    await expect(errorPage(page, NETWORK)).toBeVisible();
    await expect(page.getByRole('button', { name: /^返\s*回$/ })).toBeVisible();
    await shot(page, '13-team-loader-network');

    ctl.off();
    await retryButton(page).click();
    await expect(errorPage(page, NETWORK)).toHaveCount(0);
  });
});

test.describe('讨论', () => {
  test('句子的讨论列表（DiscussionListCard）：错误页代替空列表', async ({ page }) => {
    const ctl = await intercept(page, (p) => p === '/api/v3/discussions', refused);
    await page.goto(`workspace/discussion/show/sentence/${DISCUSSION_SENTENCE}`);
    await expect(errorPage(page, NETWORK)).toBeVisible();
    await expectNoList(page);
    await shot(page, '14-discussion-list-network');

    ctl.off();
    await retryButton(page).click();
    await expect(errorPage(page, NETWORK)).toHaveCount(0);
    await expect(page.locator('.ant-pro-list').first()).toBeVisible();
  });

  test('章节讨论面板（ChapterDiscussionList）：错误页代替空列表', async ({ page }) => {
    const ctl = await intercept(page, (p) => p.startsWith('/api/v3/tipitaka-discussions/'), refused);
    await page.goto(`workspace/tipitaka/chapter/${CHAPTER}?channel=${CHAPTER_CHANNEL}`, { waitUntil: 'networkidle' });
    await page.getByRole('button', { name: '讨论' }).first().click();
    await expect(errorPage(page, NETWORK)).toBeVisible();
    await shot(page, '15-chapter-discussion-network');

    ctl.off();
    await retryButton(page).click();
    await expect(errorPage(page, NETWORK)).toHaveCount(0);
    await expect(page.locator('.ant-pro-list').first()).toBeVisible();
  });
});

test.describe('编辑器版本面板', () => {
  const chapterUrl = `workspace/tipitaka/chapter/${CHAPTER}?channel=${CHAPTER_CHANNEL}`;
  const progress = (p: string) => p === '/api/v3/channels/batch-progress';

  test('版本面板（ChannelMy）：错误页代替空树', async ({ page }) => {
    const ctl = await intercept(page, progress, refused);
    await page.goto(chapterUrl, { waitUntil: 'networkidle' });
    await page.getByRole('button', { name: '版本' }).first().click();
    await expect(errorPage(page, NETWORK)).toBeVisible();
    // 页面上还有章节目录树；版本面板是错误页所在的那张卡片
    const panel = page.locator('.ant-card').filter({ has: page.locator('.ant-result') }).last();
    await expect(panel.locator('.ant-tree')).toHaveCount(0);
    await expectNoToast(page);
    await shot(page, '16-channel-panel-network');

    ctl.off();
    await retryButton(page).click();
    await expect(errorPage(page, NETWORK)).toHaveCount(0);
    await expect(page.locator('.ant-tree-treenode').filter({ has: page.locator('.ant-dropdown-trigger') }).first()).toBeVisible();
  });

  test('「复制到」的版本选择表（ChannelPickerTable）：错误页代替空列表', async ({ page }) => {
    const ctl = await intercept(page, progress, refused, { enabled: false });
    await page.goto(chapterUrl, { waitUntil: 'networkidle' });
    await page.getByRole('button', { name: '版本' }).first().click();
    const node = page.locator('.ant-tree-treenode').filter({ has: page.locator('.ant-dropdown-trigger') }).first();
    await expect(node).toBeVisible();

    ctl.on();
    await node.locator('.ant-dropdown-trigger').click();
    await page.locator('.ant-dropdown:visible .ant-dropdown-menu-item').filter({ hasText: '复制到' }).click();
    const modal = page.locator('.ant-modal:visible');
    await expect(errorPage(modal, NETWORK)).toBeVisible();
    await expect(modal.locator('.ant-empty')).toHaveCount(0);
    await shot(page, '17-channel-picker-network');

    ctl.off();
    await modal.getByRole('button', { name: /^重\s*试$/ }).click();
    await expect(errorPage(modal, NETWORK)).toHaveCount(0);
    await expect(modal.locator('.ant-pro-list').first()).toBeVisible();
  });
});
