@extends('blog.layouts.app')

@section('title', $user["nickName"])

@section('content')

<header>
    <h3 class="section-title">Categories</h3>

    <div class="section-card">
        <div class="section-details">
            <h3 class="section-count">{{ $count }} page</h3>
            <h1 class="section-term">
                @foreach ($current as $category)
                /<a href="{{ route('blog.category', ['user' => $user['userName'],'category1' => $category['id'],]) }}">
                    {{ $category['label'] }}
                </a>
                @endforeach
            </h1>
            <section class="widget tagCloud">
                <div class="tagCloud-tags">
                    @foreach($tagOptions as $id => $tag)
                    @if($tag['count'] < $count)
                        <a href="{{ rtrim(url()->current(), '/') . '/' . $tag['tag']->name }}">{{ $tag['tag']->name }}({{ $tag['count'] }})</a>
                        @endif
                        @endforeach
                </div>
            </section>
        </div>
    </div>
</header>

<section class="article-list--compact">
    @foreach ($posts as $post)
    <article>
        <a href="{{ route('library.book.read', $post['uid']) }}">
            <div class="article-details">
                <h2 class="article-title">{{ $post->title }}</h2>
                <footer class="article-time">
                    <time datetime="2022-03-06T00:00:00Z">{{ $post->formatted_updated_at }}</time>
                </footer>
            </div>
            <div class="article-image">
                <img
                    src="./Category_ Example Category - Hugo Theme Stack Starter_files/cover_hu6307248181568134095.jpg"
                    width="120"
                    height="120"
                    alt="Hello World"
                    loading="lazy" />
            </div>
        </a>
    </article>
    @endforeach
</section>

@endsection
