{{-- resources/views/library/tipitaka/show.blade.php --}}
@extends('layouts.library')

@section('title', $book['title'] . ' · 巴利书库')

@push('styles')
@vite('resources/css/modules/_tipitaka.css')
@endpush

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('library.home') }}">首页</a>
</li>
<li class="breadcrumb-item">
    <a href="{{ route('library.tipitaka.index') }}">三藏</a>
</li>
<li class="breadcrumb-item active">{{ $book['title'] }}</li>
@endsection

@section('content')
<div class="page-body">
    <div class="container-xl wiki-layout">

        {{-- 左侧边栏：封面 + 操作按钮 --}}
        <aside class="wiki-sidebar-left">
            <div class="wiki-sidebar-section" style="padding: 1rem;">

                {{-- 封面 --}}
                <x-ui.book-cover
                    :image="$book['cover'] ?? null"
                    :gradient="$book['cover_gradient'] ?? 'linear-gradient(135deg, #2d2010 0%, #1a1208 100%)'"
                    :title="$book['title']"
                    size="md"
                    :style3d="false"
                    style="width: 100%; min-width: unset; height: 220px;" />

                {{-- 操作按钮 --}}
                <div style="margin-top: 1rem;">
                    <a href="{{ route('library.tipitaka.read', $book['id']) }}"
                        class="btn btn-primary w-100 mb-2">
                        <i class="ti ti-book-2 me-1"></i>
                        在线阅读
                    </a>
                    <button class="btn btn-outline-secondary w-100">
                        <i class="ti ti-download me-1"></i>
                        下载
                    </button>
                </div>

            </div>
        </aside>

        {{-- 主内容区 --}}
        <main class="wiki-main">

            {{-- 书籍信息 --}}
            <div class="wiki-card">
                <div class="wiki-entry-header">
                    <div class="wiki-entry-title">{{ $book['title'] }}</div>
                </div>

                <table class="wiki-meta-table" style="margin-bottom: 1.25rem;">
                    <tr>
                        <td>作者</td>
                        <td>{{ $book['author'] }}</td>
                    </tr>
                    @if(isset($book['publisher']))
                    <tr>
                        <td>出版</td>
                        <td>
                            <a href="{{ route('blog.index', ['user' => $book['publisher']->username]) }}"
                                style="color: var(--tblr-primary); text-decoration: none;">
                                {{ $book['publisher']->nickname }}
                            </a>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td>语言</td>
                        <td>{{ $book['language'] ?? '巴利语' }}</td>
                    </tr>
                </table>

                @if(!empty($book['description']))
                <div class="wiki-content-body">
                    <p>{{ $book['description'] }}</p>
                </div>
                @endif
            </div>

            {{-- 目录 --}}
            @if(isset($book['contents']) && count($book['contents']) > 0)
            <div class="wiki-card">
                <div class="wiki-sidebar-title" style="margin-bottom: 1rem;">目录</div>
                <ul class="wiki-toc-list">
                    @foreach($book['contents'] as $chapter)
                    <li>
                        <a href="{{ route('library.tipitaka.read', $chapter['id']) }}?channel={{ $chapter['channel'] }}">
                            <div style="display: flex;">
                                <div>{{ $chapter['title'] }}</div>
                                <div>
                                    <span style="margin-left: auto; font-size: 0.75rem; color: var(--tblr-secondary);">
                                        {{ $chapter['progress'] }}%
                                    </span>
                                </div>
                            </div>
                            @if(isset($chapter['summary']))
                            <span style="font-size: 0.75rem; color: var(--tblr-secondary); margin-left: 0.5rem;" class="line2">
                                {{ $chapter['summary'] }}
                            </span>
                            @endif
                            @if(isset($chapter['progress']) && $chapter['progress'] > 0)

                            @endif
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

        </main>

        {{-- 右侧边栏 --}}
        <aside class="wiki-sidebar-right">

            {{-- 书籍元信息 --}}
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">书籍信息</div>
                <table class="wiki-meta-table">
                    <tr>
                        <td>语言</td>
                        <td>{{ $book['language'] ?? '巴利语' }}</td>
                    </tr>
                    @if(!empty($book['type']))
                    <tr>
                        <td>类型</td>
                        <td>{{ $book['type'] }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            {{-- 其他版本 --}}
            @if(!empty($otherVersions) && count($otherVersions) > 0)
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">其他版本</div>
                <ul class="wiki-related-list">
                    @foreach($otherVersions as $version)
                    <li>
                        <a href="{{ route('library.tipitaka.show', $version['id']) }}">
                            {{ $version['title'] }}
                            <span class="wiki-related-zh">{{ $version['language'] ?? '巴利语' }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

        </aside>

    </div>
</div>
@endsection
