import { test, expect, type Page } from '@playwright/test';
import { collect } from '../lib/collect.js';

/**
 * /workspace/channel 及其子路由（:channelId、:channelId/setting）
 *
 * 截图输出目录由 E2E_SHOT 指定，默认写到 documents/testlog/assets/channel_latest，
 * 正式记录时传入带时间戳的目录，与 documents/testlog/channel_<时间戳>.md 对应。
 */
const SHOT = process.env.E2E_SHOT ?? 'documents/testlog/assets/channel_latest';
const TAB = '.ant-pro-table-list-toolbar-inline-menu-item';

const shot = (page: Page, name: string) =>
  page.screenshot({ path: `${SHOT}/${name}.png`, fullPage: true });
const rows = (page: Page) => page.locator('.ant-table-tbody tr.ant-table-row').count();
const toast = (page: Page) =>
  page.locator('.ant-message').innerText().catch(() => '(无)');

/* 各 describe 相互独立：一个失败不应阻断其余用例（顺序由 workers:1 保证） */

test.describe('频道列表 /workspace/channel', () => {
  test('列表加载、页签、搜索、筛选、排序、新建表单', async ({ page }) => {
    const log = collect(page);

    await test.step('1. 列表页加载', async () => {
      await page.goto('workspace/channel', { waitUntil: 'networkidle' });
      await page.waitForTimeout(2000);
      console.log('title:', await page.title(), '| rows:', await rows(page));
      await expect(page.locator('.ant-table')).toBeVisible();
      await shot(page, '01-list');
      log.dump('1. 列表页加载');
    });

    await test.step('2. 工具栏页签', async () => {
      const n = await page.locator(TAB).count();
      expect(n).toBeGreaterThan(0);
      for (let i = 0; i < n; i++) {
        const label = (await page.locator(TAB).nth(i).innerText()).trim().replace(/\s+/g, '');
        await page.locator(TAB).nth(i).click();
        await page.waitForTimeout(2500);
        console.log(`\n>>> 页签「${label}」rows: ${await rows(page)}`);
        await shot(page, `02-tab-${i}`);
        log.dump(`2.${i} 页签 ${label}`);
      }
      await page.locator(TAB).nth(0).click();
      await page.waitForTimeout(2000);
      log.reset();
    });

    await test.step('3. 关键词搜索', async () => {
      const kw = page.getByPlaceholder('请输入').first();
      await kw.fill('test');
      await page.keyboard.press('Enter');
      await page.waitForTimeout(2500);
      const hit = await rows(page);
      console.log('\n>>> 搜索 "test" rows:', hit);
      expect(hit).toBeGreaterThan(0);
      await shot(page, '03-search');
      log.dump('3. 关键词搜索');

      await kw.fill('');
      await page.keyboard.press('Enter');
      await page.waitForTimeout(2000);
      console.log('>>> 清空搜索 rows:', await rows(page));
      log.dump('3b. 清空搜索');
    });

    await test.step('4. 类型列筛选', async () => {
      await page.locator('th .ant-table-filter-trigger').nth(1).click();
      await page.waitForTimeout(1200);
      const items = await page
        .locator('.ant-dropdown:visible .ant-dropdown-menu-item')
        .allInnerTexts();
      console.log('\n>>> 类型筛选项:', items.join(' / '));
      expect(items.length).toBeGreaterThan(0);
      await page.keyboard.press('Escape');
      await page.waitForTimeout(600);
      log.dump('4. 类型列筛选');
    });

    await test.step('5. 创建时间排序', async () => {
      await page.locator('th.ant-table-column-has-sorters').first().click();
      await page.waitForTimeout(2500);
      console.log('\n>>> 排序后 rows:', await rows(page));
      log.dump('5. 创建时间排序');
    });

    await test.step('6. 新建频道表单（不提交）', async () => {
      await page.mouse.move(800, 700);
      await page.waitForTimeout(800);
      await page.getByRole('button', { name: /新建|新增/ }).first().click();
      await page.waitForTimeout(2000);
      await shot(page, '06-create');
      const labels = await page.locator('.ant-popover:visible label').allInnerTexts();
      console.log('\n>>> 新建表单字段:', labels.join(' / '));
      expect(labels.length).toBeGreaterThan(0);
      log.dump('6. 新建频道表单');
      await page.keyboard.press('Escape');
      await page.waitForTimeout(1000);
    });
  });
});

