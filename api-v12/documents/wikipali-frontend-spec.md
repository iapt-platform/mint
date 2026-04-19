# WikiPali 前端开发规范

> 版本：1.0 · 适用范围：`library/*` · `blog/*` 全站前端

---

## 1. 技术栈

| 层级 | 技术 |
|---|---|
| 后端框架 | Laravel 12 |
| 模板引擎 | Blade |
| CSS 框架 | Tabler CSS（基于 Bootstrap 5） |
| 构建工具 | Vite |
| 交互 | Vanilla JS + Tabler Offcanvas/Dropdown |
| 搜索引擎 | OpenSearch |

无 Livewire、无 Alpine.js、无额外前端框架。JS 保持轻量，仅在必要时引入模块。

---

## 2. 目录结构

```
resources/
├── css/
│   ├── library.css              # library/* 入口（纯 @import）
│   ├── blog.css                 # blog/* 入口（纯 @import）
│   ├── reader.css               # 全站阅读页入口（纯 @import）
│   ├── app.css                  # 过渡期保留，稳定后删除
│   │
│   ├── base/
│   │   ├── _variables.css       # Tabler 变量覆盖 + 自定义 token
│   │   ├── _reset.css           # normalize / reset
│   │   └── _typography.css      # 字体、行高、标题
│   │
│   ├── layout/
│   │   ├── _grid.css            # 断点定义 + 1/2/3 栏容器（全站唯一）
│   │   ├── _navbar.css          # 顶部导航（desktop 展开 / mobile 汉堡）
│   │   ├── _drawer.css          # 左右 Drawer（基于 Tabler Offcanvas）
│   │   ├── _hero.css            # Hero 区域
│   │   ├── _footer.css
│   │   └── _toolbar.css         # 页内工具条
│   │
│   ├── components/
│   │   ├── _search-input.css    # 搜索框 + 提示下拉
│   │   ├── _search-results.css  # 搜索结果页布局
│   │   ├── _card.css            # 通用卡片（title + list）
│   │   ├── _card-book.css       # 书籍卡片（封面 + 标题）
│   │   ├── _badge.css
│   │   └── _pagination.css
│   │
│   └── modules/                 # 各栏目差异样式，仅覆盖变量或追加规则
│       ├── _wiki.css            # wiki 专属（词条详情、质量徽章等）
│       ├── _tipitaka.css
│       ├── _anthology.css
│       ├── _blog.css
│       └── _reader.css          # 阅读正文区（字号、行高、夜间模式）
│
├── js/
│   ├── app.js                   # 全局入口
│   ├── bootstrap.js
│   └── modules/
│       ├── term-tooltip.js      # 术语提示（原 term-tooltip.js）
│       ├── toc.js               # TOC 折叠 + 滚动高亮
│       ├── theme.js             # 夜间模式切换
│       └── search-suggest.js    # 搜索提示下拉
│
└── views/
    ├── layouts/                 # 布局文件（全站仅 3 个）
    │   ├── base.blade.php       # HTML 骨架
    │   ├── library.blade.php    # library/* 含 navbar + footer
    │   ├── blog.blade.php       # blog/* 独立三栏，无 library 导航
    │   └── reader.blade.php     # 全站阅读页，沉浸式，无导航
    │
    ├── components/
    │   ├── ui/                  # 纯 UI 组件，全站通用
    │   │   ├── drawer.blade.php
    │   │   ├── search-input.blade.php
    │   │   ├── card.blade.php
    │   │   ├── card-book.blade.php
    │   │   ├── badge.blade.php
    │   │   └── pagination.blade.php
    │   │
    │   └── library/             # library 布局专用
    │       ├── navbar.blade.php
    │       ├── nav-links.blade.php
    │       ├── hero.blade.php
    │       ├── toolbar.blade.php
    │       ├── footer.blade.php
    │       ├── toc.blade.php
    │       ├── layout-1col.blade.php
    │       ├── layout-2col.blade.php
    │       └── layout-3col.blade.php
    │
    ├── library/
    │   ├── index.blade.php
    │   ├── search.blade.php          # 统一搜索结果页
    │   ├── tipitaka/
    │   │   ├── index.blade.php
    │   │   ├── category.blade.php
    │   │   ├── show.blade.php
    │   │   └── read.blade.php
    │   ├── anthology/
    │   │   ├── index.blade.php
    │   │   ├── show.blade.php
    │   │   └── read.blade.php
    │   └── wiki/
    │       ├── home.blade.php
    │       ├── index.blade.php
    │       └── show.blade.php
    │
    └── blog/
        ├── index.blade.php
        ├── category.blade.php
        └── show.blade.php
```

---

## 3. 路由与页面对应

