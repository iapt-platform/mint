{{-- resources/views/library/tipitaka/channels/show.blade.php
     单个 channel 收录的作品（分页）。
     变量：$channelInfo、$books（书卡片）、$paginator、$sort、$sortList、$topCategories
--}}
@extends('layouts.library')

@section('title', $channelInfo['name'] . ' · ' . __('library.site_name'))

@push('styles')
@vite('resources/css/modules/tipitaka.css')
@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('library.home') }}">{{ __('library.home') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('library.tipitaka.index') }}">{{ __('library.tipitaka') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('library.tipitaka.channels.index') }}">{{ __('library.channels') }}</a></li>
<li class="breadcrumb-item active">{{ $channelInfo['name'] }}</li>
@endsection

@section('content')
<div class="page-body">
    <div class="container-xl">
        @include('library.tipitaka.channels._header', ['tab' => 'all'])
    </div>
    <div class="container-xl wiki-layout wiki-layout--two">

        <aside class="wiki-sidebar-left">
            <div class="wiki-sidebar-section">
                <div class="wiki-sidebar-title">{{ __('library.categories') }}</div>
                <ul class="wiki-cat-list">
                    @foreach($topCategories as $top)
                    @if($top['count'] > 0)
                    <li>
                        <a href="{{ route('library.tipitaka.channels.category', ['channel' => $channelInfo['uid'], 'id' => $top['id']]) }}">
                            {{ $top['name'] }}
                            <span class="wiki-cat-count">{{ $top['count'] }}</span>
                        </a>
                    </li>
                    @endif
                    @endforeach
                </ul>
            </div>
        </aside>

        <main class="wiki-main">
            <div class="tipitaka-sort-bar">
                <span class="tipitaka-sort-bar__count">
                    {{ __('library.total_prefix') }} <strong>{{ $paginator->total() }}</strong> {{ __('library.work_unit') }}
                    · {{ __('library.page_of', ['page' => $paginator->currentPage(), 'last' => $paginator->lastPage()]) }}
                </span>
                <div class="tipitaka-sort-bar__right">
                    <span class="tipitaka-sort-bar__label">{{ __('library.sort') }}</span>
                    <div class="tipitaka-filter-pills">
                        @foreach($sortList as $item)
                        <a href="{{ request()->fullUrlWithQuery(['sort' => $item['key'] === 'canon' ? null : $item['key'], 'page' => null]) }}"
                            class="tipitaka-pill {{ $sort === $item['key'] ? 'tipitaka-pill--active' : '' }}">{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="wiki-card">
                <x-ui.book-grid :books="$books" />
                <div class="tipitaka-pager">{{ $paginator->links('library.anthology.pagination') }}</div>
            </div>
        </main>

    </div>
</div>
@endsection
