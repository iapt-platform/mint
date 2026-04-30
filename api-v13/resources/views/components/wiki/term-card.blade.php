{{-- resources/views/components/wiki/term-card.blade.php --}}
{{-- props: word, meaning, summary --}}
@props(['word', 'meaning' => '', 'summary' => ''])

@php $showSummary = $summary && $summary !== $meaning; @endphp

<div class="wiki-term-card">
    <div class="wiki-term-card-word">{{ $word }}</div>
    <div class="wiki-term-card-body">
        @if ($meaning)
        <div class="wiki-term-card-meaning">{{ $meaning }}</div>
        @endif
        @if ($showSummary)
        <div class="wiki-term-card-summary">{{ $summary }}</div>
        @endif
    </div>
</div>
