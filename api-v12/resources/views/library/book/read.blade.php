{{-- resources/views/library/book/read.blade.php
     全站共用阅读器。供 anthology/read 和 tipitaka/read 路由使用。
     重构：改为 @extends('layouts.reader')，移除 CDN 引入，JS 提取为模块。
--}}
@extends('layouts.reader')

@section('title', $book['title'])

@section('body-class', session('theme', 'light') . '-mode')

{{-- 术语抽屉（所有阅读页统一使用 wiki.term-drawer） --}}
@push('scripts')
@vite('resources/js/modules/term-tooltip.js')
@endpush

@section('reader-content')

{{-- 术语抽屉 --}}
<x-wiki.term-drawer />

{{-- Navbar --}}
<header class="navbar navbar-expand-md navbar-light d-print-none">
    <div class="container-xl">
        <button class="navbar-toggler"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#tocDrawer"
            aria-controls="tocDrawer">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-brand d-flex flex-column lh-1">
            @if(!empty($book['anthology']))
            <small class="text-muted" style="font-size:.75rem;">
                <a href="{{ route('library.anthology.show', $book['anthology']['id']) }}"
                    class="text-muted text-decoration-none">
                    {{ $book['anthology']['title'] }}
                </a>
            </small>
            @endif
        </div>

        <div class="navbar-nav flex-row order-md-last align-items-center">

            {{-- 编辑器按钮 --}}
            @if(!empty($editor_link))
            <div class="nav-item me-2">
                <a href="{{ $editor_link }}" target="_blank" class="nav-link">
                    <i class="ti ti-pencil me-1 d-none d-md-inline"></i>
                    <span class="d-none d-md-inline">编辑器</span>
                    <i class="ti ti-pencil d-md-none"></i>
                </a>
            </div>
            @endif

            {{-- 设置 --}}
            <div class="nav-item me-2">
                <a href="#"
                    class="nav-link"
                    data-bs-toggle="modal"
                    data-bs-target="#settingsModal">
                    <i class="ti ti-settings me-1 d-none d-md-inline"></i>
                    <span class="d-none d-md-inline">设置</span>
                    <i class="ti ti-settings d-md-none"></i>
                </a>
            </div>

            {{-- 版本切换：desktop 版本在右侧边栏展示，mobile 触发 offcanvas --}}
            @if(!empty($channels))
            <div class="nav-item d-md-none me-2">
                <a href="#" class="nav-link"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#channelDrawer">
                    <i class="ti ti-layers"></i>
                </a>
            </div>
            @endif

            {{-- 夜间模式 --}}
            <div class="nav-item">
                <a href="#" class="nav-link" id="themeToggle">
                    <i class="ti ti-moon"></i>
                </a>
            </div>

            @auth
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0"
                    data-bs-toggle="dropdown">
                    <span class="avatar avatar-sm"
                        style="background-image: url({{ auth()->user()->avatar ?? '' }})">
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="{{ route('logout') }}">退出</a>
                </div>
            </div>
            @endauth

        </div>
    </div>
</header>

{{-- 版本 Offcanvas（mobile） --}}
@if(!empty($channels))
<div class="offcanvas offcanvas-end" tabindex="-1" id="channelDrawer">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">选择版本</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <div class="list-group list-group-flush">
            @foreach($channels as $channel)
            <a href="{{ request()->fullUrlWithQuery(['channel' => $channel['id']]) }}"
                class="list-group-item list-group-item-action">
                <div class="fw-bold">{{ $channel['name'] }}</div>
                <small class="text-muted">{{ __('language.' . $channel['lang']) }}</small>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- TOC Offcanvas（mobile） --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="tocDrawer">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">目录</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        @include('library.book._toc', ['toc' => $book['toc'] ?? []])
    </div>
</div>

