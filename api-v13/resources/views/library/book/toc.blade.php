{{-- resources/views/library/book/toc.blade.php
     TOC 列表局部视图，在 offcanvas 和侧边栏中复用。
     变量：$toc array，$anthologyId（可选）
     每个 item：active（当前章节高亮）、hide（折叠模式下隐藏的节点）。
     折叠/展开由 .toc-tree 上的 .toc-expanded 类切换（见 reader.js / reader.css）。
--}}
@if(!empty($toc))
@php $hasHidden = collect($toc)->contains(fn ($item) => $item['hide'] ?? false); @endphp
<div class="toc-tree" data-toc-tree>
    @if($hasHidden)
    <button type="button" class="toc-toggle-all" data-toc-toggle>
        <i class="ti ti-arrows-maximize me-1"></i>
        <span class="toc-toggle-expand">{{ __('library.expand_all') }}</span>
        <span class="toc-toggle-collapse">{{ __('library.collapse_toc') }}</span>
    </button>
    @endif
    <ul>
        @foreach ($toc as $item)
        <li class="toc_item toc-level-{{ $item['level'] }}
                   {{ ($item['active'] ?? false) ? 'toc-active' : ($item['disabled'] ? 'toc-disabled' : '') }}
                   {{ ($item['hide'] ?? false) ? 'toc-hidden' : '' }}">
            @if($item['active'] ?? false)
        <span title="{{ $item['title'] }}">{{ $item['title'] }}</span>
        @elseif(!$item['disabled'])
        @if(isset($anthologyId))
        <a href="{{ route('library.anthology.read', ['anthology' => $anthologyId, 'article' => $item['id'], 'channel' => request('channel')]) }}"
            title="{{ $item['title'] }}">{{ $item['title'] }}</a>
        @else
        <a href="{{ route('library.tipitaka.read', ['id' => $item['id'], 'channel' => request('channel')]) }}"
            title="{{ $item['title'] }}">{{ $item['title'] }}</a>
        @endif
        @else
        <span title="{{ $item['title'] }}">{{ $item['title'] }}</span>
        @endif
        </li>
        @endforeach
    </ul>
</div>
@else
<div class="alert alert-warning">{{ __('library.no_toc') }}</div>
@endif
