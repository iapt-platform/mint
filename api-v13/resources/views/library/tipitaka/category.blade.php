{{-- resources/views/library/tipitaka/category.blade.php
     Tipitaka 分类页 / 首页（$currentCategory['id'] 为 null 时是首页）
     三栏：左边栏（大分类）/ 中间（子分类+过滤器+书籍列表）/ 右边栏（推荐+译者）
--}}
@extends('layouts.library')

@section('title', $currentCategory['name'] . ' · ' . __('library.site_name'))

@push('styles')
@vite('resources/css/modules/tipitaka.css')
@endpush

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('library.home') }}">{{ __('library.home') }}</a>
</li>
@if($currentCategory['id'])
<li class="breadcrumb-item">
    <a href="{{ route('library.tipitaka.index') }}">{{ __('library.tipitaka') }}</a>
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
@else
<li class="breadcrumb-item active">{{ __('library.tipitaka') }}</li>
@endif
@endsection

@section('content')
<div class="page-body">
    <div class="container-xl wiki-layout">

        {{-- ══ 左边栏：大分类导航 ══ --}}
        <aside class="wiki-sidebar-left">



            {{-- 大分类 --}}
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">{{ __('library.categories') }}</div>
                <ul class="wiki-cat-list">
                    <li>
                        <a href="{{ route('library.tipitaka.index') }}"
                            class="{{ !$currentCategory['id'] ? 'active' : '' }}">
                            {{ __('library.all') }}
                        </a>
                    </li>
                    @foreach($types as $type)
                    <li>
                        <a href="{{ route('library.tipitaka.category', ['id' => $type['id']]) }}"
                            class="{{ ($currentCategory['id'] ?? null) == $type['id'] ? 'active' : '' }}">
                            {{ $type['name'] }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

        </aside>

        {{-- ══ 主内容 ══ --}}
        <main class="wiki-main">

            {{-- 搜索框 --}}
            <div>
                <x-ui.search-input
                    :value="request('q')"
                    :placeholder="__('library.search_tipitaka')"
                    :hidden-fields="['resource_type' => 'tipitaka']" />
            </div>



            {{-- 2. 过滤器区 --}}
            <div class="wiki-card tipitaka-filters">

                {{-- 类型 --}}
                <div class="tipitaka-filter-row">
                    <span class="tipitaka-filter-label">{{ __('library.type') }}</span>
                    <div class="tipitaka-filter-pills">
                        @foreach($filterOptions['types'] as $opt)
                        <a href="{{ request()->fullUrlWithQuery(['type' => $opt['value'], 'page' => null]) }}"
                            class="tipitaka-pill {{ $selected['type'] === $opt['value'] ? 'tipitaka-pill--active' : '' }}">
                            {{ $opt['label'] }}
                            @if($opt['count'] > 0)
                            <span class="tipitaka-pill-count">{{ $opt['count'] }}</span>
                            @endif
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- 语言 --}}
                <div class="tipitaka-filter-row">
                    <span class="tipitaka-filter-label">{{ __('library.language') }}</span>
                    <div class="tipitaka-filter-pills">
                        @foreach($filterOptions['languages'] as $opt)
                        <a href="{{ request()->fullUrlWithQuery(['lang' => $opt['value'], 'page' => null]) }}"
                            class="tipitaka-pill {{ $selected['lang'] === $opt['value'] ? 'tipitaka-pill--active' : '' }}">
                            {{ $opt['label'] }}
                            @if($opt['count'] > 0)
                            <span class="tipitaka-pill-count">{{ $opt['count'] }}</span>
                            @endif
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- 作者 --}}
                <div class="tipitaka-filter-row" style="display:none">
                    <span class="tipitaka-filter-label">{{ __('library.author') }}</span>
                    <select class="form-select form-select-sm tipitaka-author-select"
                        onchange="window.location=this.value">
                        @foreach($filterOptions['authors'] as $opt)
                        <option value="{{ request()->fullUrlWithQuery(['author' => $opt['value'], 'page' => null]) }}"
                            {{ $selected['author'] === $opt['value'] ? 'selected' : '' }}>
                            {{ $opt['label'] }}@if($opt['count'] > 0)（{{ $opt['count'] }}）@endif
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- 清除过滤器 --}}
                @php
                $hasFilter = $selected['type'] !== 'all'
                || $selected['lang'] !== 'all'
                || $selected['author'] !== 'all';
                @endphp
                @if($hasFilter)
                <div class="tipitaka-filter-clear">
                    <a href="{{ request()->fullUrlWithQuery(['type' => null, 'lang' => null, 'author' => null, 'page' => null]) }}"
                        class="tipitaka-clear-btn">
                        <i class="ti ti-x"></i> {{ __('library.clear_filters') }}
                    </a>
                </div>
                @endif

            </div>

            {{-- 1. 子分类 --}}
            @if(count($subCategories) > 0)
            <div class="wiki-card tipitaka-subcategories">
                <div class="tipitaka-subcategory-grid">
                    @foreach($subCategories as $sub)
                    <a href="{{ route('library.tipitaka.category', array_filter(['id' => $sub['id'], 'book' => $sub['book'] ?? null])) }}"
                        class="tipitaka-subcategory-item">
                        <i class="ti {{ !empty($sub['book']) ? 'ti-book' : 'ti-folder' }} tipitaka-subcategory-icon" aria-hidden="true"></i>
                        <span class="tipitaka-subcategory-name">{{ $sub['name'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 3. 排序 + 结果数 --}}
            <div class="tipitaka-sort-bar">
                <span class="tipitaka-sort-bar__count">
                    {{ __('library.total_prefix') }} <strong>{{ $totalCount }}</strong> {{ __('library.book_unit') }}
                </span>
                <div class="tipitaka-sort-bar__right">
                    <span class="tipitaka-sort-bar__label">{{ __('library.sort') }}</span>
                    <select class="form-select form-select-sm tipitaka-sort-select"
                        onchange="window.location=this.value">
                        @foreach($sortList as $sort)
                        <option value="{{ request()->fullUrlWithQuery(['sort' => $sort['key']]) }}"
                            {{ $selected['sort'] === $sort['key'] ? 'selected' : '' }}>{{ $sort['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- 4. 书籍列表 --}}
            <div class="wiki-card">
                <x-ui.book-grid :books="$categoryBooks" />
            </div>

        </main>

        {{-- ══ 右边栏 ══ --}}
        <aside class="wiki-sidebar-right">

            {{-- 本周推荐 --}}
            @if(!empty($recommended))
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">{{ __('library.weekly_picks') }}</div>
                <ul class="wiki-cat-list">
                    @foreach($recommended as $item)
                    <li>
                        <a href="{{ route('library.tipitaka.show', $item['id']) }}">
                            {{ $item['title'] }}
                            <span class="wiki-cat-count">{{ $item['category'] }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- 活跃译者 --}}
            @if(!empty($activeAuthors))
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">{{ __('library.active_authors') }}</div>
                <ul class="tipitaka-author-list">
                    @foreach($activeAuthors as $author)
                    <li>
                        <a href="{{ request()->fullUrlWithQuery(['author' => \Str::slug($author['name']), 'page' => null]) }}"
                            class="tipitaka-author-item">
                            <x-ui.author-avatar
                                :avatar="$author['avatar'] ?? null"
                                :color="$author['color']"
                                :initials="$author['initials']"
                                size="sm" />
                            <div class="tipitaka-author-item__info">
                                <span class="tipitaka-author-item__name">{{ $author['name'] }}</span>
                                <span class="tipitaka-author-item__count">{{ $author['count'] }} {{ __('library.book_unit') }}</span>
                            </div>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

        </aside>

    </div>
</div>
@endsection
