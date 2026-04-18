{{-- api-v12/resources/views/library/index.blade.php --}}
@extends('library.layouts.app')

@section('title', __('labels.home'))

@section('hero')
<section class="hero-section" style="background-image: url('{{ URL::asset("assets/images/hero-2.jpg") }}')">

    <div class="hero-overlay"></div>

    <div class="hero-content">
        <h1 class="hero-title">巴利书库</h1>
        <p class="hero-subtitle">探索wikipali，开启智慧之门</p>

        <div class="search-box">
            <div class="input-group">
                <input
                    type="text"
                    class="form-control form-control-lg"
                    placeholder="搜索图书、作者或主题...">

                <button class="btn btn-primary btn-lg" type="button">
                    <i class="ti ti-search"></i>
                </button>
            </div>
        </div>
    </div>

</section>
@endsection


@section('content')

<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">巴利书库</h2>
                    <div class="text-muted mt-1">探索古老的佛教经典</div>
                </div>
            </div>
        </div>

        {{-- ✅ 所有卡片的统一容器 --}}
        <div class="row g-4">
            @foreach($categoryData as $data)
            {{-- ✅ 响应式列 --}}
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <svg class="icon me-2" width="24" height="24">
                                <use xlink:href="#tabler-book"></use>
                            </svg>
                            {{ $data['category']['name'] }}
                        </h3>
                        <div class="card-actions">
                            <a href="{{ route('library.category.show', $data['category']['id']) }}"
                                class="btn btn-primary btn-sm">
                                {{ __('buttons.more') }}
                                <svg class="icon ms-1" width="24" height="24">
                                    <use xlink:href="#tabler-arrow-right"></use>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        @foreach($data['children'] as $child)
                        <div class="mb-1">
                            <a href="{{ route('library.category.show', $child['id']) }}">
                                {{ $child['name'] }}
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">
                    update
                </h3>
            </div>
            <div class="card-body">
                @include('components.book-list', ['books' => $update])
            </div>
        </div>
    </div>
</div>
@endsection
