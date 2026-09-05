{{-- resources/views/pages/home.blade.php
     WikiPāli 首页：品牌 hero + 四张通栏分流卡片。
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

    {{-- 右上角语言选择，复用 library 的切换组件 --}}
    <div class="home-lang">
        <x-language-switcher />
    </div>

    {{-- 品牌 Hero：站名 + 一行说明，居中 --}}
    <header class="home-hero">
        <h1 class="home-hero__title">{{ __('home.hero_title') }}</h1>
        <p class="home-hero__lead">{{ __('home.hero_lead') }}</p>
    </header>

    {{-- 四张通栏卡片，纵向排列 --}}
    @foreach ($cards as $card)
        <x-home.card
            :slug="$card['slug']"
            :eyebrow="$card['eyebrow']"
            :title="$card['title']"
            :lead="$card['lead']"
            :image="$card['image']"
            :tint="$card['tint']"
            :align="$card['align']"
            :href="$card['href']"
            :cta="$card['cta']"
            :available="$card['available']"
            :eager="$loop->first"
        />
    @endforeach

</main>
@endsection
