{{-- resources/views/library/tipitaka/show.blade.php --}}
@extends('layouts.library')

@section('title', $book['title'] . ' · ' . __('library.site_name'))

@push('styles')
@vite('resources/css/modules/tipitaka.css')
@endpush

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('library.home') }}">{{ __('library.home') }}</a>
</li>
<li class="breadcrumb-item">
    <a href="{{ route('library.tipitaka.index') }}">{{ __('library.tipitaka') }}</a>
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
                        {{ __('library.read_online') }}
                    </a>
                    <button class="btn btn-outline-secondary w-100">
                        <i class="ti ti-download me-1"></i>
                        {{ __('library.download') }}
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
                        <td>{{ __('library.author') }}</td>
                        <td>{{ $book['author'] }}</td>
                    </tr>
                    @if(isset($book['publisher']))
                    <tr>
                        <td>{{ __('library.publisher') }}</td>
                        <td>
                            <a href="{{ route('blog.index', ['user' => $book['publisher']->username]) }}"
                                style="color: var(--tblr-primary); text-decoration: none;">
                                {{ $book['publisher']->nickname }}
                            </a>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td>{{ __('library.language') }}</td>
                        <td>{{ $book['language'] ?? __('library.pali') }}</td>
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
                <div class="wiki-sidebar-title" style="margin-bottom: 1rem;">{{ __('library.toc') }}</div>
                <ul class="wiki-toc-list">
                    @foreach($book['contents'] as $chapter)
                    <li>
                        <a href="{{ route('library.tipitaka.read', $chapter['id']) }}?channel={{ $chapter['channel'] }}" target="_blank">
                            <div class="toc-item">
                                <div class="toc-title">
                                    {{ $chapter['title'] }}
                                </div>

                                <div class="toc-progress">
                                    @php
                                    $p = $chapter['progress'];
                                    $color = $p >= 80 ? 'bg-green' : ($p >= 30 ? 'bg-yellow' : 'bg-red');
                                    @endphp

                                    <div class="progress" style="height: 6px;">
                                        <div
                                            class="progress-bar {{ $color }}"
                                            style="width: {{ $p }}%">
                                        </div>
                                    </div>
                                    <div class="toc-progress-text">
                                        {{ $p }}%
                                    </div>
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
                <div class="wiki-sidebar-title">{{ __('library.book_info') }}</div>
                <table class="wiki-meta-table">
                    <tr>
                        <td>{{ __('library.language') }}</td>
                        <td>{{ $book['language'] ?? __('library.pali') }}</td>
                    </tr>
                    @if(!empty($book['type']))
                    <tr>
                        <td>{{ __('library.type') }}</td>
                        <td>{{ $book['type'] }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            {{-- 其他版本 --}}
            @if(!empty($otherVersions) && count($otherVersions) > 0)
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">{{ __('library.other_versions') }}</div>
                <ul class="wiki-related-list">
                    @foreach($otherVersions as $version)
                    <li>
                        <a href="{{ route('library.tipitaka.show', $version['id']) }}">
                            {{ $version['title'] }}
                            <span class="wiki-related-zh">{{ $version['language'] ?? __('library.pali') }}</span>
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
