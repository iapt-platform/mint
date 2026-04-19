{{-- api-v12/resources/views/library/anthology/show.blade.php --}}
@extends('library.layouts.app')

@section('title', $anthology['title'] . ' · 巴利书库')

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('library.home') }}">首页</a>
</li>

<li class="breadcrumb-item">
    <a href="{{ route('library.anthology.index') }}">文集</a>
</li>

<li class="breadcrumb-item active">
    {{ $anthology['title'] }}
</li>
@endsection

@once
@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght@400;600;700&family=Noto+Sans+SC:wght@300;400;500&display=swap" rel="stylesheet">
<style>
    body {
        background: var(--sf-pale) !important;
        font-family: 'Noto Sans SC', sans-serif;
    }


    /* Hero */
    .anthology-hero {
        background: linear-gradient(135deg, var(--ink) 0%, #2d2010 100%);
        padding: 2.5rem 0;
    }

    .hero-inner {
        display: flex;
        gap: 2.25rem;
        align-items: flex-start;
    }

    /* Book cover */
    .book-cover-3d {
        width: 155px;
        min-width: 155px;
        height: 215px;
        border-radius: 3px 9px 9px 3px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1.25rem .9rem;
        position: relative;
        overflow: hidden;
        box-shadow: -4px 0 0 rgba(0, 0, 0, .3), -6px 4px 14px rgba(0, 0, 0, .4), 4px 4px 18px rgba(0, 0, 0, .3);
        flex-shrink: 0;
    }

    .book-cover-3d img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .book-cover-3d::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 13px;
        background: linear-gradient(to right, rgba(0, 0, 0, .4), rgba(0, 0, 0, .1));
        border-radius: 3px 0 0 3px;
        z-index: 2;
    }

    .book-cover-3d::after {
        content: '';
        position: absolute;
        inset: 0;
        background: repeating-linear-gradient(45deg, transparent, transparent 8px, rgba(255, 255, 255, .015) 8px, rgba(255, 255, 255, .015) 9px);
        z-index: 1;
    }

    .book-text-wrap {
        position: relative;
        z-index: 3;
        text-align: center;
    }

    .book-title-text {
        font-family: 'Noto Serif SC', serif;
        font-size: 1.05rem;
        font-weight: 600;
        color: #fff;
        line-height: 1.65;
        letter-spacing: .13em;
        word-break: break-all;
    }

    .book-divider {
        width: 32px;
        height: 1px;
        background: var(--sf);
        margin: .65rem auto;
    }

    .book-sub-text {
        font-size: .65rem;
        color: rgba(255, 255, 255, .5);
        letter-spacing: .06em;
        line-height: 1.5;
    }

    /* Hero right */
    .hero-content {
        flex: 1;
        min-width: 0;
    }

    .hero-title {
        font-family: 'Noto Serif SC', serif;
        font-size: 1.75rem;
        font-weight: 700;
        color: #fff;
        line-height: 1.3;
        margin-bottom: .4rem;
    }

    .hero-subtitle {
        font-size: .88rem;
        color: rgba(255, 255, 255, .45);
        font-style: italic;
        letter-spacing: .04em;
        margin-bottom: 1.1rem;
    }

    .hero-tags {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
        margin-bottom: 1.3rem;
    }

    .hero-tag {
        font-size: .72rem;
        padding: 2px 9px;
        border-radius: 20px;
        background: rgba(200, 134, 10, .2);
        color: var(--sf);
        border: 1px solid rgba(200, 134, 10, .3);
    }

    .hero-info-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1.4rem;
        margin-bottom: 1.3rem;
    }

    .hi-item {
        display: flex;
        align-items: center;
        gap: .45rem;
    }

    .hi-label {
        font-size: .72rem;
        color: rgba(255, 255, 255, .4);
        letter-spacing: .04em;
        display: block;
    }

    .hi-value {
        font-size: .83rem;
        color: rgba(255, 255, 255, .82);
        display: block;
    }

    .hi-avatar {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .68rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .hero-desc {
        font-size: .85rem;
        color: rgba(255, 255, 255, .6);
        line-height: 1.85;
        margin-bottom: 1.6rem;
        max-width: 600px;
    }

    .btn-read-primary {
        background: var(--sf);
        color: var(--ink);
        font-weight: 700;
        font-size: .88rem;
        padding: .55rem 1.6rem;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        transition: background .2s, transform .15s;
    }

    .btn-read-primary:hover {
        background: #dea020;
        color: var(--ink);
        transform: translateY(-1px);
    }

    .btn-outline-hero {
        background: transparent;
        color: rgba(255, 255, 255, .7);
        font-size: .85rem;
        padding: .5rem 1.3rem;
        border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, .2);
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        transition: all .2s;
        margin-left: .65rem;
    }

    .btn-outline-hero:hover {
        border-color: rgba(255, 255, 255, .5);
        color: #fff;
    }

    /* Section card */
    .sec-card {
        background: var(--card-bg);
        border: 1px solid var(--bdr);
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 1.3rem;
    }

    .sec-header {
        padding: .85rem 1.4rem;
        border-bottom: 1px solid var(--bdr);
        display: flex;
        align-items: center;
        gap: .55rem;
    }

    .sec-bar {
        width: 3px;
        height: 15px;
        background: var(--sf);
        border-radius: 2px;
        flex-shrink: 0;
    }

    .sec-title {
        font-family: 'Noto Serif SC', serif;
        font-size: .9rem;
        font-weight: 600;
        color: var(--ink-soft);
        letter-spacing: .04em;
    }

    .sec-count {
        margin-left: auto;
        font-size: .75rem;
        color: var(--ink-muted);
        background: var(--sf-light);
        padding: 2px 8px;
        border-radius: 10px;
    }

    /* About */
    .sec-body {
        padding: 1.15rem 1.4rem;
        font-size: .855rem;
        color: var(--ink-soft);
        line-height: 1.95;
    }

    .sec-body p {
        margin-bottom: .8rem;
    }

    .sec-body p:last-child {
        margin-bottom: 0;
    }

    /* TOC */
    .toc-ul {
        list-style: none;
        padding: .35rem 0;
        margin: 0;
    }

    .toc-ul li a {
        display: flex;
        align-items: center;
        padding: .65rem 1.4rem;
        text-decoration: none;
        border-bottom: 1px solid rgba(232, 221, 208, .5);
        transition: background .15s;
    }

    .toc-ul li:last-child a {
        border-bottom: none;
    }

    .toc-ul li a:hover {
        background: var(--sf-pale);
    }

    .toc-num {
        font-size: .72rem;
        color: var(--ink-muted);
        width: 26px;
        flex-shrink: 0;
    }

    .toc-name {
        font-size: .855rem;
        color: var(--ink-soft);
        flex: 1;
        line-height: 1.4;
    }

    .toc-ul li a:hover .toc-name {
        color: var(--sf);
    }

    .toc-arrow {
        color: var(--bdr);
        font-size: .85rem;
    }

    .toc-ul li a:hover .toc-arrow {
        color: var(--sf);
    }

    /* Sidebar */
    .sb-card {
        background: var(--card-bg);
        border: 1px solid var(--bdr);
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 1.15rem;
    }

    .sb-head {
        padding: .8rem 1.2rem;
        border-bottom: 1px solid var(--bdr);
        font-family: 'Noto Serif SC', serif;
        font-size: .875rem;
        font-weight: 600;
        color: var(--ink-soft);
        letter-spacing: .04em;
        display: flex;
        align-items: center;
        gap: .45rem;
    }

    .sb-head::before {
        content: '';
        display: block;
        width: 3px;
        height: 13px;
        background: var(--sf);
        border-radius: 2px;
    }

    .smeta-row {
        display: flex;
        padding: .7rem 1.2rem;
        border-bottom: 1px solid var(--bdr);
        font-size: .8rem;
        align-items: flex-start;
        gap: .45rem;
    }

    .smeta-row:last-child {
        border-bottom: none;
    }

    .smeta-label {
        color: var(--ink-muted);
        min-width: 65px;
        flex-shrink: 0;
    }

    .smeta-value {
        color: var(--ink-soft);
        font-weight: 500;
    }

    .smeta-value a {
        color: var(--sf);
        text-decoration: none;
    }

    .smeta-value a:hover {
        text-decoration: underline;
    }

    /* Author card */
    .author-block {
        display: flex;
        align-items: center;
        gap: .8rem;
        padding: 1.1rem 1.2rem;
    }

    .author-av-lg {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .author-av-img {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .hi-avatar {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .68rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .hi-avatar-img {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .author-block-name {
        font-weight: 600;
        font-size: .9rem;
        color: var(--ink);
        margin-bottom: .18rem;
    }

    .author-block-stats {
        font-size: .75rem;
        color: var(--ink-muted);
    }

    .author-bio {
        font-size: .78rem;
        color: var(--ink-muted);
        line-height: 1.65;
        padding: 0 1.2rem 1.1rem;
        border-top: 1px solid var(--bdr);
        padding-top: .9rem;
    }

    /* Related */
    .related-ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .related-ul li a {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .7rem 1.2rem;
        border-bottom: 1px solid var(--bdr);
        text-decoration: none;
        transition: background .15s;
    }

    .related-ul li:last-child a {
        border-bottom: none;
    }

    .related-ul li a:hover {
        background: var(--sf-pale);
    }

    .related-cover-mini {
        width: 34px;
        height: 46px;
        border-radius: 2px 5px 5px 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .6rem;
        color: rgba(255, 255, 255, .8);
        font-family: 'Noto Serif SC', serif;
        flex-shrink: 0;
        text-align: center;
        line-height: 1.3;
    }

    .related-t {
        font-size: .8rem;
        color: var(--ink-soft);
        font-weight: 500;
        margin-bottom: .18rem;
        line-height: 1.3;
    }

    .related-ul li a:hover .related-t {
        color: var(--sf);
    }

    .related-a {
        font-size: .7rem;
        color: var(--ink-muted);
    }

    @media (max-width: 900px) {
        .hero-inner {
            flex-direction: column;
            align-items: center;
        }

        .book-cover-3d {
            height: 170px;
        }
    }
</style>
@endpush
@endonce

@section('content')


{{-- Hero --}}
<div class="anthology-hero">
    <div class="container-xl">
        <div class="hero-inner">

            {{-- 3D Book Cover --}}
            <div class="book-cover-3d" style="{{ $anthology['cover_image'] ? '' : 'background: ' . $anthology['cover_gradient'] }}">
                @if($anthology['cover_image'])
                <img src="{{ $anthology['cover_image'] }}" alt="{{ $anthology['title'] }}">
                @else
                <div class="book-text-wrap">
                    <div class="book-title-text">{{ $anthology['title'] }}</div>
                    <div class="book-divider"></div>
                    <div class="book-sub-text">{{ $anthology['subtitle'] ?? '' }}</div>
                </div>
                @endif
            </div>

            {{-- Content --}}
            <div class="hero-content">
                <div class="hero-title">{{ $anthology['title'] }}</div>
                @if(!empty($anthology['subtitle']))
                <div class="hero-subtitle">{{ $anthology['subtitle'] }}</div>
                @endif

                @if(!empty($anthology['tags']))
                <div class="hero-tags">
                    @foreach($anthology['tags'] as $tag)
                    <span class="hero-tag">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif

                <div class="hero-info-row">
                    <div class="hi-item">
                        @if(!empty($anthology['author']['avatar']))
                        <img src="{{ $anthology['author']['avatar'] }}" class="hi-avatar-img" alt="">
                        @else
                        <div class="hi-avatar" style="background: {{ $anthology['author']['color'] }}; color: #fff">
                            {{ $anthology['author']['initials'] }}
                        </div>
                        @endif
                        <div>
                            <span class="hi-label">作者</span>
                            <span class="hi-value">{{ $anthology['author']['name'] }}</span>
                        </div>
                    </div>
                    <div class="hi-item">
                        <div>
                            <span class="hi-label">最后更新</span>
                            <span class="hi-value">{{ $anthology['updated_at'] }}</span>
                        </div>
                    </div>
                    <div class="hi-item">
                        <div>
                            <span class="hi-label">章节数</span>
                            <span class="hi-value">{{ $anthology['children_number'] }} 章节</span>
                        </div>
                    </div>
                    <div class="hi-item">
                        <div>
                            <span class="hi-label">创建时间</span>
                            <span class="hi-value">{{ $anthology['created_at'] }}</span>
                        </div>
                    </div>
                </div>

                @if(!empty($anthology['description']))
                <div class="hero-desc">{{ $anthology['description'] }}</div>
                @endif

                <div>
                    @if(!empty($anthology['articles']))
                    <a href="{{ route('library.anthology.read', ['anthology' => $anthology['id'], 'article' => $anthology['articles'][0]['id']]) }}" class="btn-read-primary">
                        <i class="ti ti-book-2"></i>
                        在线阅读
                    </a>
                    @endif
                    <a href="{{ config('mint.server.dashboard_base_path') }}/workspace/anthology/{{ $anthology['id'] }}" class="btn-outline-hero">
                        <i class="ti ti-pencil"></i>
                        在编辑器中打开
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Body --}}
<div class="page-body" style="background: var(--sf-pale);">
    <div class="container-xl">
        <div class="row mt-2">

            {{-- Left --}}
            <div class="col-lg-8">

                {{-- About --}}
                @if(!empty($anthology['about']))
                <div class="sec-card">
                    <div class="sec-header">
                        <div class="sec-bar"></div>
                        <div class="sec-title">关于本文集</div>
                    </div>
                    <div class="sec-body">
                        @foreach(explode("\n", $anthology['about']) as $para)
                        @if(trim($para))
                        <p>{{ trim($para) }}</p>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- TOC --}}
                <div class="sec-card">
                    <div class="sec-header">
                        <div class="sec-bar"></div>
                        <div class="sec-title">目录</div>
                        <div class="sec-count">{{ $anthology['children_number'] }} 章节</div>
                    </div>
                    <ul class="toc-ul">
                        @foreach($anthology['articles'] as $article)
                        <li>
                            <a href="{{ route('library.anthology.read', ['anthology' => $anthology['id'], 'article' => $article['id']]) }}">
                                <span class="toc-num">{{ str_pad($article['order'], 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="toc-name">{{ $article['title'] }}</span>
                                <span class="toc-arrow">›</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>

            </div>{{-- /col --}}

            {{-- Sidebar --}}
            <div class="col-lg-4">

                {{-- Meta --}}
                <div class="sb-card">
                    <div class="sb-head">文集信息</div>
                    <div class="smeta-row">
                        <span class="smeta-label">作者</span>
                        <span class="smeta-value"><a href="#">{{ $anthology['author']['name'] }}</a></span>
                    </div>
                    @if(!empty($anthology['language']))
                    <div class="smeta-row">
                        <span class="smeta-label">语言</span>
                        <span class="smeta-value">{{ $anthology['language'] }}</span>
                    </div>
                    @endif
                    <div class="smeta-row">
                        <span class="smeta-label">章节</span>
                        <span class="smeta-value">{{ $anthology['children_number'] }} 章节</span>
                    </div>
                    <div class="smeta-row">
                        <span class="smeta-label">创建</span>
                        <span class="smeta-value">{{ $anthology['created_at'] }}</span>
                    </div>
                    <div class="smeta-row">
                        <span class="smeta-label">更新</span>
                        <span class="smeta-value">{{ $anthology['updated_at'] }}</span>
                    </div>
                    @if(!empty($anthology['category']))
                    <div class="smeta-row">
                        <span class="smeta-label">分类</span>
                        <span class="smeta-value">{{ $anthology['category'] }}</span>
                    </div>
                    @endif
                </div>

                {{-- Author --}}
                <div class="sb-card">
                    <div class="sb-head">作者</div>
                    <div class="author-block">
                        @if(!empty($anthology['author']['avatar']))
                        <img src="{{ $anthology['author']['avatar'] }}" class="author-av-img" alt="">
                        @else
                        <div class="author-av-lg" style="background: {{ $anthology['author']['color'] }}; color: #fff">
                            {{ $anthology['author']['initials'] }}
                        </div>
                        @endif
                        <div>
                            <div class="author-block-name">{{ $anthology['author']['name'] }}</div>
                            <div class="author-block-stats">
                                @if($anthology['author']['article_count'])
                                {{ $anthology['author']['article_count'] }} 篇文章
                                @endif
                            </div>
                        </div>
                    </div>
                    @if(!empty($anthology['author']['bio']))
                    <div class="author-bio">{{ $anthology['author']['bio'] }}</div>
                    @endif
                </div>

                {{-- Related --}}
                @if($related->count())
                <div class="sb-card">
                    <div class="sb-head">相关文集</div>
                    <ul class="related-ul">
                        @foreach($related as $rel)
                        <li>
                            <a href="{{ route('library.anthology.show', $rel['id']) }}">
                                <div class="related-cover-mini" style="background: {{ $rel['cover_gradient'] }}">
                                    {{ mb_substr($rel['title'], 0, 4) }}
                                </div>
                                <div>
                                    <div class="related-t">{{ $rel['title'] }}</div>
                                    <div class="related-a">{{ $rel['author_name'] }}</div>
                                </div>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

            </div>

        </div>
    </div>
</div>
@endsection