```
/library                           → library/index.blade.php
/library/search?q=&type=          → library/search.blade.php
/library/tipitaka                  → library/tipitaka/index.blade.php
/library/tipitaka/category/{id}    → library/tipitaka/category.blade.php
/library/tipitaka/{id}             → library/tipitaka/show.blade.php
/library/tipitaka/{id}/read        → library/tipitaka/read.blade.php
/library/anthology                 → library/anthology/index.blade.php
/library/anthology/{id}            → library/anthology/show.blade.php
/library/anthology/{id}/read/{art} → library/anthology/read.blade.php
/library/wiki                      → library/wiki/home.blade.php
/library/wiki/{lang}               → library/wiki/index.blade.php
/library/wiki/{lang}/{word}        → library/wiki/show.blade.php
/blog/{user}                       → blog/index.blade.php
/blog/{user}/{post}                → blog/show.blade.php
/blog/{user}/category/...          → blog/category.blade.php
```

---

## 4. 栏目划分

| 栏目 | 路由前缀 | 布局 | 说明 |
|---|---|---|---|
| Library 门户 | `/library` | `layouts/library` | 入口页，含 Hero |
| Tipitaka（巴利三藏） | `/library/tipitaka` | `layouts/library` | 含阅读页 |
| Anthology（文集） | `/library/anthology` | `layouts/library` | 含阅读页 |
| Wiki（词典） | `/library/wiki` | `layouts/library` | 词条详情为阅读页 |
| Search（搜索） | `/library/search` | `layouts/library` | 统一结果页，`?type=` 区分栏目 |
| Blog（博客） | `/blog` | `layouts/blog` | 独立三栏，与 library 完全隔离 |

**阅读页统一使用 `layouts/reader`**，包括：

- `library/tipitaka/read.blade.php`
- `library/anthology/read.blade.php`
- `library/wiki/show.blade.php`（词条详情本质为阅读）
- `blog/show.blade.php`（博文详情）

---

## 5. Blade 调用层级

```
layouts/base.blade.php
│   HTML 骨架，@vite，@stack('styles')，@stack('scripts')
│   不含任何导航或布局
│
├── layouts/library.blade.php
│   @extends('layouts.base')
│   包含：navbar + hero(可选) + toolbar(可选) + @yield('content') + footer
│   页面通过 @section('content') 填充正文
│   栏目模块 CSS 通过 @push('styles') 注入
│
├── layouts/blog.blade.php
│   @extends('layouts.base')
│   独立三栏：左侧博主信息 + 中间内容 + 右侧过滤工具
│   无 library navbar / footer
│
└── layouts/reader.blade.php
    @extends('layouts.base')
    沉浸式，无 navbar，无 footer
    包含：TOC（desktop 常驻，mobile 左侧 Drawer）+ 正文区
```

**页面文件示例：**

```blade
{{-- library/wiki/show.blade.php --}}
@extends('layouts.reader')

@push('styles')
    @vite('resources/css/modules/_wiki.css')  {{-- wiki 专属样式 --}}
@endpush

@section('content')
    ...
@endsection
```

---

## 6. CSS 架构与复用

### 基准层

wiki 栏目的现有 CSS 作为全站基准，其他栏目默认继承，需要时通过 `modules/_xxx.css` 覆盖。

### 入口文件职责

入口文件（`library.css` / `blog.css` / `reader.css`）**只做 `@import`，不写任何样式规则**。

```css
/* library.css */
@import './base/_variables.css';
@import './base/_reset.css';
@import './base/_typography.css';
@import './layout/_grid.css';
@import './layout/_navbar.css';
@import './layout/_drawer.css';
@import './layout/_hero.css';
@import './layout/_footer.css';
@import './layout/_toolbar.css';
@import './components/_search-input.css';
@import './components/_card.css';
@import './components/_card-book.css';
@import './components/_badge.css';
@import './components/_pagination.css';
/* modules 不在此引入，由各页面按需 @push */
```

### 变量覆盖机制

`_variables.css` 覆盖 Tabler 的 CSS custom properties，各栏目 module 文件**只重定义变量，不重写规则**：

```css
/* modules/_wiki.css */
:root {
  --color-primary: #5c6bc0;
}
/* 仅追加 wiki 专属规则 */
```

### Vite 入口配置

```js
// vite.config.js
laravel({
    input: [
        'resources/css/library.css',
        'resources/css/blog.css',
        'resources/css/reader.css',
        'resources/js/app.js',
    ],
    refresh: true,
})
```

---

## 7. 页面布局（从上到下）

### library/* 页面

```
┌────────────────────────────────────┐
│  Navbar                            │  所有 library 页面
│  左：面包屑  右：导航按钮          │
├────────────────────────────────────┤
│  Hero（可选）                      │  首页、栏目首页
├────────────────────────────────────┤
│  Toolbar（可选）                   │  列表页、详情页
├──────────┬────────────┬────────────┤
│  左边栏  │  主内容    │  右边栏    │  3 栏（wiki 词条等）
│（可选）  │            │（可选）    │  2 栏（tipitaka 等）
│          │            │            │  1 栏（首页、搜索）
└──────────┴────────────┴────────────┘
│  Footer                            │
└────────────────────────────────────┘
```

