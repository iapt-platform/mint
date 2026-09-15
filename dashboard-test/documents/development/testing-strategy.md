# 前端测试方案

> dashboard-v6 · React 19 + Vite + Ant Design · 记录于 2026-09-07
> 实现位于独立目录 `mint/dashboard-test`，运行方式见 [README](../../README.md)

---

## 目标

把「人工点页面 + 肉眼看 console」的手工测试，交给 AI 助手（Claude Code）在容器内自动完成，
同时**不改动 dashboard-v6 的依赖**——测试代码与依赖全部隔离在独立目录 `mint/dashboard-test`。

---

## 方案总览

| 层面       | 选择                                    | 说明                                    |
| ---------- | --------------------------------------- | --------------------------------------- |
| 浏览器驱动 | Playwright Test（headless chromium）    | 独立目录 `dashboard-test` 的 devDependencies |
| 运行位置   | 开发容器内                              | 与 dev server 同一网络，直连 127.0.0.1  |
| 交互方式   | `@playwright/test` spec 文件            | 而非 MCP 逐步操作，理由见下             |
| 采集内容   | console 日志、pageerror、HTTP 4xx/5xx、请求失败、截图 |                           |

### 为什么不用 Playwright MCP

MCP 每步一次工具调用，交互性确实更好，但有四个代价：

1. **上下文消耗大** —— 每步返回整页 accessibility snapshot，中等复杂度页面就是几万 token，
   点十次即耗尽上下文。脚本方式一次跑完只回来十几行日志。
2. **每步一次往返** —— 「登录 → 列表 → 筛选 → 详情 → 断言」= 5 次调用。测已知流程时纯浪费。
3. **不留资产** —— 点完即逝，下次重测要从头再点；脚本是文件，可重跑、可沉淀为回归测试。
4. **不确定性** —— 同样指令两次可能点到不同元素；脚本里的选择器是确定的。

**结论：探索性调试（不知道页面哪坏了、需要边看边试）可临时启用 MCP；
已知流程的重复验证一律用脚本。** 当前默认走脚本。

---

## 环境

### 依赖安装位置

测试是一个独立的 npm 工程 `mint/dashboard-test`，与 dashboard-v6 完全隔离：

```bash
cd dashboard-test
npm install                      # @playwright/test + @types/node，均在 devDependencies
npx playwright install chromium
```

dashboard-v6 的 `package.json`、`package-lock.json`、`node_modules` **零改动**。

### dev server 启动

由 AI 在后台启动，端口如下：

| 服务             | 命令                                             | 地址                                 |
| ---------------- | ------------------------------------------------ | ------------------------------------ |
| api-v13 后端     | `cd api-v13 && php artisan serve`                | http://127.0.0.1:8000                |
| api-v13 前端     | `cd api-v13 && npm run dev`                      | http://127.0.0.1:5173（占用则 5174） |
| **dashboard-v6** | `cd dashboard-v6 && npm run dev`                 | **http://127.0.0.1:4000/pcd-v2026/** |

---

## 实现要点

- **登录**：`src/tests/auth.setup.ts` 作为 setup project 跑一次登录，
  `storageState` 存到 `playwright/.auth/user.json`，各用例通过
  `dependencies: ['setup']` 复用（[官方推荐模式](https://playwright.dev/docs/auth)）。
- **日志采集**：`src/lib/collect.ts` 挂 `console` / `pageerror` / `requestfailed` /
  `response>=400`，并过滤掉 antd、React 开发态的已知废弃警告，
  只留真正值得看的内容。这是替代"人肉盯控制台"的关键。
- **已知缺陷写成断言**：发现的 bug 直接写进 `expect`，注释标注 BUG 编号，
  修复前保持红灯，修复后自动变绿——测试同时充当回归网。
- **失败留证**：`trace: 'retain-on-failure'` + `video: 'retain-on-failure'`，
  失败用例可用 `npx playwright show-trace` 逐帧回放。

---

## 目录约定

| 路径                                       | 内容                                       |
| ------------------------------------------ | ------------------------------------------ |
| `src/tests/<路由名>.spec.ts`                    | 一个路由一个 spec                          |
| `src/tests/auth.setup.ts`                       | 登录并保存 storageState                    |
| `src/lib/collect.ts`                            | 日志采集与噪音过滤                         |
| `README.md`                                     | 运行方式与环境变量                         |
| `documents/development/testing-strategy.md`     | 本文：方案取舍与约定                       |
| `documents/testlog/<路由名>_<时间戳>.md`        | 每次测试的日志，待修项用 `- [ ]` 代办标记  |
| `documents/testlog/assets/<路由名>_<时间戳>/`   | 该次测试的截图，在 md 中内嵌引用           |

目录布局参照 `dashboard-v6`：代码在 `src/`，文档在 `documents/`。

时间戳格式 `YYYYMMDD_HHMMSS`，例：`channel_20260907_034904.md`。

**测试日志固定结构**：概览表格 → 结论 → 一、通过的功能（含截图）→
二、待修复（每条一个 `- [ ]` 代办，含日志片段、截图、定位到文件行号的原因、修复方向）→
三、未覆盖（破坏性操作等）。

**破坏性操作**（新建、保存、删除）在开发机上实际执行。约定：
- 只操作脚本自己创建的临时数据，命名 `e2e-tmp-<timestamp>`
- 测试结束必须清理，日志中声明「未留残留数据」
- 用 `E2E_DESTRUCTIVE=0` 可跳过这部分
- 涉及第三方（转让、分享授权）的操作仍只验证弹窗渲染，列入「未覆盖」

---

## 待办

- [x] **登录态**：`harness.login()` 走一遍登录流程后用 `context.storageState()`
      存到 `.auth.json`（已 gitignore）复用。测试账号通过 `E2E_USER` / `E2E_PASS` 传入。
- [x] **脚本入库**：已迁到独立工程 `mint/dashboard-test`，随代码进 git，
      依赖隔离在自己的 `package.json`。
- [ ] **端口冲突**：5173 常被宿主机上已有的 dev server 占用，注意避免重复启动。

---

## 未来方向（需要时再动依赖）

若接受修改 `package.json`，业界主流组合为：

| 层面      | 方案                        | 备注                                          |
| --------- | --------------------------- | --------------------------------------------- |
| 单元/组件 | **Vitest** + React Testing Library | 已基本取代 Jest；browser mode 在真实浏览器跑组件测试 |
| E2E       | **Playwright**              | ✅ 已采用（本目录）                            |
| API mock  | **MSW**                     | 网络层拦截，单测与浏览器共用一套 handler      |
| 视觉回归  | Playwright `toHaveScreenshot()` | 需团队协作看 diff 才上 Chromatic / Percy  |

E2E 一层已落地。单元/组件测试要落地必须动 dashboard-v6 的 `package.json`，
待需要时再议。
