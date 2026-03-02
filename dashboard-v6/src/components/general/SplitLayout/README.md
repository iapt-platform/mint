# SplitLayout

可复用的左右分栏页面框架组件。基于 **React 18 + Ant Design v6 Splitter** 实现。

---

## 设计意图

### 核心问题

右侧内容区是**不确定的、可更换的**，但框架需要在左侧面板收起时，向右侧注入一个"展开按钮"。  
这是一个跨层通信问题：框架不能侵入右侧组件的内部结构，右侧组件也不应该依赖具体的框架实现。

### 解决方案：Render Props（方案 A）+ Context（方案 B）并存

| 方案 | 适用场景 | 特点 |
|------|----------|------|
| **方案 A** Render Props | 右侧组件接受 `expandButton` prop | 明确、类型安全，调用方控制放置位置 |
| **方案 B** Context Hook | 右侧深层组件自己取按钮 | 解耦、灵活，无需层层透传 |

框架对外同时支持两种用法，调用方按需选择。

### 布局结构

```
SplitLayout
├── Context.Provider          ← 提供 collapsed / toggle / expandButton
├── antd Splitter
│   ├── Panel left (可拖拽调宽)
│   │   ├── sidebarHeader
│   │   │   ├── sidebarTitle  ← 调用方传入（ReactNode）
│   │   │   └── 收起按钮      ← 始终在标题行右侧，collapsed=false 时可见
│   │   └── sidebarContent   ← 调用方传入 sidebar
│   └── Panel right
│       └── children          ← render props 或普通 ReactNode
```

### 展开/收起按钮的位置逻辑

```
collapsed = false（展开状态）:
  左侧面板正常显示
  sidebarHeader 右侧有「收起」按钮（MenuFoldOutlined）
  expandButton = null（右侧无需渲染任何东西）

collapsed = true（收起状态）:
  左侧面板宽度 → 0，内容隐藏（display: none）
  expandButton = 真实按钮节点（MenuUnfoldOutlined）
  右侧组件决定把它放在哪里（header 角落、工具栏等）
```

---

## 目录归属

```
src/components/SplitLayout/
├── SplitLayout.tsx          # 框架主组件 + Context + Hook
├── SplitLayout.module.css   # 样式（CSS Modules）
├── index.ts                 # 统一出口
└── README.md                # 本文件
```

> **归属原则**：`components/` — 纯 UI，无业务逻辑，跨项目可复用。  
> 右侧具体页面属于 `features/` 或 `pages/`，通过 render props 或 `useSplitLayout()` 取得 `expandButton`，自行决定渲染位置。

---

## API

### `<SplitLayout>` Props

| Prop | 类型 | 默认值 | 说明 |
|------|------|--------|------|
| `sidebarTitle` | `ReactNode` | — | 左侧面板标题区左侧内容 |
| `sidebar` | `ReactNode` | — | 左侧面板主体内容 |
| `children` | `ReactNode \| (ctx) => ReactNode` | — | 右侧内容，支持 render props |
| `defaultSidebarSize` | `number` | `240` | 左侧面板默认宽度（px） |
| `minSidebarSize` | `number` | `160` | 左侧面板最小宽度（px） |
| `maxSidebarSize` | `number` | `480` | 左侧面板最大宽度（px） |

### `useSplitLayout()` 返回值

| 字段 | 类型 | 说明 |
|------|------|------|
| `collapsed` | `boolean` | 当前是否已收起 |
| `toggle` | `() => void` | 切换收起/展开 |
| `expandButton` | `ReactNode` | 展开按钮节点，`collapsed=false` 时为 `null` |

> ⚠️ `useSplitLayout()` 必须在 `<SplitLayout>` 的子树内调用，否则抛出错误。

---

## 使用示例

### 方案 A：Render Props

右侧组件接受 `expandButton` 作为 prop，自行决定放置位置。  
**适合**：右侧组件较简单，可以接受外部注入的场景。

```tsx
// pages/DeployPage.tsx
import SplitLayout from "@/components/SplitLayout";
import FileTree from "@/features/deploy/FileTree";
import ContentArea from "@/features/deploy/ContentArea";

export default function DeployPage() {
  return (
    <SplitLayout
      sidebarTitle="mint / deploy"
      sidebar={<FileTree />}
    >
      {({ expandButton }) => (
        // expandButton 在收起时是真实按钮，展开时是 null
        // ContentArea 自己决定把它放在 header 的哪个位置
        <ContentArea headerExtra={expandButton} />
      )}
    </SplitLayout>
  );
}
```

```tsx
// features/deploy/ContentArea.tsx
interface ContentAreaProps {
  headerExtra?: ReactNode;
}

export default function ContentArea({ headerExtra }: ContentAreaProps) {
  return (
    <div>
      <header>
        <h2>deploy / group_vars</h2>
        {/* 收起状态下显示展开按钮，展开状态下此处为空 */}
        {headerExtra}
      </header>
      <main>{/* 内容 */}</main>
    </div>
  );
}
```

---

### 方案 B：useSplitLayout Hook

右侧深层组件直接从 Context 取按钮，无需层层透传 prop。  
**适合**：右侧组件层级复杂，或展开按钮需要放在深层子组件的场景。

```tsx
// pages/DeployPage.tsx
import SplitLayout from "@/components/SplitLayout";
import FileTree from "@/features/deploy/FileTree";
import ComplexContent from "@/features/deploy/ComplexContent";

export default function DeployPage() {
  return (
    <SplitLayout
      sidebarTitle="mint / deploy"
      sidebar={<FileTree />}
    >
      {/* 普通 ReactNode，ComplexContent 内部自己取 */}
      <ComplexContent />
    </SplitLayout>
  );
}
```

```tsx
// features/deploy/ComplexContent.tsx
import { useSplitLayout } from "@/components/SplitLayout";

export default function ComplexContent() {
  // 从 Context 直接取，不需要 prop 传递
  const { expandButton } = useSplitLayout();

  return (
    <div>
      <header>
        {expandButton}
        <nav>{/* 面包屑等 */}</nav>
      </header>
      <DeepChildComponent />
    </div>
  );
}
```

---

### 方案 A + B 混用

```tsx
// 某些场景下，既用 render props 传 expandButton，
// 右侧某个深层组件又需要知道 collapsed 状态
<SplitLayout sidebarTitle="Files" sidebar={<Tree />}>
  {({ expandButton }) => (
    <Layout headerExtra={expandButton}>
      {/* 这里的 DeepWidget 可以用 useSplitLayout() 取 collapsed */}
      <DeepWidget />
    </Layout>
  )}
</SplitLayout>
```

---

## 注意事项

1. **antd v6**：`Splitter` 是 v5.21+ 引入的组件，请确认版本 ≥ 5.21 或已使用 v6。
2. **高度**：`SplitLayout` 自身不设定高度，父容器需提供明确高度（如 `height: 100vh` 或 `height: 100%`）。
3. **CSS Modules**：样式通过 CSS Modules 隔离，不会污染全局。框架内的 antd token 变量（`--ant-color-*`）会自动跟随 `ConfigProvider` 的主题。
4. **收起时宽度**：`size={0}` + `display: none` 双重保护，避免内容溢出影响布局。
