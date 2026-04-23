{{-- resources/views/components/wiki/search-box.blade.php (Tabler 增强版) --}}
@props([
'action' => '/library/search',
'method' => 'GET',
'placeholder' => '搜索佛法词条、经典、人物...',
'buttonText' => '搜索',
'queryParam' => 'q',
'typeParam' => 'type',
'typeValue' => 'wiki',
'autoFocus' => false,
'size' => 'md', // sm, md, lg
'class' => '',
])

<div {{ $attributes->merge(['class' => $class]) }}>
    <form action="{{ $action }}" method="{{ $method }}" role="search">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="input-icon">
                    <span class="input-icon-addon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <circle cx="10" cy="10" r="7" />
                            <line x1="21" y1="21" x2="15" y2="15" />
                        </svg>
                    </span>
                    <input
                        type="text"
                        name="{{ $queryParam }}"
                        class="form-control"
                        placeholder="{{ $placeholder }}"
                        value="{{ request($queryParam) }}"
                        {{ $autoFocus ? 'autofocus' : '' }}
                        aria-label="{{ $placeholder }}">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <span class="d-none d-sm-inline ms-2">{{ $buttonText }}</span>
                </button>
            </div>
        </div>

        @if($typeParam && $typeValue)
        <input type="hidden" name="{{ $typeParam }}" value="{{ $typeValue }}">
        @endif

        {{ $slot }}
    </form>
</div>
