{{-- resources/views/components/book-item.blade.php --}}
<div class="book-item">
    <div class="card h-100">
        <div class="card-body">
            <div class="book-cover-container">
                <a href="{{ route('library.book.show', $book['id']) }}" class="text-decoration-none">
                    <img src="{{ $book['cover'] ?? 'https://via.placeholder.com/300x400?text=No+Cover' }}"
                        alt="{{ $book['title'] ?? '未知书籍' }}"
                        class="book-cover"
                        loading="lazy">
                </a>
            </div>
            <div class="book-info">
                <div class="book-title">{{ $book['title'] ?? '未知书籍' }}</div>
                <div class="book-author">{{ $book['author'] ?? '未知作者' }}</div>
                <div class="book-author">
                    <a href="{{ route('blog.index', ['user' => $book['publisher']->username]) }}">
                        {{ $book['publisher']->nickname }}
                    </a>
                </div>
                <div class="book-language">
                    <span class="language-badge">{{ $book['language'] ?? '未知语言' }}</span>
                    <span class="language-badge">{{ $book['type'] ?? '未知类型' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
