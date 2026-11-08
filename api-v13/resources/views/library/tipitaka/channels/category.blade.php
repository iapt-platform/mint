{{-- resources/views/library/tipitaka/channels/category.blade.php
     单个 channel 的分类目录树（无边栏）。样式参考 documents/tipitaka_org.html。
     顶部一排：左边切换「全部」与四个藏，右边是展开 / 折叠 / 显示未收录。
     树里的节点只是分组（不可点）；末级分类是卡片，卡片里每部作品一条进度条。
     变量：$channelInfo、$tree（TipitakaChannelController::buildNode()，可能为 null；全部时是虚拟根节点）、
           $topCategories、$currentTop（全部时为 null）、$showAll
--}}
@extends('layouts.library')

@section('title', $channelInfo['name'] . ' · ' . __('library.site_name'))

@push('styles')
@vite('resources/css/modules/tipitaka.css')
@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('library.home') }}">{{ __('library.home') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('library.tipitaka.index') }}">{{ __('library.tipitaka') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('library.tipitaka.channels.index') }}">{{ __('library.channels') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('library.tipitaka.channels.show', ['channel' => $channelInfo['uid']]) }}">{{ $channelInfo['name'] }}</a></li>
<li class="breadcrumb-item active">{{ $tree['name'] ?? __('library.by_category') }}</li>
@endsection

@section('content')
<div class="page-body">
    <div class="container-xl">
        @include('library.tipitaka.channels._header', ['tab' => 'category'])

        <div class="tipitaka-tree-bar">
            <div class="tipitaka-filter-pills">
                <a href="{{ route('library.tipitaka.channels.category', array_filter(['channel' => $channelInfo['uid'], 'all' => $showAll ? 1 : null])) }}"
                    class="tipitaka-pill {{ $currentTop === null ? 'tipitaka-pill--active' : '' }}">
                    {{ __('library.all') }} <span class="tipitaka-pill-count">{{ array_sum(array_column($topCategories, 'count')) }}</span>
                </a>
                @foreach($topCategories as $top)
                <a href="{{ route('library.tipitaka.channels.category', array_filter(['channel' => $channelInfo['uid'], 'id' => $top['id'], 'all' => $showAll ? 1 : null])) }}"
                    class="tipitaka-pill {{ $currentTop === $top['id'] ? 'tipitaka-pill--active' : '' }}">
                    {{ $top['name'] }} <span class="tipitaka-pill-count">{{ $top['count'] }}</span>
                </a>
                @endforeach
            </div>
            <div class="tipitaka-tree-bar__actions">
                <button type="button" class="btn btn-sm" data-tree-fold="expand">{{ __('library.expand_all') }}</button>
                <button type="button" class="btn btn-sm" data-tree-fold="collapse">{{ __('library.collapse_to_cards') }}</button>
                <a class="btn btn-sm" href="{{ request()->fullUrlWithQuery(['all' => $showAll ? null : 1]) }}">
                    {{ $showAll ? __('library.only_included') : __('library.show_missing') }}
                </a>
            </div>
        </div>

        <div class="tipitaka-tree" data-tree>
            @if($tree)
            <ul class="tipitaka-tree__root">
                {{-- 全部：不画虚拟根节点，四个藏直接作为顶层 --}}
                @foreach($currentTop === null ? $tree['children'] : [$tree] as $root)
                @include('library.tipitaka.channels._tree-node', ['node' => $root, 'level' => 0])
                @endforeach
            </ul>
            @else
            <div class="wiki-card"><x-ui.empty-state :title="__('library.no_works_in_category')" /></div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function() {
        const tree = document.querySelector('[data-tree]');
        if (!tree) return;
        const setCollapsed = (li, collapsed) => {
            li.classList.toggle('collapsed', collapsed);
            const toggle = li.querySelector(':scope > .tipitaka-tree__node [data-tree-toggle]');
            if (toggle) toggle.textContent = collapsed ? '▸' : '▾';
        };
        tree.addEventListener('click', (e) => {
            const toggle = e.target.closest('[data-tree-toggle]');
            if (!toggle) return;
            const li = toggle.closest('li');
            setCollapsed(li, !li.classList.contains('collapsed'));
        });
        document.querySelectorAll('[data-tree-fold]').forEach((btn) => btn.addEventListener('click', () => {
            const collapse = btn.dataset.treeFold === 'collapse';
            // 折叠到卡片：只折叠直接带卡片、没有下级分组的节点
            tree.querySelectorAll('li[data-node]').forEach((li) => setCollapsed(li,
                collapse && li.querySelector(':scope > .tipitaka-tree__cards') !== null && li.querySelector(':scope > ul') === null));
        }));
    })();
</script>
@endpush