test.describe('频道详情 /workspace/channel/:channelId', () => {
  test('详情页、term/chapter 页签、分享弹窗', async ({ page }) => {
    const log = collect(page);

    await page.goto('workspace/channel', { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    log.reset();

    const link = page.locator('.ant-table-tbody tr.ant-table-row button.ant-btn-link').first();
    const name = (await link.innerText()).trim();

    await test.step('7. 打开频道详情', async () => {
      await link.click();
      await page.waitForTimeout(3500);
      console.log(`\n>>> 打开频道「${name}」-> ${page.url()}\n    title: ${await page.title()}`);
      await expect(page).toHaveURL(/\/workspace\/channel\/[^/]+$/);
      await shot(page, '07-show');
      log.dump('7. 频道详情页');
    });

    for (const t of ['term', 'chapter']) {
      await test.step(`8. 详情 tab ${t}`, async () => {
        await page.locator('.ant-tabs-tab', { hasText: new RegExp(`^${t}$`, 'i') }).first().click();
        await page.waitForTimeout(3000);
        await shot(page, `08-${t}`);
        log.dump(`8. 详情 tab ${t}`);
      });
    }

    await test.step('9. 分享弹窗', async () => {
      await page.getByRole('button', { name: /分享|share/i }).first().click();
      await page.waitForTimeout(3000);
      await shot(page, '09-share');
      const n = await page.locator('.ant-modal:visible, .ant-drawer:visible').count();
      console.log('\n>>> 分享弹窗数量:', n);
      expect(n).toBe(1);
      log.dump('9. 分享弹窗');
      await page.keyboard.press('Escape');
      await page.waitForTimeout(1000);
    });
  });
});

test.describe('频道设置 /workspace/channel/:channelId/setting', () => {
  test('基本信息与 Webhooks 页签', async ({ page }) => {
    const log = collect(page);

    await page.goto('workspace/channel', { waitUntil: 'networkidle' });
    await page.waitForTimeout(2000);
    await page.locator('.ant-table-tbody tr.ant-table-row button.ant-btn-link').first().click();
    await page.waitForTimeout(3000);
    const chId = page.url().split('/channel/')[1].split(/[/?]/)[0];
    log.reset();

    await test.step('10. 设置页 基本信息', async () => {
      await page.goto(`workspace/channel/${chId}/setting`, { waitUntil: 'networkidle' });
      await page.waitForTimeout(3000);
      console.log(`\n>>> 设置页 ${page.url()} | title: ${await page.title()}`);
      console.log('    tabs:', (await page.locator('.ant-tabs-tab').allInnerTexts()).join(' / '));
      await shot(page, '10-setting');
      log.dump('10. 设置页 基本信息');
    });

    await test.step('11. Webhooks 页签', async () => {
      await page.locator('.ant-tabs-tab', { hasText: /webhook/i }).first().click();
      await page.waitForTimeout(3000);
      console.log('\n>>> 点 Webhooks 后 url:', page.url());
      await shot(page, '11-webhooks');
      log.dump('11. 设置页 Webhooks');

      // BUG-1: 应停留在 /workspace/... 而非跳到已废弃的 /studio/...
      // 修复前此断言失败，是预期的红灯。
      await expect(page, 'Webhooks 页签不应跳到已废弃的 /studio 路径').toHaveURL(
        /\/workspace\/channel\/[^/]+\/setting/
      );
    });
  });

  test('12. 不存在的 channelId', async ({ page }) => {
    const log = collect(page);
    await page.goto('workspace/channel/00000000-0000-0000-0000-000000000000', {
      waitUntil: 'networkidle',
    });
    await page.waitForTimeout(3000);
    await shot(page, '12-bad-id');
    const body = (await page.locator('body').innerText()).slice(0, 200).replace(/\n+/g, ' | ');
    console.log('\n>>> 页面文本:', body);
    log.dump('12. 不存在的 channelId');

    // BUG-4: 应展示友好的错误页，而非 React Router 默认 ErrorBoundary
    expect(body, '不存在的频道应有友好提示').not.toContain('Unexpected Application Error');
  });
});

/* ══════════════════════════════════════════════════════════
 *  破坏性操作 —— 会真实写库，只操作脚本自建的 e2e-tmp-* 数据
 *  E2E_DESTRUCTIVE=0 可跳过
 * ══════════════════════════════════════════════════════════ */
test.describe('频道增删改（破坏性）', () => {
  test.skip(process.env.E2E_DESTRUCTIVE === '0', '已通过 E2E_DESTRUCTIVE=0 跳过');

  test('新建 → 编辑保存 → 转让弹窗 → 删除 → 校验失效', async ({ page }) => {
    const log = collect(page);
    const TMP = `e2e-tmp-${Date.now()}`;
    let newId = '';

    await test.step('13. 新建频道（提交）', async () => {
      await page.goto('workspace/channel', { waitUntil: 'networkidle' });
      await page.waitForTimeout(2000);
      const before = await rows(page);
      log.reset();

      await page.mouse.move(800, 700);
      await page.waitForTimeout(500);
      await page.getByRole('button', { name: /新建|新增/ }).first().click();
      await page.waitForTimeout(1500);

      const pop = page.locator('.ant-popover:visible');
      await pop.locator('input:visible').first().fill(TMP);
      await pop.locator('.ant-select').last().click(); // 语言必填
      await page.waitForTimeout(1200);
      await page.locator('.ant-select-dropdown:visible .ant-select-item-option').first().click();
      await page.waitForTimeout(500);
      await shot(page, '13-create-filled');

      await pop.getByRole('button', { name: /提 *交/ }).click();
      await page.waitForTimeout(3000);
      const after = await rows(page);
      console.log(`\n>>> 新建「${TMP}」rows: ${before} -> ${after}`);
      console.log('    提示:', (await toast(page)).trim());
      await shot(page, '13-create-done');
      log.dump('13. 新建频道（提交）');
      expect(after).toBe(before + 1);
    });

    await test.step('14. 编辑基本信息（保存 + 持久化校验）', async () => {
      await page.goto('workspace/channel', { waitUntil: 'networkidle' });
      await page.waitForTimeout(2500);
      const kw = page.getByPlaceholder('请输入').first();
      await kw.fill(TMP);
      await page.keyboard.press('Enter');
      await page.waitForTimeout(2500);
      expect(await rows(page), '应能搜到刚新建的频道').toBe(1);

      await page.locator('.ant-table-tbody tr.ant-table-row a', { hasText: /设置/ }).first().click();
      await page.waitForTimeout(3500);
      newId = page.url().split('/channel/')[1].split('/')[0];
      console.log(`\n>>> 设置页 ${page.url()}`);
      log.reset();

      const SUMMARY = `e2e 自动化测试写入 ${new Date().toISOString()}`;
      await page.locator('textarea').first().fill(SUMMARY);
      await shot(page, '14-edit-filled');
      await page.getByRole('button', { name: /提 *交|保 *存/ }).first().click();
      await page.waitForTimeout(2500);
      console.log('    保存提示:', (await toast(page)).trim());
      await shot(page, '14-edit-saved');
      log.dump('14. 编辑基本信息（保存）');

      await page.reload({ waitUntil: 'networkidle' });
      await page.waitForTimeout(3000);
      const persisted = await page.locator('textarea').first().inputValue();
      console.log(`>>> 重载后 summary ${persisted === SUMMARY ? '✓ 已持久化' : '✗ 未持久化'}`);
      log.dump('14b. 重载验证持久化');
      expect(persisted, 'summary 应已写库').toBe(SUMMARY);
    });

    await test.step('15. 转让弹窗（不提交，需接收方）', async () => {
      await page.goto('workspace/channel', { waitUntil: 'networkidle' });
      await page.waitForTimeout(2000);
      const kw = page.getByPlaceholder('请输入').first();
      await kw.fill(TMP);
      await page.keyboard.press('Enter');
      await page.waitForTimeout(2500);
      log.reset();

      await page.locator('.ant-table-tbody tr.ant-table-row button.ant-dropdown-trigger').first().click();
      await page.waitForTimeout(1500);
      await shot(page, '15-menu');
      const menu = await page.locator('.ant-dropdown:visible .ant-dropdown-menu-item').allInnerTexts();
      console.log('\n>>> 行操作菜单:', menu.join(' / '));

      await page.locator('.ant-dropdown:visible .ant-dropdown-menu-item', { hasText: /转让/ }).first().click();
      await page.waitForTimeout(3000);
      await shot(page, '15-transfer');
      const text = await page
        .locator('.ant-modal:visible, .ant-drawer:visible')
        .innerText()
        .catch(() => '(无)');
      console.log('>>> 弹窗文本:', text.slice(0, 300).replace(/\n+/g, ' | '));
      expect(text).toContain('转让');
      log.dump('15. 转让弹窗');
      await page.keyboard.press('Escape');
      await page.waitForTimeout(1500);
    });

    await test.step('16. 删除频道', async () => {
      await page.goto('workspace/channel', { waitUntil: 'networkidle' });
      await page.waitForTimeout(2000);
      const beforeDel = await rows(page);
      const kw = page.getByPlaceholder('请输入').first();
      await kw.fill(TMP);
      await page.keyboard.press('Enter');
      await page.waitForTimeout(2500);
      log.reset();

      await page.locator('.ant-table-tbody tr.ant-table-row button.ant-dropdown-trigger').first().click();
      await page.waitForTimeout(1500);
      await page.locator('.ant-dropdown:visible .ant-dropdown-menu-item', { hasText: /删除/ }).first().click();
      await page.waitForTimeout(2000);
      await shot(page, '16-delete-confirm');
      const confirm = await page.locator('.ant-modal-confirm:visible').innerText().catch(() => '(无)');
      console.log('\n>>> 删除确认弹窗:', confirm.replace(/\n+/g, ' | ').slice(0, 200));
      expect(confirm).toContain('不可撤销');

      await page
        .locator('.ant-modal-confirm:visible .ant-btn-dangerous, .ant-modal-confirm:visible .ant-btn-primary')
        .first()
        .click();
      await page.waitForTimeout(1200);
      console.log('    删除提示:', (await toast(page)).trim());
      await shot(page, '16-delete-done');
      await page.waitForTimeout(2500);
      log.dump('16. 删除频道');

      await page.goto('workspace/channel', { waitUntil: 'networkidle' });
      await page.waitForTimeout(2500);
      const afterDel = await rows(page);
      console.log(`\n>>> 删除后列表 rows: ${beforeDel} -> ${afterDel}`);
      expect(afterDel).toBe(beforeDel - 1);
    });

    await test.step('17. 删除后访问原详情页', async () => {
      log.reset();
      await page.goto(`workspace/channel/${newId}`, { waitUntil: 'networkidle' });
      await page.waitForTimeout(3000);
      await shot(page, '17-deleted-detail');
      console.log('>>> 访问已删除频道:', (await page.locator('body').innerText()).slice(0, 150).replace(/\n+/g, ' | '));
      log.dump('17. 删除后访问原详情页');
    });
  });
});
