{{-- 分类页左栏的分类树（递归）。变量：$nodes（TipitakaController::sideTree()） --}}
<ul class="wiki-cat-list tipitaka-side-tree {{ ($nested ?? false) ? 'tipitaka-side-tree__children' : '' }}">
    @foreach($nodes as $node)
    <li>
        <a href="{{ route('library.tipitaka.category', ['id' => $node['id']]) }}"
            class="{{ $node['active'] ? 'active' : '' }} {{ $node['open'] ? 'is-open' : '' }}">{{ $node['name'] }}</a>
        @if(!empty($node['children']))
        @include('library.tipitaka._side-tree', ['nodes' => $node['children'], 'nested' => true])
        @endif
    </li>
    @endforeach
</ul>
