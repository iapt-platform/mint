{{-- resources/views/components/book-list.blade.php --}}
@once
@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/tabler/1.0.0-beta19/css/tabler.min.css" rel="stylesheet">
<style>
    .book-list-container {
        max-width: 1024px;
        margin: 0 auto;
        padding: 20px;
    }

    .book-item {
        margin-bottom: 24px;
        transition: all 0.3s ease;
    }

    .book-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    .book-cover {
        width: 100%;
        aspect-ratio: 3/4;
        object-fit: contain !important;
        border-radius: 6px;
        background-color: #f8f9fa;
    }

    .book-info {
        padding: 16px 0;
    }

    .book-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 8px;
        line-height: 1.4;
    }

    .book-author {
        color: #6b7280;
        font-size: 0.95rem;
        margin-bottom: 6px;
    }

    .book-language {
        color: #9ca3af;
        font-size: 0.875rem;
    }

    .language-badge {
        display: inline-block;
        padding: 2px 8px;
        background-color: #e5e7eb;
        color: #374151;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    /* 桌面端布局 */
    @media (min-width: 576px) {
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 24px;
        }
    }

    /* 手机端布局 */
    @media (max-width: 575px) {
        .book-item .card-body {
            display: flex;
            gap: 16px;
            align-items: stretch;
        }

        .book-cover-container {
            flex: 0 0 120px;
        }

        .book-cover {
            height: 160px;
            width: 120px;
        }

        .book-info {
            flex: 1;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .book-title {
            font-size: 1rem;
            margin-bottom: 12px;
        }
    }
</style>
@endpush
@endonce

<div>
    @if(!empty($books) && count($books) > 0)
    <div class="book-grid">
        @foreach($books as $book)
        @include('components.book-item', ['book' => $book])
        @endforeach
    </div>
    @else
    <div class="text-center py-5">
        <p class="text-muted">暂无图书数据</p>
    </div>
    @endif
</div>
