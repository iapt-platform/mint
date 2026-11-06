{{-- resources/views/library/tipitaka/show.blade.php
     作品详情：某 channel 的某部作品。
     变量：$book（TipitakaCatalogService::bookCard() + channel / pali_title / contents / categories / read_url）、
           $otherVersions（同作品的其他公开 channel）、$relatedWorks（同分类、本 channel 收录的其他作品）
--}}
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
<li class="breadcrumb-item">
    <a href="{{ route('library.tipitaka.channels.index') }}">{{ __('library.channels') }}</a>
</li>
<li class="breadcrumb-item">
    <a href="{{ route('library.tipitaka.channels.show', ['channel' => $book['channel']['uid']]) }}">{{ $book['channel']['name'] }}</a>
</li>
<li class="breadcrumb-item active">{{ $book['title'] }}</li>
@endsection

@section('content')
<div class="page-body">
    <div class="container-xl wiki-layout">

        {{-- 左侧边栏：封面 + 操作按钮 --}}
        <aside class="wiki-sidebar-left">
            <div class="wiki-sidebar-section" style="padding: 1rem;">

                <x-ui.book-cover
                    :image="$book['cover']"
                    :gradient="$book['cover_gradient']"
                    :title="$book['pali_title']"
                    :subtitle="$book['subTitle'] ?? ''"
                    size="md"
                    :style3d="false"
                    style="width: 100%; min-width: unset; height: 220px;" />

                <div style="margin-top: 1rem;">
                    <a href="{{ $book['read_url'] }}" class="btn btn-primary w-100 mb-2">
                        <i class="ti ti-book-2 me-1"></i>
                        {{ __('library.read_online') }}
                    </a>
                    {{-- FIXME: 下载还没有实现 --}}
                    <button class="btn btn-outline-secondary w-100" disabled>
                        <i class="ti ti-download me-1"></i>
                        {{ __('library.download') }}
                    </button>
                </div>

            </div>
        </aside>

        {{-- 主内容区 --}}
        <main class="wiki-main">

            <div class="wiki-card">
                <div class="wiki-entry-header">
                    <div class="wiki-entry-title">{{ $book['title'] }}</div>
                    @if($book['pali_title'] !== $book['title'])
                    <div class="tipitaka-work-pali">{{ $book['pali_title'] }}</div>
                    @endif
                </div>

                <table class="wiki-meta-table" style="margin-bottom: 1.25rem;">
                    <tr>
                        <td>{{ __('library.channel') }}</td>
                        <td>
                            <a href="{{ route('library.tipitaka.channels.show', ['channel' => $book['channel']['uid']]) }}">{{ $book['channel']['name'] }}</a>
                            <span class="card-book__badge">{{ $book['type'] }}</span>
                        </td>
                    </tr>
                    @if(!empty($book['publisher']))
                    <tr>
                        <td>{{ __('library.publisher') }}</td>
                        <td>
                            <a href="{{ route('blog.index', ['user' => $book['publisher']->username]) }}">
                                {{ $book['publisher']->nickname }}
                            </a>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td>{{ __('library.language') }}</td>
                        <td>{{ $book['language'] }}</td>
                    </tr>
                    @if(!empty($book['categories']))
                    <tr>
                        <td>{{ __('library.category') }}</td>
                        <td>
                            @foreach($book['categories'] as $category)
                            <a href="{{ $category['url'] }}">{{ $category['name'] }}</a>@if(!$loop->last) › @endif
                            @endforeach
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td>{{ __('library.progress') }}</td>
                        <td><x-ui.progress-bar :value="$book['progress']" style="max-width: 240px" /></td>
                    </tr>
                    @if(!empty($book['updated_at']))
                    <tr>
                        <td>{{ __('library.updated') }}</td>
                        <td>{{ $book['updated_at'] }}</td>
                    </tr>
                    @endif
                </table>

                @if(!empty($book['description']))
                <div class="wiki-content-body">
                    <p>{{ $book['description'] }}</p>
                </div>
                @endif
            </div>

            {{-- 目录：无译文的章节置灰不可点 --}}
            @if(!empty($book['contents']))
            <div class="wiki-card">
                <div class="wiki-sidebar-title" style="margin-bottom: 1rem;">{{ __('library.toc') }}</div>
                <ul class="wiki-toc-list">
                    @foreach($book['contents'] as $chapter)
                    <li class="{{ $chapter['disabled'] ? 'toc-disabled' : '' }}">
                        @if($chapter['disabled'])
                        <div class="toc-item">
                            <div class="toc-title">{{ $chapter['title'] }}</div>
                        </div>
                        @else
                        <a href="{{ route('library.tipitaka.channels.chapter', ['channel' => $chapter['channel'], 'chapter' => $chapter['id']]) }}">
                            <div class="toc-item">
                                <div class="toc-title">{{ $chapter['title'] }}</div>
                                <x-ui.progress-bar :value="$chapter['progress']" class="toc-progress" />
                            </div>
                            @if(!empty($chapter['summary']))
                            <span class="line2 tipitaka-toc-summary">{{ $chapter['summary'] }}</span>
                            @endif
                        </a>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

        </main>

        {{-- 右侧边栏 --}}
        <aside class="wiki-sidebar-right">

            @if(!empty($otherVersions))
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">{{ __('library.other_versions') }}</div>
                <ul class="wiki-related-list">
                    @foreach($otherVersions as $version)
                    <li>
                        <a href="{{ $version['url'] }}">
                            {{ $version['name'] }}
                            <span class="wiki-related-zh">{{ __('language.'.$version['lang']) }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($relatedWorks))
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">{{ __('library.related_works') }}</div>
                <ul class="wiki-related-list">
                    @foreach($relatedWorks as $related)
                    <li><a href="{{ $related['url'] }}">{{ $related['title'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            @endif

        </aside>

    </div>
</div>
@endsection
