{{-- resources/views/library/tipitaka/channels/index.blade.php
     公开 channel 列表：一个列表，按收录作品数降序，分页（线上 channel 多，不全部列出）。
     变量：$channels（当前页）、$paginator、$types / $languages（过滤项）、$selectedType、$selectedLang
--}}
@extends('layouts.library')

@section('title', __('library.channels') . ' · ' . __('library.site_name'))

@push('styles')
@vite('resources/css/modules/tipitaka.css')
@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('library.home') }}">{{ __('library.home') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('library.tipitaka.index') }}">{{ __('library.tipitaka') }}</a></li>
<li class="breadcrumb-item active">{{ __('library.channels') }}</li>
@endsection

@section('content')
<div class="page-body">
    <div class="container-xl wiki-layout wiki-layout--two">

        <aside class="wiki-sidebar-left">
            @foreach([['key' => 'type', 'title' => __('library.type'), 'options' => $types, 'selected' => $selectedType],
                      ['key' => 'channel_lang', 'title' => __('library.language'), 'options' => $languages, 'selected' => $selectedLang]] as $filter)
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">{{ $filter['title'] }}</div>
                <div class="tipitaka-filter-pills">
                    @foreach($filter['options'] as $opt)
                    <a href="{{ request()->fullUrlWithQuery([$filter['key'] => $opt['value'] === 'all' ? null : $opt['value'], 'page' => null]) }}"
                        class="tipitaka-pill {{ $filter['selected'] === $opt['value'] ? 'tipitaka-pill--active' : '' }}">
                        {{ $opt['label'] }}
                        <span class="tipitaka-pill-count">{{ $opt['count'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endforeach
        </aside>

        <main class="wiki-main">
            <div class="tipitaka-sort-bar">
                <span class="tipitaka-sort-bar__count">
                    {{ __('library.total_prefix') }} <strong>{{ $paginator->total() }}</strong> {{ __('library.channel_unit') }}
                    · {{ __('library.page_of', ['page' => $paginator->currentPage(), 'last' => $paginator->lastPage()]) }}
                </span>
            </div>
            <div class="wiki-card">
                @forelse($channels as $channel)
                @include('library.tipitaka.channels._row', ['channel' => $channel, 'showType' => true])
                @empty
                <x-ui.empty-state :title="__('library.no_channels')" />
                @endforelse
                <div class="tipitaka-pager">{{ $paginator->links('library.anthology.pagination') }}</div>
            </div>
        </main>

    </div>
</div>
@endsection
