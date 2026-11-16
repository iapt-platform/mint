import {
  test,
  expect,
  type APIRequestContext,
  type Browser,
  type Page,
  type Response,
} from '@playwright/test';
import { collect } from '../lib/collect.js';
import { authFile, call, type Account } from '../lib/api.js';
import {
  BASE,
  COURSE_ANSWER_CHANNEL,
  COURSE_ARTICLE,
  COURSE_TEXTBOOK,
  COURSE_WORD,
  DESTRUCTIVE,
  HAS_ASSISTANT,
  HAS_STUDENT,
  LIBRARY,
  shotDir,
} from '../lib/env.js';

/**
 * 课程模块：dashboard-v6 的 /workspace/course/*，以及 Laravel 端的 /library/course
 *
 * 三个账号：E2E_USER = 老师（课程拥有者），E2E_STUDENT_* = 学生，E2E_ASSISTANT_* = 助教。
 * 没配学生 / 助教账号时，需要它们的用例自动跳过（只跑老师单人能做的部分）。
 *
 * 数据：每个 describe 在 beforeAll 里用 v2 API 以老师身份新建自己的课程（标题 e2e-tmp-<时间戳>-<用途>），
 * 再用 PUT 把报名 / 课程时间设到需要的窗口——学生与老师能做哪些操作完全由
 * 「报名方式 × 成员状态 × 当前处于报名期 / 开课前 / 开课中 / 结课后」决定
 * （dashboard-v6/src/components/course/RolePower.ts），所以每个流程用自己的时间窗。
 * 跑完**不删**（本地开发库的测试数据要留给人看，见 README「破坏性操作」），
 * 结尾打印本次建出的课程与 channel，方便去页面上看结果。只有「删除课程」那一条会真的删掉它自建的课程。
 *
 * 教材：COURSE_TEXTBOOK 文集里的 COURSE_ARTICLE（默认「初级班课本范例 / 练习一」，句子 168-916-2-9），
 * 标准答案 COURSE_ANSWER_CHANNEL 在这一句上有逐词解析。
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * 测试项目一览
 * ─────────────────────────────────────────────────────────────────────────────
 * 一、老师：课程增删改查（只需 E2E_USER）
 *   1.1  列表页 /workspace/course：「我建立的 / 我参加的 / 我任教的」三个页签，角标数字与 course-my-course 一致
 *   1.2  新建课程（工具栏「新建」气泡表单）→ 出现在「我建立的课程」，标签「人工审核」「拥有者」
 *   1.3  同名再建 → 后端拒绝（ok:false），列表不多出一条
 *   1.4  「设置」链接指向 /workspace/course/:id/setting
 *   1.5  设置页「基本信息」：改副标题、主讲人、招生数量、报名时间、课程时间、课本、标准答案、
 *        公开性、录取方式、报名消息、简介 → 保存 → PUT 请求体正确；刷新后回显
 *   1.6  公开性 = 私有 时录取方式只能选「仅限邀请」；公开时「仅限邀请」不可选
 *   1.7  详情页 /workspace/course/:id：标题、副标题、报名 / 课程时间与状态标签、录取方式说明、主讲人、简介、课本目录
 *   1.8  列表行「删除」→ 确认 → 消失，角标减一
 *   1.9  不存在的课程 id：详情页不出现 Unexpected Application Error（BUG-C1）
 *
 * 二、人工审核（manual）课程：学生报名，老师录取 / 不录取 / 清退（需学生账号）
 *   2.1  报名期内、开课前：详情页显示「可报名」「未开始」与录取方式说明，只有「报名」按钮
 *   2.2  学生报名：确认框里显示课程的报名消息 → 状态「已经报名」，按钮变「取消」
 *   2.3  学生取消报名 →「已经取消」，可以再次「报名」
 *   2.4  再次报名 →「已经报名」
 *   2.5  老师成员页：该生「已经报名」，菜单里「录取」「不录取」可点、「清退」「撤销邀请」不可点 → 录取 →「已录取」
 *   2.6  老师点该生 → 右侧「参课记录」时间线列出报名 / 取消 / 再报名 / 录取全部历史
 *   2.7  学生详情页：「已录取」，只剩「退出」按钮
 *   2.8  学生列表页「我参加的课程」：有这门课，状态「已录取」，菜单里「退出」可点、「报名」不可点
 *   2.9  开课后：老师菜单「清退」可点 → 清退 → 学生看到「已清退」且没有任何按钮
 *   2.10 不录取：另一门课，学生报名 → 老师「不录取」→ 学生看到「未录取」且不能再报名
 *   2.11 学生退出：录取后在开课前「退出」→「已退出」，仍可重新「报名」
 *   2.12 报名未开始：报名时间在未来 → 标签「未开始」，没有「报名」按钮
 *   2.13 报名已结束：→ 标签「已结束」，没有「报名」按钮
 *   2.14 名额已满：招生数量 1、已有一人报名 → 另一个人（助教账号充当）看到「名额已满」，没有「报名」按钮
 *
 * 三、邀请制（invite，私有）课程：老师邀请学生与助教（需学生 + 助教账号）
 *   3.1  老师成员页「邀请」三步弹窗：选用户 → 选身份「学生」→ 完成 → 成员列表出现「已经邀请」
 *   3.2  同样邀请助教（身份「助教」）→ 标签「助理老师」
 *   3.3  学生「拒绝邀请」→「已拒绝邀请」
 *   3.4  老师再次邀请（弹窗第二步提示该生当前状态）→ 老师「撤销邀请」→ 学生看到「已经撤销邀请」
 *   3.5  老师第三次邀请 → 学生「接受邀请」→「已接受邀请」，按钮「退出」
 *   3.6  助教「接受邀请」→ 助教列表页「我任教的课程」有这门课，带「设置」链接与状态「已接受邀请」
 *   3.7  成员页「导出成员列表」：老师下载到 xlsx
 *
 * 四、开放自学（open）课程（需学生账号）
 *   4.1  开课中：学生「参课」→「已经参加了」，按钮「退出」
 *   4.2  学生「退出」→「已退出」→ 再「参课」→「已经参加了」
 *   4.3  学生列表页「我参加的课程」有这门课
 *
 * 五、/library/course（Laravel 端公开课程栏目，只需 E2E_USER）
 *   5.1  首页统计条三个数字
 *   5.2  公开、报名中、开课中的课程同时出现在「最新课程」卡片与「开放报名」列表，带「报名」引导；
 *        链接指向 dashboard-v6 的 /workspace/course/:id，点开能看到课程
 *   5.3  私有课程不出现在栏目里
 *   5.4  已结课的公开课程出现在「历史课程」预览与 /library/course/history，状态「已截止」类标签，不在「开放报名」
 *
 * 六、教材与作业（需学生 + 助教账号）
 *   6.1  学生从详情页的课本目录点进课文 → /workspace/course/:id/textbook/:article
 *   6.2  第一次进来提示「请选择作业的存放位置」→ 选自己的 channel → set-channel 成功 → 刷新后提示消失
 *   6.3  学生视角：角色标签「学生」，URL 的 channel 换成学生自己的作业 channel
 *   6.4  课文顶部「课程：」链接指向 v6 存在的页面（BUG-C2：现在是 v4 的 /course/show/:id）
 *   6.5  做作业：逐词模式下点一个词 → 填「整体含义」→ 保存 → 写进学生自己的 channel
 *   6.6  对答案：逐词菜单「显示/隐藏错误提示」→ 拉取课程标准答案（wbw-sentence view=course-answer）
 *   6.7  助教视角：角色标签「助理老师」，channel 是标准答案；拉到课程成员名单
 *   6.8  助教看作业：句子下「逐词解析」页签列出学生的逐词作业（view=sent-can-read&course=），并有「无作业：」名单
 *   6.9  老师（拥有者）视角：角色标签「拥有者」
 *   6.10 非成员打开课文：应给出提示，而不是一直显示 loading（BUG-C3）
 *
 * 七、权限（助教 UI + 直接调 v2 后端）
 *   7.1  助教能进设置页，但保存基本信息被后端拒绝（403），页面提示失败
 *   7.2  助教在成员页看不到录取 / 清退等操作菜单
 *   7.3  学生不能修改、删除课程
 *   7.4  学生不能把自己的报名状态改成「已录取」（BUG-C4：course-member PUT 没有权限校验）
 *   7.5  学生不能以「邀请」的名义把别人拉进课程（BUG-C5：course-member POST invited 没有权限校验）
 *   7.6  未登录不能导出成员名单（BUG-C6：course-member-export 没有鉴权）
 *
 * 未覆盖：封面上传（要选本地图片文件）、成员列表的筛选 / 搜索（search 有已知 FIXME）、
 * {{exercise}} 模板（v6 的 MdTpl 尚未实现，ExerciseAnswer 组件无人引用）。
 */

const SHOT = shotDir('course');
const shot = (page: Page, name: string) =>
  page.screenshot({ path: `${SHOT}/${name}.png`, fullPage: true });

