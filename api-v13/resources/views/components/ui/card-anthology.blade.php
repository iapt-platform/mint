{{-- resources/views/components/ui/card-anthology.blade.php
     文集卡片组件。横向布局：封面左 + 内容右。
     用于 anthology/index 列表。
     Props:
       $item — 文集数据数组，包含以下字段：
               id, title, subtitle, description, cover_image, cover_gradient,
               author{name, avatar, color, initials},
               chapters[], children_number, updated_at
       $href — 卡片链接
--}}
@props([
    'item',
    'href',
])

<a href="{{ $href }}" class="anthology-card">

    {{-- 封面 --}}
    <x-ui.book-cover
        :image="$item['cover_image'] ?? null"
        :gradient="$item['cover_gradient'] ?? ''"
        :title="$item['title']"
        :subtitle="$item['subtitle'] ?? ''"
        size="md"
        :style3d="false"
    />

    {{-- 内容 --}}
    <div class="anthology-card__body">
        <div class="anthology-card__title">{{ $item['title'] }}</div>

        @if(!empty($item['description']))
        <div class="anthology-card__desc">{{ $item['description'] }}</div>
        @endif

        <div class="anthology-card__author">
            <x-ui.author-avatar
                :avatar="$item['author']['avatar'] ?? null"
                :color="$item['author']['color'] ?? '#888'"
                :initials="$item['author']['initials'] ?? '?'"
                :name="$item['author']['name']"
                size="sm"
            />
        </div>

        @if(!empty($item['chapters']))
        <div class="anthology-card__tags">
            @foreach(array_slice($item['chapters'], 0, 4) as $ch)
            <span class="anthology-tag">{{ mb_strimwidth($ch, 0, 14, '…') }}</span>
            @endforeach
            @if($item['children_number'] > 4)
            <span class="anthology-tag anthology-tag--more">+{{ $item['children_number'] - 4 }} 章</span>
            @endif
        </div>
        @endif

        <div class="anthology-card__meta">
            <span class="anthology-meta-item">
                <i class="ti ti-calendar"></i>
                {{ $item['updated_at'] }}
            </span>
            <span class="anthology-meta-item">
                <i class="ti ti-file-text"></i>
                {{ $item['children_number'] }} 章节
            </span>
        </div>
    </div>

</a>
