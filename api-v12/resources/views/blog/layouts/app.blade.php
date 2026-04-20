{{-- resources/views/layouts/blog.blade.php
     Blog 栏目布局。继承 layouts/base。
     使用 Stack 主题 CSS（静态文件，不走 Vite）。
     三栏结构：左边栏（博主信息+导航）/ 正文 / 右边栏（搜索+分类+标签）。
     右边栏数据（$categories, $tags, $archives）由 View Composer 自动注入。
--}}
@extends('layouts.base')

@push('styles')
    <link rel="stylesheet"
          href="{{ URL::asset('assets/css/blog/style.min.css') }}">
    <link href="{{ URL::asset('assets/css/blog/css2') }}"
          type="text/css" rel="stylesheet">
@endpush

@section('body-class', '')

@section('page')

<div class="container main-container flex on-phone--column extended">

    {{-- ── 左边栏 ── --}}
    <aside class="sidebar left-sidebar sticky">

        <button class="hamburger hamburger--spin"
                type="button"
                id="toggle-menu"
                aria-label="Toggle Menu">
            <span class="hamburger-box">
                <span class="hamburger-inner"></span>
            </span>
        </button>

        <header>
            <figure class="site-avatar">
                <a href="{{ route('blog.index', ['user' => $user['userName']]) }}">
                    <img src="{{ $user['avatar'] ?? '' }}"
                         width="300" height="300"
                         class="site-logo"
                         loading="lazy"
                         alt="Avatar" />
                </a>
                @if(!empty($user['level']))
                <span class="emoji" style="font-size: 11px;">LV{{ $user['level'] }}</span>
                @endif
            </figure>

            <div class="site-meta">
                <h1 class="site-name">
                    <a href="{{ route('blog.index', ['user' => $user['userName']]) }}">
                        {{ $user['nickName'] }}
                    </a>
                </h1>
                @if(!empty($user['description']))
                <h2 class="site-description">{{ $user['description'] }}</h2>
                @endif
            </div>
        </header>

        {{-- 社交链接 --}}
        @if(!empty($user['social']))
        <ol class="menu-social">
            @foreach($user['social'] as $social)
            <li>
                <a href="{{ $social['url'] }}"
                   target="_blank"
                   title="{{ $social['name'] }}"
                   rel="me">
                    <i class="ti ti-brand-{{ strtolower($social['name']) }}"></i>
                </a>
            </li>
            @endforeach
        </ol>
        @endif

        {{-- 主导航 --}}
        <ol class="menu" id="main-menu">
            <li class="{{ request()->routeIs('blog.index') ? 'current' : '' }}">
                <a href="{{ route('blog.index', ['user' => $user['userName']]) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-home"
                         width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                         stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z"></path>
                        <polyline points="5 12 3 12 12 3 21 12 19 12"></polyline>
                        <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"></path>
                        <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"></path>
                    </svg>
                    <span>Home</span>
                </a>
            </li>

            <li>
                <a href="#">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user"
                         width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                         stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                        <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
                    </svg>
                    <span>About</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('blog.archives*') ? 'current' : '' }}">
                <a href="{{ route('blog.archives', ['user' => $user['userName']]) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-archive"
                         width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                         stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z"></path>
                        <rect x="3" y="4" width="18" height="4" rx="2"></rect>
                        <path d="M5 8v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-10"></path>
                        <line x1="10" y1="12" x2="14" y2="12"></line>
                    </svg>
                    <span>Archives</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('blog.search') ? 'current' : '' }}">
                <a href="{{ route('blog.search', ['user' => $user['userName']]) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-search"
                         width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                         stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z"></path>
                        <circle cx="10" cy="10" r="7"></circle>
                        <line x1="21" y1="21" x2="15" y2="15"></line>
                    </svg>
                    <span>Search</span>
                </a>
            </li>

            {{-- 暗色模式切换 --}}
            <li class="menu-bottom-section">
                <ol class="menu">
                    <li id="dark-mode-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-toggle-left"
                             width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                             stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <circle cx="8" cy="12" r="2"></circle>
                            <rect x="2" y="6" width="20" height="12" rx="6"></rect>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-toggle-right"
                             width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                             stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <circle cx="16" cy="12" r="2"></circle>
                            <rect x="2" y="6" width="20" height="12" rx="6"></rect>
                        </svg>
                        <span>Dark Mode</span>
                    </li>
                </ol>
            </li>
        </ol>

    </aside>

    {{-- ── 正文 ── --}}
    <main class="main full-width">
        <div>
            @yield('content')
        </div>

        <footer class="site-footer">
            {{-- 博主版权 --}}
            <section class="copyright">
                &copy; 2020 - {{ date('Y') }} {{ $user['nickName'] }}
            </section>

            {{-- 主题版权（原样保留设计者信息） --}}
            <section class="powerby">
                &copy; 2020 - 2026 Hugo Theme Stack &nbsp;
                Theme <b><a href="https://github.com/CaiJimmy/hugo-theme-stack"
                             target="_blank" rel="noopener" data-version="3.30.0">Stack</a></b>
                designed by
                <a href="https://jimmycai.com/" target="_blank" rel="noopener">Jimmy</a>
            </section>
        </footer>
    </main>

    {{-- ── 右边栏 ── --}}
    <aside class="sidebar right-sidebar sticky">

        {{-- 搜索 --}}
        <form action="{{ route('blog.search', ['user' => $user['userName']]) }}"
              method="GET"
              class="search-form widget">
            <p>
                <label>Search</label>
                <input name="q"
                       placeholder="Type something..."
                       value="{{ request('q') }}" />
                <button type="submit" title="Search">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-search"
                         width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                         stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z"></path>
                        <circle cx="10" cy="10" r="7"></circle>
                        <line x1="21" y1="21" x2="15" y2="15"></line>
                    </svg>
                </button>
            </p>
        </form>

        {{-- Archives --}}
        @if(!empty($archives))
        <section class="widget archives">
            <div class="widget-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-infinity"
                     width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                     stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z"></path>
                    <path d="M9.828 9.172a4 4 0 1 0 0 5.656 a10 10 0 0 0 2.172 -2.828a10 10 0 0 1 2.172 -2.828 a4 4 0 1 1 0 5.656a10 10 0 0 1 -2.172 -2.828a10 10 0 0 0 -2.172 -2.828"></path>
                </svg>
            </div>
            <h2 class="widget-title section-title">Archives</h2>
            <div class="widget-archive--list">
                @foreach($archives as $archive)
                <div class="archives-year">
                    <a href="{{ route('blog.archives.year', ['user' => $user['userName'], 'year' => $archive['year']]) }}">
                        <span class="year">{{ $archive['year'] }}</span>
                        <span class="count">{{ $archive['count'] }}</span>
                    </a>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Categories --}}
        @if(!empty($categories))
        <section class="widget tagCloud">
            <div class="widget-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-hash"
                     width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                     stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z"></path>
                    <line x1="5" y1="9" x2="19" y2="9"></line>
                    <line x1="5" y1="15" x2="19" y2="15"></line>
                    <line x1="11" y1="4" x2="7" y2="20"></line>
                    <line x1="17" y1="4" x2="13" y2="20"></line>
                </svg>
            </div>
            <h2 class="widget-title section-title">Categories</h2>
            <div class="tagCloud-tags">
                @foreach($categories as $category)
                <a href="{{ route('blog.category', ['user' => $user['userName'], 'category1' => $category['id']]) }}">
                    {{ $category['label'] }}
                </a>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Tags --}}
        @if(!empty($tags))
        <section class="widget tagCloud">
            <div class="widget-icon">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-tag"
                     width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                     stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z"></path>
                    <path d="M11 3L20 12a1.5 1.5 0 0 1 0 2L14 20a1.5 1.5 0 0 1 -2 0L3 11v-4a4 4 0 0 1 4 -4h4"></path>
                    <circle cx="9" cy="9" r="2"></circle>
                </svg>
            </div>
            <h2 class="widget-title section-title">Tags</h2>
            <div class="tagCloud-tags">
                @foreach($tags as $tag)
                <a href="{{ route('blog.tag', ['user' => $user['userName'], 'tag' => $tag['name']]) }}"
                   class="font_size_1">
                    {{ $tag['name'] }}
                </a>
                @endforeach
            </div>
        </section>
        @endif

    </aside>

