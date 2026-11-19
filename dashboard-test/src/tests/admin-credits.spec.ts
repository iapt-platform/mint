import { test, expect, type Browser, type Page } from '@playwright/test';
import { collect } from '../lib/collect.js';
import { authFile } from '../lib/api.js';
import { BASE, DESTRUCTIVE, HAS_ADMIN, USER, shotDir } from '../lib/env.js';

/**
 * 管理后台 · AI 积分：/admin/credits
 *
 * 后端是 v3：admin/credit-overview、credit-accounts、credit-ledger、credit-adjustments、credit-settlements。
 * 管理员账号由 E2E_ADMIN_USER / E2E_ADMIN_PASS 配置（没配就跳过管理员用例）；E2E_USER 是普通用户，
 * 用来验证 403 页面，也是被加减积分的那个人。
 *
 * 写操作全是真实后端，且不留净额：
 * - 结算可重复执行（同一用户同一天的积分覆盖，不累加）；
 * - 给 E2E_USER 先加 100 再扣 100，备注 `e2e-tmp-<时间戳>`，流水留着看（流水本来就不能删）。
 *
 * 截图输出目录由 E2E_SHOT 指定，默认 testlog/assets/admin-credits_latest。
 */
const SHOT = shotDir('admin-credits');
const shot = (page: Page, name: string) => page.screenshot({ path: `${SHOT}/${name}.png`, fullPage: true });

async function openAdmin(browser: Browser) {
  const ctx = await browser.newContext({ baseURL: BASE, storageState: authFile('admin') });
  const page = await ctx.newPage();
  const log = collect(page);
  await page.goto('admin/credits');
  await expect(page.getByRole('heading', { name: 'AI 积分' })).toBeVisible();
  return { ctx, page, log };
}

/** 用户选择框（UserSelect，按用户名 / 昵称搜索）里搜 E2E_USER 并选第一项 */
async function pickUser(page: Page, dialog: ReturnType<Page['getByRole']>) {
  await dialog.getByLabel('用户').fill(USER);
  const option = page.locator('.ant-select-dropdown:visible .ant-select-item-option').first();
  await expect(option).toBeVisible();
  await option.click();
}

/** antd message 弹出的提示 */
const toast = (page: Page, text: string | RegExp) => page.locator('.ant-message').getByText(text);

test('普通用户打开 /admin/credits 看到 403', async ({ page }) => {
  const log = collect(page);
  await page.goto('admin/credits');
  await expect(page.getByText('只有管理员可以访问这个页面。')).toBeVisible();
  await shot(page, '01-forbidden');
  log.dump('普通用户 403');
});

