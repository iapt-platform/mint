{{-- resources/views/library/search.blade.php
     全站统一搜索结果页。
     路由：/library/search?q=&type=&category=&lang=
     原 wiki/search.blade.php 移至此处，@extends 路径更新，
     搜索框改用 <x-ui.search-input>，空状态改用 <x-ui.empty-state>。
--}}
@extends('library.wiki.layouts.app')


@section('title', $query ? '"' . $query . '" 的搜索结果 · WikiPāli' : '搜索 · WikiPāli')

@section('wiki-content')

{{-- 搜索框 --}}
<div class="wiki-search-bar-wrap">
    <x-ui.search-input
        :action="route('library.search')"
        :value="$query"
        placeholder="搜索条目、巴利文、梵文…"
        :autofocus="true"
        :hidden-fields="array_filter(['category' => $category !== 'all' ? $category : null])" />
</div>

{{-- 结果摘要 --}}
<div class="wiki-search-summary">
    @if ($query)
    搜索 <strong>「{{ $query }}」</strong>
    @if ($pagination['total'] > 0)
    ，共找到 <strong>{{ $pagination['total'] }}</strong> 条结果
    @if ($pagination['last_page'] > 1)
    （第 {{ $pagination['current_page'] }} / {{ $pagination['last_page'] }} 页）
    @endif
    @else
    ，未找到相关条目
    @endif
    @endif
</div>

{{-- 结果列表 --}}
@if (count($results) > 0)

<div class="wiki-card wiki-search-results">
    @foreach ($results as $result)
    <x-wiki.search-result-card :result="$result" :lang="$lang" />
    @endforeach
</div>

{{-- 分页 --}}
@if ($pagination['last_page'] > 1)
<x-wiki.pagination
    :pagination="$pagination"
    routeName="library.search"
    :queryParams="array_filter([
                'q'        => $query,
                'lang'     => $lang,
                'category' => $category !== 'all' ? $category : null,
            ])" />
@endif

@else

<div class="wiki-card">
    <x-ui.empty-state
        title="未找到相关条目"
        desc="请尝试其他关键词" />
</div>

@endif

@endsection

@section('wiki-sidebar')

{{-- 分类筛选 --}}
@isset($filters)
@foreach ($filters as $key=>$filter)
<div class="wiki-sidebar-section">
    <div class="wiki-sidebar-title">按{{ $key }}筛选</div>
    <ul class="wiki-cat-list">
        <li>
            <a href="{{ route('library.search', ['q' => $query]) }}"
                class="{{ $category === 'all' ? 'active' : '' }}">
                全部
            </a>
        </li>

        @foreach ($filter['buckets'] as $bucket)
        <li>
            <a href="{{ route('library.search', ['q' => $query, $key => $bucket['key']]) }}"
                class="{{ $category === $bucket['key'] ? 'active' : '' }}">
                {{ $bucket['key'] }}
                <span class="wiki-cat-count">{{ $bucket['doc_count'] }}</span>
            </a>
        </li>
        @endforeach
    </ul>
</div>
@endforeach
@endisset

{{-- 近似词条（无结果时显示） --}}
@if (count($results) === 0 && $query)
<div class="wiki-sidebar-section">
    <div class="wiki-sidebar-title">你可能在找</div>
    <ul class="wiki-related-list">
        <li>
            <a href="{{ route('library.search', ['q' => substr($query, 0, -1), 'lang' => $lang]) }}">
                {{ substr($query, 0, -1) }}
            </a>
        </li>
    </ul>
</div>
@endif

@endsection


@section('wiki-sidebar-left')



<div class="wiki-sidebar-section">
    <div class="wiki-sidebar-title">分类浏览</div>
    <ul class="wiki-cat-list">
        <li>
            <a href="{{ route('library.search', ['q' => $query]) }}"
                class="{{ $category === 'all' ? 'active' : '' }}">
                全部
            </a>
        </li>
        @foreach ($types as $type)
        <li>
            <a href="{{ route('library.search', ['q' => $query,'resource_type' => $type['slug']]) }}"
                class="{{ (request('resource_type', 'all') === $type['slug']) ? 'active' : '' }}">
                {{ $type['label'] }}
                @if(isset($type['count']))
                <span class="wiki-cat-count">{{ $type['count'] }}</span>
                @endif
            </a>
        </li>
        @endforeach
    </ul>
</div>

@endsection