const STAMP = `e2e-tmp-${Date.now()}`;
/** 本次建出的数据：建出来就打印（失败重启 worker 也不丢） */
const created = { push: (line: string) => console.log(`   [data] ${line}`) };

test.skip(!DESTRUCTIVE, 'E2E_DESTRUCTIVE=0：课程测试全部是增删改，跳过');

// ─── 时间 ──────────────────────────────────────────────────────────────────

const DAY = 86_400_000;
const pad = (n: number) => String(n).padStart(2, '0');
/** 距今 days 天的时刻，后端存的格式 `YYYY-MM-DD HH:mm:ss`（UTC，与后端 app.timezone 一致） */
const at = (days: number) => {
  const d = new Date(Date.now() + days * DAY);
  return (
    `${d.getUTCFullYear()}-${pad(d.getUTCMonth() + 1)}-${pad(d.getUTCDate())} ` +
    `${pad(d.getUTCHours())}:${pad(d.getUTCMinutes())}:${pad(d.getUTCSeconds())}`
  );
};
const ymd = (days: number) => at(days).slice(0, 10);

// ─── 账号 ──────────────────────────────────────────────────────────────────

interface Me {
  id: string;
  userName: string;
  nickName: string;
}
const meCache = new Map<Account, Me>();
async function me(request: APIRequestContext, who: Account): Promise<Me> {
  const hit = meCache.get(who);
  if (hit) return hit;
  const r = await call(request, 'GET', '/v3/me', undefined, who);
  expect(r.status, `${who} 的 token 无效：${JSON.stringify(r.body)}`).toBe(200);
  meCache.set(who, r.body.data);
  return r.body.data;
}

/** 以某个账号开一个新浏览器上下文（默认的 page 是老师） */
async function open(browser: Browser, who: Account) {
  const ctx = await browser.newContext({ baseURL: BASE, storageState: authFile(who) });
  return { ctx, page: await ctx.newPage() };
}

// ─── 课程数据 ──────────────────────────────────────────────────────────────

type Join = 'open' | 'manual' | 'invite';
interface CourseSetting {
  title: string;
  subtitle?: string;
  summary?: string;
  content?: string;
  sign_up_message?: string | null;
  teacher_id?: string;
  publicity: number;
  anthology_id?: string;
  channel_id?: string;
  start_at: string;
  end_at: string;
  sign_up_start_at: string;
  sign_up_end_at: string;
  join: Join;
  request_exp?: string;
  number: number;
}
const settings = new Map<string, CourseSetting>();

/**
 * 老师建一门课并设好。`signUp` / `course` 是相对今天的天数区间。
 * v2 的 PUT 是整体覆盖（没传的字段会被清空），所以这里记住全部字段，后面 `patchCourse` 在其上合并。
 */
async function makeCourse(
  request: APIRequestContext,
  purpose: string,
  opt: {
    join: Join;
    publicity?: number;
    signUp: [number, number];
    course: [number, number];
    number?: number;
    textbook?: boolean;
    sign_up_message?: string;
  },
) {
  const teacher = await me(request, 'user');
  const title = `${STAMP}-${purpose}`;
  const r = await call(request, 'POST', '/v2/course', { title, lang: 'zh-Hans', studio: teacher.userName });
  expect(r.body?.ok, JSON.stringify(r.body)).toBe(true);
  const id: string = r.body.data.id;
  created.push(`课程 ${title}  ${BASE}workspace/course/${id}`);
  settings.set(id, {
    title,
    subtitle: `${purpose} 副标题`,
    summary: `${purpose} 摘要（dashboard-test）`,
    content: `由 dashboard-test 建立：${purpose}`,
    sign_up_message: opt.sign_up_message ?? null,
    teacher_id: teacher.id,
    publicity: opt.publicity ?? (opt.join === 'invite' ? 10 : 30),
    anthology_id: opt.textbook === false ? undefined : COURSE_TEXTBOOK,
    channel_id: opt.textbook === false ? undefined : COURSE_ANSWER_CHANNEL,
    sign_up_start_at: at(opt.signUp[0]),
    sign_up_end_at: at(opt.signUp[1]),
    start_at: at(opt.course[0]),
    end_at: at(opt.course[1]),
    join: opt.join,
    request_exp: 'none',
    number: opt.number ?? 0,
  });
  await patchCourse(request, id, {});
  return { id, title };
}

async function patchCourse(request: APIRequestContext, id: string, patch: Partial<CourseSetting>) {
  const next = { ...settings.get(id)!, ...patch };
  settings.set(id, next);
  const r = await call(request, 'PUT', `/v2/course/${id}`, next);
  expect(r.body?.ok, JSON.stringify(r.body)).toBe(true);
}

/** 把开课时间挪到「开课中」 */
const startCourse = (request: APIRequestContext, id: string) =>
  patchCourse(request, id, { start_at: at(-1), end_at: at(30) });

/** 某人在某课的当前成员记录（老师身份查，可查他人） */
async function memberOf(request: APIRequestContext, courseId: string, userId: string) {
  const r = await call(request, 'GET', `/v2/course-member/${courseId}?user_uid=${userId}`);
  return r.body?.ok ? r.body.data : null;
}

/** 后端直接写成员状态：只用于准备前置数据，被测的那一步一律走页面 */
async function seedMember(
  request: APIRequestContext,
  courseId: string,
  who: Account,
  status: 'applied' | 'joined' | 'invited',
  role: 'student' | 'assistant' = 'student',
) {
  const u = await me(request, who);
  // 自己报名 / 加入用自己的身份；邀请用老师的身份
  const as: Account = status === 'invited' ? 'user' : who;
  const r = await call(
    request,
    'POST',
    '/v2/course-member',
    { user_id: u.id, course_id: courseId, role, status },
    as,
  );
  expect(r.body?.ok, JSON.stringify(r.body)).toBe(true);
  return r.body.data.id as string;
}
async function setMemberStatus(request: APIRequestContext, memberId: string, status: string, who: Account = 'user') {
  const r = await call(request, 'PUT', `/v2/course-member/${memberId}`, { status }, who);
  expect(r.body?.ok, JSON.stringify(r.body)).toBe(true);
  return r.body.data.id as string;
}

// ─── 页面操作 ──────────────────────────────────────────────────────────────

/** antd 会在两个汉字的按钮中间插空格：「报名」渲染成「报 名」 */
const btnName = (text: string) => new RegExp(`(^|\\s)${[...text].join(' ?')}$`);

/** 只看课程自己闯的祸：页面异常、请求失败、课程接口报错 */
const courseProblems = (all: string[]) =>
  all.filter(
    (l) =>
      l.startsWith('[PAGEERROR]') ||
      // 课文里嵌的外站视频在翻页时被浏览器中止（ERR_ABORTED），不算错
      (l.startsWith('[REQFAIL]') && !l.includes('ERR_ABORTED')) ||
      (l.startsWith('[HTTP') && /\/api\/v2\/course/.test(l)),
  );

const isMemberWrite = (r: Response) =>
  /\/api\/v2\/course-member(\/[^/?]*)?$/.test(new URL(r.url()).pathname) &&
  ['POST', 'PUT'].includes(r.request().method());

/** 确认框（App.useApp().modal.confirm）点「确定」 */
const confirmOk = (page: Page) => page.locator('.ant-modal-confirm-btns .ant-btn-primary').click();

/** 详情页的报名状态区（Status.tsx）：状态文字 + 操作按钮 */
async function openDetail(page: Page, courseId: string) {
  const loaded = page.waitForResponse(
    (r) => r.url().includes(`/api/v2/course-member/${courseId}`) && r.request().method() === 'GET',
  );
  await page.goto(`workspace/course/${courseId}`);
  await loaded;
  await expect(page.locator('h3').first()).toBeVisible();
}
/** 状态区的按钮（课本目录里没有 button，CourseHead 里只有状态区有） */
const statusButtons = (page: Page) => page.locator('div.ant-typography button');

/** 学生在详情页点一个状态按钮并确认，返回新的成员记录 */
async function studentDo(page: Page, label: string, expectMessage?: string | RegExp) {
  const written = page.waitForResponse(isMemberWrite);
  await statusButtons(page).filter({ hasText: btnName(label) }).click();
  if (expectMessage) {
    await expect(page.locator('.ant-modal-confirm-content')).toContainText(expectMessage);
  }
  await confirmOk(page);
  const body = await (await written).json();
  expect(body.ok, JSON.stringify(body)).toBe(true);
  return body.data;
}

async function expectButtons(page: Page, labels: string[]) {
  await expect(statusButtons(page)).toHaveText(labels.map(btnName));
}

/** 设置页「成员」页签 */
async function openMembers(page: Page, courseId: string) {
  const listed = page.waitForResponse(
    (r) => r.url().includes('/api/v2/course-member?view=course') && r.url().includes(courseId),
  );
  await page.goto(`workspace/course/${courseId}/setting`);
  await page.getByRole('tab', { name: '成员' }).click();
  await listed;
}
const memberRow = (page: Page, nick: string) =>
  page.locator('.ant-pro-list-row').filter({ has: page.getByText(nick, { exact: true }) });

