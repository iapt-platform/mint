{{-- resources/views/components/wiki/quality-badge.blade.php --}}
@props(['quality' => null])

@if ($quality)
<span class="wiki-quality-badge wiki-badge--{{ $quality }}">
    <span class="wiki-quality-dot"></span>
    {{ $quality }}
</span>
@endif
