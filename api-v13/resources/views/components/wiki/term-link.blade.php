{{-- resources/views/components/wiki/term-link.blade.php --}}
{{--
    单个术语链接，两种状态：
    published → 蓝色链接
    draft     → 灰色，仍可点击
--}}
@props(['entry', 'lang'])

<a href="{{ route('library.wiki.show', [$entry['lang'] ?? $lang, $entry['quality']==='pending'?$entry['id']:$entry['word']]) }}"
    class="wiki-term-link wiki-term-link--{{ $entry['quality'] }}"
    title="{{ $entry['quality'] }}">
    <span class="wiki-term-link-zh">{{ $entry['zh'] ?? $entry['meaning'] ?? '' }}</span>
    <span class="wiki-term-link-word">{{ $entry['word'] }}</span>
</a>
