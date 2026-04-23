{{-- resources/views/wiki/show.blade.php --}}
@extends('library.wiki.layouts.app')

@section('title', $entry['meaning'] . '（' . $entry['word'] . '）· WikiPāli')

@section('wiki-content')
{{-- 搜索框组件 --}}
<div class="wiki-search-wrapper">
    <x-wiki.search-box
        :action="route('library.search')"
        placeholder="搜索佛法词条、经典、人物..."
        button-text="搜索"
        size="lg" />
</div>
<article class="wiki-card">

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
        <a class="wiki-tag" href="{{ route('library.wiki.index') }}?tag={{ $tag }}">
            {{ $tag }}
        </a>
        @endforeach
    </div>

</article>

@endsection

@section('wiki-sidebar')

{{-- 目录 --}}
<div class="wiki-sidebar-section">
    <div class="wiki-sidebar-title">目录</div>
    <ul class="wiki-toc-list">
        @foreach ($entry['toc'] as $i => $item)
        <li class="toc-level-{{ $item['level'] }}">
            <a href="#{{ $item['id'] }}">
                <span class="wiki-toc-num">{{ $i + 1 }}</span>
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
    <div class="wiki-sidebar-title">条目信息</div>
    <table class="wiki-meta-table">
        <tr>
            <td>分类</td>
            <td>{{ $entry['category'] }}</td>
        </tr>
        <tr>
            <td>质量</td>
            <td><x-wiki.quality-badge :quality="$entry['quality']" /></td>
        </tr>
    </table>
</div>

@endsection

@push('scripts')
@vite('resources/js/modules/term-tooltip.js')
@endpush
