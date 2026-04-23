{{-- resources/views/wiki/layouts/app.blade.php
     Wiki 栏目布局中间层。
     继承 layouts.library（提供 navbar + footer 外壳）。
     负责：wiki 三栏容器、wiki CSS 注入、term-drawer 全局组件。
--}}
@extends('layouts.library')

@push('styles')
    @vite('resources/css/modules/_wiki.css')
@endpush

@section('content')

<div class="container-xl wiki-layout">

    {{-- 左侧边栏 --}}
    @hasSection('wiki-sidebar-left')
        <aside class="wiki-sidebar-left">
            @yield('wiki-sidebar-left')
        </aside>
    @else
        @if(isset($lang))
        <aside class="wiki-sidebar-left">

            @isset($categories)
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">分类浏览</div>
                <ul class="wiki-cat-list">
                    @foreach ($categories as $cat)
                    <li>
                        <a href="{{ route('library.wiki.index', ['lang' => $lang]) }}?category={{ $cat['slug'] }}"
                           class="{{ (request('category', 'all') === $cat['slug']) ? 'active' : '' }}">
                            {{ $cat['label'] }}
                            @if(isset($cat['count']))
                                <span class="wiki-cat-count">{{ $cat['count'] }}</span>
                            @endif
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endisset

            @isset($recentUpdates)
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">最近更新</div>
                <ul class="wiki-cat-list">
                    @foreach ($recentUpdates as $item)
                    <li>
                        <a href="{{ route('library.wiki.show', [$item['lang'], $item['word']]) }}">
                            {{ $item['word'] }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endisset

        </aside>
        @endif
    @endif

    {{-- 主内容区 --}}
    <main class="wiki-main">
        @yield('wiki-content')
    </main>

    {{-- 右侧边栏 --}}
    <aside class="wiki-sidebar-right">
        @yield('wiki-sidebar')
    </aside>

</div>

{{-- 术语抽屉（移动端，所有阅读页公用，在此统一挂载） --}}
<x-wiki.term-drawer />

@endsection
