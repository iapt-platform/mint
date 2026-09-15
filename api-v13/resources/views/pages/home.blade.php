{{-- resources/views/pages/home.blade.php
     WikiPāli 首页：顶部导航（logo + 旧版入口 + 语言切换）+ 品牌 hero + 2×2 分流卡片。
     卡片数据全部来自 PageIndexController 传入的 $cards，模板不硬编码栏目文案。
--}}
@extends('layouts.base')

@section('title', __('home.title'))

@push('styles')
@vite(['resources/css/home.css'])
@endpush

@section('body-class', 'home-page')

@section('page')
<main class="home">

    {{-- 顶部导航：左 logo，右 旧版入口 + 语言切换 --}}
    <nav class="home-nav">
        <div class="home-nav__inner">
            <a class="home-nav__brand" href="/" aria-label="WikiPāli">
                <img src="{{ asset('assets/images/wikipali_logo_dark.svg') }}" alt="WikiPāli" class="home-nav__logo" />
            </a>

            <div class="home-nav__actions">
                <a class="home-nav__legacy"
                    href="{{ config('mint.server.dashboard_base_path') }}"
                    target="_blank"
                    rel="noopener">
                    {{ __('home.legacy') }}
                </a>
                <div class="home-nav__lang">
                    <x-language-switcher />
                </div>
            </div>
        </div>
    </nav>

    {{-- 品牌 Hero：站名 + 一行说明，居中 --}}
    <header class="home-hero">
        <h1 class="home-hero__title">{{ __('home.hero_title') }}</h1>
        <p class="home-hero__lead">{{ __('home.hero_lead') }}</p>
    </header>

    {{-- 四张分流卡片，2×2 网格 --}}
    <div class="home-grid">
        @foreach ($cards as $card)
            <x-home.card
                :slug="$card['slug']"
                :eyebrow="$card['eyebrow']"
                :title="$card['title']"
                :lead="$card['lead']"
                :icon="$card['icon']"
                :tint="$card['tint']"
                :href="$card['href']"
                :cta="$card['cta']"
                :available="$card['available']"
            />
        @endforeach
    </div>

    {{-- Footer：版权 + 备案号（ICP / 公安备案），参考 typhoon 模板 --}}
    <footer class="home-footer">
        <div class="home-footer__inner">
            <span class="home-footer__item">© {{ now()->year }} WikiPāli</span>
            @if (!empty(config('mint.app.icp_code')))
                <span class="home-footer__item">
                    <span>ICP备案号：</span>
                    <a href="https://beian.miit.gov.cn/" target="_blank" rel="noreferrer">{{ config('mint.app.icp_code') }}</a>
                </span>
                <span class="home-footer__item">
                    <img src="{{ asset('assets/images/logo_mps.png') }}" alt="公安备案" class="home-footer__icon" />
                    @if (empty(config('mint.app.mps_code')))
                        <span>滇公网安备[审批中]号</span>
                    @else
                        <span>{{ config('mint.app.mps_code') }}</span>
                    @endif
                </span>
            @endif
        </div>
    </footer>

</main>
@endsection
