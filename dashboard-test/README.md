# dashboard-test

`dashboard-v6` 的 Playwright 端到端测试。**本目录仅用于 dashboard 测试**，
不含任何应用代码，也不参与 dashboard-v6 的构建。

它替代的是「人工点页面 + 肉眼盯 console」的手工测试：脚本驱动真实浏览器操作页面，
自动采集 console 报错、未捕获异常、请求失败和 HTTP 4xx/5xx，并逐步截图。

设计取舍见 [`documents/development/testing-strategy.md`](./documents/development/testing-strategy.md)。

---

## 安装

```bash
cd dashboard-test
npm install
npx playwright install chromium
```

依赖只有两个，且都在 `devDependencies`：

| 包                | 用途                     |
| ----------------- | ------------------------ |
| `@playwright/test`| 测试运行器 + 浏览器驱动  |
| `@types/node`     | TypeScript 类型          |

## 前置：启动 dev server

测试直连本地 dev server，需先在**另外的终端**起好：

```bash
cd ../api-v13      && php artisan serve      # http://127.0.0.1:8000
cd ../dashboard-v6 && npm run dev            # http://127.0.0.1:4000/pcd-v2026/
```

## 运行

```bash
npm test                    # 跑全部
npm run test:channel        # 只跑 channel 路由
npm run test:ui             # UI Mode，可视化单步调试
npm run test:headed         # 显示浏览器窗口
npm run report              # 打开上次的 HTML 报告
npm run codegen             # 录制操作生成选择器
```

正式记录一次测试时，把截图输出到带时间戳的目录：

```bash
E2E_SHOT=documents/testlog/assets/channel_20260907_034904 npm run test:channel
```

### 环境变量

| 变量               | 默认值                              | 说明                         |
| ------------------ | ----------------------------------- | ---------------------------- |
| `E2E_BASE`         | `http://127.0.0.1:4000/pcd-v2026/`  | 被测站点根地址               |
| `E2E_USER`         | `test161`                           | 测试账号                     |
| `E2E_PASS`         | `12345`                             | 密码                         |
| `E2E_SHOT`         | `documents/testlog/assets/channel_latest` | 截图输出目录           |
| `E2E_DESTRUCTIVE`  | 未设置（执行）                      | 设为 `0` 跳过增删改用例      |

---

## 目录结构

目录布局参照 `dashboard-v6`：代码在 `src/`，文档在 `documents/`。

```
dashboard-test/
├── playwright.config.ts       配置：baseURL、串行执行、trace/video、setup 依赖
├── src/
│   ├── tests/
│   │   ├── auth.setup.ts      登录一次，存 storageState
│   │   └── channel.spec.ts    /workspace/channel 及子路由
│   └── lib/
│       └── collect.ts         console / pageerror / HTTP 错误采集与噪音过滤
├── documents/
│   ├── development/
│   │   └── testing-strategy.md   方案取舍与约定
│   └── testlog/
│       ├── channel_<时间戳>.md    每次测试的结论与待办
│       └── assets/<路由>_<时间戳>/ 该次测试的截图
├── playwright/.auth/          登录态缓存（gitignore）
├── playwright-report/         HTML 报告（gitignore）
└── test-results/              失败用例的 trace / video（gitignore）
```

## 登录

按 [官方推荐的 setup project 模式](https://playwright.dev/docs/auth)：
`src/tests/auth.setup.ts` 作为 `setup` 项目先跑一次登录，把会话存到
`playwright/.auth/user.json`；`chromium` 项目通过 `dependencies: ['setup']`
和 `storageState` 复用，所有用例都以已登录状态开始。

---

## 约定

### 一个路由一个 spec

`src/tests/<路由名>.spec.ts`，用 `test.describe` 按子路由分组，
`test.step` 对应测试日志里的编号步骤。

各 `describe` 相互独立（不用 `mode: 'serial'`），一个失败不阻断其余用例；
执行顺序由 `workers: 1` 保证。

### 已知缺陷写成断言

发现的 bug 直接写进断言，注释标明对应的 BUG 编号，**修复前保持红灯**。
当前 `src/tests/channel.spec.ts` 有两条这样的断言：

- BUG-1 Webhooks 页签不应跳到已废弃的 `/studio/...`
- BUG-4 不存在的频道不应出现 `Unexpected Application Error`

所以 `npm test` 现在是 **4 通过 / 2 失败**，两个失败即上述待修项。

### 破坏性操作

在开发机上**实际执行**（新建、保存、删除），但：

- 只操作脚本自建的临时数据，命名 `e2e-tmp-<timestamp>`
- 测试结束必须清理干净，日志中声明无残留
- 涉及第三方的操作（转让、分享授权）只验证弹窗渲染，不提交
- `E2E_DESTRUCTIVE=0` 可整体跳过

### 测试日志

每次正式测试在 `documents/testlog/` 下写一个
`<路由名>_<YYYYMMDD_HHMMSS>.md`，截图放同名 assets 目录并在 md 中内嵌引用。

固定结构：概览表 → 结论 → 一、通过的功能（含截图）→ 二、待修复
（每条一个 `- [ ]` 代办，含日志片段、截图、定位到文件行号的原因、修复方向）→
三、未覆盖 / 待确认。

已有记录：[`documents/testlog/channel_20260907_034904.md`](./documents/testlog/channel_20260907_034904.md)
