@extends('library.layouts.app')

@section('title', __('labels.home'))

@section('content')
<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">下载</h2>
                    <div class="text-muted mt-1">APP 离线数据包</div>
                </div>
                <div class="col-auto ms-auto">
                    <span class="badge bg-blue-lt fs-6">{{ count($packets) }} 个数据包</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-archive me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M3 4m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" />
                        <path d="M5 8v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-10" />
                        <path d="M10 12l2 2l2 -2" />
                    </svg>
                    离线数据包
                </h3>
            </div>

            @php
            function formatFilesize(int $size): string {
            if ($size >= 1048576) return number_format($size / 1048576, 2) . ' MB';
            if ($size >= 1024) return number_format($size / 1024, 1) . ' KB';
            return $size . ' B';
            }
            @endphp

            @if(empty($packets))
            {{-- ── Empty state ── --}}
            <div class="card-body">
                <div class="empty">
                    <div class="empty-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-inbox-off" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M8 4h11a2 2 0 0 1 2 2v9m-1.172 2.821a2 2 0 0 1 -.828 .179h-14a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 1.172 -1.821" />
                            <path d="M4 12h4l3 3h2l3 -3h1" />
                            <path d="M3 3l18 18" />
                        </svg>
                    </div>
                    <p class="empty-title">暂无数据包</p>
                    <p class="empty-subtitle text-muted">当前没有可用的离线数据包。</p>
                </div>
            </div>

            @else

            {{-- ════════════════════════════════════════════
                     ① 桌面端 Table  ≥ md (768px)
                     ════════════════════════════════════════════ --}}
            <div class="d-none d-md-block table-responsive">
                <table class="table table-vcenter card-table table-hover">
                    <thead>
                        <tr>
                            <th>数据包名称</th>
                            <th class="text-center" style="width:90px">文件大小</th>
                            <th class="text-center" style="width:90px">最低版本</th>
                            <th class="text-center" style="width:160px">创建时间</th>
                            <th style="min-width:240px">下载链接</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packets as $packet)
                        <tr>
                            {{-- 名称 --}}
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm me-3 bg-blue-lt text-blue">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-zip" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M6 20.735a2 2 0 0 1 -1 -1.735v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-1" />
                                            <path d="M11 17a2 2 0 0 1 2 2v2a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1v-2a2 2 0 0 1 2 -2z" />
                                            <path d="M11 5l-1 0" />
                                            <path d="M13 7l-1 0" />
                                            <path d="M11 9l-1 0" />
                                            <path d="M13 11l-1 0" />
                                            <path d="M11 13l-1 0" />
                                            <path d="M13 15l-1 0" />
                                        </svg>
                                    </span>
                                    <div>
                                        <div class="fw-semibold text-body">{{ $packet['title'] ?? $packet['id'] }}</div>
                                        <div class="text-muted small font-monospace">{{ $packet['filename'] ?? '' }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- 大小 --}}
                            <td class="text-center">
                                <span class="badge bg-azure-lt text-azure">
                                    {{ formatFilesize((int)($packet['filesize'] ?? 0)) }}
                                </span>
                            </td>

                            {{-- 最低版本 --}}
                            <td class="text-center">
                                @if(!empty($packet['min_app_ver']))
                                <span class="badge bg-lime-lt text-lime">v{{ $packet['min_app_ver'] }}</span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- 创建时间 --}}
                            <td class="text-center text-muted small">
                                {{ $packet['create_at'] ?? '—' }}
                            </td>

                            {{-- 下载链接 --}}
                            <td>
                                @if(!empty($packet['url']) && is_array($packet['url']))
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($packet['url'] as $index => $urlItem)
                                    <a href="{{ $urlItem['link'] }}"
                                        class="btn btn-sm {{ $index === 0 ? 'btn-primary' : 'btn-outline-secondary' }}"
                                        target="_blank" rel="noopener noreferrer"
                                        title="{{ $urlItem['hostname'] ?? '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                            <path d="M7 11l5 5l5 -5" />
                                            <path d="M12 4l0 12" />
                                        </svg>
                                        {{ $urlItem['hostname'] ?? '下载' }}
                                    </a>
                                    @endforeach
                                </div>
                                @else
                                <span class="text-muted">暂无链接</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ════════════════════════════════════════════
                     ② 移动端 Card list  < md (768px)
                     ════════════════════════════════════════════ --}}
            <div class="d-md-none">
                <div class="list-group list-group-flush">
                    @foreach($packets as $packet)
                    <div class="list-group-item p-3">

                        {{-- 顶部：图标 + 标题 + 大小 badge --}}
                        <div class="d-flex align-items-start gap-3">
                            <span class="avatar avatar-md bg-blue-lt text-blue flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-zip" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M6 20.735a2 2 0 0 1 -1 -1.735v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-1" />
                                    <path d="M11 17a2 2 0 0 1 2 2v2a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1v-2a2 2 0 0 1 2 -2z" />
                                    <path d="M11 5l-1 0" />
                                    <path d="M13 7l-1 0" />
                                    <path d="M11 9l-1 0" />
                                    <path d="M13 11l-1 0" />
                                    <path d="M11 13l-1 0" />
                                    <path d="M13 15l-1 0" />
                                </svg>
                            </span>
                            <div class="flex-fill min-w-0 overflow-hidden">
                                <div class="fw-semibold text-body text-truncate">{{ $packet['title'] ?? $packet['id'] }}</div>
                                <div class="text-muted small font-monospace text-truncate">
                                    {{ $packet['filename'] ?? '' }}
                                </div>
                            </div>
                            <span class="badge bg-azure-lt text-azure flex-shrink-0">
                                {{ formatFilesize((int)($packet['filesize'] ?? 0)) }}
                            </span>
                        </div>

                        {{-- 中部：时间 + 最低版本 --}}
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-2 ps-1">
                            <span class="text-muted small">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-calendar me-1" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                    <path d="M16 3v4" />
                                    <path d="M8 3v4" />
                                    <path d="M4 11h16" />
                                    <path d="M11 15h1" />
                                    <path d="M12 15v3" />
                                </svg>
                                {{ $packet['create_at'] ?? '—' }}
                            </span>
                            @if(!empty($packet['min_app_ver']))
                            <span class="badge bg-lime-lt text-lime">
                                最低版本 v{{ $packet['min_app_ver'] }}
                            </span>
                            @endif
                        </div>

                        {{-- 底部：下载按钮，全宽竖排 --}}
                        @if(!empty($packet['url']) && is_array($packet['url']))
                        <div class="d-flex flex-column gap-2 mt-3">
                            @foreach($packet['url'] as $index => $urlItem)
                            <a href="{{ $urlItem['link'] }}"
                                class="btn btn-sm {{ $index === 0 ? 'btn-primary' : 'btn-outline-secondary' }} w-100"
                                target="_blank" rel="noopener noreferrer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                    <path d="M7 11l5 5l5 -5" />
                                    <path d="M12 4l0 12" />
                                </svg>
                                {{ $urlItem['hostname'] ?? '下载' }}
                            </a>
                            @endforeach
                        </div>
                        @endif

                    </div>{{-- /.list-group-item --}}
                    @endforeach
                </div>
            </div>

            @endif
        </div>{{-- /.card --}}
    </div>
</div>
@endsection
