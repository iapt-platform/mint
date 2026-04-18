{{-- resources/views/wiki/search.blade.php --}}
@extends('wiki.layouts.app')

@section('title', $query ? '"' . $query . '" 的搜索结果 · WikiPāli' : '搜索 · WikiPāli')

@section('wiki-content')

{{-- 搜索栏 --}}
<div class="wiki-search-bar-wrap">
    <form action="{{ route('library.search') }}"
        method="GET"
        class="wiki-search-form">
        <div class="input-group">
            <input
                type="text"
                name="q"
                class="form-control wiki-search-input"
                value="{{ $query }}"
                placeholder="搜索条目、巴利文、梵文…"
                autofocus />
            @if ($category !== 'all')
            <input type="hidden" name="category" value="{{ $category }}">
            @endif
            <button class="btn btn-primary" type="submit">搜索</button>
        </div>
    </form>
</div>

{{-- 结果摘要 --}}
<div class="wiki-search-summary">
    @if ($query)
    搜索
    <strong>「{{ $query }}」</strong>
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
    :queryParams="array_filter(['q' => $query,'lang' => $lang, 'category' => $category === 'all' ? null : $category])" />
@endif

@else

{{-- 空状态 --}}
<div class="wiki-card wiki-empty-state">
    <div class="wiki-empty-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="1.5">
            <circle cx="11" cy="11" r="8" />
            <path d="M21 21l-4.35-4.35" stroke-linecap="round" />
            <path d="M8 11h6M11 8v6" stroke-linecap="round" />
        </svg>
    </div>
    <div class="wiki-empty-title">未找到相关条目</div>
    <div class="wiki-empty-desc">
        请尝试其他关键词
    </div>
</div>

@endif

@endsection

@section('wiki-sidebar')

{{-- 分类筛选 --}}
<div class="wiki-sidebar-section">
    <div class="wiki-sidebar-title">按分类筛选</div>
    <ul class="wiki-cat-list">
        <li>
            <a href="{{ route('library.search') }}?q={{ urlencode($query) }}"
                class="{{ $category === 'all' ? 'active' : '' }}">
                全部
                <span class="wiki-cat-count">{{ $pagination['total'] }}</span>
            </a>
        </li>
        @if(isset($filters))
        @foreach ($filters as $cat)
        <li>
            <a href="{{ route('library.search') }}?q={{ urlencode($query) }}&category={{ $cat->key }}"
                class="{{ $category === $cat->key ? 'active' : '' }}">
                {{ $cat->key }}
                <span class="wiki-cat-count">{{ $cat->doc_count }}</span>
            </a>
        </li>
        @endforeach
        @endif

    </ul>
</div>

{{-- 近似词条（无结果时显示） --}}
@if (count($results) === 0 && $query)
<div class="wiki-sidebar-section">
    <div class="wiki-sidebar-title">你可能在找</div>
    <ul class="wiki-related-list">
        <li>
            <a href="{{ route('library.search', ['lang' => $lang]) }}?q={{ urlencode(substr($query, 0, -1)) }}">
                {{ substr($query, 0, -1) }}
            </a>
        </li>
    </ul>
</div>
@endif

@endsection
