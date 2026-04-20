{{-- resources/views/library/index.blade.php
     Library 门户首页。
--}}
@extends('layouts.library')

@section('title', '巴利书库')

@section('hero')
<section class="hero-section"
         style="background-image: url('{{ URL::asset('assets/images/hero-2.jpg') }}')">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="hero-title">巴利书库</h1>
        <p class="hero-subtitle">探索 WikiPāli，开启智慧之门</p>
        <div class="search-box">
            <x-ui.search-input
                :action="route('library.search')"
                placeholder="搜索图书、作者或主题…"
                size="lg"
            />
        </div>
    </div>
</section>
@endsection

@section('content')
<div class="page-body">
    <div class="container-xl">

        {{-- 分类卡片 --}}
        <div class="row g-4 mt-1">
            @foreach($categoryData as $data)
            <div class="col-12 col-md-6 col-lg-3">
                <div class="wiki-card h-100">
                    <div class="wiki-sidebar-title" style="margin-bottom: 0.75rem;">
                        <i class="ti ti-book me-1"></i>
                        {{ $data['category']['name'] }}
                        <a href="{{ route('library.tipitaka.category', ['id' => $data['category']['id']]) }}"
                           class="btn btn-sm btn-primary ms-auto" style="float:right; font-size: 0.75rem;">
                            更多 <i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                    <ul class="wiki-cat-list">
                        @foreach($data['children'] as $child)
                        <li>
                            <a href="{{ route('library.tipitaka.category', ['id' => $child['id']]) }}">
                                {{ $child['name'] }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endforeach
        </div>

        {{-- 最近更新 --}}
        @isset($update)
        <div class="wiki-card mt-4">
            <div class="wiki-sidebar-title" style="margin-bottom: 1rem;">最近更新</div>
            <x-ui.book-grid :books="$update" />
        </div>
        @endisset

    </div>
</div>
@endsection
