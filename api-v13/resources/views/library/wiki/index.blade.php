{{-- resources/views/wiki/index.blade.php --}}
@extends('library.wiki.layouts.app')

@section('title', 'WikiPāli · ' . __('library.wiki_encyclopedia'))

@section('wiki-content')
{{-- 搜索框组件 --}}
<div class="wiki-search-wrapper">
    <x-ui.search-input
        :value="request('q')"
        :placeholder="__('library.wiki_search_placeholder')"
        size="lg"
        :hidden-fields="['resource_type' => 'term']" />
</div>
{{-- 今日条目 --}}
@isset($today)
<div class="wiki-today-banner">
    <div class="wiki-today-icon">☸</div>
    <div class="wiki-today-body">
        <div class="wiki-today-label">{{ __('library.today_entry') }}</div>
        <div class="wiki-today-title">{{ $today['meaning'] }}（{{ $today['word'] }}）</div>
        <div class="wiki-today-snippet">
            {!! Str::limit(strip_tags($today['content']), 120) !!}
        </div>
        <a class="wiki-today-link"
            href="{{ route('library.wiki.show', [$today['lang'], $today['word']]) }}">
            {{ __('library.read_full_entry') }}
        </a>
    </div>
</div>
@endisset

{{-- 精选条目 --}}
@if(isset($featured) && is_array($featured) && count($featured)>0)
<div class="wiki-card">
    <div class="wiki-sidebar-title" style="margin-bottom: 14px;">{{ __('library.featured_entries') }}</div>
    <div class="wiki-featured-grid">
        @foreach ($featured as $item)
        <a class="wiki-featured-card"
            href="{{ route('library.wiki.show', [$item['lang'], $item['word']]) }}">
            <div class="wiki-featured-label">{{ $item['category'] }}</div>
            <div class="wiki-featured-title">{{ $item['zh'] }}</div>
            <div class="wiki-featured-pali">{{ $item['word'] }}</div>
        </a>
        @endforeach
    </div>
</div>
@endif


@if(isset($subs) && is_array($subs) && count($subs) > 0)

{{-- 取一级分类名称作为标题 --}}
@php
$catLabel = collect(config('taxonomy'))->firstWhere('id', $category)['label'] ?? $category;
@endphp

<div class="wiki-card wiki-subcat-block">

    <div class="wiki-subcat-block-header">
        <span class="wiki-subcat-block-title">{{ $catLabel }}</span>
        <a class="wiki-subcat-block-more"
            href="{{ route('library.wiki.index', ['lang' => $lang]) }}?category={{ $category }}">
            {{ __('library.view_all') }}
        </a>
    </div>

    @foreach ($subs as $sub)
    <x-wiki.sub-category :sub="$sub" :lang="$lang" />
    @endforeach

</div>

@endif


@endsection

@section('wiki-sidebar')

<div class="wiki-sidebar-section">
    <div class="wiki-sidebar-title">{{ __('library.statistics') }}</div>
    <table class="wiki-meta-table">
        <tr>
            <td>{{ __('library.total_entries') }}</td>
            <td>{{ number_format($stats['total']) }}</td>
        </tr>
        <tr>
            <td>{{ __('library.this_month') }}</td>
            <td>{{ $stats['this_month'] }}</td>
        </tr>
        <tr>
            <td>{{ __('library.contributors') }}</td>
            <td>{{ $stats['contributors'] }}</td>
        </tr>
    </table>
</div>




<div class="wiki-sidebar-section">
    <div class="wiki-sidebar-title">{{ __('library.quality_level') }}</div>
    <ul class="wiki-cat-list" id="qualityFilterList">
        @foreach ($qualities as $q)
        <li>
            <a href="{{ request()->fullUrlWithQuery(['quality' => $q['value']]) }}"
                class="wiki-quality-filter-item {{ $quality === $q['value'] ? 'active' : '' }}"
                data-quality="{{ $q['value'] }}">
                <span>{{ $q['label'] }}</span><span>{{ $q['subtitle'] }}</span>
            </a>
        </li>
        @endforeach
    </ul>
</div>

@endsection