test.describe('管理员', () => {
  test.skip(!HAS_ADMIN, '未配置 E2E_ADMIN_USER / E2E_ADMIN_PASS');

  test('概览：合计、结算面板、最近 14 天', async ({ browser }) => {
    const { ctx, page, log } = await openAdmin(browser);

    // 菜单项高亮
    await expect(page.locator('.ant-menu-item-selected')).toContainText('AI 积分');
    for (const label of ['用户积分余额合计', '累计结算赚取', '累计手工调整', '今日消耗', '有积分的用户', '游客设备']) {
      await expect(page.locator('.ant-statistic-title', { hasText: label })).toBeVisible();
    }
    await expect(page.getByText('每日积分结算')).toBeVisible();
    await expect(page.getByText(/^策略 \d{8}：/)).toBeVisible();
    // 最近 14 天表：14 行，第一行是今天
    const days = page.locator('.ant-table-tbody').first().locator('tr.ant-table-row');
    await expect(days).toHaveCount(14);
    await expect(days.first()).toContainText('今天');
    await shot(page, '02-overview');
    expect(log.all, '控制台与 HTTP 错误').toEqual([]);
    log.dump('概览');
    await ctx.close();
  });

  test('补齐漏结算、重算某一天：可重复执行，最近一次结算记为后台手工', async ({ browser }) => {
    test.skip(!DESTRUCTIVE, 'E2E_DESTRUCTIVE=0');
    const { ctx, page, log } = await openAdmin(browser);

    for (const round of [1, 2]) {
      await page.getByRole('button', { name: '补齐漏结算' }).click();
      await page.locator('.ant-popconfirm').getByRole('button', { name: /确 ?定|OK/ }).click();
      await expect(toast(page, /补结算了 \d+ 位用户的 \d+ 个「用户 \+ 日」/)).toBeVisible();
      if (round === 1) await shot(page, '03-settle-missing');
      // 第二次重复执行：已经全部结清，补 0 个
      if (round === 2) await expect(toast(page, '补结算了 0 位用户的 0 个「用户 + 日」')).toBeVisible();
    }
    await expect(page.getByText(/后台手工（/)).toBeVisible();

    // 重算前天
    const day = new Date(Date.now() - 2 * 86400_000).toISOString().slice(0, 10);
    const picker = page.getByPlaceholder('选一天（UTC）');
    await picker.click();
    await picker.fill(day);
    await picker.press('Enter');
    await page.getByRole('button', { name: '重算这一天' }).click();
    await page.locator('.ant-popconfirm').getByRole('button', { name: /确 ?定|OK/ }).click();
    await expect(toast(page, new RegExp(`${day}：结算了 \\d+ 位用户`))).toBeVisible();
    await shot(page, '04-settle-day');

    expect(log.all, '控制台与 HTTP 错误').toEqual([]);
    log.dump('手工结算');
    await ctx.close();
  });

  test('手工加 100 再扣 100：入账、流水里看得到操作人与备注', async ({ browser }) => {
    test.skip(!DESTRUCTIVE, 'E2E_DESTRUCTIVE=0');
    const { ctx, page, log } = await openAdmin(browser);
    const note = `e2e-tmp-${Date.now()}`;

    for (const delta of [100, -100]) {
      await page.getByRole('button', { name: '手工加减积分' }).click();
      const dialog = page.getByRole('dialog', { name: '手工加减积分' });
      await pickUser(page, dialog);
      await dialog.getByLabel('积分').fill(String(delta));
      if (delta < 0) await expect(dialog.getByText('这是扣减。扣减后余额不能为负。')).toBeVisible();
      await dialog.getByLabel('原因').fill(`${note} ${delta > 0 ? '加' : '扣'}`);
      if (delta > 0) await shot(page, '05-adjust-dialog');
      await dialog.getByRole('button', { name: /确 ?定/ }).click();
      await expect(toast(page, /已入账，当前余额 \d+/)).toBeVisible();
      await expect(dialog).toBeHidden();
    }

    // 全部流水：两条都在，带操作人标签
    await page.getByRole('tab', { name: '全部流水' }).click();
    const ledger = page.getByRole('tabpanel', { name: '全部流水' });
    await expect(ledger.getByText(`${note} 加`)).toBeVisible();
    await expect(ledger.getByText(`${note} 扣`)).toBeVisible();
    await expect(ledger.locator('tr', { hasText: `${note} 加` })).toContainText('+100');
    await expect(ledger.locator('tr', { hasText: `${note} 扣` })).toContainText('-100');
    // 操作人标签（带用户图标）
    await expect(ledger.locator('tr', { hasText: `${note} 加` }).locator('.ant-tag', { has: page.locator('.anticon-user') })).toBeVisible();
    await shot(page, '06-ledger');

    // 账号表：搜 E2E_USER，点开看他的流水抽屉
    await page.getByRole('tab', { name: '账号' }).click();
    await page.getByPlaceholder('用户名 / 昵称 / 邮箱，或设备 ID').fill(USER);
    await page.getByPlaceholder('用户名 / 昵称 / 邮箱，或设备 ID').press('Enter');
    const row = page.getByRole('tabpanel', { name: '账号' }).locator('tr.ant-table-row', { hasText: `@${USER}` });
    await expect(row).toBeVisible();
    await row.click();
    const drawer = page.getByRole('dialog', { name: /的积分流水$/ });
    await expect(drawer).toBeVisible();
    await expect(drawer.getByText(`${note} 扣`)).toBeVisible();
    await shot(page, '07-account-drawer');

    expect(log.all, '控制台与 HTTP 错误').toEqual([]);
    log.dump('手工加减');
    await ctx.close();
  });

  test('扣成负数：表单项下显示后端的字段错误', async ({ browser }) => {
    const { ctx, page, log } = await openAdmin(browser);

    await page.getByRole('button', { name: '手工加减积分' }).click();
    const dialog = page.getByRole('dialog', { name: '手工加减积分' });
    // 用户是搜索选择的，选不到不存在的人；找不到用户的 422 由 Pest / api-test 覆盖
    await pickUser(page, dialog);
    await dialog.getByLabel('积分').fill('-1000000');
    await dialog.getByLabel('原因').fill('e2e-tmp 扣成负数');
    await dialog.getByRole('button', { name: /确 ?定/ }).click();
    await expect(dialog.getByText(/扣减后余额会变成负数（当前余额 \d+）。/)).toBeVisible();
    await shot(page, '08-field-errors');

    // 422 是预期内的
    expect(log.all.filter((l) => !l.includes('[HTTP 422]') && !l.includes('status of 422')), '控制台与 HTTP 错误').toEqual([]);
    log.dump('字段错误');
    await ctx.close();
  });
});
