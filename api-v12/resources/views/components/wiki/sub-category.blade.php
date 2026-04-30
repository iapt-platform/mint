{{-- resources/views/components/wiki/sub-category.blade.php --}}
@props(['sub', 'lang'])

<div class="wiki-subcat">
    <div class="wiki-subcat-header">
        <span class="wiki-subcat-title">{{ $sub['label'] }}</span>
        <span class="wiki-subcat-count">{{ count($sub['entries']) }} 条</span>
    </div>
    <div class="wiki-subcat-entries">
        @foreach ($sub['entries'] as $entry)
            <x-wiki.term-link :entry="$entry" :lang="$lang" />
        @endforeach
    </div>
</div>
