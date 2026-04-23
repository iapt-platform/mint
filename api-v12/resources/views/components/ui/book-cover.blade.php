{{-- resources/views/components/ui/book-cover.blade.php
     书籍封面组件。支持图片封面和渐变+文字封面两种形式。
     Props:
       $image    — 封面图片 URL（为空时显示渐变+文字）
       $gradient — 渐变 CSS 字符串，image 为空时使用
       $title    — 书名（image 为空时显示在封面上）
       $subtitle — 副标题（可选）
       $size     — sm | md | lg（控制尺寸）
       $style3d  — true | false（是否显示 3D 书脊效果，默认 true）
       $alt      — img alt 文字（默认使用 $title）
--}}
@props([
    'image'    => null,
    'gradient' => 'linear-gradient(135deg, #2d2010 0%, #1a1208 100%)',
    'title'    => '',
    'subtitle' => '',
    'size'     => 'md',
    'style3d'  => true,
    'alt'      => null,
])

@php
$sizes = [
    'sm' => 'book-cover--sm',
    'md' => 'book-cover--md',
    'lg' => 'book-cover--lg',
];
$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div class="book-cover {{ $sizeClass }} {{ $style3d ? 'book-cover--3d' : '' }}"
     style="{{ $image ? '' : 'background: ' . $gradient }}">

    @if($image)
        <img src="{{ $image }}" alt="{{ $alt ?? $title }}" class="book-cover__img">
    @else
        <div class="book-cover__text">
            <div class="book-cover__title">{{ $title }}</div>
            @if($subtitle)
                <div class="book-cover__divider"></div>
                <div class="book-cover__subtitle">{{ $subtitle }}</div>
            @endif
        </div>
    @endif

</div>
