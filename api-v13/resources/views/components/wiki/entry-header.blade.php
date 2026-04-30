{{-- resources/views/components/wiki/entry-header.blade.php --}}
@props(['entry'])

<div class="wiki-entry-header">

    <x-wiki.quality-badge :quality="$entry['quality']" />

    <h1 class="wiki-entry-title">{{ $entry['meaning'] }}</h1>
    <div>{{ $entry['word'] }}</div>

</div>