/** 打开某成员行的操作菜单，返回菜单项 locator */
async function memberMenu(page: Page, nick: string) {
  const row = memberRow(page, nick);
  await row.locator('button:has(.anticon-ellipsis)').hover();
  return page.locator('.ant-dropdown:not(.ant-dropdown-hidden) .ant-dropdown-menu-item');
}
async function expectMenu(page: Page, nick: string, enabled: string[], disabled: string[]) {
  const items = await memberMenu(page, nick);
  for (const t of enabled) {
    await expect(items.filter({ hasText: new RegExp(`^${t}$`) })).not.toHaveClass(/disabled/);
  }
  for (const t of disabled) {
    await expect(items.filter({ hasText: new RegExp(`^${t}$`) })).toHaveClass(/disabled/);
  }
  await page.mouse.move(0, 0);
}
/** 老师对某成员执行菜单里的一项并确认 */
async function managerDo(page: Page, nick: string, action: string) {
  const items = await memberMenu(page, nick);
  const written = page.waitForResponse(isMemberWrite);
  await items.filter({ hasText: new RegExp(`^${action}$`) }).click();
  await confirmOk(page);
  const body = await (await written).json();
  expect(body.ok, JSON.stringify(body)).toBe(true);
  return body.data;
}

/** 成员页「邀请」三步弹窗 */
async function invite(page: Page, user: Me, role: '学生' | '助教', alert?: RegExp) {
  await page.getByRole('button', { name: /邀请/ }).first().click();
  const modal = page.locator('.ant-modal').filter({ hasText: '选择用户' });
  await modal.locator('.ant-select:visible').click();
  await modal.locator('.ant-select:visible input').pressSequentially(user.userName, { delay: 30 });
  await page
    .locator('.ant-select-dropdown:not(.ant-select-dropdown-hidden) .ant-select-item-option')
    .filter({ hasText: `${user.userName}-${user.nickName}` })
    .first()
    .click();
  await modal.getByRole('button', { name: btnName('下一步') }).click();

  if (alert) await expect(modal.locator('.ant-alert')).toContainText(alert);
  await modal.locator('.ant-select:visible').click();
  await page
    .locator('.ant-select-dropdown:not(.ant-select-dropdown-hidden) .ant-select-item-option')
    .filter({ hasText: new RegExp(`^${role}$`) })
    .click();
  const posted = page.waitForResponse(
    (r) => r.url().endsWith('/api/v2/course-member') && r.request().method() === 'POST',
  );
  await modal.getByRole('button', { name: btnName('下一步') }).click();
  const body = await (await posted).json();
  expect(body.ok, JSON.stringify(body)).toBe(true);
  expect(body.data.status).toBe('invited');
  await expect(modal.getByText('已经成功邀请')).toBeVisible();
  await modal.getByRole('button', { name: btnName('提交') }).click();
  await expect(modal).toBeHidden();
  return body.data;
}

/** 列表页切到某个页签，返回该课程的行 */
async function listTab(page: Page, tab: '我建立的课程' | '我参加的课程' | '我任教的课程', title: string) {
  const view = { 我建立的课程: 'create', 我参加的课程: 'study', 我任教的课程: 'teach' }[tab];
  if (!page.url().endsWith('workspace/course')) await page.goto('workspace/course');
  const loaded = page.waitForResponse((r) => r.url().includes(`/api/v2/course?view=${view}`));
  await page.locator('.ant-pro-table-list-toolbar-menu, .ant-pro-table').getByText(tab).click();
  await loaded;
  return page.locator('tr.ant-table-row').filter({ hasText: title });
}

/** 一个 antd 表单项（按 label 文本精确匹配） */
const formItem = (page: Page, label: string) =>
  page
    .locator('.ant-form-item')
    .filter({ has: page.locator('.ant-form-item-label label').getByText(label, { exact: true }) });

async function pick(page: Page, label: string, option: string | RegExp, search?: string) {
  const item = formItem(page, label);
  await item.locator('.ant-select').click();
  if (search) await item.locator('.ant-select input').pressSequentially(search, { delay: 30 });
  const dropdown = page.locator('.ant-select-dropdown:visible:not([class*="-leave"])').last();
  const target = dropdown.locator('.ant-select-item-option').filter({ hasText: option }).first();
  await expect(dropdown.locator('.ant-select-item-option').first()).toBeVisible();
  // 下拉是虚拟列表，只渲染可见的几项：往下滚着找
  for (let i = 0; i < 40 && !(await target.isVisible()); i++) {
    await dropdown.hover();
    await page.mouse.wheel(0, 96);
    await page.waitForTimeout(150);
  }
  await target.click();
}

/**
 * 按 value 选下拉项：标签可能重名（两个 channel 都叫「visuddhinanda/译文」），
 * 用方向键逐项走，读 aria-activedescendant 指向的无障碍 option（它的文本是 value）。
 */
async function pickByValue(page: Page, label: string, value: string, search: string) {
  const item = formItem(page, label);
  await item.locator('.ant-select').click();
  const input = item.locator('.ant-select input');
  await input.pressSequentially(search, { delay: 30 });
  await expect(page.locator('.ant-select-dropdown:visible:not([class*="-leave"]) .ant-select-item-option').first()).toBeVisible();
  for (let i = 0; i < 60; i++) {
    const id = await input.getAttribute('aria-activedescendant');
    const active = page.locator(`[id="${id}"]`);
    const text = id && (await active.count()) ? await active.textContent() : '';
    if (text?.includes(value)) {
      await input.press('Enter');
      return;
    }
    await input.press('ArrowDown');
  }
  throw new Error(`${label}：下拉里找不到 ${value}`);
}

async function fillRange(page: Page, label: string, from: string, to: string) {
  const inputs = formItem(page, label).locator('input');
  await inputs.nth(0).click();
  await inputs.nth(0).fill(from);
  await inputs.nth(0).press('Enter');
  await inputs.nth(1).fill(to);
  await inputs.nth(1).press('Enter');
  await page.keyboard.press('Escape');
}


// ═══════════════════════════════════════════════════════════════════════════
// 一、老师：课程增删改查
// ═══════════════════════════════════════════════════════════════════════════

