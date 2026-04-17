{{-- resources/views/components/wiki/related-entries.blade.php --}}
@props(['entries' => []])

@if (count($entries))
<div class="wiki-sidebar-section">
    <div class="wiki-sidebar-title">相关条目</div>
    <ul class="wiki-related-list">
        @foreach ($entries as $entry)
        <li>
            <a href="{{ route('library.wiki.show', [$entry['lang'], $entry['word']]) }}">
                {{ $entry['word'] }}
                <span class="wiki-related-zh">{{ $entry['zh'] }}</span>
            </a>
        </li>
        @endforeach
    </ul>
</div>
@endif
