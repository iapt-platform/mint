import type { Page } from '@playwright/test';

/** 已知的第三方库废弃警告 / React 开发态噪音，不计入问题清单 */
const NOISE =
  /Spin.*deprecated|destroyOnClose|Dropdown\.Button.*deprecated|ProList.*deprecated|Alert.*deprecated|React does not recognize|Invalid DOM property|empty string \(""\) was passed|Static function can not consume|React DevTools/;

/**
 * 挂上 console / pageerror / 请求失败 / HTTP 4xx-5xx 采集。
 * 这是本套测试的核心——它替代的正是"人肉盯着 devtools 控制台"这件事。
 */
export function collect(page: Page) {
  let logs: string[] = [];

  page.on('console', (m) => {
    const t = m.type();
    if ((t === 'error' || t === 'warning') && !NOISE.test(m.text())) {
      logs.push(`[${t}] ${m.text().slice(0, 250)}`);
    }
  });
  page.on('pageerror', (e) => logs.push(`[PAGEERROR] ${e.message.slice(0, 250)}`));
  page.on('requestfailed', (r) =>
    logs.push(`[REQFAIL] ${r.url()} :: ${r.failure()?.errorText}`)
  );
  page.on('response', (r) => {
    if (r.status() >= 400) logs.push(`[HTTP ${r.status()}] ${r.url()}`);
  });

  return {
    /** 当前累积的日志 */
    get all() {
      return logs;
    },
    reset() {
      logs = [];
    },
    /** 打印某个测试步骤采集到的内容并清空 */
    dump(step: string) {
      console.log(`\n### ${step}`);
      logs.length ? logs.forEach((l) => console.log('   ' + l)) : console.log('   (clean)');
      logs = [];
    },
  };
}
