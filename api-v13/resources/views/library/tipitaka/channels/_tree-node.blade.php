{{-- 目录树的一个节点（递归）。变量：$node（TipitakaChannelController::buildNode()）、$level --}}
<li class="tipitaka-tree__level-{{ min($level, 2) }}" data-node>
    <span class="tipitaka-tree__node">
        <button type="button" class="tipitaka-tree__toggle" data-tree-toggle aria-label="{{ __('library.expand_all') }}">▾</button>
        <span class="tipitaka-tree__name">{{ $node['name'] }}</span>
        @if($node['name'] !== $node['pali'])
        <span class="tipitaka-tree__count">{{ $node['pali'] }}</span>
        @endif
        <span class="tipitaka-tree__count">{{ $node['mine'] }}@if(request()->boolean('all')) / {{ $node['total'] }}@endif {{ __('library.work_unit') }}</span>
        <x-ui.progress-bar :value="$node['avg']" class="tipitaka-tree__progress" />
    </span>

    @php
    $leaves = array_filter($node['children'], fn ($c) => $c['leaf']);
    $branches = array_filter($node['children'], fn ($c) => ! $c['leaf']);
    @endphp

    @if(!empty($node['works']) || !empty($leaves))
    <div class="tipitaka-tree__cards">
        @if(!empty($node['works']))
        @include('library.tipitaka.channels._tree-card', ['title' => $node['leaf'] ? $node['name'] : null, 'works' => $node['works']])
        @endif
        @foreach($leaves as $leaf)
        @include('library.tipitaka.channels._tree-card', ['title' => $leaf['name'], 'works' => $leaf['works']])
        @endforeach
    </div>
    @endif

    @if(!empty($branches))
    <ul>
        @foreach($branches as $child)
        @include('library.tipitaka.channels._tree-node', ['node' => $child, 'level' => $level + 1])
        @endforeach
    </ul>
    @endif
</li>
