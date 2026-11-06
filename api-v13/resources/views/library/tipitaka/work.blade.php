{{-- resources/views/library/tipitaka/work.blade.php
     作品版本页：一部作品在所有公开译本里的版本，版式同译本列表页（按 type 分组 + 语言过滤）。
     变量：$work（TipitakaCatalogService::work()）、$breadcrumbs（分类路径）、$kind、$groups、$languages、$selectedLang
--}}
@extends('layouts.library')

@section('title', $work['title'] . ' · ' . __('library.site_name'))

@push('styles')
@vite('resources/css/modules/tipitaka.css')
@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('library.home') }}">{{ __('library.home') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('library.tipitaka.index') }}">{{ __('library.tipitaka') }}</a></li>
@foreach($breadcrumbs as $breadcrumb)
<li class="breadcrumb-item">
    <a href="{{ route('library.tipitaka.category', ['id' => $breadcrumb['id']]) }}">{{ $breadcrumb['name'] }}</a>
</li>
@endforeach
<li class="breadcrumb-item active">{{ $work['title'] }}</li>
@endsection

@section('content')
<div class="page-body">
    <div class="container-xl">
        <div class="wiki-card tipitaka-channel-head">
            <div class="wiki-entry-header">
                <div class="wiki-entry-title">{{ $work['title'] }}</div>
                <div class="tipitaka-work-pali">
                    @if($kind){{ $kind }} · @endif
                    {{ __('library.total_prefix') }} {{ $languages[0]['count'] }} {{ __('library.version_unit') }}
                </div>
            </div>
        </div>
    </div>
    <div class="container-xl wiki-layout wiki-layout--two">
        @include('library.tipitaka.channels._grouped-list')
    </div>
</div>
@endsection