</div>

@push('scripts')
<script>
    (function() {
        const colorSchemeKey = "StackColorScheme";
        if (!localStorage.getItem(colorSchemeKey)) {
            localStorage.setItem(colorSchemeKey, "auto");
        }
    })();
    (function() {
        const colorSchemeKey = "StackColorScheme";
        const colorSchemeItem = localStorage.getItem(colorSchemeKey);
        const supportDarkMode = window.matchMedia("(prefers-color-scheme: dark)").matches === true;
        if (colorSchemeItem == "dark" || (colorSchemeItem === "auto" && supportDarkMode)) {
            document.documentElement.dataset.scheme = "dark";
        } else {
            document.documentElement.dataset.scheme = "light";
        }
    })();
</script>
<script src="{{ URL::asset('assets/js/blog/vibrant.min.js') }}"
        integrity="sha256-awcR2jno4kI5X0zL8ex0vi2z+KMkF24hUW8WePSA9HM="
        crossorigin="anonymous"></script>
<script src="{{ URL::asset('assets/js/blog/main.1e9a3bafd846ced4c345d084b355fb8c7bae75701c338f8a1f8a82c780137826.js') }}"
        type="text/javascript" defer></script>
<script>
    (function() {
        const customFont = document.createElement("link");
        customFont.href = "https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap";
        customFont.type = "text/css";
        customFont.rel = "stylesheet";
        document.head.appendChild(customFont);
    })();
</script>
@endpush

@endsection
