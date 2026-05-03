{{-- resources/views/components/ui/card-book.blade.php
     书籍卡片组件。纵向布局：封面上 + 信息下。
     用于 tipitaka 列表页。
     Props:
       $book — 书籍数据数组，包含：
               id, title, author, cover(可选),
               cover_gradient(可选), publisher(object, 可选),
               language(可选), type(可选)
--}}
@props(['book'])

<div class="card-book">
    <a href="{{ route('library.tipitaka.show', $book['id']) }}" class="card-book__link">

        {{-- 封面 --}}
        <x-ui.book-cover
            :image="$book['cover'] ?? null"
            :gradient="$book['cover_gradient'] ?? 'linear-gradient(135deg, #2d2010 0%, #1a1208 100%)'"
            :title="$book['title'] ?? ''"
            :subtitle="'义注'"
            size="md"
            :style3d="false" />

        {{-- 书籍信息 --}}
        <div class="card-book__info">
            <div class="card-book__title">{{ $book['title'] ?? '未知书籍' }}</div>

            @if(!empty($book['author']))
            <div class="card-book__author">{{ $book['author'] }}</div>
            @endif

            @if(isset($book['publisher']))
            <div class="card-book__publisher">
                <a href="{{ route('blog.index', ['user' => $book['publisher']->username]) }}"
                    class="card-book__publisher-link">
                    {{ $book['publisher']->nickname }}
                </a>
            </div>
            @endif

            <div class="card-book__badges">
                @if(!empty($book['language']))
                <span class="card-book__badge">{{ $book['language'] }}</span>
                @endif
                @if(!empty($book['type']))
                <span class="card-book__badge">{{ $book['type'] }}</span>
                @endif
                @if(!empty($book['completed_chapters']))
                <span class="card-book__badge">§{{ $book['completed_chapters'] }}</span>
                @endif
            </div>
        </div>

    </a>
</div>
