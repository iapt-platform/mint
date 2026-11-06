{{-- resources/views/library/tipitaka/index.blade.php
     三藏首页：分类（复用 library 首页的两级卡片）→ 译本 → 最近更新。
     变量：$categoryData（顶级分类 + 下一级）、$channels（公开 channel，按作品数）、$recentBooks（书卡片）
--}}
@extends('layouts.library')

@section('title', __('library.tipitaka') . ' · ' . __('library.site_name'))

@push('styles')
@vite(['resources/css/modules/library-index.css', 'resources/css/modules/tipitaka.css'])
@endpush

@section('hero')
{{-- 底图是深色的，hero-section--dark 把标题改成白字 --}}
<section class="hero-section hero-section--dark"
    style="background-image: url('{{ URL::asset('assets/images/hero-tipitaka.jpg') }}')">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="hero-title">{{ __('library.tipitaka') }}</h1>
        <p class="hero-subtitle">{{ __('library.tipitaka_hero_subtitle') }}</p>
        <div class="search-box">
            <x-ui.search-input
                :placeholder="__('library.search_tipitaka')"
                :hidden-fields="['resource_type' => 'tipitaka']"
                size="lg" />
        </div>
    </div>
</section>
@endsection

@section('content')
<div class="page-body">
    <div class="container-xl">

        {{-- ── 分类 ── --}}
        <div class="lib-section">
            <div class="lib-section__header">
                <h2 class="lib-section__title">
                    <i class="ti ti-books"></i>
                    {{ __('library.categories') }}
                </h2>
            </div>
            <div class="row g-3">
                @foreach($categoryData as $data)
                <div class="col-6 col-md-3">
                    <div class="wiki-card h-100">
                        <div class="lib-cat-card__head">
                            <span class="lib-cat-card__name">{{ $data['category']['local_name'] }}</span>
                            <a href="{{ route('library.tipitaka.category', ['id' => $data['category']['id']]) }}"
                                class="lib-cat-card__more">
                                {{ __('library.more') }} <i class="ti ti-arrow-right"></i>
                            </a>
                        </div>
                        <ul class="wiki-cat-list">
                            @foreach($data['children'] as $child)
                            <li>
                                <a href="{{ route('library.tipitaka.category', ['id' => $child['id']]) }}">
                                    {{ $child['local_name'] }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── 译本 ── --}}
        @if(!empty($channels))
        <div class="lib-section">
            <div class="lib-section__header">
                <h2 class="lib-section__title">
                    <i class="ti ti-language"></i>
                    {{ __('library.channels') }}
                </h2>
                <a href="{{ route('library.tipitaka.channels.index') }}" class="lib-section__more">
                    {{ __('library.view_all') }} <i class="ti ti-arrow-right"></i>
                </a>
            </div>
            <div class="tipitaka-channel-tiles">
                @foreach($channels as $channel)
                <a href="{{ route('library.tipitaka.channels.show', ['channel' => $channel['uid']]) }}"
                    class="tipitaka-channel-tile">
                    <x-ui.author-avatar
                        :color="\App\Services\TipitakaCatalogService::channelColor($channel['uid'])"
                        :initials="mb_substr($channel['name'], 0, 1)"
                        size="sm" />
                    <span class="tipitaka-channel-tile__info">
                        <span class="tipitaka-channel-tile__name">{{ $channel['name'] }}</span>
                        <span class="tipitaka-channel-tile__meta">
                            {{ __('language.'.$channel['lang']) }} · {{ $channel['works'] }} {{ __('library.work_unit') }}
                        </span>
                    </span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── 最近更新 ── --}}
        @if(!empty($recentBooks))
        <div class="lib-section">
            <div class="lib-section__header">
                <h2 class="lib-section__title">
                    <i class="ti ti-clock"></i>
                    {{ __('library.recent_updates') }}
                </h2>
            </div>
            <div class="wiki-card">
                <x-ui.book-grid :books="$recentBooks" />
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
