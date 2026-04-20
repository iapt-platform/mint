{{-- resources/views/library/tipitaka/category.blade.php --}}
@extends('layouts.library')

@section('title', $currentCategory['name'] . ' · 巴利书库-重构')

@push('styles')
@vite(['resources/css/modules/_tipitaka.css','resources/css/modules/_wiki.css','resources/css/modules/_anthology.css'])
@endpush

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('library.home') }}">首页</a>
</li>
<li class="breadcrumb-item">
    <a href="{{ route('library.tipitaka.index') }}">三藏</a>
</li>
@foreach($breadcrumbs as $breadcrumb)
@if($loop->last)
<li class="breadcrumb-item active">{{ $breadcrumb['name'] }}</li>
@else
<li class="breadcrumb-item">
    <a href="{{ route('library.tipitaka.category', ['id' => $breadcrumb['id']]) }}">
        {{ $breadcrumb['name'] }}
    </a>
</li>
@endif
@endforeach
@endsection

@section('content')
<div class="page-body">
    <div class="container-xl wiki-layout">

        {{-- 左侧边栏 --}}
        <aside class="wiki-sidebar-left">
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">分类导航</div>
                <ul class="wiki-cat-list">
                    <li>
                        <a href="{{ route('library.tipitaka.index') }}">
                            <i class="ti ti-home me-1"></i>三藏首页
                        </a>
                    </li>
                    @foreach($breadcrumbs as $breadcrumb)
                    <li>
                        <a href="{{ route('library.tipitaka.category', ['id' => $breadcrumb['id']]) }}"
                            class="{{ $loop->last ? 'active' : '' }}">
                            {{ $breadcrumb['name'] }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </aside>

        {{-- 主内容区 --}}
        <main class="wiki-main">

            {{-- 子分类 --}}
            @if(count($subCategories) > 0)
            <div class="wiki-card">
                <div class="wiki-sidebar-title" style="margin-bottom: 1rem;">子分类</div>
                <div class="book-grid" style="grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));">
                    @foreach($subCategories as $subCategory)
                    <a href="{{ route('library.tipitaka.category', ['id' => $subCategory['id']]) }}"
                        class="wiki-featured-card">
                        <div class="wiki-featured-title">{{ $subCategory['name'] }}</div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 书籍列表 --}}
            <div class="wiki-card">
                <div class="wiki-sidebar-title" style="margin-bottom: 1rem;">
                    {{ $currentCategory['name'] }}
                </div>
                <x-ui.book-grid :books="$categoryBooks" />
            </div>

        </main>

        {{-- 右侧边栏 --}}
        <aside class="wiki-sidebar-right">
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">当前分类</div>
                <div style="font-size: 0.875rem; color: var(--tblr-body-color); font-weight: 500;">
                    {{ $currentCategory['name'] }}
                </div>
            </div>
        </aside>

    </div>
</div>
@endsection
