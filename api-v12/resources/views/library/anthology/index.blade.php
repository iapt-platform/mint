@extends('library.layouts.app')

@section('title', '文集 · 巴利书库')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght@400;600;700&family=Noto+Sans+SC:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root {
    --sf: #c8860a;
    --sf-light: #f5e6c8;
    --sf-pale: #fdf8f0;
    --ink: #1a1208;
    --ink-soft: #4a3f2f;
    --ink-muted: #8a7a68;
    --bdr: #e8ddd0;
    --card-bg: #fffdf9;
}
body { background: var(--sf-pale) !important; font-family: 'Noto Sans SC', sans-serif; }

/* Page header */
.anthology-page-header {
    background: linear-gradient(135deg, var(--ink) 0%, #2d2010 100%);
    padding: 2.25rem 0 2rem;
    position: relative;
    overflow: hidden;
    margin-bottom: 0;
}
.anthology-page-header::before {
    content: '藏';
    font-family: 'Noto Serif SC', serif;
    font-size: 16rem;
    font-weight: 700;
    color: rgba(255,255,255,0.03);
    position: absolute;
    right: -1rem;
    top: -2.5rem;
    line-height: 1;
    pointer-events: none;
}
.anthology-page-header h1 {
    font-family: 'Noto Serif SC', serif;
    font-size: 1.75rem;
    font-weight: 600;
    color: #fff;
    margin: 0 0 0.3rem;
    letter-spacing: 0.08em;
}
.anthology-page-header p { color: rgba(255,255,255,0.45); font-size: 0.85rem; margin: 0; }
.result-badge {
    background: var(--sf);
    color: var(--ink);
    font-size: 0.75rem;
    font-weight: 700;
    padding: 2px 9px;
    border-radius: 20px;
    margin-left: 0.6rem;
    vertical-align: middle;
}

/* Card */
.anthology-card {
    background: var(--card-bg);
    border: 1px solid var(--bdr);
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    transition: box-shadow .25s, transform .25s;
    margin-bottom: 1.1rem;
    text-decoration: none;
    color: inherit;
}
.anthology-card:hover {
    box-shadow: 0 8px 28px rgba(200,134,10,.12), 0 2px 8px rgba(0,0,0,.06);
    transform: translateY(-2px);
    color: inherit;
    text-decoration: none;
}
.card-cover {
    width: 130px;
    min-width: 130px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.1rem 0.7rem;
    position: relative;
    overflow: hidden;
}
.card-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    inset: 0;
}
.card-cover::after {
    content: '';
    position: absolute;
    inset: 0;
    background: repeating-linear-gradient(45deg, transparent, transparent 8px, rgba(255,255,255,.015) 8px, rgba(255,255,255,.015) 9px);
}
.cover-text-wrap { position: relative; z-index: 1; text-align: center; }
.cover-title {
    font-family: 'Noto Serif SC', serif;
    font-size: 1rem;
    font-weight: 600;
    color: #fff;
    line-height: 1.6;
    letter-spacing: .12em;
    word-break: break-all;
}
.cover-divider { width: 28px; height: 1px; background: var(--sf); margin: .5rem auto; }
.cover-sub { font-size: .65rem; color: rgba(255,255,255,.45); letter-spacing: .04em; }

