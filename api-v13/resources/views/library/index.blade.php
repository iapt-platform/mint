{{-- resources/views/library/index.blade.php
     Library 门户首页。
     区块：Hero → 三藏分类 → 最新译文 → 栏目导航
--}}
@extends('layouts.library')

@section('title', __('library.portal_title'))

@push('styles')
@vite('resources/css/modules/library-index.css')
@endpush

{{-- Hero --}}
@section('hero')
<section class="hero-section"
    style="background-image: url('{{ URL::asset('assets/images/hero-2.jpg') }}')">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="hero-title">{{ __('library.portal_hero_title') }}</h1>
        <p class="hero-subtitle">{{ __('library.portal_hero_subtitle') }}</p>
        <div class="search-box">
            <x-ui.search-input
                :placeholder="__('library.search_placeholder_home')"
                size="lg" />
        </div>
    </div>
</section>
@endsection

@section('content')
<div class="page-body">
    <div class="container-xl">

        {{-- ── 一、三藏分类卡片 ── --}}
        <div class="lib-section">
            <div class="lib-section__header">
                <h2 class="lib-section__title">
                    <i class="ti ti-books"></i>
                    {{ __('library.section_tipitaka') }}
                </h2>
                <a href="{{ route('library.tipitaka.index') }}"
                    class="lib-section__more">
                    {{ __('library.enter_tipitaka') }} <i class="ti ti-arrow-right"></i>
                </a>
            </div>

            <div class="row g-3">
                @foreach($categoryData as $data)
                <div class="col-6 col-md-3">
                    <div class="wiki-card h-100">
                        <div class="lib-cat-card__head">
                            <span class="lib-cat-card__name">
                                {{ $data['category']['local_name'] }}
                            </span>
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

        {{-- ── 二、最新译文 ── --}}
        @isset($recentBooks)
        <div class="lib-section">
            <div class="lib-section__header">
                <h2 class="lib-section__title">
                    <i class="ti ti-clock"></i>
                    {{ __('library.section_recent') }}
                    <span class="lib-live-badge">
                        <span class="lib-live-dot"></span>
                        {{ __('library.updating_badge') }}
                    </span>
                </h2>
                <a href="{{ route('library.tipitaka.index') }}"
                    class="lib-section__more">
                    {{ __('library.view_all') }} <i class="ti ti-arrow-right"></i>
                </a>
            </div>

            <div class="wiki-card lib-recent">
                @foreach($recentBooks as $book)
                <a href="{{ route('library.tipitaka.show', $book['id']) }}"
                    class="lib-recent__item">

                    {{-- 封面缩略图 --}}
                    <x-ui.book-cover
                        :image="$book['cover'] ?? null"
                        :gradient="$book['cover_gradient'] ?? 'linear-gradient(135deg,#2d2010,#1a1208)'"
                        :title="$book['title']"
                        size="sm"
                        :style3d="false" />

                    {{-- 信息 --}}
                    <div class="lib-recent__info">
                        <div class="lib-recent__title">{{ $book['title'] }}</div>
                        <div class="lib-recent__meta">
                            <span class="lib-recent__category">{{ $book['category'] }}</span>
                            <span class="lib-recent__sep">·</span>
                            <span class="lib-recent__author">{{ $book['author'] }}</span>
                        </div>
                    </div>

                    {{-- 右侧：标签 + 时间 --}}
                    <div class="lib-recent__right">
                        @if($book['is_new'])
                        <span class="lib-new-badge">{{ __('library.badge_new') }}</span>
                        @else
                        <span class="lib-update-badge">{{ __('library.badge_updated') }}</span>
                        @endif
                        <span class="lib-recent__time">{{ $book['updated_at'] }}</span>
                    </div>

                </a>
                @endforeach
            </div>
        </div>
        @endisset

        {{-- ── 三、工具箱 ── --}}
        <div class="lib-section">
            <div class="lib-section__header">
                <h2 class="lib-section__title">
                    <i class="ti ti-tools"></i>
                    {{ __('labels.toolbox') }}
                </h2>
            </div>

            <div class="row g-3">
                <div class="col-6 col-sm-4 col-md-3">
                    <a href="{{ route('library.tools.script-convertor') }}" class="lib-nav-card">
                        <i class="ti ti-transform lib-nav-card__icon"></i>
                        <div class="lib-nav-card__name">{{ __('labels.tool_script_convertor') }}</div>
                        <div class="lib-nav-card__desc">{{ __('labels.tool_script_convertor_desc') }}</div>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
