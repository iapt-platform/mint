# Laravel Blade + Inertia

> api-v13 是 Laravel + **Inertia（React）** 项目：页面主体是 Inertia 的 React 页面，Blade 负责应用外壳与纯服务端页面。两者都用 Tailwind v4 样式。

## Blade 目录与职责

- `resources/views/layouts/`：布局骨架（`@yield` / `@stack`）。
- `resources/views/components/`：匿名组件（`<x-*>`）或类组件。
- `resources/views/partials/`：可复用片段（`@include`）。
- `resources/js/Pages/`：Inertia 页面（React 组件）。

## 布局继承

```blade
{{-- layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @vite(['resources/css/app.css', 'resources/js/app.jsx'])
  @stack('head')
</head>
<body class="bg-slate-50 text-slate-800">
  <main>@yield('content')</main>
  @stack('scripts')
</body>
</html>
```

```blade
{{-- 页面 --}}
@extends('layouts.app')

@section('content')
  <x-card class="max-w-2xl">
    <h1 class="text-2xl font-bold">标题</h1>
    {{-- ... --}}
  </x-card>
@endsection
```

## 匿名组件（推荐）

```blade
{{-- resources/views/components/card.blade.php --}}
@props(['title' => null])

<div {{ $attributes->merge(['class' => 'rounded-md border border-slate-200 bg-white p-6']) }}>
  @if ($title)
    <h3 class="text-base font-semibold text-slate-800">{{ $title }}</h3>
  @endif
  {{ $slot }}
</div>
```

- 用 `$attributes->merge(['class' => ...])` 让外部能追加 class，别把样式写死在外层 div 上。
- 需要透传数据的用类组件（`php artisan make:component`），否则优先匿名组件。

## 表单与安全

```blade
<form method="POST" action="{{ route('term.update', $term) }}" class="space-y-4">
  @csrf
  @method('PATCH')
  {{-- 字段 --}}
  <button type="submit" class="...">保存</button>
</form>
```

- 写操作必带 `@csrf`，PUT/PATCH/DELETE 带 `@method`。
- 校验错误用 `@error('field') <p class="text-xs text-red-600">{{ $message }}</p> @enderror`。

## Inertia 集成

```tsx
import { Head, Link, useForm } from '@inertiajs/react'

export default function TermEdit({ term, errors }) {
  const { data, setData, post, processing } = useForm({ name: term.name })
  return (
    <>
      <Head title="编辑术语" />
      {/* 页面主体，Tailwind v4 样式 */}
      <Link href="/terms">返回</Link>
    </>
  )
}
```

- 页面内导航用 Inertia 的 `<Link>`（保留 SPA 状态），不要用整页 `<a>` 跳转。
- 每个页面加 `<Head>` 设标题。
- Inertia 页面样式走 Tailwind v4（见 `tailwind-v4.md`），不引入 antd。

## i18n 与命名

- 文案用 `__('messages.xxx')` 或 Blade `@lang('messages.xxx')`，不硬编码中文散落各处。
- 路由命名、Blade 组件命名遵循现有约定（小写 kebab-case）。
