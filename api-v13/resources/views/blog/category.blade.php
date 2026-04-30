{{-- resources/views/blog/category.blade.php --}}
@extends('blog.layouts.app')

@section('title', $user['nickName'] . ' · ' . collect($current)->pluck('label')->implode(' / '))

@section('content')

<header>
    <h3 class="section-title">Categories</h3>
    <div class="section-card">
        <div class="section-details">
            <h3 class="section-count">{{ $count }} 篇</h3>
            <h1 class="section-term">
                @foreach($current as $category)
                / <a href="{{ route('blog.category', ['user' => $user['userName'], 'category1' => $category['id']]) }}">
                    {{ $category['label'] }}
                </a>
                @endforeach
            </h1>

            {{-- 子分类标签 --}}
            @if(!empty($tagOptions))
            <section class="widget tagCloud">
                <div class="tagCloud-tags">
                    @foreach($tagOptions as $id => $tag)
                        @if($tag['count'] < $count)
                        <a href="{{ rtrim(url()->current(), '/') . '/' . $tag['tag']->name }}">
                            {{ $tag['tag']->name }} ({{ $tag['count'] }})
                        </a>
                        @endif
                    @endforeach
                </div>
            </section>
            @endif
        </div>
    </div>
</header>

<section class="article-list--compact">
    @forelse($posts as $post)
    <article>
        <a href="{{ route('library.tipitaka.read', ['id' => $post['uid']]) }}">
            <div class="article-details">
                <h2 class="article-title">{{ $post->title }}</h2>
                <footer class="article-time">
                    <time>{{ $post->formatted_updated_at }}</time>
                </footer>
            </div>

            @if(!empty($post->cover))
            <div class="article-image">
                <img src="{{ $post->cover }}"
                     width="120" height="120"
                     alt="{{ $post->title }}"
                     loading="lazy" />
            </div>
            @endif
        </a>
    </article>
    @empty
    <div class="not-found-card" style="padding: 20px;">
        <p>此分类下暂无文章</p>
    </div>
    @endforelse
</section>

@endsection
