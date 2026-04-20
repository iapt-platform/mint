{{-- resources/views/library/anthology/index.blade.php --}}
@extends('layouts.library')

@section('title', '文集 · 巴利书库')

@push('styles')
    @vite('resources/css/modules/_anthology.css')
@endpush

@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('library.home') }}">首页</a>
</li>
<li class="breadcrumb-item active">文集</li>
@endsection

@section('hero')
<div class="anthology-page-header">
    <div class="container-xl">
        <h1>文集 <span class="result-badge">{{ $total }}</span></h1>
        <p>经论注疏 · 禅修指引 · 法义探讨</p>
    </div>
</div>
@endsection

@section('content')
<div class="page-body">
    <div class="container-xl">
        <div class="row mt-3">

            {{-- 文集列表 --}}
            <div class="col-lg-9">

                @forelse($anthologies as $item)
                    <x-ui.card-anthology
                        :item="$item"
                        :href="route('library.anthology.show', $item['id'])"
                    />
                @empty
                    <div class="wiki-card">
                        <x-ui.empty-state title="暂无文集" />
                    </div>
                @endforelse

                {{-- 分页 --}}
                <div class="d-flex justify-content-center mt-3">
                    {{ $anthologies->links('library.anthology.pagination') }}
                </div>

            </div>

            {{-- 侧边栏 --}}
            <div class="col-lg-3">

                {{-- 搜索 --}}
                <div class="sb-card" style="padding: .85rem 1.1rem; margin-bottom: 1.1rem;">
                    <x-ui.search-input
                        :action="route('library.search')"
                        placeholder="搜索文集…"
                        :hidden-fields="['type' => 'anthology']"
                    />
                </div>

                {{-- 作者列表 --}}
                @if(!empty($authors))
                <div class="sb-card">
                    <div class="sb-head">作者</div>
                    <ul class="author-ul">
                        @foreach($authors as $author)
                        <li>
                            <a href="#">
                                <x-ui.author-avatar
                                    :avatar="$author['avatar'] ?? null"
                                    :color="$author['color']"
                                    :initials="$author['initials']"
                                    :name="$author['name']"
                                    :sub="$author['count'] . ' 篇文集'"
                                    size="md"
                                />
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

            </div>

        </div>
    </div>
</div>
@endsection
