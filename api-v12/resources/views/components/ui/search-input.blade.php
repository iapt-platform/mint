{{-- resources/views/components/ui/search-input.blade.php
     通用搜索输入框组件。
     Props:
       $action      — 表单提交路由
       $value       — 当前搜索词（默认空）
       $placeholder — 占位文字
       $buttonText  — 按钮文字（默认"搜索"）
       $size        — lg | md（默认 md）
       $hiddenFields — 额外隐藏字段 array ['name' => 'value']
       $autofocus   — 是否自动聚焦（默认 false）
--}}
@props([
    'action',
    'value'        => '',
    'placeholder'  => '搜索…',
    'buttonText'   => '搜索',
    'size'         => 'md',
    'hiddenFields' => [],
    'autofocus'    => false,
])

<form action="{{ $action }}" method="GET" class="search-input-form">
    <div class="input-group {{ $size === 'lg' ? 'input-group-lg' : '' }}">
        <input
            type="text"
            name="q"
            class="form-control search-input"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            {{ $autofocus ? 'autofocus' : '' }} />

        @foreach ($hiddenFields as $name => $val)
            @if ($val)
                <input type="hidden" name="{{ $name }}" value="{{ $val }}">
            @endif
        @endforeach

        <button class="btn btn-primary" type="submit">
            {{ $buttonText }}
        </button>
    </div>
</form>