test.describe('一、老师：课程增删改查', () => {
  test('1.1 列表页三个页签与数量角标', async ({ page, request }) => {
    const log = collect(page);
    const teacher = await me(request, 'user');
    const counted = page.waitForResponse((r) => r.url().includes('/api/v2/course-my-course'));
    await page.goto('workspace/course');
    const n = (await (await counted).json()).data;
    const menu = page.locator('.ant-pro-table-list-toolbar-menu, .ant-pro-table-list-toolbar');
    for (const [tab, count] of [
      ['我建立的课程', n.create],
      ['我参加的课程', n.study],
      ['我任教的课程', n.teach],
    ] as const) {
      await expect(menu.getByText(tab)).toBeVisible();
      await expect(menu.locator('span', { hasText: tab }).locator('.ant-badge')).toHaveText(
        count ? String(count) : '',
      );
    }
    expect(teacher.userName).toBeTruthy();
    await shot(page, '1.1-list');
    const problems = courseProblems(log.all);
    log.dump('1.1 列表页');
    expect(problems).toEqual([]);
  });

  test('1.2–1.8 新建 → 重名 → 设置 → 详情 → 删除', async ({ page, request }) => {
    const log = collect(page);
    const teacher = await me(request, 'user');
    const title = `${STAMP}-crud`;
    let courseId = '';

    await test.step('1.2 工具栏「新建」→ 填名称、语言 → 提交 → 出现在「我建立的课程」', async () => {
      await page.goto('workspace/course');
      await page.getByRole('button', { name: btnName('新建') }).click();
      const pop = page.locator('.ant-popover:not(.ant-popover-hidden)');
      await pop.getByLabel('名称').fill(title);
      await pop.locator('.ant-select').click();
      await page
        .locator('.ant-select-dropdown:not(.ant-select-dropdown-hidden) .ant-select-item-option')
        .filter({ hasText: 'zh-Hans' })
        .click();
      const posted = page.waitForResponse(
        (r) => r.url().endsWith('/api/v2/course') && r.request().method() === 'POST',
      );
      await pop.getByRole('button', { name: btnName('提交') }).click();
      const body = await (await posted).json();
      expect(body.ok, JSON.stringify(body)).toBe(true);
      courseId = body.data.id;
      created.push(`课程 ${title}（1.8 会删掉）`);
      const row = page.locator('tr.ant-table-row').filter({ hasText: title });
      await expect(row).toBeVisible();
      // 新建的课程默认 join 为空 → 标签取不到文案；拥有者标签一定有
      await expect(row.getByText('拥有者')).toBeVisible();
      await shot(page, '1.2-created');
      log.dump('1.2 新建');
    });

    await test.step('1.3 同名再建 → 后端拒绝，列表仍只有一条', async () => {
      await page.getByRole('button', { name: btnName('新建') }).click();
      const pop = page.locator('.ant-popover:not(.ant-popover-hidden)');
      await pop.getByLabel('名称').fill(title);
      const posted = page.waitForResponse(
        (r) => r.url().endsWith('/api/v2/course') && r.request().method() === 'POST',
      );
      await pop.getByRole('button', { name: btnName('提交') }).click();
      const body = await (await posted).json();
      expect(body.ok).toBe(false);
      await expect(page.locator('.ant-message-error')).toBeVisible();
      await page.keyboard.press('Escape');
      await page.reload();
      await expect(page.locator('tr.ant-table-row').filter({ hasText: title })).toHaveCount(1);
      log.dump('1.3 重名');
    });

    await test.step('1.4 「设置」链接指向设置页', async () => {
      const row = page.locator('tr.ant-table-row').filter({ hasText: title });
      await expect(row.getByRole('link', { name: '设置' })).toHaveAttribute(
        'href',
        new RegExp(`workspace/course/${courseId}/setting$`),
      );
      await expect(row.getByRole('link', { name: title })).toHaveAttribute(
        'href',
        new RegExp(`workspace/course/${courseId}$`),
      );
    });

    const answer = (await call(request, 'GET', `/v2/channel/${COURSE_ANSWER_CHANNEL}`)).body.data;
    const textbookTitle = (await call(request, 'GET', `/v2/anthology/${COURSE_TEXTBOOK}`)).body.data.title;

    await test.step('1.5 设置页：改全部基本信息 → 保存 → 请求体正确', async () => {
      const loaded = page.waitForResponse((r) => r.url().endsWith(`/api/v2/course/${courseId}`));
      await page.goto(`workspace/course/${courseId}/setting`);
      await loaded;
      await expect(formItem(page, '标题').locator('input')).toHaveValue(title);

      await formItem(page, '副标题').locator('input').fill('e2e 副标题');
      await pick(page, '主讲人', `${teacher.userName}-${teacher.nickName}`, teacher.userName);
      await formItem(page, '招生数量').locator('input').fill('5');
      await fillRange(page, '报名时间', ymd(-1), ymd(7));
      await fillRange(page, '课程时间', ymd(3), ymd(30));
      await pick(page, '课本', textbookTitle, textbookTitle.slice(0, 3));
      await pickByValue(page, '标准答案', COURSE_ANSWER_CHANNEL, answer.name);
      await pick(page, '公开性', /^公开$/);
      await pick(page, '录取方式', '人工审核');
      const md = page.locator('.w-md-editor textarea');
      await md.nth(0).fill('e2e 报名须知：请先读完第一单元');
      await md.nth(1).fill('# e2e 课程简介\n\n由 dashboard-test 填写');
      await shot(page, '1.5-setting-filled');

      const put = page.waitForRequest(
        (r) => r.url().endsWith(`/api/v2/course/${courseId}`) && r.method() === 'PUT',
      );
      const saved = page.waitForResponse(
        (r) => r.url().endsWith(`/api/v2/course/${courseId}`) && r.request().method() === 'PUT',
      );
      await page.getByRole('button', { name: btnName('提交') }).click();
      const sent = (await put).postDataJSON();
      expect((await (await saved).json()).ok).toBe(true);
      expect(sent).toMatchObject({
        title,
        subtitle: 'e2e 副标题',
        teacher_id: teacher.id,
        number: 5,
        sign_up_start_at: expect.stringContaining(ymd(-1)),
        sign_up_end_at: expect.stringContaining(ymd(7)),
        start_at: expect.stringContaining(ymd(3)),
        end_at: expect.stringContaining(ymd(30)),
        anthology_id: COURSE_TEXTBOOK,
        channel_id: COURSE_ANSWER_CHANNEL,
        publicity: 30,
        join: 'manual',
        sign_up_message: 'e2e 报名须知：请先读完第一单元',
      });
      expect(sent.content).toContain('e2e 课程简介');
      await expect(page.locator('.ant-message-success')).toBeVisible();
      log.dump('1.5 保存设置');
    });

    await test.step('1.5 刷新后回显', async () => {
      await page.reload();
      await expect(formItem(page, '副标题').locator('input')).toHaveValue('e2e 副标题');
      await expect(formItem(page, '招生数量').locator('input')).toHaveValue('5');
      await expect(formItem(page, '课本')).toContainText(textbookTitle);
      await expect(formItem(page, '标准答案')).toContainText(answer.name);
      await expect(formItem(page, '录取方式')).toContainText('人工审核');
      await expect(formItem(page, '报名时间').locator('input').nth(0)).toHaveValue(ymd(-1));
      await shot(page, '1.5-setting-reloaded');
    });

    await test.step('1.6 公开性 = 私有 → 只能选「仅限邀请」；公开 →「仅限邀请」不可选', async () => {
      await formItem(page, '录取方式').locator('.ant-select').click();
      const opts = page.locator('.ant-select-dropdown:not(.ant-select-dropdown-hidden) .ant-select-item-option');
      await expect(opts.filter({ hasText: '仅限邀请' })).toHaveClass(/disabled/);
      await expect(opts.filter({ hasText: '开放自学' })).not.toHaveClass(/disabled/);
      await page.keyboard.press('Escape');

      await pick(page, '公开性', /^私有$/);
      await formItem(page, '录取方式').locator('.ant-select').click();
      await expect(opts.filter({ hasText: '仅限邀请' })).not.toHaveClass(/disabled/);
      await expect(opts.filter({ hasText: '人工审核' })).toHaveClass(/disabled/);
      await expect(opts.filter({ hasText: '开放自学' })).toHaveClass(/disabled/);
      await page.keyboard.press('Escape');
      // 不保存：恢复成服务器上的值
      await page.reload();
    });

    await test.step('1.7 详情页：标题、时间、状态标签、录取方式、主讲人、简介、课本目录', async () => {
      await openDetail(page, courseId);
      await expect(page.locator('h3', { hasText: title })).toBeVisible();
      await expect(page.locator('h5', { hasText: 'e2e 副标题' })).toBeVisible();
      const signUpLine = page.locator('.ant-space').filter({ hasText: /^报名时间:/ }).last();
      await expect(signUpLine).toContainText(`${ymd(-1)}——${ymd(7)}`);
      await expect(signUpLine.locator('.ant-tag')).toHaveText('可报名');
      const courseLine = page.locator('.ant-space').filter({ hasText: /^课程时间:/ }).last();
      await expect(courseLine).toContainText(`${ymd(3)}——${ymd(30)}`);
      await expect(courseLine.locator('.ant-tag')).toHaveText('未开始');
      await expect(page.getByText('本课程为报名后人工审核课程')).toBeVisible();
      await expect(page.locator('.ant-space').filter({ hasText: /^主讲人：/ }).last()).toContainText(
        teacher.nickName,
      );
      await expect(page.locator('h1', { hasText: 'e2e 课程简介' })).toBeVisible();
      // 课本目录（AnthologyDetail）里能看到要用的那一课
      const article = (await call(request, 'GET', `/v2/article/${COURSE_ARTICLE}`)).body.data;
      await expect(page.getByText(article.title, { exact: true }).first()).toBeVisible();
      await expect(page).toHaveTitle(new RegExp(title));
      await shot(page, '1.7-detail');
      const problems = courseProblems(log.all);
      log.dump('1.7 详情页');
      expect(problems).toEqual([]);
    });

    await test.step('1.8 列表行「删除」→ 确认 → 消失', async () => {
      await page.goto('workspace/course');
      const row = page.locator('tr.ant-table-row').filter({ hasText: title });
      await row.locator('button:has(.anticon-ellipsis)').hover();
      await page
        .locator('.ant-dropdown:not(.ant-dropdown-hidden) .ant-dropdown-menu-item')
        .filter({ hasText: '删除' })
        .click();
      await expect(page.locator('.ant-modal-confirm-content')).toHaveText(title);
      const deleted = page.waitForResponse(
        (r) => r.url().endsWith(`/api/v2/course/${courseId}`) && r.request().method() === 'DELETE',
      );
      await page.locator('.ant-modal-confirm-btns .ant-btn-dangerous').click();
      expect((await (await deleted).json()).ok).toBe(true);
      await expect(row).toHaveCount(0);
      const gone = await call(request, 'GET', `/v2/course/${courseId}`);
      expect(gone.body?.ok ?? false).toBe(false);
      await shot(page, '1.8-deleted');
      log.dump('1.8 删除');
    });
  });

  test('1.9 不存在的课程：不应出现 Unexpected Application Error（BUG-C1）', async ({ page }) => {
    // BUG-C1：courseLoader 在 v2 返回 404 时 throw new Response(…)，路由没有 errorElement 接住
    await page.goto('workspace/course/00000000-0000-4000-8000-000000000000');
    await page.waitForLoadState('networkidle');
    await shot(page, '1.9-not-found');
    await expect(page.getByText('Unexpected Application Error')).toHaveCount(0);
  });
});

// ═══════════════════════════════════════════════════════════════════════════
// 二、人工审核课程
// ═══════════════════════════════════════════════════════════════════════════

