{{-- resources/views/library/book/toc.blade.php
     TOC 列表局部视图，在 offcanvas 和侧边栏中复用。
     变量：$toc array，$anthologyId（可选）
--}}
@if(!empty($toc))
<ul>
    @foreach ($toc as $item)
    <li class="toc_item toc-level-{{ $item['level'] }}
               {{ ($item['active'] ?? false) ? 'toc-active' : ($item['disabled'] ? 'toc-disabled' : '') }}">
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
@else
<div class="alert alert-warning">此书没有目录</div>
@endif
