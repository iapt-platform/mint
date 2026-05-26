{{-- resources/views/wiki/show.blade.php --}}
@extends('library.wiki.layouts.app')

@section('title', $entry['meaning'] . '（' . $entry['word'] . '）· WikiPāli')

@section('wiki-content')
{{-- 搜索框组件 --}}
<div class="wiki-search-wrapper">
    <x-ui.search-input
        :action="route('library.search')"
        :value="request('q')"
        :placeholder="__('library.wiki_search_placeholder')"
        size="lg"
        :hidden-fields="['resource_type' => 'term']" />
</div>
<article class="wiki-card" style="position: relative;">

    <x-wiki.entry-actions
        :editUrl="$entry['edit_url']"
        :title="$entry['zh']" />

    {{-- 条目头部 --}}
    <x-wiki.entry-header :entry="$entry" />

    {{-- 语言版本切换 --}}
    <x-wiki.entry-langs :langs="$entry['langs']" :current="$entry['lang']" />

    {{-- 正文 --}}
    <div class="wiki-content-body">
        {!! $entry['content'] !!}
    </div>

    {{-- 标签 --}}
    <div class="wiki-tags">
        @foreach ($entry['tags'] as $tag)
        <a class="wiki-tag" href="{{ route('library.wiki.index',[$lang]) }}?tag={{ $tag }}">
            {{ $tag }}
        </a>
        @endforeach
    </div>

    {{-- 其他版本 --}}
    @if(isset($entry['other_versions']) && count($entry['other_versions']) > 0)
    <div class="wiki-other-versions">
        <div class="wiki-sidebar-title" style="margin-bottom: 0.75rem;">{{ __('library.other_versions') }}</div>
        @foreach ($entry['other_versions'] as $version)
        <x-wiki.search-result-card :result="$version" :lang="$lang" />
        @endforeach
    </div>
    @endif

</article>

@endsection

@section('wiki-sidebar')

{{-- 目录 --}}
<div class="wiki-sidebar-section">
    <div class="wiki-sidebar-title">{{ __('library.toc') }}</div>
    <ul class="wiki-toc-list">
        @foreach ($entry['toc'] as $i => $item)
        <li class="toc-level-{{ $item['level'] }}">
            <a href="#{{ $item['id'] }}">
                {{ $item['text'] }}
            </a>
        </li>
        @endforeach
    </ul>
</div>

{{-- 相关条目 --}}
<x-wiki.related-entries :entries="$entry['related']" />

{{-- 条目元信息 --}}
<div class="wiki-sidebar-section">
    <div class="wiki-sidebar-title">{{ __('library.entry_info') }}</div>
    <table class="wiki-meta-table">
        <tr>
            <td>{{ __('library.category') }}</td>
            <td>{{ $entry['category'] }}</td>
        </tr>
        <tr>
            <td>{{ __('library.quality') }}</td>
            <td><x-wiki.quality-badge :quality="$entry['quality']" /></td>
        </tr>
    </table>
</div>

@endsection

@push('scripts')
@vite('resources/js/modules/term-tooltip.js')
@endpush