test.describe('二、人工审核课程：报名 / 取消 / 录取 / 不录取 / 清退 / 退出', () => {
  test.skip(!HAS_STUDENT, '未配置学生账号 E2E_STUDENT_USER / E2E_STUDENT_PASS');

  test('2.1–2.9 报名 → 取消 → 再报名 → 录取 → 时间线 → 开课后清退', async ({ page, browser, request }) => {
    const student = await me(request, 'student');
    const { id, title } = await makeCourse(request, 'manual', {
      join: 'manual',
      signUp: [-1, 7],
      course: [3, 30],
      sign_up_message: 'e2e 报名须知：录取后请准时上课',
    });
    const s = await open(browser, 'student');
    const slog = collect(s.page);
    const log = collect(page);

    await test.step('2.1 报名期内、开课前：「可报名」「未开始」，只有「报名」', async () => {
      await openDetail(s.page, id);
      await expect(s.page.locator('.ant-space').filter({ hasText: /^报名时间:/ }).last().locator('.ant-tag')).toHaveText('可报名');
      await expect(s.page.locator('.ant-space').filter({ hasText: /^课程时间:/ }).last().locator('.ant-tag')).toHaveText('未开始');
      await expect(s.page.getByText('本课程为报名后人工审核课程')).toBeVisible();
      await expectButtons(s.page, ['报名']);
      await shot(s.page, '2.1-before-apply');
    });

    await test.step('2.2 报名：确认框显示报名消息 →「已经报名」，按钮「取消」', async () => {
      const m = await studentDo(s.page, '报名', 'e2e 报名须知：录取后请准时上课');
      expect(m).toMatchObject({ status: 'applied', role: 'student', user_id: student.id });
      await expect(s.page.getByText('已经报名', { exact: true })).toBeVisible();
      await expectButtons(s.page, ['取消']);
      await shot(s.page, '2.2-applied');
    });

    await test.step('2.3 取消 →「已经取消」，可再「报名」', async () => {
      expect((await studentDo(s.page, '取消')).status).toBe('canceled');
      await expect(s.page.getByText('已经取消', { exact: true })).toBeVisible();
      await expectButtons(s.page, ['报名']);
    });

    await test.step('2.4 再次报名 →「已经报名」', async () => {
      expect((await studentDo(s.page, '报名')).status).toBe('applied');
      await expect(s.page.getByText('已经报名', { exact: true })).toBeVisible();
    });

    await test.step('2.5 老师成员页：「录取」「不录取」可点、「清退」「撤销邀请」不可点 → 录取', async () => {
      await openMembers(page, id);
      const row = memberRow(page, student.nickName);
      await expect(row).toContainText('已经报名');
      await expect(row).toContainText('学生');
      await expect(row).toContainText('未绑定'); // 还没选作业 channel
      await expectMenu(page, student.nickName, ['录取', '不录取'], ['清退', '撤销邀请', '邀请']);
      await shot(page, '2.5-members-applied');
      expect((await managerDo(page, student.nickName, '录取')).status).toBe('accepted');
      await expect(memberRow(page, student.nickName)).toContainText('已录取');
      log.dump('2.5 录取');
    });

    await test.step('2.6 点该生 → 右侧「参课记录」列出全部历史', async () => {
      const timeline = page.waitForResponse((r) => r.url().includes('view=timeline'));
      await memberRow(page, student.nickName).click();
      const rows = (await (await timeline).json()).data.rows as { status: string }[];
      expect(rows.map((r) => r.status)).toEqual(
        expect.arrayContaining(['applied', 'canceled', 'accepted']),
      );
      expect(rows.length).toBeGreaterThanOrEqual(4);
      const panel = page.getByRole('tabpanel', { name: '参课记录' });
      await expect(panel.getByText('已录取').first()).toBeVisible();
      await expect(panel.getByText('已经取消').first()).toBeVisible();
      await shot(page, '2.6-timeline');
    });

    await test.step('2.7 学生详情页：「已录取」，只剩「退出」', async () => {
      await openDetail(s.page, id);
      await expect(s.page.getByText('已录取', { exact: true })).toBeVisible();
      await expectButtons(s.page, ['退出']);
    });

    await test.step('2.8 学生列表页「我参加的课程」：状态「已录取」，「退出」可点、「报名」不可点', async () => {
      const row = await listTab(s.page, '我参加的课程', title);
      await expect(row).toContainText('已录取');
      await expect(row).toContainText('人工审核');
      await row.locator('button:has(.anticon-ellipsis)').hover();
      const items = s.page.locator('.ant-dropdown:not(.ant-dropdown-hidden) .ant-dropdown-menu-item');
      await expect(items.filter({ hasText: /^退出$/ })).not.toHaveClass(/disabled/);
      await expect(items.filter({ hasText: /^报名$/ })).toHaveClass(/disabled/);
      await expect(items.filter({ hasText: /^参课$/ })).toHaveClass(/disabled/);
      await shot(s.page, '2.8-study-list');
      await s.page.mouse.move(0, 0);
    });

    await test.step('2.9 开课后：老师「清退」→ 学生「已清退」且没有按钮', async () => {
      await startCourse(request, id);
      await openMembers(page, id);
      await expectMenu(page, student.nickName, ['清退'], ['录取', '不录取']);
      expect((await managerDo(page, student.nickName, '清退')).status).toBe('blocked');
      await expect(memberRow(page, student.nickName)).toContainText('已清退');

      await openDetail(s.page, id);
      await expect(s.page.locator('.ant-space').filter({ hasText: /^课程时间:/ }).last().locator('.ant-tag')).toHaveText('进行中');
      await expect(s.page.getByText('已清退', { exact: true })).toBeVisible();
      await expect(statusButtons(s.page)).toHaveCount(0);
      await shot(s.page, '2.9-blocked');
    });

    const problems = [...courseProblems(log.all), ...courseProblems(slog.all)];
    log.dump('老师');
    slog.dump('学生');
    expect(problems).toEqual([]);
    await s.ctx.close();
  });

  test('2.10 不录取 → 学生「未录取」且不能再报名', async ({ page, browser, request }) => {
    const student = await me(request, 'student');
    const { id } = await makeCourse(request, 'reject', { join: 'manual', signUp: [-1, 7], course: [3, 30] });
    await seedMember(request, id, 'student', 'applied');

    await openMembers(page, id);
    expect((await managerDo(page, student.nickName, '不录取')).status).toBe('rejected');
    await expect(memberRow(page, student.nickName)).toContainText('未录取');

    const s = await open(browser, 'student');
    await openDetail(s.page, id);
    await expect(s.page.getByText('未录取', { exact: true })).toBeVisible();
    await expect(statusButtons(s.page)).toHaveCount(0);
    await shot(s.page, '2.10-rejected');
    await s.ctx.close();
  });

  test('2.11 录取后在开课前「退出」→「已退出」，可重新报名', async ({ browser, request }) => {
    const { id } = await makeCourse(request, 'leave', { join: 'manual', signUp: [-1, 7], course: [3, 30] });
    const mid = await seedMember(request, id, 'student', 'applied');
    await setMemberStatus(request, mid, 'accepted');

    const s = await open(browser, 'student');
    await openDetail(s.page, id);
    expect((await studentDo(s.page, '退出', /要退出.*吗/)).status).toBe('left');
    await expect(s.page.getByText('已退出', { exact: true })).toBeVisible();
    await expectButtons(s.page, ['报名']);
    expect((await studentDo(s.page, '报名')).status).toBe('applied');
    await shot(s.page, '2.11-left-reapply');
    await s.ctx.close();
  });

  test('2.12 报名未开始：标签「未开始」，没有「报名」', async ({ browser, request }) => {
    const { id } = await makeCourse(request, 'signup-later', { join: 'manual', signUp: [2, 5], course: [6, 30] });
    const s = await open(browser, 'student');
    await openDetail(s.page, id);
    await expect(s.page.locator('.ant-space').filter({ hasText: /^报名时间:/ }).last().locator('.ant-tag')).toHaveText('未开始');
    await expect(statusButtons(s.page)).toHaveCount(0);
    await shot(s.page, '2.12-signup-not-started');
    await s.ctx.close();
  });

  test('2.13 报名已结束：标签「已结束」，没有「报名」', async ({ browser, request }) => {
    const { id } = await makeCourse(request, 'signup-closed', { join: 'manual', signUp: [-10, -5], course: [-4, 30] });
    const s = await open(browser, 'student');
    await openDetail(s.page, id);
    await expect(s.page.locator('.ant-space').filter({ hasText: /^报名时间:/ }).last().locator('.ant-tag')).toHaveText('已结束');
    await expect(s.page.locator('.ant-space').filter({ hasText: /^课程时间:/ }).last().locator('.ant-tag')).toHaveText('进行中');
    await expect(statusButtons(s.page)).toHaveCount(0);
    await shot(s.page, '2.13-signup-closed');
    await s.ctx.close();
  });

  test('2.14 名额已满：另一个人看到「名额已满」，没有「报名」', async ({ browser, request }) => {
    test.skip(!HAS_ASSISTANT, '需要第二个账号（借用助教账号当另一个报名者）');
    const { id } = await makeCourse(request, 'full', { join: 'manual', signUp: [-1, 7], course: [3, 30], number: 1 });
    await seedMember(request, id, 'student', 'applied');
    const other = await open(browser, 'assistant');
    await openDetail(other.page, id);
    await expect(other.page.getByText('名额已满')).toBeVisible();
    await expect(statusButtons(other.page)).toHaveCount(0);
    await shot(other.page, '2.14-full');
    await other.ctx.close();
  });
});