{{-- 主内容区 --}}
<div class="container-xl">
    <div class="main-container">

        {{-- TOC 侧边栏（tablet+） --}}
        <div class="toc-sidebar card">
            <div class="card-body">
                <h5>目录</h5>
                @include('library.book._toc', ['toc' => $book['toc'] ?? []])
            </div>
        </div>

        {{-- 正文 --}}
        <div class="content-area card">
            <div class="card-body">

                <h2>{{ $book['title'] }}</h2>
                <p>
                    <strong>作者：</strong>
                    {{ $book['author'] }}
                    @if(isset($book['publisher']))
                    @ <a href="{{ route('blog.index', ['user' => $book['publisher']->username]) }}">
                        {{ $book['publisher']->nickname }}
                    </a>
                    @endif
                </p>

                <div class="content">
                    @if(isset($book['content']) && count($book['content']) > 0)
                    @foreach ($book['content'] as $paragraph)
                    <div id="para-{{ $paragraph['id'] }}">
                        @foreach ($paragraph['text'] as $rows)
                        <div style="display:flex;">
                            @foreach ($rows as $col)
                            <div style="flex:1;">
                                @if($paragraph['level'] < 8)
                                    <h{{ $paragraph['level'] }}>{!! $col !!}</h{{ $paragraph['level'] }}>
                                    @else
                                    <p>{!! $col !!}</p>
                                    @endif
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                    @else
                    <div>没有内容</div>
                    @endif
                </div>

                {{-- 上下翻页 --}}
                <div class="mt-6 pt-6">
                    <ul class="pagination">
                        @if(!empty($book['pagination']['prev']))
                        <li class="page-item page-prev">
                            <a class="page-link" href="{{ isset($anthologyId)
                            ? route('library.anthology.read', ['anthology' => $anthologyId, 'article' => $book['pagination']['prev']['id'], 'channel' => request('channel')])
                            : route('library.tipitaka.read', ['id' => $book['pagination']['prev']['id'], 'channel' => request('channel')]) }}">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M15 6l-6 6l6 6"></path>
                                        </svg>
                                    </div>
                                    <div class="col">
                                        <div class="page-item-subtitle">上一篇</div>
                                        <div class="page-item-title">{{ $book['pagination']['prev']['title'] }}</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        @endif
                        @if(!empty($book['pagination']['next']))
                        <li class="page-item page-next">
                            <a class="page-link" href="{{ isset($anthologyId)
                            ? route('library.anthology.read', ['anthology' => $anthologyId, 'article' => $book['pagination']['next']['id'], 'channel' => request('channel')])
                            : route('library.tipitaka.read', ['id' => $book['pagination']['next']['id'], 'channel' => request('channel')]) }}">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="page-item-subtitle">下一篇</div>
                                        <div class="page-item-title">{{ $book['pagination']['next']['title'] }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 6l6 6l-6 6"></path>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>

            </div>
        </div>

        {{-- 右侧边栏 --}}
        <div class="right-sidebar">

            {{-- 版本卡片（desktop，wiki 侧边栏同款） --}}
            @if(!empty($channels))
            <div class="reader-channel-card">
                <div class="reader-channel-title">版本</div>
                <ul class="reader-channel-list">
                    @foreach($channels as $channel)
                    <li>
                        <a href="{{ request()->fullUrlWithQuery(['channel' => $channel['id']]) }}"
                            class="{{ request('channel') == $channel['id'] ? 'active' : '' }}">
                            {{ $channel['name'] }}
                            <span class="reader-channel-lang">{{ __('language.' . $channel['lang']) }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- 下载 --}}
            @if(!empty($book['downloads']))
            <div class="reader-channel-card">
                <div class="reader-channel-title">下载</div>
                <ul class="list-unstyled mb-0">
                    @foreach ($book['downloads'] as $download)
                    <li>
                        <a href="{{ $download['url'] }}" class="btn btn-outline-primary mb-2 w-100">
                            <i class="ti ti-download me-2"></i>{{ $download['format'] }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- 标签 --}}
            @if(!empty($book['tags']))
            <div class="reader-channel-card">
                <div class="reader-channel-title">标签</div>
                @foreach ($book['tags'] as $tag)
                <span class="badge me-1">{{ $tag['name'] }}</span>
                @endforeach
            </div>
            @endif

        </div>

    </div>
</div>

{{-- 阅读设置 Modal --}}
<div class="modal modal-blur fade" id="settingsModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="settingsForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">阅读设置</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <label class="form-label">显示原文</label>
                    <label class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="showOrigin">
                        <span class="form-check-label">开启/关闭原文显示</span>
                    </label>
                </div>
                <div class="mb-4">
                    <label class="form-label">界面语言</label>
                    <select class="form-select" id="uiLanguage">
                        <option value="auto">自动</option>
                        <option value="zh">简体中文</option>
                        <option value="en">英文</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label">巴利文脚本</label>
                    <select class="form-select" id="paliScript">
                        <option value="auto">自动</option>
                        <option value="roman">罗马</option>
                        <option value="myanmar">缅文</option>
                        <option value="thai">泰文</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">取消</button>
                <button type="submit" class="btn btn-primary">确定</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // 夜间模式
    document.getElementById('themeToggle').addEventListener('click', function(e) {
        e.preventDefault();
        const isDark = document.body.classList.contains('dark-mode');
        document.body.classList.toggle('dark-mode', !isDark);
        document.body.classList.toggle('light-mode', isDark);
        this.innerHTML = isDark ?
            '<i class="ti ti-moon"></i>' :
            '<i class="ti ti-sun"></i>';
        fetch('{{ route("theme.toggle") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                theme: isDark ? 'light' : 'dark'
            })
        });
    });

    // 阅读设置
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
        return null;
    }

    function setCookie(name, value, days = 365) {
        const date = new Date();
        date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = name + '=' + encodeURIComponent(value) + '; expires=' + date.toUTCString() + '; path=/';
    }

    function toggleOriginDisplay(show) {
        document.querySelectorAll('.origin').forEach(el => {
            el.style.display = show ? 'unset' : 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const showOrigin = getCookie('show_origin') === 'true';
        document.getElementById('showOrigin').checked = showOrigin;
        document.getElementById('uiLanguage').value = getCookie('ui_language') || 'auto';
        document.getElementById('paliScript').value = getCookie('pali_script') || 'auto';
        toggleOriginDisplay(showOrigin);
    });

    document.getElementById('settingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        setCookie('show_origin', document.getElementById('showOrigin').checked);
        setCookie('ui_language', document.getElementById('uiLanguage').value);
        setCookie('pali_script', document.getElementById('paliScript').value);
        location.reload();
    });
</script>
@endpush
