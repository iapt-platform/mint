{{-- resources/views/wiki/home.blade.php
     Wiki 门户首页。
     布局：单栏居中，法轮图标 + 标题 + 搜索框 + 热门标签 + 语言选择。
     所有样式来自 modules/wiki.css，无内联 <style>。
--}}
@extends('library.wiki.layouts.app')

@section('title', 'WikiPāli · ' . __('library.wiki_encyclopedia'))

@section('wiki-content')
<div class="wiki-home-container">

    {{-- 法轮图标 --}}
    <div class="wiki-home-wheel">
        <img src="{{ asset('assets/images/dhamma-wheel.svg') }}"
            alt="Dharma Wheel"
            class="wiki-home-wheel-img">
    </div>

    {{-- 欢迎标题 --}}
    <div class="wiki-home-title">
        <h1>{{ __('library.wiki_encyclopedia') }}</h1>
        <p class="text-muted">{{ __('library.wiki_subtitle') }}</p>
    </div>

    {{-- 搜索框 --}}
    <div class="wiki-home-search">
        <x-ui.search-input
            :value="request('q')"
            :placeholder="__('library.wiki_search_placeholder')"
            size="lg"
            :hidden-fields="['resource_type' => 'term']" />
    </div>

    {{-- 热门搜索标签 --}}
    @isset($hotTags)
    <div class="wiki-home-hot-tags">
        <span class="text-muted me-2">{{ __('library.hot') }}</span>
        @foreach($hotTags as $tag)
        <a href="{{ route('library.search', ['q' => $tag, 'type' => 'wiki']) }}"
            class="wiki-hot-tag">
            {{ $tag }}
        </a>
        @endforeach
    </div>
    @endisset

    {{-- 语言选择器 --}}
    <div class="wiki-home-languages">
        <div class="wiki-home-divider">
            <span>{{ __('library.wiki_language_divider') }}</span>
        </div>
        <div class="wiki-language-tags">
            @foreach($languages as $lang)
            <a href="{{ route('library.wiki.index', ['lang' => $lang['code']]) }}"
                class="wiki-language-tag {{ ($currentLang ?? 'zh-Hans') === $lang['code'] ? 'active' : '' }}">
                {{ $lang['name'] }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- 统计信息 --}}
    @isset($stats)
    <div class="wiki-home-stats">
        <span class="text-muted">
            📚 {{ number_format($stats['total_articles'] ?? 0) }} {{ __('library.articles') }}
            @if(isset($stats['today_updates']))
            · 🆕 {{ __('library.today_updates') }} {{ $stats['today_updates'] }}
            @endif
            @if(isset($stats['contributors']))
            · 👥 {{ number_format($stats['contributors']) }} {{ __('library.contributors_suffix') }}
            @endif
        </span>
    </div>
    @endisset

</div>
@endsection
