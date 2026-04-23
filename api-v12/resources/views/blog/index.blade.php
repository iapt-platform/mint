{{-- resources/views/blog/index.blade.php --}}
@extends('blog.layouts.app')

@section('title', $user['nickName'])

@section('content')
<h2 class="section-title">推荐阅读</h2>
<div class="subsection-list">
    <div class="article-list--tile">
        <article class="has-image"><a href="/categories/documentation/">
                <div class="article-image"><img alt="Featured image of post Documentation" height="150" loading="lazy" src="/categories/documentation/hutomo-abrianto-l2jk-uxb1BY-unsplash_hu_543ba73fab19d0c8.jpg" srcset="/categories/documentation/hutomo-abrianto-l2jk-uxb1BY-unsplash_hu_543ba73fab19d0c8.jpg 1x, /categories/documentation/hutomo-abrianto-l2jk-uxb1BY-unsplash_hu_2b27d90ffb967881.jpg 2x" width="250"></div>
                <div class="article-details" style="background:linear-gradient(0deg,#bc8d7280 0%,#d0cfcdC0 100%)">
                    <h2 class="article-title">Documentation</h2>
                </div>
            </a></article>
        <article><a href="/categories/syntax/">
                <div class="article-details">
                    <h2 class="article-title">Syntax</h2>
                </div>
            </a></article>
        <article><a href="/categories/themes/">
                <div class="article-details">
                    <h2 class="article-title">Themes</h2>
                </div>
            </a></article>
    </div>
</div>
<section class="article-list">
    @forelse($posts as $post)
    <article class="{{ !empty($post->cover) ? 'has-image' : '' }}">
        <header class="article-header">

            @if(!empty($post->cover))
            <div class="article-image">
                <a href="{{ route('library.tipitaka.read', ['id' => $post['uid']]) }}">
                    <img src="{{ $post->cover }}"
                        width="800" height="450"
                        loading="lazy"
                        alt="{{ $post->title }}" />
                </a>
            </div>
            @endif

            <div class="article-details">

                @if(!empty($post->categories))
                <header class="article-category">
                    @foreach($post->categories as $category)
                    <a href="{{ route('blog.category', ['user' => $user['userName'], 'category1' => $category['id']]) }}">
                        {{ $category['label'] }}
                    </a>
                    @endforeach
                </header>
                @endif

                <div class="article-title-wrapper">
                    <h2 class="article-title">
                        <a href="{{ route('library.tipitaka.read', ['id' => $post['uid']]) }}">
                            {{ $post->title }}
                        </a>
                    </h2>
                    @if(!empty($post->summary))
                    <h3 class="article-subtitle">{{ $post->summary }}</h3>
                    @endif
                </div>

                <footer class="article-time">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-calendar-time"
                            width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <path d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4"></path>
                            <circle cx="18" cy="18" r="4"></circle>
                            <path d="M15 3v4"></path>
                            <path d="M7 3v4"></path>
                            <path d="M3 11h16"></path>
                            <path d="M18 16.496v1.504l1 1"></path>
                        </svg>
                        <time>{{ $post->formatted_updated_at }}</time>
                    </div>
                </footer>

            </div>
        </header>
    </article>
    @empty
    <div class="not-found-card">
        <p>暂无文章</p>
    </div>
    @endforelse
</section>
@endsection
