{{-- resources/views/components/wiki/quality-badge.blade.php --}}
@props(['quality' => null])

@php
    $map = [
        'featured' => ['label' => '精选条目', 'class' => 'wiki-badge--featured'],
        'review'   => ['label' => '待审核',   'class' => 'wiki-badge--review'],
        'stub'     => ['label' => '存根',     'class' => 'wiki-badge--stub'],
    ];
    $config = $map[$quality] ?? null;
@endphp

@if ($config)
    <span class="wiki-quality-badge {{ $config['class'] }}">
        <span class="wiki-quality-dot"></span>
        {{ $config['label'] }}
    </span>
@endif
