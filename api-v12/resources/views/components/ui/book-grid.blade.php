{{-- resources/views/components/ui/book-grid.blade.php
     书籍网格组件。替代原 components/book-list.blade.php。
     去除内联 <style> 和 CDN 引入，样式由 modules/_tipitaka.css 提供。
     Props:
       $books      — 书籍数组
       $emptyText  — 空状态文字（默认"暂无图书"）
--}}
@props([
    'books'     => [],
    'emptyText' => '暂无图书',
])

@if(!empty($books) && count($books) > 0)
<div class="book-grid">
    @foreach($books as $book)
        <x-ui.card-book :book="$book" />
    @endforeach
</div>
@else
<x-ui.empty-state :title="$emptyText" />
@endif