.card-body-inner { padding: 1.1rem 1.4rem; flex: 1; display: flex; flex-direction: column; }
.card-main-title {
    font-family: 'Noto Serif SC', serif;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: .35rem;
    line-height: 1.4;
}
.anthology-card:hover .card-main-title { color: var(--sf); }
.card-desc { font-size: .8rem; color: var(--ink-muted); margin-bottom: .65rem; line-height: 1.65; }
.card-author { display: flex; align-items: center; gap: .45rem; margin-bottom: .7rem; }
.a-avatar {
    width: 24px; height: 24px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .65rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
.a-avatar-img {
    width: 24px; height: 24px; border-radius: 50%;
    object-fit: cover; flex-shrink: 0;
}
.a-name { font-size: .8rem; color: var(--ink-soft); font-weight: 500; }
.chapter-tags { display: flex; flex-wrap: wrap; gap: .3rem; margin-top: auto; }
.c-tag {
    font-size: .7rem; color: var(--ink-muted);
    background: var(--sf-light); border: 1px solid var(--bdr);
    padding: 1px 7px; border-radius: 4px; white-space: nowrap;
}
.c-tag.more { background: transparent; border-color: transparent; color: var(--sf); }
.card-meta-row {
    display: flex; align-items: center; gap: .85rem;
    margin-top: .65rem; padding-top: .65rem;
    border-top: 1px solid var(--bdr);
}
.meta-it { font-size: .72rem; color: var(--ink-muted); display: flex; align-items: center; gap: .25rem; }

/* Pagination — force horizontal */
.anthology-pagination .pagination {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: wrap;
    gap: 3px;
    justify-content: center;
    list-style: none;
    padding: 0;
    margin: 0;
}
.anthology-pagination .page-item { display: inline-block; }
.anthology-pagination .page-item .page-link {
    border: 1px solid var(--bdr);
    color: var(--ink-soft);
    font-size: .82rem;
    padding: 5px 11px;
    background: var(--card-bg);
    border-radius: 5px;
    display: inline-block;
    line-height: 1.5;
    text-decoration: none;
}
.anthology-pagination .page-item.active .page-link { background: var(--sf); border-color: var(--sf); color: #fff; }
.anthology-pagination .page-item .page-link:hover { background: var(--sf-light); color: var(--sf); }
.anthology-pagination .page-item.disabled .page-link { opacity: .45; pointer-events: none; }

/* Sidebar */
.sidebar-box {
    background: var(--card-bg);
    border: 1px solid var(--bdr);
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 1.1rem;
}
.sb-header {
    padding: .75rem 1.15rem;
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
.sb-header::before {
    content: '';
    display: block;
    width: 3px;
    height: 13px;
    background: var(--sf);
    border-radius: 2px;
}
.author-ul { list-style: none; padding: .35rem 0; margin: 0; }
.author-ul li a {
    display: flex; align-items: center; gap: .6rem;
    padding: .45rem 1.15rem;
    text-decoration: none;
    transition: background .15s;
}
.author-ul li a:hover { background: var(--sf-pale); }
.au-avatar {
    width: 28px; height: 28px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .68rem; font-weight: 700; color: #fff; flex-shrink: 0;
}
.au-avatar-img {
    width: 28px; height: 28px; border-radius: 50%;
    object-fit: cover; flex-shrink: 0;
}
.au-name { font-size: .8rem; color: var(--ink-soft); font-weight: 500; display: block; }
.au-count { font-size: .7rem; color: var(--ink-muted); }

/* Search */
.search-wrap { background: var(--card-bg); border: 1px solid var(--bdr); border-radius: 10px; padding: .85rem 1.1rem; margin-bottom: 1.1rem; }
.search-input {
    width: 100%;
    border: 1px solid var(--bdr);
    border-radius: 6px;
    padding: .45rem .9rem;
    font-size: .83rem;
    font-family: 'Noto Sans SC', sans-serif;
    background: var(--sf-pale);
    color: var(--ink);
    outline: none;
    transition: border-color .2s;
}
.search-input:focus { border-color: var(--sf); background: #fff; }
.search-input::placeholder { color: var(--ink-muted); }

@media (max-width:768px) {
    .anthology-card { flex-direction: column; }
    .card-cover { width: 100%; min-width: unset; height: 90px; }
}
</style>
@endpush

@section('content')
<div class="anthology-page-header">
    <div class="container-xl">
        <h1>文集 <span class="result-badge">{{ $total }}</span></h1>
        <p>经论注疏 · 禅修指引 · 法义探讨</p>
    </div>
</div>

<div class="page-body" style="background: var(--sf-pale);">
    <div class="container-xl">
        <div class="row mt-3">

            {{-- Left --}}
            <div class="col-lg-9">

                @forelse($anthologies as $item)
                <a href="{{ route('library.anthology.show', $item['id']) }}" class="anthology-card">
                    {{-- Cover --}}
                    <div class="card-cover" style="{{ $item['cover_image'] ? '' : 'background: ' . $item['cover_gradient'] }}">
                        @if($item['cover_image'])
                            <img src="{{ $item['cover_image'] }}" alt="{{ $item['title'] }}">
                        @else
                            <div class="cover-text-wrap">
                                <div class="cover-title">{{ $item['title'] }}</div>
                                <div class="cover-divider"></div>
                                <div class="cover-sub">{{ $item['subtitle'] ?? '' }}</div>
                            </div>
                        @endif
                    </div>

                    {{-- Body --}}
                    <div class="card-body-inner">
                        <div class="card-main-title">{{ $item['title'] }}</div>
                        @if(!empty($item['description']))
                        <div class="card-desc">{{ $item['description'] }}</div>
                        @endif
                        <div class="card-author">
                            @if(!empty($item['author']['avatar']))
                                <img src="{{ $item['author']['avatar'] }}" class="a-avatar-img" alt="">
                            @else
                                <div class="a-avatar" style="background: {{ $item['author']['color'] }}">
                                    {{ $item['author']['initials'] }}
                                </div>
                            @endif
                            <span class="a-name">{{ $item['author']['name'] }}</span>
                        </div>
                        <div class="chapter-tags">
                            @foreach(array_slice($item['chapters'], 0, 4) as $ch)
                            <span class="c-tag" title="{{ $ch }}">{{ mb_strimwidth($ch, 0, 14, '…') }}</span>
                            @endforeach
                            @if($item['children_number'] > 4)
                            <span class="c-tag more">+{{ $item['children_number'] - 4 }} 章</span>
                            @endif
                        </div>
                        <div class="card-meta-row">
                            <span class="meta-it">
                                <i class="ti ti-calendar" style="font-size:.82rem;"></i>
                                {{ $item['updated_at'] }}
                            </span>
                            <span class="meta-it">
                                <i class="ti ti-file-text" style="font-size:.82rem;"></i>
                                {{ $item['children_number'] }} 章节
                            </span>
                        </div>
                    </div>
                </a>
                @empty
                <div class="text-center py-5 text-muted">暂无文集</div>
                @endforelse

                {{-- Pagination --}}
                <div class="d-flex justify-content-center mt-3 anthology-pagination">
                    {{ $anthologies->links('library.anthology.pagination') }}
                </div>

            </div>{{-- /col --}}

            {{-- Sidebar --}}
            <div class="col-lg-3">
                <div class="search-wrap">
                    <input type="text" class="search-input" placeholder="搜索文集…">
                </div>

                <div class="sidebar-box">
                    <div class="sb-header">作者</div>
                    <ul class="author-ul">
                        @foreach($authors as $author)
                        <li>
                            <a href="#">
                                @if(!empty($author['avatar']))
                                    <img src="{{ $author['avatar'] }}" class="au-avatar-img" alt="">
                                @else
                                    <div class="au-avatar" style="background: {{ $author['color'] }}">{{ $author['initials'] }}</div>
                                @endif
                                <div>
                                    <span class="au-name">{{ $author['name'] }}</span>
                                    <span class="au-count">{{ $author['count'] }} 篇文集</span>
                                </div>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
