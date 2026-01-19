@extends('library.layouts.app')

@section('title', __('labels.home'))

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

        @foreach($categoryData as $data)
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <svg class="icon me-2" width="24" height="24">
                        <use xlink:href="#tabler-book"></use>
                    </svg>
                    {{ $data['category']['name'] }}
                </h3>
                <div class="card-actions">
                    <a href="{{ route('library.category.show', $data['category']['id']) }}" class="btn btn-primary btn-sm">
                        {{ __('buttons.more') }}
                        <svg class="icon ms-1" width="24" height="24">
                            <use xlink:href="#tabler-arrow-right"></use>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="card-body">
                @include('components.book-list', ['books' => $data['books']])
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
