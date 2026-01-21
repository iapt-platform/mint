@extends('library.layouts.app')

@section('title', $currentCategory['name'] . ' - 巴利书库')

@section('content')
<div class="page-body">
    <div class="container-xl">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('library.home') }}">{{ __('labels.home') }}</a></li>
                            @foreach($breadcrumbs as $breadcrumb)
                            @if($loop->last)
                            <li class="breadcrumb-item active">{{ $breadcrumb['name'] }}</li>
                            @else
                            <li class="breadcrumb-item">
                                <a href="{{ route('library.category.show', $breadcrumb['id']) }}">{{ $breadcrumb['name'] }}</a>
                            </li>
                            @endif
                            @endforeach
                        </ol>
                    </nav>
                    <h2 class="page-title">{{ $currentCategory['name'] }}</h2>
                </div>
            </div>
        </div>

        @if(count($subCategories) > 0)
        <div class="row row-cards mb-4">
            @foreach($subCategories as $subCategory)
            <div class="col-sm-6 col-lg-3">
                <div class="card">
                    <div class="card-body text-center">
                        <a href="{{ route('library.category.show', $subCategory['id']) }}" class="btn btn-primary">
                            {{ $subCategory['name'] }}
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif



        <div class="card">
            <div class="card-header">
                <h3 class="card-title">图书列表</h3>
            </div>
            <div class="card-body">
                @include('components.book-list', ['books' => $categoryBooks])
            </div>
        </div>

    </div>
</div>
@endsection
