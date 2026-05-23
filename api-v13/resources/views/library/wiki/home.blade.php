{{-- resources/views/wiki/home.blade.php
     Wiki 门户首页。
     布局：单栏居中，法轮图标 + 标题 + 搜索框 + 热门标签 + 语言选择。
     所有样式来自 modules/wiki.css，无内联 <style>。
--}}
@extends('library.wiki.layouts.app')

@section('title', 'WikiPāli · 佛教百科-重构')

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
        <h1>佛教百科</h1>
        <p class="text-muted">探索佛法智慧 · 开启觉悟之门</p>
    </div>

    {{-- 搜索框 --}}
    <div class="wiki-home-search">
        <x-ui.search-input
            :action="route('library.search')"
            :value="request('q')"
            placeholder="搜索佛法词条、经典、人物..."
            size="lg"
            :hidden-fields="['resource_type' => 'term']" />
    </div>

    {{-- 热门搜索标签 --}}
    @isset($hotTags)
    <div class="wiki-home-hot-tags">
        <span class="text-muted me-2">热门：</span>
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
            <span>以您的语言阅读佛教百科</span>
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
            📚 {{ number_format($stats['total_articles'] ?? 0) }} 词条
            @if(isset($stats['today_updates']))
            · 🆕 今日更新 {{ $stats['today_updates'] }}
            @endif
            @if(isset($stats['contributors']))
            · 👥 {{ number_format($stats['contributors']) }} 位贡献者
            @endif
        </span>
    </div>
    @endisset

</div>
@endsection
