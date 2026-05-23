{{-- resources/views/library/anthology/show.blade.php --}}
@extends('layouts.library')

@section('title', $anthology['title'] . ' · 巴利书库')

@push('styles')
@vite('resources/css/modules/anthology.css')
@endpush

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('library.home') }}">首页</a>
</li>
<li class="breadcrumb-item">
    <a href="{{ route('library.anthology.index') }}">文集</a>
</li>
<li class="breadcrumb-item active">{{ $anthology['title'] }}</li>
@endsection

@section('hero')
<div class="anthology-hero">
    <div class="container-xl">
        <div class="hero-inner">

            {{-- 3D 书籍封面 --}}
            <x-ui.book-cover
                :image="$anthology['cover_image'] ?? null"
                :gradient="$anthology['cover_gradient']"
                :title="$anthology['title']"
                :subtitle="$anthology['subtitle'] ?? ''"
                size="lg"
                :style3d="true" />

            {{-- 文集信息 --}}
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
                        <x-ui.author-avatar
                            :avatar="$anthology['author']['avatar'] ?? null"
                            :color="$anthology['author']['color']"
                            :initials="$anthology['author']['initials']"
                            :name="$anthology['author']['name']"
                            size="sm" />
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
                    <a href="{{ route('library.anthology.read', [
                            'anthology' => $anthology['id'],
                            'article'   => $anthology['articles'][0]['id']
                        ]) }}"
                        class="btn-read-primary">
                        <i class="ti ti-book-2"></i>
                        在线阅读
                    </a>
                    @endif
                    <a href="{{ config('mint.server.dashboard_base_path') }}/workspace/anthology/{{ $anthology['id'] }}"
                        class="btn-outline-hero">
                        <i class="ti ti-pencil"></i>
                        在编辑器中打开
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('content')
<div class="page-body">
    <div class="container-xl">
        <div class="row mt-2">

            {{-- 主内容 --}}
            <div class="col-lg-8">

                {{-- 关于本文集 --}}
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

                {{-- 目录 --}}
                <div class="sec-card">
                    <div class="sec-header">
                        <div class="sec-bar"></div>
                        <div class="sec-title">目录</div>
                        <div class="sec-count">{{ $anthology['children_number'] }} 章节</div>
                    </div>
                    <ul class="toc-ul">
                        @foreach($anthology['articles'] as $article)
                        <li>
                            <a href="{{ route('library.anthology.read', [
                                    'anthology' => $anthology['id'],
                                    'article'   => $article['id']
                                ]) }}">
                                <span class="toc-num">{{ str_pad($article['order'], 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="toc-name">{{ $article['title'] }}</span>
                                <span class="toc-arrow">›</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>

            </div>

            {{-- 侧边栏 --}}
            <div class="col-lg-4">

                {{-- 文集信息 --}}
                <div class="sb-card">
                    <div class="sb-head">文集信息</div>
                    <div class="smeta-row">
                        <span class="smeta-label">作者</span>
                        <span class="smeta-value">
                            <a href="#">{{ $anthology['author']['name'] }}</a>
                        </span>
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

                {{-- 作者 --}}
                <div class="sb-card">
                    <div class="sb-head">作者</div>
                    <div class="author-block">
                        <x-ui.author-avatar
                            :avatar="$anthology['author']['avatar'] ?? null"
                            :color="$anthology['author']['color']"
                            :initials="$anthology['author']['initials']"
                            size="lg" />
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

                {{-- 相关文集 --}}
                @if($related->count())
                <div class="sb-card">
                    <div class="sb-head">相关文集</div>
                    <ul class="related-ul">
                        @foreach($related as $rel)
                        <li>
                            <a href="{{ route('library.anthology.show', $rel['id']) }}">
                                <x-ui.book-cover
                                    :image="null"
                                    :gradient="$rel['cover_gradient']"
                                    :title="mb_substr($rel['title'], 0, 4)"
                                    size="sm"
                                    :style3d="false" />
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
