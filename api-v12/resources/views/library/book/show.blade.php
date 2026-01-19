@extends('library.layouts.app')

@section('title', $book['title'] . ' - 巴利书库')

@section('content')
<div class="page-body">
    <style>
        .line2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            /* 限制显示两行 */
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            /* 超出部分显示省略号 */
        }
    </style>
    <div class="container-xl">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('library.home') }}">{{ __('labels.home') }}</a></li>
                            <li class="breadcrumb-item active">{{ $book['title'] }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <img src="{{ $book['cover'] }}" class="card-img-top" alt="{{ $book['title'] }}" style="max-height: 400px; width: fit-content;">
                    <div class="card-body text-center">
                        <a href="{{ route('library.book.read', $book['id']) }}" class="btn btn-primary btn-lg w-100 mb-2">
                            <svg class="icon me-2" width="24" height="24">
                                <use xlink:href="#tabler-book-2"></use>
                            </svg>
                            {{ __('buttons.online-read') }}
                        </a>
                        <button class="btn btn-outline-secondary w-100">
                            <svg class="icon me-2" width="24" height="24">
                                <use xlink:href="#tabler-download"></use>
                            </svg>
                            下载
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ $book['title'] }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>作者:</strong></div>
                            <div class="col-sm-9">{{ $book['author'] }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>出版:</strong></div>
                            <div class="col-sm-9">
                                <a href="{{ route('blog.index', ['user' => $book['publisher']->username]) }}">
                                    {{ $book['publisher']->nickname }}
                                </a>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>语言:</strong></div>
                            <div class="col-sm-9">{{ $book['language'] ?? '巴利语' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3"><strong>简介:</strong></div>
                            <div class="col-sm-9">{{ $book['description'] }}</div>
                        </div>
                    </div>
                </div>

                @if(isset($book['contents']) && count($book['contents']) > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">目录</h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @foreach($book['contents'] as $chapter)
                            <a href="{{ route('library.book.read', $chapter['id']) }}?chapter={{ $loop->iteration }}"
                                class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h4 class="mb-1">{{ $chapter['title'] }}</h4>
                                    <div class="d-flex" style="width:150px;">
                                        @if($chapter['progress']>0)
                                        <div class="progress">
                                            <div class="progress-bar" style="width: {{ $chapter['progress'] }}%"></div>
                                        </div>
                                        @else
                                        <small>无数据</small>
                                        @endif

                                    </div>

                                </div>
                                @if(isset($chapter['summary']))
                                <p class="mb-1 text-muted line2">{{ $chapter['summary'] }}</p>
                                @endif
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                @if(count($otherVersions) > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">其他版本</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($otherVersions as $version)
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <img src="{{ $version['cover'] }}" class="me-3" style="width: 60px; height: 80px; object-fit: cover;" alt="{{ $version['title'] }}">
                                    <div>
                                        <h6><a href="{{ route('library.book.show', $version['id']) }}">{{ $version['title'] }}</a></h6>
                                        <div class="text-muted small">{{ $version['author'] }}</div>
                                        <div class="text-muted small">{{ $version['language'] ?? '巴利语' }}</div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
