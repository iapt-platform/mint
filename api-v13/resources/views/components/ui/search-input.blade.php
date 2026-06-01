{{-- resources/views/components/ui/search-input.blade.php --}}
@props([
'action' => route('library.search'),
'value' => '',
'placeholder' => '搜索…',
'buttonText' => '搜索',
'size' => 'md',
'hiddenFields' => [],
'autofocus' => false,
])

<form action="{{ $action }}" method="GET" class="search-input-form position-relative">
    <div class="input-group {{ $size === 'lg' ? 'input-group-lg' : '' }}">

        {{-- 🔍 图标输入框 --}}
        <div class="input-icon flex-grow-1">
            <span class="input-icon-addon">
                {{-- Tabler 搜索图标 --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <circle cx="10" cy="10" r="7" />
                    <line x1="21" y1="21" x2="15" y2="15" />
                </svg>
            </span>

            <input
                type="text"
                name="q"
                class="form-control search-input"
                value="{{ $value }}"
                placeholder="{{ $placeholder }}"
                autocomplete="off"
                data-suggest-url="/api/v3/search-suggest"
                {{ $autofocus ? 'autofocus' : '' }} />
        </div>

        {{-- 隐藏字段 --}}
        @foreach ($hiddenFields as $name => $val)
        @if ($val)
        <input type="hidden" name="{{ $name }}" value="{{ $val }}">
        @endif
        @endforeach

        {{-- 按钮 --}}
        <button class="btn btn-primary" type="submit">
            {{ $buttonText }}
        </button>
    </div>

    {{-- 自动补全下拉 --}}
    <div class="search-suggest-dropdown dropdown-menu w-100"></div>
</form>
