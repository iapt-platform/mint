{{-- resources/views/layouts/library.blade.php
     library/* 所有列表页、详情页的布局外壳。
     包含：navbar（header 组件）、可选 hero、可选 toolbar、主内容区、footer。
     阅读页使用 layouts/reader，不继承此文件。
--}}
@extends('layouts.base')

@push('styles')
@vite(['resources/css/library.css', 'resources/js/app.js'])
@endpush

@section('page')

{{-- 导航 + 可选 Hero 包裹层（Hero 存在时 breadcrumb bar 绝对定位覆盖其上） --}}
<div class="hero-wrapper">
    <x-library.navbar />
    @yield('hero')
</div>

{{-- 可选工具条 --}}
@hasSection('toolbar')
<div class="page-toolbar">
    <div class="container-xl">
        @yield('toolbar')
    </div>
</div>
@endif

{{-- 主内容区 --}}
<div class="page-wrapper">
    @yield('content')
</div>

{{-- Footer --}}
<x-library.footer />

@endsection