// ═══════════════════════════════════════════════════════════════════════════
// 三、邀请制课程
// ═══════════════════════════════════════════════════════════════════════════

test.describe('三、邀请制课程：邀请 / 拒绝 / 撤销 / 接受 + 助教', () => {
  test.skip(!HAS_STUDENT || !HAS_ASSISTANT, '需要学生与助教账号');

  test('3.1–3.7 老师邀请学生与助教，学生拒绝 / 被撤销 / 接受，助教接受', async ({ page, browser, request }) => {
    const student = await me(request, 'student');
    const assistant = await me(request, 'assistant');
    const { id, title } = await makeCourse(request, 'invite', { join: 'invite', signUp: [-1, 7], course: [3, 30] });
    const log = collect(page);
    const s = await open(browser, 'student');
    const a = await open(browser, 'assistant');

    await test.step('3.1 老师「邀请」学生：三步弹窗 → 成员列表「已经邀请」', async () => {
      await openMembers(page, id);
      await shot(page, '3.1-members-empty');
      await invite(page, student, '学生');
      await expect(memberRow(page, student.nickName)).toContainText('已经邀请');
      await expectMenu(page, student.nickName, ['撤销邀请'], ['录取', '清退']);
      log.dump('3.1 邀请学生');
    });

    await test.step('3.2 邀请助教 →「助理老师」「已经邀请」', async () => {
      await invite(page, assistant, '助教');
      const row = memberRow(page, assistant.nickName);
      await expect(row).toContainText('助理老师');
      await expect(row).toContainText('已经邀请');
      await shot(page, '3.2-invited');
    });

    await test.step('3.3 学生「拒绝邀请」→「已拒绝邀请」', async () => {
      await openDetail(s.page, id);
      await expect(s.page.getByText('本课程仅限内部邀请')).toBeVisible();
      await expect(s.page.getByText('已经邀请', { exact: true })).toBeVisible();
      await expectButtons(s.page, ['接受邀请', '拒绝邀请']);
      expect((await studentDo(s.page, '拒绝邀请')).status).toBe('disagreed');
      await expect(s.page.getByText('已拒绝邀请', { exact: true })).toBeVisible();
      await shot(s.page, '3.3-disagreed');
    });

    await test.step('3.4 老师再邀请（弹窗提示当前状态）→ 撤销邀请 → 学生「已经撤销邀请」', async () => {
      await openMembers(page, id);
      await expect(memberRow(page, student.nickName)).toContainText('已拒绝邀请');
      await invite(page, student, '学生', /disagreed/);
      await expect(memberRow(page, student.nickName)).toContainText('已经邀请');
      expect((await managerDo(page, student.nickName, '撤销邀请')).status).toBe('revoked');
      await expect(memberRow(page, student.nickName)).toContainText('已经撤销邀请');

      await openDetail(s.page, id);
      await expect(s.page.getByText('已经撤销邀请', { exact: true })).toBeVisible();
      await expect(statusButtons(s.page).filter({ hasText: btnName('接受邀请') })).toHaveCount(0);
      await shot(s.page, '3.4-revoked');
    });

    await test.step('3.5 第三次邀请 → 学生「接受邀请」→「已接受邀请」，按钮「退出」', async () => {
      await invite(page, student, '学生', /revoked/);
      await openDetail(s.page, id);
      expect((await studentDo(s.page, '接受邀请')).status).toBe('agreed');
      await expect(s.page.getByText('已接受邀请', { exact: true })).toBeVisible();
      await expectButtons(s.page, ['退出']);
      const row = await listTab(s.page, '我参加的课程', title);
      await expect(row).toContainText('已接受邀请');
      await shot(s.page, '3.5-agreed');
    });

    await test.step('3.6 助教「接受邀请」→「我任教的课程」有这门课，带「设置」链接', async () => {
      await openDetail(a.page, id);
      expect((await studentDo(a.page, '接受邀请')).status).toBe('agreed');
      const row = await listTab(a.page, '我任教的课程', title);
      await expect(row).toContainText('已接受邀请');
      await expect(row).toContainText('助理老师');
      await expect(row.getByRole('link', { name: '设置' })).toBeVisible();
      await shot(a.page, '3.6-teach-list');

      await openMembers(page, id);
      await expect(memberRow(page, assistant.nickName)).toContainText('已接受邀请');
      await expect(memberRow(page, student.nickName)).toContainText('已接受邀请');
      await shot(page, '3.6-members-final');
    });

    await test.step('3.7 导出成员列表：老师下载到 xlsx', async () => {
      const link = page.locator('a[href*="course-member-export"]');
      await expect(link).toHaveAttribute('href', new RegExp(`course_id=${id}`));
      const res = await page.request.get(new URL(`/api/v2/course-member-export?course_id=${id}`, BASE).toString());
      expect(res.status()).toBe(200);
      expect(res.headers()['content-disposition']).toContain('course_member.xlsx');
    });

    const problems = courseProblems(log.all);
    log.dump('3 老师');
    expect(problems).toEqual([]);
    await s.ctx.close();
    await a.ctx.close();
  });
});

// ═══════════════════════════════════════════════════════════════════════════
// 四、开放自学课程
// ═══════════════════════════════════════════════════════════════════════════

test.describe('四、开放自学课程：参课 / 退出 / 再参课', () => {
  test.skip(!HAS_STUDENT, '未配置学生账号');

  test('4.1–4.3 参课 → 退出 → 再参课 → 我参加的课程', async ({ browser, request }) => {
    const { id, title } = await makeCourse(request, 'open', { join: 'open', signUp: [-1, 30], course: [-1, 30] });
    const s = await open(browser, 'student');
    const slog = collect(s.page);

    await test.step('4.1 开课中「参课」→「已经参加了」，按钮「退出」', async () => {
      await openDetail(s.page, id);
      await expect(s.page.getByText('本课程为开放课程')).toBeVisible();
      await expectButtons(s.page, ['参课']);
      expect((await studentDo(s.page, '参课', /自学课程/)).status).toBe('joined');
      await expect(s.page.getByText('已经参加了', { exact: true })).toBeVisible();
      await expectButtons(s.page, ['退出']);
      await shot(s.page, '4.1-joined');
    });

    await test.step('4.2 「退出」→「已退出」→ 再「参课」', async () => {
      expect((await studentDo(s.page, '退出')).status).toBe('left');
      await expect(s.page.getByText('已退出', { exact: true })).toBeVisible();
      await expectButtons(s.page, ['参课']);
      expect((await studentDo(s.page, '参课')).status).toBe('joined');
      await expect(s.page.getByText('已经参加了', { exact: true })).toBeVisible();
    });

    await test.step('4.3 「我参加的课程」有这门课', async () => {
      const row = await listTab(s.page, '我参加的课程', title);
      await expect(row).toContainText('已经参加了');
      await expect(row).toContainText('开放自学');
      await shot(s.page, '4.3-study-list');
    });

    const problems = courseProblems(slog.all);
    slog.dump('4 学生');
    expect(problems).toEqual([]);
    await s.ctx.close();
  });
});

// ═══════════════════════════════════════════════════════════════════════════
// 五、/library/course
// ═══════════════════════════════════════════════════════════════════════════

