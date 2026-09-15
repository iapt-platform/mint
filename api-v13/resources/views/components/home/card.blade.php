{{-- resources/views/components/home/card.blade.php
     WikiPāli 首页 2×2 分流卡片（DeepSeek 风格：图标 + 标题 + 描述 + CTA）。
     available && href 非空 → 整卡可点击；否则渲染不可点击的状态角标。
--}}
@props([
    'slug',
    'eyebrow',
    'title',
    'lead',
    'icon',
    'tint',
    'href' => null,
    'cta',
    'available' => false,
])

@php
    $titleId = "{$slug}-title";
@endphp

@if ($available && filled($href))
    <a id="card-{{ $slug }}"
        class="home-card home-card--link"
        href="{{ $href }}"
        style="--card-tint: {{ $tint }}">
        <span class="home-card__icon" aria-hidden="true"><i class="ti {{ $icon }}"></i></span>
        <p class="home-card__eyebrow">{{ $eyebrow }}</p>
        <h2 class="home-card__title" id="{{ $titleId }}">{{ $title }}</h2>
        <p class="home-card__lead">{{ $lead }}</p>
        <span class="home-card__cta">{{ $cta }}<i class="ti ti-arrow-right" aria-hidden="true"></i></span>
    </a>
@else
    <div id="card-{{ $slug }}"
        class="home-card"
        style="--card-tint: {{ $tint }}">
        <span class="home-card__icon" aria-hidden="true"><i class="ti {{ $icon }}"></i></span>
        <p class="home-card__eyebrow">{{ $eyebrow }}</p>
        <h2 class="home-card__title" id="{{ $titleId }}">{{ $title }}</h2>
        <p class="home-card__lead">{{ $lead }}</p>
        <span class="home-card__badge"><i class="ti ti-clock" aria-hidden="true"></i>{{ $cta }}</span>
    </div>
@endif
