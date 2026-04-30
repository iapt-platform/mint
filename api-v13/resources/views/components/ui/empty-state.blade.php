{{-- resources/views/components/ui/empty-state.blade.php
     通用空状态组件。
     Props:
       $title — 标题文字
       $desc  — 描述文字（支持 HTML，可选）
       $icon  — 自定义图标 slot（可选，默认搜索图标）
--}}
@props([
    'title' => '未找到相关内容',
    'desc'  => '',
])

<div class="wiki-empty-state">
    <div class="wiki-empty-icon">
        {{ $icon ?? '' }}
        @unless($icon ?? false)
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="1.5">
            <circle cx="11" cy="11" r="8" />
            <path d="M21 21l-4.35-4.35" stroke-linecap="round" />
            <path d="M8 11h6M11 8v6" stroke-linecap="round" />
        </svg>
        @endunless
    </div>

    <div class="wiki-empty-title">{{ $title }}</div>

    @if ($desc)
    <div class="wiki-empty-desc">{!! $desc !!}</div>
    @endif

    @isset($slot)
        {{ $slot }}
    @endisset
</div>
