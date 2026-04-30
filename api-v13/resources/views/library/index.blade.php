{{-- resources/views/library/index.blade.php
     Library 门户首页。
     区块：Hero → 三藏分类 → 最新译文 → 栏目导航
--}}
@extends('layouts.library')

@section('title', 'WikiPāli · 巴利书库')

@push('styles')
    @vite('resources/css/modules/_library-index.css')
@endpush

{{-- Hero --}}
@section('hero')
<section class="hero-section"
         style="background-image: url('{{ URL::asset('assets/images/hero-2.jpg') }}')">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="hero-title">WikiPāli 巴利书库</h1>
        <p class="hero-subtitle">探索巴利三藏 · 开启智慧之门</p>
        <div class="search-box">
            <x-ui.search-input
                :action="route('library.search')"
                placeholder="搜索经典、词条、文集…"
                size="lg"
            />
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
                    巴利三藏
                </h2>
                <a href="{{ route('library.tipitaka.index') }}"
                   class="lib-section__more">
                    进入三藏 <i class="ti ti-arrow-right"></i>
                </a>
            </div>

            <div class="row g-3">
                @foreach($categoryData as $data)
                <div class="col-6 col-md-3">
                    <div class="wiki-card h-100">
                        <div class="lib-cat-card__head">
                            <span class="lib-cat-card__name">
                                {{ $data['category']['name'] }}
                            </span>
                            <a href="{{ route('library.tipitaka.category', ['id' => $data['category']['id']]) }}"
                               class="lib-cat-card__more">
                                更多 <i class="ti ti-arrow-right"></i>
                            </a>
                        </div>
                        <ul class="wiki-cat-list">
                            @foreach($data['children'] as $child)
                            <li>
                                <a href="{{ route('library.tipitaka.category', ['id' => $child['id']]) }}">
                                    {{ $child['name'] }}
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
                    最新译文
                    <span class="lib-live-badge">
                        <span class="lib-live-dot"></span>
                        持续更新中
                    </span>
                </h2>
                <a href="{{ route('library.tipitaka.index') }}"
                   class="lib-section__more">
                    查看全部 <i class="ti ti-arrow-right"></i>
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
                        :style3d="false"
                    />

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
                        <span class="lib-new-badge">新增</span>
                        @else
                        <span class="lib-update-badge">更新</span>
                        @endif
                        <span class="lib-recent__time">{{ $book['updated_at'] }}</span>
                    </div>

                </a>
                @endforeach
            </div>
        </div>
        @endisset

        {{-- ── 三、栏目导航 ── --}}
        <div class="lib-section">
            <div class="lib-section__header">
                <h2 class="lib-section__title">
                    <i class="ti ti-layout-grid"></i>
                    全部栏目
                </h2>
            </div>

            <div class="row g-3">
                <div class="col-6 col-sm-4 col-md">
                    <a href="{{ route('library.tipitaka.index') }}" class="lib-nav-card">
                        <i class="ti ti-books lib-nav-card__icon"></i>
                        <div class="lib-nav-card__name">三藏</div>
                        <div class="lib-nav-card__desc">巴利文原典及译文</div>
                    </a>
                </div>
                <div class="col-6 col-sm-4 col-md">
                    <a href="{{ route('library.wiki.home') }}" class="lib-nav-card">
                        <i class="ti ti-world lib-nav-card__icon"></i>
                        <div class="lib-nav-card__name">百科</div>
                        <div class="lib-nav-card__desc">佛法术语词典</div>
                    </a>
                </div>
                <div class="col-6 col-sm-4 col-md">
                    <a href="{{ route('library.anthology.index') }}" class="lib-nav-card">
                        <i class="ti ti-notebook lib-nav-card__icon"></i>
                        <div class="lib-nav-card__name">文集</div>
                        <div class="lib-nav-card__desc">法义探讨与注疏</div>
                    </a>
                </div>
                <div class="col-6 col-sm-4 col-md">
                    <a href="{{ route('library.course') }}" class="lib-nav-card">
                        <i class="ti ti-school lib-nav-card__icon"></i>
                        <div class="lib-nav-card__name">课程</div>
                        <div class="lib-nav-card__desc">系统学习路径</div>
                    </a>
                </div>
                <div class="col-6 col-sm-4 col-md">
                    <a href="{{ route('library.download') }}" class="lib-nav-card">
                        <i class="ti ti-download lib-nav-card__icon"></i>
                        <div class="lib-nav-card__name">下载</div>
                        <div class="lib-nav-card__desc">离线阅读资源</div>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
