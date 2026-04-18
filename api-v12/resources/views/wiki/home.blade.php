{{-- resources/views/wiki/home.blade.php --}}
@extends('wiki.layouts.app')

@section('wiki-content')
<div class="wiki-home-container">
    {{-- 中央法轮图 --}}
    <div class="dharma-wheel-wrapper">
        <img src="{{ asset('assets/images/dhamma-wheel.svg') }}" alt="Dharma Wheel" class="dharma-wheel-img">
    </div>

    {{-- 欢迎标题 --}}
    <div class="wiki-welcome-title">
        <h1 class="display-4 fw-bold text-primary mb-2">佛教百科</h1>
        <p class="text-muted">探索佛法智慧 · 开启觉悟之门</p>
    </div>

    {{-- 搜索框组件 --}}
    <div class="wiki-search-wrapper">
        <x-wiki.search-box
            :action="route('library.search')"
            placeholder="搜索佛法词条、经典、人物..."
            button-text="搜索"
            size="lg" />
    </div>

    {{-- 热门搜索标签 --}}
    @isset($hotTags)
    <div class="wiki-hot-tags">
        <span class="text-muted me-2">热门：</span>
        @foreach($hotTags as $tag)
        <a href="{{ route('library.search', ['q' => $tag, 'type' => 'wiki']) }}" class="badge bg-secondary-light text-dark me-1 text-decoration-none">
            {{ $tag }}
        </a>
        @endforeach
    </div>
    @endisset

    {{-- 分隔横线 + 语言选择器 --}}
    <div class="wiki-language-section">
        <div class="divider">
            <span class="divider-text">以您的语言阅读佛教百科</span>
        </div>
        <div class="language-tags">
            @foreach($languages as $lang)
            <a href="{{ route('library.wiki.index', ['lang' => $lang['code']]) }}"
                class="language-tag {{ ($currentLang ?? 'zh-Hans') === $lang['code'] ? 'active' : '' }}">
                {{ $lang['name'] }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- 统计信息 --}}
    @isset($stats)
    <div class="wiki-stats">
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

@push('styles')
<style>
    .wiki-home-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 300px);
        padding: 3rem 1.5rem;
        background: linear-gradient(135deg, #fef9f0 0%, #fff9f5 100%);
        border-radius: 1rem;
        margin: 1rem;
    }

    .dharma-wheel-wrapper {
        margin-bottom: 1.5rem;
        animation: subtle-float 3s ease-in-out infinite;
    }

    .dharma-wheel-img {
        width: 140px;
        height: auto;
        filter: drop-shadow(0 8px 20px rgba(0, 0, 0, 0.1));
        transition: transform 0.3s ease;
    }

    .dharma-wheel-img:hover {
        transform: scale(1.05);
    }

    .wiki-welcome-title {
        text-align: center;
        margin-bottom: 2rem;
    }

    .wiki-welcome-title h1 {
        background: linear-gradient(135deg, #8b5e3c 0%, #c49a6c 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
    }

    .wiki-search-wrapper {
        width: 100%;
        max-width: 640px;
        margin: 0 auto 1.5rem;
    }

    .wiki-hot-tags {
        text-align: center;
        margin-bottom: 3rem;
        font-size: 0.9rem;
    }

    .wiki-hot-tags .badge {
        padding: 0.4rem 0.8rem;
        transition: all 0.2s ease;
        background-color: #f0e6d8;
        color: #5a3a2a;
        font-weight: 500;
    }

    .wiki-hot-tags .badge:hover {
        background-color: #c49a6c;
        color: white;
        transform: translateY(-2px);
    }

    /* 分隔横线样式 */
    .wiki-language-section {
        width: 100%;
        max-width: 800px;
        margin: 1rem auto 2rem;
    }

    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin-bottom: 2rem;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }

    .divider::before {
        margin-right: 1.5rem;
    }

    .divider::after {
        margin-left: 1.5rem;
    }

    .divider-text {
        font-size: 0.95rem;
        color: #8b7355;
        letter-spacing: 1px;
        font-weight: 500;
        white-space: nowrap;
    }

    /* 语言标签样式 */
    .language-tags {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.75rem;
    }

    .language-tag {
        display: inline-block;
        padding: 0.5rem 1.25rem;
        background-color: #f5f0ea;
        color: #5a3a2a;
        text-decoration: none;
        border-radius: 30px;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.25s ease;
        border: 1px solid transparent;
    }

    .language-tag:hover {
        background-color: #e8ddd0;
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        color: #3a2518;
    }

    .language-tag.active {
        background: linear-gradient(135deg, #8b5e3c 0%, #6b4226 100%);
        color: white;
        border-color: #8b5e3c;
        box-shadow: 0 2px 8px rgba(139, 94, 60, 0.3);
    }

    .language-tag.active:hover {
        background: linear-gradient(135deg, #9b6e4c 0%, #7b5236 100%);
        transform: translateY(-2px);
    }

    .wiki-stats {
        text-align: center;
        font-size: 0.875rem;
        margin-top: 1rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(0, 0, 0, 0.08);
    }

    @keyframes subtle-float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-6px);
        }
    }

    /* 移动端适配 */
    @media (max-width: 768px) {
        .wiki-home-container {
            min-height: calc(100vh - 200px);
            padding: 2rem 1rem;
            margin: 0.5rem;
        }

        .dharma-wheel-img {
            width: 100px;
        }

        .wiki-welcome-title h1 {
            font-size: 1.8rem;
        }

        .wiki-welcome-title p {
            font-size: 0.9rem;
        }

        .wiki-search-wrapper {
            max-width: 100%;
        }

        .divider-text {
            font-size: 0.85rem;
            white-space: normal;
            text-align: center;
        }

        .divider::before,
        .divider::after {
            margin-right: 1rem;
            margin-left: 1rem;
        }

        .language-tag {
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
        }

        .language-tags {
            gap: 0.6rem;
        }
    }

    /* 暗色模式适配 */
    @media (prefers-color-scheme: dark) {
        .wiki-home-container {
            background: linear-gradient(135deg, #2a2418 0%, #1f1b14 100%);
        }

        .wiki-welcome-title h1 {
            background: linear-gradient(135deg, #d4a574 0%, #e8c4a0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .wiki-welcome-title p {
            color: #a89880;
        }

        .wiki-hot-tags .badge {
            background-color: #3a3025;
            color: #d4c4b0;
        }

        .wiki-hot-tags .badge:hover {
            background-color: #c49a6c;
            color: #1f1b14;
        }

        .divider::before,
        .divider::after {
            border-bottom-color: rgba(255, 255, 255, 0.15);
        }

        .divider-text {
            color: #b8a88a;
        }

        .language-tag {
            background-color: #3a3025;
            color: #d4c4b0;
        }

        .language-tag:hover {
            background-color: #4a3e30;
            color: #f0e0c0;
        }

        .language-tag.active {
            background: linear-gradient(135deg, #c49a6c 0%, #a07850 100%);
            color: #1f1b14;
        }

        .wiki-stats {
            border-top-color: rgba(255, 255, 255, 0.1);
        }
    }
</style>
@endpush
