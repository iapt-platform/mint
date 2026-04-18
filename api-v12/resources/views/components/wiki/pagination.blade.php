{{-- resources/views/components/wiki/pagination.blade.php --}}
{{--
    props:
      $pagination  array  { total, per_page, current_page, last_page }
      $routeName   string  route name
      $routeParams array   route params (不含分页参数)
      $queryParams array   额外 query string 参数，如 ['q'=>'...', 'category'=>'...']
--}}
@props([
    'pagination',
    'routeName',
    'routeParams'  => [],
    'queryParams'  => [],
])

@php
    $current  = $pagination['current_page'];
    $last     = $pagination['last_page'];

    if ($last <= 1) return;  // 只有一页不渲染

    // 生成带页码的 URL
    $pageUrl = function (int $page) use ($routeName, $routeParams, $queryParams): string {
        $qs = array_merge($queryParams, ['page' => $page]);
        return route($routeName, $routeParams) . '?' . http_build_query($qs);
    };

    // 计算显示的页码范围（当前页前后各 2 页）
    $window = 2;
    $start  = max(1, $current - $window);
    $end    = min($last, $current + $window);
@endphp

<nav class="wiki-pagination" aria-label="分页导航">

    {{-- 上一页 --}}
    @if ($current > 1)
        <a class="wiki-page-btn" href="{{ $pageUrl($current - 1) }}" aria-label="上一页">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                <path d="M10 12L6 8l4-4" stroke="currentColor" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    @else
        <span class="wiki-page-btn wiki-page-btn--disabled" aria-disabled="true">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                <path d="M10 12L6 8l4-4" stroke="currentColor" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
    @endif

    {{-- 第一页 + 省略号 --}}
    @if ($start > 1)
        <a class="wiki-page-btn" href="{{ $pageUrl(1) }}">1</a>
        @if ($start > 2)
            <span class="wiki-page-ellipsis">…</span>
        @endif
    @endif

    {{-- 页码窗口 --}}
    @for ($p = $start; $p <= $end; $p++)
        @if ($p === $current)
            <span class="wiki-page-btn wiki-page-btn--active" aria-current="page">{{ $p }}</span>
        @else
            <a class="wiki-page-btn" href="{{ $pageUrl($p) }}">{{ $p }}</a>
        @endif
    @endfor

    {{-- 省略号 + 最后一页 --}}
    @if ($end < $last)
        @if ($end < $last - 1)
            <span class="wiki-page-ellipsis">…</span>
        @endif
        <a class="wiki-page-btn" href="{{ $pageUrl($last) }}">{{ $last }}</a>
    @endif

    {{-- 下一页 --}}
    @if ($current < $last)
        <a class="wiki-page-btn" href="{{ $pageUrl($current + 1) }}" aria-label="下一页">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                <path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    @else
        <span class="wiki-page-btn wiki-page-btn--disabled" aria-disabled="true">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none">
                <path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
    @endif

</nav>
