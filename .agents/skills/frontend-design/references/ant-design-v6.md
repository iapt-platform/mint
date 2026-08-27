# Ant Design v6（dashboard-v6）

> dashboard-v6 用 **antd v6**（`antd` + `@ant-design/pro-components` + `@ant-design/charts`/`plots`）。核心：**用 ConfigProvider 的 theme 统一风格，不要在每个组件上写内联样式**。

## 统一主题（最重要）

在应用根节点用 `ConfigProvider` 设 token，全站一致：

```tsx
import { ConfigProvider, theme } from 'antd'

<ConfigProvider
  theme={{
    token: {
      colorPrimary: '#2563eb',
      borderRadius: 8,
      fontSize: 14,
      colorText: '#1f2937',
      colorTextSecondary: '#6b7280',
      colorBorder: '#e5e7eb',
    },
    components: {
      Table: { headerBg: '#f8fafc' },
      Card: { borderRadiusLG: 12 },
    },
  }}
>
  <App />
</ConfigProvider>
```

- 颜色、圆角、字号都在 token 层定义；单个组件的特殊样式用 `components.*` 覆盖，而非散落 `style={{...}}`。
- 主色、语义色（`colorSuccess` / `colorWarning` / `colorError` / `colorInfo`）与 `design-tokens.md` 对齐。

## ProComponents 用法

- `ProTable`：表格优先用它，自带查询表单、分页、工具栏；列定义里数字列用 `align: 'right'`。
- `ProForm`：表单优先用它，`ProFormText` / `ProFormSelect` 等；校验错误、必填标记自动处理。
- 不要为了「看起来高级」而手写原生 table/form 绕开 Pro 组件——那会破坏一致性。

## 图表（@ant-design/charts）

- 图表配色与主色一致，最多 5–6 种区分色，不要默认彩虹色。
- 数字轴右对齐、单位标注清楚；图例可读；空数据给占位提示。

```tsx
import { Column } from '@ant-design/plots'

<Column
  data={rows}
  xField="month"
  yField="value"
  color="#2563eb"
  columnStyle={{ radius: [6, 6, 0, 0] }}
/>
```

## 布局与间距

- 用 antd 的 `Space`（`size` 取 8 / 16 / 24）和 `Row/Col`（`gutter` 取 16 / 24）控制间距，不写零碎 `margin`。
- 卡片间距 `16`，区块间距 `24`，与 8pt 网格一致。
- 表格行高、弹窗宽度、按钮尺寸都用默认档位，不要逐处改。

## 状态与反馈

- 空状态：`Empty` + 一句说明 + 一个行动按钮，不要空白。
- 加载：`Skeleton` 或 `Spin`，表格用 `Table loading` / `ProTable` 自带。
- 错误：`Alert` 或 `Result status="error"` 说明「发生什么 + 怎么处理」。

## 一致性红线

- 同一页面圆角 / 阴影 / 图标风格统一（图标用 `@ant-design/icons`）。
- 别混用 antd 组件与手写 div 模拟相同语义（如用 div 造按钮）。
- 弹窗、抽屉、消息（`message` / `notification`）走 antd 全局实例，不自己写。
