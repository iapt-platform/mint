{{-- resources/views/wiki/layouts/app.blade.php --}}
@extends('library.layouts.app')

@push('styles')
@vite(['resources/css/wiki.css', 'resources/css/wiki-content.css','resources/css/wiki-search.css'])
@endpush

@section('content')

<div class="container-xl wiki-layout">

    {{-- 左侧边栏 --}}
    @if(isset($lang))
    <aside class="wiki-sidebar-left">

        {{-- 分类导航 --}}
        @isset($categories)
        <div class="wiki-sidebar-section">
            <div class="wiki-sidebar-title">分类浏览</div>
            <ul class="wiki-cat-list">
                @foreach ($categories as $cat)
                <li>
                    <a href="{{ route('library.wiki.index',['lang'=>$lang]) }}?category={{ $cat['slug'] }}"
                        class="{{ (request('category', 'all') === $cat['slug']) ? 'active' : '' }}">
                        {{ $cat['label'] }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        @endisset

        {{-- 最近更新 --}}
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
        </div>
        @endisset

    </aside>
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

{{-- 术语抽屉（移动端，全局唯一） --}}
<x-wiki.term-drawer />
@endsection

@push('scripts')
@vite(['resources/js/term-tooltip.js'])
@endpush
