{{-- resources/views/components/wiki/entry-langs.blade.php --}}
@props(['langs', 'current'])

@if (count($langs) > 1)
<div class="wiki-entry-lang-switcher">
    <span class="wiki-entry-lang-label">其他语言版本：</span>
    @foreach ($langs as $item)
    @if ($item['lang'] !== $current)
    <a class="wiki-entry-lang-btn"
        href="{{ route('library.wiki.show', [$item['lang'], $item['word']]) }}">
        {{ $item['label'] }} · {{ $item['word'] }}
    </a>
    @endif
    @endforeach
</div>
@endif