test.describe('五、/library/course 公开课程栏目', () => {
  let openCourse = { id: '', title: '' };
  let privateCourse = { id: '', title: '' };
  let pastCourse = { id: '', title: '' };

  test.beforeAll(async ({ request }) => {
    openCourse = await makeCourse(request, 'library-open', { join: 'open', signUp: [-1, 30], course: [-1, 30] });
    privateCourse = await makeCourse(request, 'library-private', {
      join: 'invite',
      publicity: 10,
      signUp: [-1, 30],
      course: [-1, 30],
    });
    pastCourse = await makeCourse(request, 'library-history', { join: 'manual', signUp: [-30, -21], course: [-20, -1] });
  });

  test('5.1–5.3 栏目首页：统计条、最新课程、开放报名；私有课程不出现', async ({ page }) => {
    const log = collect(page);
    await page.goto(new URL('course', LIBRARY).toString());

    await test.step('5.1 统计条三个数字', async () => {
      const stats = page.locator('.course-stat__value');
      await expect(stats).toHaveCount(3);
      for (const v of await stats.allTextContents()) expect(v.trim()).toMatch(/^\d+$/);
    });

    await test.step('5.2 新课同时出现在「最新课程」与「开放报名」，链接到 v6 详情页', async () => {
      const card = page.locator('.course-card').filter({ hasText: openCourse.title });
      await expect(card).toBeVisible();
      await expect(card).toHaveAttribute('href', new RegExp(`/workspace/course/${openCourse.id}$`));
      const row = page.locator('.course-row').filter({ hasText: openCourse.title });
      await expect(row).toBeVisible();
      await expect(row.locator('.course-badge--open')).toBeVisible();
      await expect(row.locator('.course-row__cta')).toBeVisible();
      await expect(row).toContainText('library-open 摘要');
      await shot(page, '5.2-library');

      // 链接在 dashboard-v6 那边能打开（老师已登录）
      const href = (await row.getAttribute('href'))!;
      const v6 = new URL(href).pathname.replace(/^.*?\/workspace\//, 'workspace/');
      await openDetail(page, openCourse.id);
      expect(href).toContain(v6);
      await expect(page.locator('h3', { hasText: openCourse.title })).toBeVisible();
    });

    await test.step('5.3 私有课程不出现', async () => {
      await page.goto(new URL('course', LIBRARY).toString());
      await expect(page.getByText(privateCourse.title)).toHaveCount(0);
    });

    const problems = log.all.filter((l) => l.startsWith('[PAGEERROR]') || /HTTP 5\d\d/.test(l));
    log.dump('5 library');
    expect(problems).toEqual([]);
  });

  test('5.4 已结课的公开课程进「历史课程」，不在「开放报名」', async ({ page }) => {
    await page.goto(new URL('course', LIBRARY).toString());
    const sections = page.locator('.lib-section');
    await expect(sections.nth(1).getByText(pastCourse.title)).toHaveCount(0);
    await expect(sections.nth(2).locator('.course-row').filter({ hasText: pastCourse.title })).toBeVisible();

    await page.goto(new URL('course/history', LIBRARY).toString());
    const row = page.locator('.course-row').filter({ hasText: pastCourse.title });
    await expect(row).toBeVisible();
    await expect(row.locator('.course-badge--closed')).toBeVisible();
    await expect(row.locator('.course-row__cta')).toHaveCount(0);
    await expect(page.getByText(openCourse.title)).toHaveCount(0);
    await shot(page, '5.4-history');
  });
});

// ═══════════════════════════════════════════════════════════════════════════
// 六、教材与作业
// ═══════════════════════════════════════════════════════════════════════════

test.describe('六、教材：选作业版本 / 做作业 / 对答案 / 助教看作业', () => {
  test.skip(!HAS_STUDENT || !HAS_ASSISTANT, '需要学生与助教账号');

  let course = { id: '', title: '' };
  let homework = ''; // 学生的作业 channel
  const textbook = () => `workspace/course/${course.id}/textbook/${COURSE_ARTICLE}`;

  test.beforeAll(async ({ request }) => {
    course = await makeCourse(request, 'textbook', { join: 'open', signUp: [-1, 30], course: [-1, 30] });
    await seedMember(request, course.id, 'student', 'joined');
    const aid = await seedMember(request, course.id, 'assistant', 'invited', 'assistant');
    await setMemberStatus(request, aid, 'agreed', 'assistant');

    const student = await me(request, 'student');
    const ch = await call(
      request,
      'POST',
      '/v2/channel',
      { name: `${STAMP}-作业`, type: 'translation', lang: 'zh-Hans', studio: student.userName },
      'student',
    );
    expect(ch.body?.ok, JSON.stringify(ch.body)).toBe(true);
    // v2 的新建响应里没有 uid（uid 是库里默认值生成的），按名字回查
    const mine = await call(request, 'GET', `/v2/channel?view=studio&name=${student.userName}&limit=1000`, undefined, 'student');
    homework = (mine.body.data.rows as { uid: string; name: string }[]).find((c) => c.name === `${STAMP}-作业`)!.uid;
    expect(homework).toBeTruthy();
    created.push(`学生作业 channel ${STAMP}-作业  ${homework}`);
  });

  test('6.1–6.6 学生：进课文 → 选作业版本 → 做逐词作业 → 对答案', async ({ browser, request }) => {
    const s = await open(browser, 'student');
    const page = s.page;
    const log = collect(page);
    const article = (await call(request, 'GET', `/v2/article/${COURSE_ARTICLE}`)).body.data;

    await test.step('6.1 从详情页的课本目录点进课文', async () => {
      await openDetail(page, course.id);
      await page.getByText(article.title, { exact: true }).first().click();
      await expect(page).toHaveURL(new RegExp(`workspace/course/${course.id}/textbook/${COURSE_ARTICLE}`));
      await expect(page).toHaveURL(/mode=read/);
    });

    await test.step('6.2 提示「请选择作业的存放位置」→ 选自己的 channel → 刷新后提示消失', async () => {
      const alert = page.locator('.ant-alert').filter({ hasText: '请选择作业的存放位置' });
      await expect(alert).toBeVisible();
      await shot(page, '6.2-select-channel');
      await alert.getByRole('button', { name: btnName('选择') }).click();
      const modal = page.locator('.ant-modal').filter({ hasText: '版本风格' });
      await modal.locator('.ant-select').click();
      await page
        .locator('.ant-select-dropdown:visible:not([class*="-leave"]) .ant-select-item-option')
        .filter({ hasText: `${STAMP}-作业` })
        .click();
      await expect(modal.locator('.ant-select')).toContainText(`${STAMP}-作业`);
      const put = page.waitForRequest((r) => r.url().includes('/api/v2/course-member_set-channel'));
      const res = page.waitForResponse((r) => r.url().includes('/api/v2/course-member_set-channel'));
      await modal.getByRole('button', { name: /确 ?[定认]/ }).click();
      expect((await put).postDataJSON()).toMatchObject({ course_id: course.id, channel_id: homework });
      expect((await res).status()).toBe(200);
      // 成功后页面会 window.location.reload()，响应体读不到了：直接查后端
      const student = await me(request, 'student');
      await expect.poll(async () => (await memberOf(request, course.id, student.id))?.channel_id).toBe(homework);
      await page.waitForLoadState('load'); // onSelected → window.location.reload()
      await expect(page.getByText('请选择作业的存放位置')).toHaveCount(0);
    });

    await test.step('6.3 学生视角：角色「学生」，channel = 自己的作业 channel', async () => {
      await expect(page.locator('.ant-tag', { hasText: /^学生$/ })).toBeVisible();
      await expect.poll(() => new URL(page.url()).searchParams.get('channel')).toBe(homework);
      await shot(page, '6.3-student-textbook');
    });

    await test.step('6.5 做作业：逐词模式点一个词 → 填含义 → 保存进学生 channel', async () => {
      await page.goto(`${textbook()}?mode=wbw&course=${course.id}`);
      const word = page.locator('.pali_shell_spell').filter({ hasText: new RegExp(`^${COURSE_WORD}$`, 'i') }).first();
      // 课文里的逐词是 edit 样式：点词只会查字典，编辑弹窗要点词上方的铅笔（鼠标移上去才出现）
      await word.hover();
      await word.locator('.edit_icon').click();
      const detail = page.locator('.wbw_detail').first();
      await expect(detail).toBeVisible();
      await detail.locator('#basic_meaning input, input#basic_meaning').first().fill(`e2e-${STAMP}`);
      const saved = page.waitForRequest((r) => r.url().endsWith('/api/v2/wbw') && r.method() === 'POST');
      const res = page.waitForResponse((r) => r.url().endsWith('/api/v2/wbw') && r.request().method() === 'POST');
      await detail.getByRole('button', { name: /保 ?存/ }).first().click();
      expect((await saved).postDataJSON().channel_id).toBe(homework);
      const body = await (await res).json();
      expect(body.ok, JSON.stringify(body)).toBe(true);
      await expect(page.getByText(`e2e-${STAMP}`).first()).toBeVisible();
      await shot(page, '6.5-homework');
      log.dump('6.5 做作业');
    });

    await test.step('6.6 对答案：逐词菜单「显示/隐藏错误提示」→ 拉取课程标准答案', async () => {
      const answer = page.waitForResponse(
        (r) => r.url().includes('/api/v2/wbw-sentence?view=course-answer') && r.url().includes(course.id),
      );
      await page.locator('.layout-h button:has(.anticon-more)').first().hover();
      await page
        .locator('.ant-dropdown:not(.ant-dropdown-hidden) .ant-dropdown-menu-item')
        .filter({ hasText: '显示/隐藏错误提示' })
        .click();
      const body = await (await answer).json();
      expect(body.ok).toBe(true);
      expect(body.data.count).toBeGreaterThan(0);
      await shot(page, '6.6-check-answer');
    });

    const problems = courseProblems(log.all);
    log.dump('6 学生');
    expect(problems).toEqual([]);
    await s.ctx.close();
  });

  test('6.7–6.8 助教：看标准答案与学生作业', async ({ browser, request }) => {
    const student = await me(request, 'student');
    const a = await open(browser, 'assistant');
    const page = a.page;
    const log = collect(page);

    await test.step('6.7 角色「助理老师」，channel = 标准答案，拉到成员名单', async () => {
      const members = page.waitForResponse((r) => r.url().includes('/api/v2/course-member?view=course'));
      await page.goto(`${textbook()}?mode=read&course=${course.id}`);
      expect((await (await members).json()).ok).toBe(true);
      await expect(page.locator('.ant-tag', { hasText: /^助理老师$/ })).toBeVisible();
      await expect.poll(() => new URL(page.url()).searchParams.get('channel')).toBe(COURSE_ANSWER_CHANNEL);
      await expect(page.getByText('请选择作业的存放位置')).toHaveCount(0);
      await shot(page, '6.7-assistant-textbook');
    });

    await test.step('6.8 句子下「逐词解析」页签列出学生作业，并有「无作业：」名单', async () => {
      // 阅读模式的句子没有页签，切到编辑模式
      await page.goto(`${textbook()}?mode=edit&course=${course.id}`);
      const listed = page.waitForResponse(
        (r) => r.url().includes('/api/v2/wbw-sentence?view=sent-can-read') && r.url().includes(`course=${course.id}`),
      );
      // 页签是 compact 模式：鼠标移到 .sent_tabs 上才可见
      const tabs = page.locator('.sent_tabs').first();
      await tabs.hover();
      await tabs.locator('.ant-tabs-tab').filter({ hasText: '逐词解析' }).click();
      const body = await (await listed).json();
      expect(body.ok).toBe(true);
      const channels = (body.data.rows as { origin?: { channel: { id: string } }[] }[]).map(
        (r) => r.origin?.[0]?.channel.id,
      );
      expect(channels).toContain(homework);
      await expect(page.getByText('无作业：').first()).toBeVisible();
      await expect(page.getByText(student.nickName).first()).toBeVisible();
      await shot(page, '6.8-assistant-homework');
    });

    const problems = courseProblems(log.all);
    log.dump('6 助教');
    expect(problems).toEqual([]);
    await a.ctx.close();
  });

  test('6.9 老师（拥有者）视角：角色「拥有者」', async ({ page }) => {
    await page.goto(`${textbook()}?mode=read&course=${course.id}`);
    await expect(page.locator('.ant-tag', { hasText: /^拥有者$/ })).toBeVisible();
    await expect.poll(() => new URL(page.url()).searchParams.get('channel')).toBe(COURSE_ANSWER_CHANNEL);
  });

  // 6.4 与 6.10 是缺陷断言（红灯）。放在本组最后：用例失败会让 Playwright 重启 worker 并重跑 beforeAll，
  // 新建一门没有作业的课，排在它们后面的 6.7–6.8 就看不到学生作业了
  test('6.4 课文顶部「课程：」链接指向 v6 存在的页面（BUG-C2）', async ({ browser }) => {
    const s = await open(browser, 'student');
    await s.page.goto(`${textbook()}?mode=read&course=${course.id}`);
    // 面包屑里也有同名链接（那个是对的），这里只看正文顶部「课程：」后面那个
    const link = s.page.getByRole('main').getByRole('link', { name: course.title });
    await expect(link).toBeVisible();
    // BUG-C2：TypeCourse.tsx 链到 v4 的 /course/show/:id，v6 没有这个路由
    await expect(link).toHaveAttribute('href', new RegExp(`workspace/course/${course.id}$`));
    await s.ctx.close();
  });

  test('6.10 非成员打开课文：应给出提示而不是一直 loading（BUG-C3）', async ({ browser, request }) => {
    // 另一门课，学生不是成员
    const other = await makeCourse(request, 'textbook-outsider', { join: 'manual', signUp: [-1, 7], course: [3, 30] });
    const s = await open(browser, 'student');
    const curr = s.page.waitForResponse((r) => r.url().includes('/api/v2/course-curr'));
    await s.page.goto(`workspace/course/${other.id}/textbook/${COURSE_ARTICLE}?mode=read`);
    expect((await (await curr).json()).ok).toBe(false); // not member
    await s.page.waitForTimeout(3000);
    await shot(s.page, '6.10-outsider');
    // BUG-C3：TypeCourse 在 course-curr 失败时 currUser 永远为空，整页只剩 "loading"
    await expect(s.page.getByText(/^loading$/)).toHaveCount(0);
    await s.ctx.close();
  });
});

// ═══════════════════════════════════════════════════════════════════════════
// 七、权限
// ═══════════════════════════════════════════════════════════════════════════

test.describe('七、权限', () => {
  test.skip(!HAS_STUDENT, '未配置学生账号');

  let course = { id: '', title: '' };
  let studentMember = '';

  test.beforeAll(async ({ request }) => {
    course = await makeCourse(request, 'power', { join: 'manual', signUp: [-1, 7], course: [3, 30] });
    studentMember = await seedMember(request, course.id, 'student', 'applied');
    if (HAS_ASSISTANT) {
      const aid = await seedMember(request, course.id, 'assistant', 'invited', 'assistant');
      await setMemberStatus(request, aid, 'agreed', 'assistant');
    }
  });

  test('7.1–7.2 助教：设置页保存被拒，成员页没有操作菜单', async ({ browser, request }) => {
    test.skip(!HAS_ASSISTANT, '未配置助教账号');
    const student = await me(request, 'student');
    const a = await open(browser, 'assistant');
    const page = a.page;

    await test.step('7.1 保存基本信息 → 403 → 页面提示失败', async () => {
      const loaded = page.waitForResponse((r) => r.url().endsWith(`/api/v2/course/${course.id}`));
      await page.goto(`workspace/course/${course.id}/setting`);
      await loaded;
      await formItem(page, '副标题').locator('input').fill('助教改的副标题');
      const saved = page.waitForResponse(
        (r) => r.url().endsWith(`/api/v2/course/${course.id}`) && r.request().method() === 'PUT',
      );
      await page.getByRole('button', { name: btnName('提交') }).click();
      expect((await saved).status()).toBe(403);
      await expect(page.locator('.ant-message-success')).toHaveCount(0);
      const after = (await call(request, 'GET', `/v2/course/${course.id}`)).body.data;
      expect(after.subtitle).not.toBe('助教改的副标题');
      await shot(page, '7.1-assistant-save');
    });

    await test.step('7.2 成员页没有操作菜单', async () => {
      await openMembers(page, course.id);
      const row = memberRow(page, student.nickName);
      await expect(row).toContainText('已经报名');
      await expect(row.locator('button:has(.anticon-ellipsis)')).toHaveCount(0);
      await shot(page, '7.2-assistant-members');
    });
    await a.ctx.close();
  });

  test('7.3 学生不能修改、删除课程', async ({ request }) => {
    const put = await call(request, 'PUT', `/v2/course/${course.id}`, { ...settings.get(course.id), subtitle: '学生改的' }, 'student');
    expect(put.status).toBe(403);
    const del = await call(request, 'DELETE', `/v2/course/${course.id}`, undefined, 'student');
    expect(del.body.ok).toBe(false);
    expect((await call(request, 'GET', `/v2/course/${course.id}`)).body.ok).toBe(true);
  });

  test('7.4 学生不能把自己改成「已录取」（BUG-C4）', async ({ request }) => {
    const student = await me(request, 'student');
    // BUG-C4：CourseMemberController@update 不校验操作者，学生可以把自己的 applied 改成 accepted
    const r = await call(request, 'PUT', `/v2/course-member/${studentMember}`, { status: 'accepted' }, 'student');
    const now = await memberOf(request, course.id, student.id);
    if (now?.status === 'accepted') {
      // 已被改掉：恢复成 applied，免得影响别的用例 / 人工查看
      await setMemberStatus(request, now.id, 'applied');
    }
    expect(r.body?.ok).toBe(false);
    expect(now?.status).toBe('applied');
  });

  test('7.5 学生不能以「邀请」把别人拉进课程（BUG-C5）', async ({ request }) => {
    const teacher = await me(request, 'user');
    const { id } = await makeCourse(request, 'power-invite', { join: 'manual', signUp: [-1, 7], course: [3, 30] });
    const outsider = HAS_ASSISTANT ? await me(request, 'assistant') : teacher;
    // BUG-C5：CourseMemberController@store 对 status=invited 不校验操作者是不是课程管理者
    const r = await call(
      request,
      'POST',
      '/v2/course-member',
      { user_id: outsider.id, course_id: id, role: 'manager', status: 'invited' },
      'student',
    );
    expect(r.body?.ok).toBe(false);
  });

  test('7.6 未登录不能导出成员名单（BUG-C6）', async ({ playwright }) => {
    const anon = await playwright.request.newContext();
    const res = await anon.get(new URL(`/api/v2/course-member-export?course_id=${course.id}`, BASE).toString());
    // BUG-C6：course-member-export 不做登录与权限校验，任何人能拿到成员昵称与用户名
    expect(res.headers()['content-disposition'] ?? '').not.toContain('course_member.xlsx');
    await anon.dispose();
  });
});