### blog/* 页面

```
┌──────────┬────────────┬────────────┐
│  左边栏  │  文章列表  │  右边栏    │
│          │            │            │
│  头像    │  卡片列表  │  搜索框    │
│  徽章    │  分页      │  标签云    │
│  栏目导航│            │  过滤器    │
└──────────┴────────────┴────────────┘
```

### 阅读页（reader）

```
┌──────────┬────────────────────────┐
│  TOC     │  正文                  │
│  常驻    │  无 navbar             │
│          │  无 footer             │
└──────────┴────────────────────────┘
```

---

## 8. 响应式规范

断点统一定义在 `layout/_grid.css`，全站唯一，不在各栏目重复定义。

| 区域 | Desktop (>1024px) | Tablet (768–1024px) | Mobile (<768px) |
|---|---|---|---|
| 顶部导航 | 完整展开 | 完整展开 | 汉堡按钮 → 右侧 Drawer |
| 左边栏 | 常驻 | 常驻 | 左侧 Drawer，默认收起 |
| 右边栏 | 常驻 | 隐藏 | 隐藏 |
| 阅读页 TOC | 左侧常驻 | 左侧 Drawer | 左侧 Drawer + 悬浮触发按钮 |
| Blog 左边栏 | 常驻 | 左侧 Drawer | 左侧 Drawer |
| Blog 右边栏 | 常驻 | 隐藏 | 隐藏 |

### Drawer 组件

基于 Tabler Offcanvas，左右方向通过 `side` prop 控制：

```blade
{{-- 导航 Drawer（右侧） --}}
<x-ui.drawer id="nav-drawer" side="end" title="导航">
    <x-library.nav-links />
</x-ui.drawer>

{{-- TOC Drawer（左侧） --}}
<x-ui.drawer id="toc-drawer" side="start" title="目录">
    <x-library.toc :items="$toc" />
</x-ui.drawer>
```

---

## 9. 可复用组件清单

| 组件 | 路径 | Props | 用途 |
|---|---|---|---|
| `<x-ui.drawer>` | `components/ui/drawer` | `id` `side=start\|end` `title` | 左/右抽屉 |
| `<x-ui.search-input>` | `components/ui/search-input` | `name` `action` `placeholder` `suggest-url` | 搜索框 + 提示 |
| `<x-ui.card>` | `components/ui/card` | `title` `items` `href` | 通用卡片 |
| `<x-ui.card-book>` | `components/ui/card-book` | `title` `cover` `href` `layout=vertical\|horizontal` | 书籍卡片 |
| `<x-ui.badge>` | `components/ui/badge` | `type` `label` | 徽章 |
| `<x-ui.pagination>` | `components/ui/pagination` | `paginator` | 分页 |
| `<x-library.navbar>` | `components/library/navbar` | — | 含汉堡触发 |
| `<x-library.toc>` | `components/library/toc` | `items` | TOC，自适应 Drawer |
| `<x-library.hero>` | `components/library/hero` | `title` `subtitle` | Hero 区域 |
| `<x-library.toolbar>` | `components/library/toolbar` | slot | 页内工具条 |
| `<x-library.layout-1col>` | `components/library/layout-1col` | slot | 单栏容器 |
| `<x-library.layout-2col>` | `components/library/layout-2col` | slot `main` `aside` | 双栏容器 |
| `<x-library.layout-3col>` | `components/library/layout-3col` | slot `left` `main` `right` | 三栏容器 |

---

## 10. 搜索规范

搜索底层统一使用 OpenSearch，结果页统一为 `library/search.blade.php`。

| 参数 | 说明 | 示例 |
|---|---|---|
| `q` | 搜索关键词 | `?q=dukkha` |
| `type` | 栏目过滤 | `?type=wiki` `?type=tipitaka` |
| `lang` | 语言过滤 | `?lang=zh` |

各栏目的搜索入口（如 wiki 首页的搜索框）跳转至统一搜索页，并预填 `type` 参数。搜索提示下拉由 `search-suggest.js` 处理，调用各自的 suggest API。

---

## 11. 迁移策略

重构为**增量迁移**，不一次性切换，降低风险。

1. **以 wiki 为基准**：wiki 现有 CSS 和组件作为公共层的起点
2. **提取公共层**：将 wiki 中可复用的部分移至 `layouts/`、`components/ui/`、`base/`
3. **wiki 改为引用公共层**：行为不变，验证通过后继续
4. **逐栏目迁移顺序**：wiki → anthology → tipitaka → blog（从简单到复杂）
5. **旧文件保留至迁移完成**：`app.css`、各栏目旧 `layouts/app.blade.php` 在对应栏目迁移完成后再删除

---

*本文档随重构进展持续更新*
