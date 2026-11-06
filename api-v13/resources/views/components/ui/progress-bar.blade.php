{{-- resources/views/components/ui/progress-bar.blade.php
     细进度条 + 百分比。≥80 绿、≥30 黄、其余红（与作品目录页的章节进度一致）。
     Props:
       $value     — 0–100
       $showLabel — 是否在右侧显示百分比（默认 true）
--}}
@props([
'value' => 0,
'showLabel' => true,
])

@php
$value = max(0, min(100, (int) $value));
$color = $value >= 80 ? 'bg-green' : ($value >= 30 ? 'bg-yellow' : 'bg-red');
@endphp

<div {{ $attributes->merge(['class' => 'ui-progress']) }} title="{{ $value }}%">
    <div class="progress ui-progress__bar">
        <div class="progress-bar {{ $color }}" style="width: {{ $value }}%"></div>
    </div>
    @if($showLabel)
    <span class="ui-progress__label">{{ $value }}%</span>
    @endif
</div>
