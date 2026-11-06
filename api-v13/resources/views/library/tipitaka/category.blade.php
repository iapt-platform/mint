{{-- resources/views/library/tipitaka/category.blade.php
     三藏分类页（不限 channel）。
     三栏：左边栏（分类树，沿当前路径展开）/ 中间（子分类 + 过滤器 + 版本列表，分页）/ 右边栏（最近更新 + 本类译本）
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
<li class="breadcrumb-item">
    <a href="{{ route('library.tipitaka.index') }}">{{ __('library.tipitaka') }}</a>
</li>
@foreach($breadcrumbs as $breadcrumb)
@if($loop->last)
<li class="breadcrumb-item active">{{ $breadcrumb['name'] }}</li>
@else
<li class="breadcrumb-item">
    <a href="{{ route('library.tipitaka.category', ['id' => $breadcrumb['id']]) }}">{{ $breadcrumb['name'] }}</a>
</li>
@endif
@endforeach
@endsection

@section('content')
<div class="page-body">
    <div class="container-xl wiki-layout">

        {{-- ══ 左边栏：分类树，沿当前路径展开 ══ --}}
        <aside class="wiki-sidebar-left">
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">{{ __('library.categories') }}</div>
                @include('library.tipitaka._side-tree', ['nodes' => $sideTree])
            </div>
        </aside>

        {{-- ══ 主内容 ══ --}}
        <main class="wiki-main">

            <div>
                <x-ui.search-input
                    :value="request('q')"
                    :placeholder="__('library.search_tipitaka')"
                    :hidden-fields="['resource_type' => 'tipitaka']" />
            </div>

            {{-- 子分类；末级分类列出其下作品，点开进作品版本页 --}}
            @if(count($subCategories) > 0)
            <div class="wiki-card tipitaka-subcategories">
                <div class="tipitaka-subcategory-grid">
                    @foreach($subCategories as $sub)
                    <a href="{{ $sub['url'] }}" class="tipitaka-subcategory-item">
                        <i class="ti {{ $sub['is_work'] ? 'ti-book' : 'ti-folder' }} tipitaka-subcategory-icon" aria-hidden="true"></i>
                        <span class="tipitaka-subcategory-name">{{ $sub['name'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 过滤器 --}}
            <div class="wiki-card tipitaka-filters">
                <div class="tipitaka-filter-row">
                    <span class="tipitaka-filter-label">{{ __('library.type') }}</span>
                    <div class="tipitaka-filter-pills">
                        @foreach($filterOptions['types'] as $opt)
                        <a href="{{ request()->fullUrlWithQuery(['type' => $opt['value'], 'page' => null]) }}"
                            class="tipitaka-pill {{ $selected['type'] === $opt['value'] ? 'tipitaka-pill--active' : '' }}">
                            {{ $opt['label'] }}
                            <span class="tipitaka-pill-count">{{ $opt['count'] }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                <div class="tipitaka-filter-row">
                    <span class="tipitaka-filter-label">{{ __('library.language') }}</span>
                    <div class="tipitaka-filter-pills">
                        @foreach($filterOptions['languages'] as $opt)
                        <a href="{{ request()->fullUrlWithQuery(['channel_lang' => $opt['value'], 'page' => null]) }}"
                            class="tipitaka-pill {{ $selected['lang'] === $opt['value'] ? 'tipitaka-pill--active' : '' }}">
                            {{ $opt['label'] }}
                            <span class="tipitaka-pill-count">{{ $opt['count'] }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                @if($selected['type'] !== 'all' || $selected['lang'] !== 'all')
                <div class="tipitaka-filter-clear">
                    <a href="{{ route('library.tipitaka.category', ['id' => $currentCategory['id']]) }}"
                        class="tipitaka-clear-btn">
                        <i class="ti ti-x"></i> {{ __('library.clear_filters') }}
                    </a>
                </div>
                @endif
            </div>


            {{-- 排序 + 结果数 --}}
            <div class="tipitaka-sort-bar">
                <span class="tipitaka-sort-bar__count">
                    {{ __('library.total_prefix') }} <strong>{{ $totalCount }}</strong> {{ __('library.version_unit') }}
                </span>
                <div class="tipitaka-sort-bar__right">
                    <span class="tipitaka-sort-bar__label">{{ __('library.sort') }}</span>
                    <select class="form-select form-select-sm tipitaka-sort-select"
                        onchange="window.location=this.value">
                        @foreach($sortList as $sort)
                        <option value="{{ request()->fullUrlWithQuery(['sort' => $sort['key'], 'page' => null]) }}"
                            {{ $selected['sort'] === $sort['key'] ? 'selected' : '' }}>{{ $sort['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- 版本列表 --}}
            <div class="wiki-card">
                <x-ui.book-grid :books="$categoryBooks" />
                <div class="tipitaka-pager">{{ $paginator->links('library.anthology.pagination') }}</div>
            </div>

        </main>

        {{-- ══ 右边栏 ══ --}}
        <aside class="wiki-sidebar-right">

            @if(!empty($recentBooks))
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">{{ __('library.recent_updates') }}</div>
                <ul class="wiki-cat-list">
                    @foreach($recentBooks as $item)
                    <li>
                        <a href="{{ $item['url'] }}">
                            {{ $item['title'] }}
                            <span class="wiki-cat-count">{{ $item['author'] }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($activeChannels))
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">{{ __('library.category_channels') }}</div>
                <ul class="tipitaka-author-list">
                    @foreach($activeChannels as $channel)
                    <li>
                        <a href="{{ route('library.tipitaka.channels.category', ['channel' => $channel['uid'], 'id' => $currentCategory['id']]) }}"
                            class="tipitaka-author-item">
                            <x-ui.author-avatar
                                :color="\App\Services\TipitakaCatalogService::channelColor($channel['uid'])"
                                :initials="mb_substr($channel['name'], 0, 1)"
                                size="sm" />
                            <div class="tipitaka-author-item__info">
                                <span class="tipitaka-author-item__name">{{ $channel['name'] }}</span>
                                <span class="tipitaka-author-item__count">{{ __('language.'.$channel['lang']) }} · {{ $channel['count'] }} {{ __('library.work_unit') }}</span>
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
