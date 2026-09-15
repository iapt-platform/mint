# Tailwind CSS v4 + React（api-v13 主方案）

> api-v13 用 Tailwind **v4**（不是 v3）。v4 是 CSS-first 配置，语法与 v3 有差异，务必按 v4 写法，别搬 v3 的 `tailwind.config.js` 老套路。

## v4 与 v3 的关键差异

- **无 `tailwind.config.js`**：v4 默认零配置，直接在 CSS 里配置。
- **入口**：`@import "tailwindcss";`
- **自定义 token**：用 `@theme` 定义设计变量，之后自动生成对应 utility。

```css
/* resources/css/app.css */
@import "tailwindcss";

@theme {
  --color-brand: #2563eb;
  --color-brand-hover: #1d4ed8;
  --color-brand-soft: #eff6ff;
  --radius-card: 10px;
}
```

- **自定义工具类**：用 `@utility` 而非 `@layer utilities`。
- **ring 语法**：v4 用 `ring` / `ring-2`，不再有 `ring-opacity-*`（用 `/50` 透明度写法）。
- **`outline-none` → `outline-hidden`**：隐藏焦点环改用 `outline-hidden`（`outline-none` 语义变了）。
- 动态 class 拼接（`bg-${color}`）在 v4 的 JIT 下不会生成——颜色必须写全称或通过 token。

## 项目里的组合工具

`class-variance-authority`（cva）+ `clsx` + `tailwind-merge` 是依赖里已装的，用它做**变体组件**：

```tsx
import { cva } from 'class-variance-authority'
import { cn } from '@/lib/cn' // clsx + tailwind-merge 封装

const button = cva('inline-flex items-center gap-2 rounded-md font-medium transition-colors', {
  variants: {
    variant: {
      primary: 'bg-brand text-white hover:bg-brand-hover',
      ghost: 'text-secondary hover:bg-slate-100',
      danger: 'bg-red-600 text-white hover:bg-red-700',
    },
    size: {
      sm: 'h-8 px-3 text-xs',
      md: 'h-10 px-4 text-sm',
    },
  },
  defaultVariants: { variant: 'primary', size: 'md' },
})

export function Button({ className, variant, size, ...props }) {
  return <button className={cn(button({ variant, size }), className)} {...props} />
}
```

## 常用漂亮组件配方

**卡片**

```tsx
<div className="rounded-md border border-slate-200 bg-white p-6">
  <h3 className="text-base font-semibold text-slate-800">标题</h3>
  <p className="mt-2 text-sm leading-6 text-slate-500">正文……</p>
  <div className="mt-4 flex justify-end gap-2">{/* 操作区右下角 */}</div>
</div>
```

**表单输入（含错误态）**

```tsx
<div>
  <label className="mb-1.5 block text-sm font-medium text-slate-700">
    字段名 <span className="text-red-500">*</span>
  </label>
  <input
    className={cn(
      'h-10 w-full rounded-md border bg-white px-3 text-sm outline-hidden',
      'focus:ring-2 focus:ring-brand/30',
      error ? 'border-red-500' : 'border-slate-200',
    )}
  />
  {error && <p className="mt-1.5 text-xs text-red-600">{error}</p>}
</div>
```

**徽章 / 状态标签**（浅底 + 前景 + 描边）

```tsx
<span className="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-green-200">
  已完成
</span>
```

**表格**

```tsx
<table className="w-full text-sm">
  <thead className="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
    <tr>{/* th py-3 px-4 */}</tr>
  </thead>
  <tbody className="divide-y divide-slate-100">
    <tr className="hover:bg-slate-50">{/* td py-3 px-4 */}</tr>
  </tbody>
</table>
```

**空状态**

```tsx
<div className="flex flex-col items-center justify-center gap-3 py-16 text-center">
  <Icon className="size-10 text-slate-300" />
  <p className="text-sm text-slate-500">暂无数据</p>
  <Button size="sm">新建</Button>
</div>
```

**骨架屏**

```tsx
<div className="animate-pulse space-y-3">
  <div className="h-4 w-1/3 rounded bg-slate-200" />
  <div className="h-4 w-full rounded bg-slate-200" />
  <div className="h-4 w-2/3 rounded bg-slate-200" />
</div>
```

## 图标与响应式约定

- 图标统一 `@tabler/icons-react`（`@tabler/icons` 已装），尺寸用 `size-4` / `size-5`，别混 FontAwesome 与 Tabler。
- 断点用 `sm md lg xl`，移动端先写、桌面端 `md:` 增强。
- 间距 / 圆角 / 颜色都从 `@theme` 的 token 取，不写魔法数字。
