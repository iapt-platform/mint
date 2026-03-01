# 前端开发规范

> React 18 + Ant Design v6 · 适用于 LLM 辅助代码重构

---

## 目录结构

```
src/
├── api/           # HTTP 请求层
├── types/         # TypeScript 类型定义
├── hooks/         # 业务逻辑 & 异步状态
├── services/      # 复杂业务逻辑（可选，简单项目可省略）
├── stores/        # 全局状态（Zustand / Jotai）
├── components/    # 通用 UI 组件（无业务逻辑）
├── features/      # 业务功能模块（胶水层）
├── pages/         # 路由入口（薄层）
├── layouts/       # 页面框架
└── utils/         # 纯工具函数
```

---

## 各层职责

| 目录          | 应该放                                                         | 不应该放                               |
| ------------- | -------------------------------------------------------------- | -------------------------------------- |
| `layouts/`    | 页面框架、导航、侧边栏、Header/Footer、Outlet                  | 业务逻辑、API 调用、具体页面内容       |
| `pages/`      | 路由入口、路由参数提取、权限守卫、页面 title、组合 features    | 业务逻辑、UI 细节、直接调 API          |
| `features/`   | 连接 hooks 与 components、业务交互回调、局部状态（筛选条件等） | 纯 UI 样式、直接 fetch、跨模块共享逻辑 |
| `components/` | 纯 UI 组件、样式、props 驱动的交互                             | API 调用、业务判断、store 依赖         |
| `api/`        | HTTP 请求函数、请求/响应结构映射、拦截器                       | 业务逻辑、UI 反馈、缓存管理            |
| `hooks/`      | 异步状态管理、业务逻辑封装、跨组件共享状态                     | JSX 渲染、直接操作 DOM、样式           |
| `services/`   | 复杂业务计算、多个 api 组合调用、领域逻辑                      | UI 反馈、React 相关代码                |
| `types/`      | 全局共享的 TS 类型、接口定义、枚举                             | 逻辑代码、默认值、工具函数             |
| `utils/`      | 纯函数工具（格式化、校验、日期处理）                           | 副作用、API 调用、React 代码           |

---

## 数据流向（单向）

```
用户操作
  → features/ 触发
    → hooks/ 处理逻辑
      → api/ 发请求
        → 响应回 hooks/（缓存/状态更新）
          → features/ 重渲染
            → components/ 展示结果
```

---

## components 与 features 的区别

**判断标准是与业务是否耦合，而不是复杂度。**

| 维度     | `components/`        | `features/`             |
| -------- | -------------------- | ----------------------- |
| 判断标准 | 不知道业务是什么     | 知道具体业务是什么      |
| 数据来源 | 只接收 props         | 自己调 hooks 取业务数据 |
| 可复用性 | 跨项目可复用         | 只在本项目有意义        |
| 复杂度   | 不限（可以非常复杂） | 不限                    |

```tsx
// ✅ components/ — 不知道「课程」是什么业务概念，换个项目也能用
<VideoPlayer attachmentId="abc123" />;

// ✅ features/ — 知道业务，负责注入业务回调
const CourseVideoPlayer = ({ lessonId }) => {
  const { attachmentId } = useLesson(lessonId); // 业务
  const { markCompleted } = useLessonProgress(lessonId); // 业务
  return <VideoPlayer attachmentId={attachmentId} onEnded={markCompleted} />;
};
```

> **原则**：`components/` 通过 props 暴露关键事件（`onEnded`、`onProgress`），`features/` 负责注入业务逻辑，保持 `components/` 干净可复用。

---

## hooks 放置规则

**核心问题：这个 hook 会不会在这个组件之外被用到？**

| 情况                                                     | 放哪里       |
| -------------------------------------------------------- | ------------ |
| 封装了业务 API 调用                                      | `src/hooks/` |
| 多个不相关的地方都会用                                   | `src/hooks/` |
| 只服务于某个组件族，外部不会用                           | 组件目录里   |
| 依赖组件内部 ref 或第三方库特定实例（playerRef、mapRef） | 组件目录里   |

```text
src/hooks/
  └── useAttachment.ts       ✅ 任何地方都可能用，与具体组件无关

components/VideoPlayer/core/
  ├── useVideoPlayer.ts      ✅ 依赖 video.js 实例，强绑定 VideoPlayer 内部
  └── useVideoControls.ts    ✅ 依赖 playerRef，离开组件没有意义
```

---

## 错误处理分层

```text
api 层              抛出结构化错误，不处理 UI
hook onError        处理业务特定错误（该 feature 内有特殊含义的错误码）
组件层 try/catch    处理仅影响当前组件交互的错误（如表单校验）
```

**决策树：**

```text
收到错误
  ├── 所有页面都一样处理？（401/403/500）
  │
  └── 只在这个业务场景特殊处理？
        ├── 影响整个 feature 的逻辑  → hook 的 onError
        └── 只影响当前组件交互       → 组件内 try/catch
```

**⚠️ antd v6 notification 问题**：`App.useApp()` 取到的实例无法在 QueryClient 回调中直接使用，需挂载单例：

```ts
// utils/antdStatic.ts
let _notification: NotificationInstance;
export const setNotification = (n: NotificationInstance) => {
  _notification = n;
};
export const staticNotification = {
  error: (args) => _notification?.error(args),
};

// layouts/AppInitializer.tsx（在 <App> 内部）
const { notification } = App.useApp();
useEffect(() => {
  setNotification(notification);
}, []);
```

---

## Ant Design v6 适配要点

**根节点配置：**

```tsx
// main.tsx
<ConfigProvider theme={theme} locale={zhCN}>
  <App>
    <QueryClientProvider client={queryClient}>
      <RouterProvider router={router} />
    </QueryClientProvider>
  </App>
</ConfigProvider>
```

**注意事项：**

- 使用 `App.useApp()` 替代 `message.xxx` / `Modal.confirm`（v6 静态方法已弃用）
- Form 数据保持在 Form 实例内，只有提交结果才流向业务层
- 复杂表格的 `columns` 单独抽成 `columns.tsx`，保持组件干净
- `ConfigProvider` 放顶层，统一管理 token、locale、theme

---

## 推荐技术栈

| 职责         | 方案                |
| ------------ | ------------------- |
| 全局同步状态 | redux               |
| 路由         | React Router v7     |
| HTTP 客户端  | fetch token、错误） |
| UI 组件库    | Ant Design v6       |

---

## 一句话总结

| 层            | 定义                                   |
| ------------- | -------------------------------------- |
| `api/`        | 只管收发 HTTP，不含任何判断            |
| `hooks/`      | 承载状态管理、缓存、错误处理，不含 JSX |
| `services/`   | 纯业务计算，与 UI/React 完全无关       |
| `components/` | 纯 UI，props 驱动，可跨项目复用        |
| `features/`   | 胶水层，把 hooks 的数据喂给 components |
| `pages/`      | 路由入口，组合 features，越薄越好      |
| `layouts/`    | 页面框架壳子，不知道任何业务           |
| `stores/`     | 只存真正需要跨模块共享的状态           |
| `utils/`      | 纯函数，无副作用，无 React             |
