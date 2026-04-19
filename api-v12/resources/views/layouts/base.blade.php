{{-- resources/views/layouts/base.blade.php
     全站 HTML 骨架。不含任何导航、页脚、布局结构。
     所有子布局继承此文件。
--}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>@yield('title', 'WikiPāli · 巴利佛典百科')</title>

    {{-- 基础样式由子布局通过 @vite 注入 --}}
    @stack('styles')
</head>

<body class="@yield('body-class')">
    <div class="page">
        @yield('page')
    </div>

    @stack('scripts')
</body>

</html>
