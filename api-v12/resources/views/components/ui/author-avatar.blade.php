{{-- resources/views/components/ui/author-avatar.blade.php
     作者头像组件。支持图片头像和文字缩写头像两种形式。
     Props:
       $avatar   — 头像图片 URL（为空时显示文字头像）
       $color    — 文字头像背景色
       $initials — 文字头像缩写
       $name     — 作者名字（显示在头像右侧）
       $size     — sm | md | lg
       $sub      — 副文字（如文章数量），可选
--}}
@props([
    'avatar'   => null,
    'color'    => '#888888',
    'initials' => '?',
    'name'     => '',
    'size'     => 'md',
    'sub'      => null,
])

<div class="author-avatar author-avatar--{{ $size }}">
    @if($avatar)
        <img src="{{ $avatar }}"
             alt="{{ $name }}"
             class="author-avatar__img">
    @else
        <div class="author-avatar__initials"
             style="background: {{ $color }}">
            {{ $initials }}
        </div>
    @endif

    @if($name)
    <div class="author-avatar__info">
        <span class="author-avatar__name">{{ $name }}</span>
        @if($sub)
        <span class="author-avatar__sub">{{ $sub }}</span>
        @endif
    </div>
    @endif
</div>
