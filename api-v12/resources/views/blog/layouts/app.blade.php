<!DOCTYPE html>
<!-- saved from url=(0032)https://demo.stack.jimmycai.com/ -->
<html lang="en-us" dir="ltr" data-scheme="light">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="generator" content="Hugo 0.134.1" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta
        name="description"
        content="Lorem ipsum dolor sit amet, consectetur adipiscing elit." />
    <title></title>
    <title>@yield('title', 'home')</title>
    <link rel="canonical" href="https://demo.stack.jimmycai.com/" />

    <link
        rel="stylesheet"
        href="{{ URL::asset('assets/css/blog/style.min.663803bebe609202d5b39d848f2d7c2dc8b598a2d879efa079fa88893d29c49c.css') }}" />
    <meta property=" og:title" content="{{ $user["nickName"] }}" />
    <meta
        property="og:description"
        content="Lorem ipsum dolor sit amet, consectetur adipiscing elit." />
    <meta property="og:url" content="https://demo.stack.jimmycai.com/" />
    <meta property="og:site_name" content="{{ $user["nickName"] }}" />
    <meta property="og:type" content="website" />
    <meta property="og:updated_time" content=" 2023-09-07T00:00:00+00:00 " />
    <meta name="twitter:title" content="{{ $user["nickName"] }}" />
    <meta
        name="twitter:description"
        content="Lorem ipsum dolor sit amet, consectetur adipiscing elit." />
    <link
        rel="alternate"
        type="application/rss+xml"
        href="https://demo.stack.jimmycai.com/index.xml" />
    <link
        rel="shortcut icon"
        href="https://demo.stack.jimmycai.com/favicon.png" />
    <link
        href="{{ URL::asset('assets/css/blog/css2') }}"
        type="text/css"
        rel="stylesheet" />
</head>

<body
    class=""
    style="transition: background-color 0.3s">
    <script>
        (function() {
            const colorSchemeKey = "StackColorScheme";
            if (!localStorage.getItem(colorSchemeKey)) {
                localStorage.setItem(colorSchemeKey, "auto");
            }
        })();
    </script>
    <script>
        (function() {
            const colorSchemeKey = "StackColorScheme";
            const colorSchemeItem = localStorage.getItem(colorSchemeKey);
            const supportDarkMode =
                window.matchMedia("(prefers-color-scheme: dark)").matches === true;

            if (
                colorSchemeItem == "dark" ||
                (colorSchemeItem === "auto" && supportDarkMode)
            ) {
                document.documentElement.dataset.scheme = "dark";
            } else {
                document.documentElement.dataset.scheme = "light";
            }
        })();
    </script>
    <div class="container main-container flex on-phone--column extended">
        <aside class="sidebar left-sidebar sticky">
            <button
                class="hamburger hamburger--spin"
                type="button"
                id="toggle-menu"
                aria-label="Toggle Menu">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>

            <header>
                <figure class="site-avatar">
                    <a href="https://demo.stack.jimmycai.com/">
                        <img
                            src="{{ $user['avatar'] ?? '' }}"
                            width="300"
                            height="300"
                            class="site-logo"
                            loading="lazy"
                            alt="Avatar" />
                    </a>

                    <span class="emoji" style="font-size: 11px;">LV10</span>
                </figure>

                <div class="site-meta">
                    <h1 class="site-name">
                        <a href="https://demo.stack.jimmycai.com/">{{ $user["nickName"] }}</a>
                    </h1>
                    <h2 class="site-description">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    </h2>
                </div>
            </header>
            <ol class="menu-social">
                <li>
                    <a
                        href="https://github.com/CaiJimmy/hugo-theme-stack"
                        target="_blank"
                        title="GitHub"
                        rel="me">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-brand-github"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path
                                d="M9 19c-4.3 1.4 -4.3 -2.5 -6 -3m12 5v-3.5c0 -1 .1 -1.4 -.5 -2c2.8 -.3 5.5 -1.4 5.5 -6a4.6 4.6 0 0 0 -1.3 -3.2a4.2 4.2 0 0 0 -.1 -3.2s-1.1 -.3 -3.5 1.3a12.3 12.3 0 0 0 -6.2 0c-2.4 -1.6 -3.5 -1.3 -3.5 -1.3a4.2 4.2 0 0 0 -.1 3.2a4.6 4.6 0 0 0 -1.3 3.2c0 4.6 2.7 5.7 5.5 6c-.6 .6 -.6 1.2 -.5 2v3.5"></path>
                        </svg>
                    </a>
                </li>

                <li>
                    <a
                        href="https://twitter.com/"
                        target="_blank"
                        title="Twitter"
                        rel="me">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-brand-twitter"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path
                                d="M22 4.01c-1 .49 -1.98 .689 -3 .99c-1.121 -1.265 -2.783 -1.335 -4.38 -.737s-2.643 2.06 -2.62 3.737v1c-3.245 .083 -6.135 -1.395 -8 -4c0 0 -4.182 7.433 4 11c-1.872 1.247 -3.739 2.088 -6 2c3.308 1.803 6.913 2.423 10.034 1.517c3.58 -1.04 6.522 -3.723 7.651 -7.742a13.84 13.84 0 0 0 .497 -3.753c-.002 -.249 1.51 -2.772 1.818 -4.013z"></path>
                        </svg>
                    </a>
                </li>
            </ol>
            <ol class="menu" id="main-menu">
                <li class="current">
                    <a href="https://demo.stack.jimmycai.com/">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-home"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <polyline points="5 12 3 12 12 3 21 12 19 12"></polyline>
                            <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"></path>
                            <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"></path>
                        </svg>

                        <span>Home</span>
                    </a>
                </li>

                <li>
                    <a href="https://demo.stack.jimmycai.com/archives/">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-archive"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <rect x="3" y="4" width="18" height="4" rx="2"></rect>
                            <path d="M5 8v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-10"></path>
                            <line x1="10" y1="12" x2="14" y2="12"></line>
                        </svg>

                        <span>Archives</span>
                    </a>
                </li>

                <li>
                    <a href="https://demo.stack.jimmycai.com/search/">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-search"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <circle cx="10" cy="10" r="7"></circle>
                            <line x1="21" y1="21" x2="15" y2="15"></line>
                        </svg>

                        <span>Search</span>
                    </a>
                </li>

                <li>
                    <a href="https://demo.stack.jimmycai.com/links/">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-link"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <path
                                d="M10 14a3.5 3.5 0 0 0 5 0l4 -4a3.5 3.5 0 0 0 -5 -5l-.5 .5"></path>
                            <path
                                d="M14 10a3.5 3.5 0 0 0 -5 0l-4 4a3.5 3.5 0 0 0 5 5l.5 -.5"></path>
                        </svg>

                        <span>Links</span>
                    </a>
                </li>

                <li class="menu-bottom-section">
                    <ol class="menu">
                        <li id="dark-mode-toggle">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="icon icon-tabler icon-tabler-toggle-left"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z"></path>
                                <circle cx="8" cy="12" r="2"></circle>
                                <rect x="2" y="6" width="20" height="12" rx="6"></rect>
                            </svg>

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="icon icon-tabler icon-tabler-toggle-right"
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                fill="none"
                                stroke-linecap="round"
                                stroke-linejoin="round">
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

        <aside class="sidebar right-sidebar sticky">
            <form
                action="https://demo.stack.jimmycai.com/search/"
                class="search-form widget">
                <p>
                    <label>Search</label>
                    <input name="keyword" required="" placeholder="Type something..." />

                    <button title="Search">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-search"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <circle cx="10" cy="10" r="7"></circle>
                            <line x1="21" y1="21" x2="15" y2="15"></line>
                        </svg>
                    </button>
                </p>
            </form>

            <section class="widget archives">
                <div class="widget-icon">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="icon icon-tabler icon-tabler-infinity"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        fill="none"
                        stroke-linecap="round"
                        stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z"></path>
                        <path
                            d="M9.828 9.172a4 4 0 1 0 0 5.656 a10 10 0 0 0 2.172 -2.828a10 10 0 0 1 2.172 -2.828 a4 4 0 1 1 0 5.656a10 10 0 0 1 -2.172 -2.828a10 10 0 0 0 -2.172 -2.828"></path>
                    </svg>
                </div>
                <h2 class="widget-title section-title">Archives</h2>

                <div class="widget-archive--list">
                    <div class="archives-year">
                        <a href="https://demo.stack.jimmycai.com/archives/#2023">
                            <span class="year">2023</span>
                            <span class="count">4</span>
                        </a>
                    </div>
                    <div class="archives-year">
                        <a href="https://demo.stack.jimmycai.com/archives/#2022">
                            <span class="year">2022</span>
                            <span class="count">1</span>
                        </a>
                    </div>
                </div>
            </section>

            <section class="widget tagCloud">
                <div class="widget-icon">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="icon icon-tabler icon-tabler-hash"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        fill="none"
                        stroke-linecap="round"
                        stroke-linejoin="round">
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
                    <a
                        href="{{ route('blog.category', ['user' => $user['userName'],'category1' => $category['id'],]) }}"
                        class="font_size_1">
                        {{ $category['label'] }}
                    </a>
                    @endforeach
                </div>
            </section>

            <section class="widget tagCloud">
                <div class="widget-icon">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="icon icon-tabler icon-tabler-tag"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        fill="none"
                        stroke-linecap="round"
                        stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z"></path>
                        <path
                            d="M11 3L20 12a1.5 1.5 0 0 1 0 2L14 20a1.5 1.5 0 0 1 -2 0L3 11v-4a4 4 0 0 1 4 -4h4"></path>
                        <circle cx="9" cy="9" r="2"></circle>
                    </svg>
                </div>
                <h2 class="widget-title section-title">Tags</h2>

                <div class="tagCloud-tags">
                    <a
                        href="https://demo.stack.jimmycai.com/tags/css/"
                        class="font_size_1">
                        Css
                    </a>

                    <a
                        href="https://demo.stack.jimmycai.com/tags/example-tag/"
                        class="font_size_1">
                        Example Tag
                    </a>

                    <a
                        href="https://demo.stack.jimmycai.com/tags/html/"
                        class="font_size_1">
                        Html
                    </a>

                    <a
                        href="https://demo.stack.jimmycai.com/tags/markdown/"
                        class="font_size_1">
                        Markdown
                    </a>

                    <a
                        href="https://demo.stack.jimmycai.com/tags/themes/"
                        class="font_size_1">
                        Themes
                    </a>
                </div>
            </section>
        </aside>

        <main class="main full-width">
            <div>
                @yield('content')
            </div>

            <footer class="site-footer">
                <section class="copyright">
                    2020 - 2025 wikipali
                </section>

                <section class="powerby">
                    Theme
                    <b><a
                            href="https://github.com/CaiJimmy/hugo-theme-stack"
                            target="_blank"
                            rel="noopener"
                            data-version="3.30.0">Stack</a></b>
                    designed by
                    <a href="https://jimmycai.com/" target="_blank" rel="noopener">Jimmy</a>
                    © 2020 - 2025 Hugo Theme Stack Starter
                </section>
            </footer>
        </main>
    </div>
    <script
        src="{{ URL::asset('assets/js/blog/vibrant.min.js') }}"
        integrity="sha256-awcR2jno4kI5X0zL8ex0vi2z+KMkF24hUW8WePSA9HM="
        crossorigin="anonymous"></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/js/blog/main.1e9a3bafd846ced4c345d084b355fb8c7bae75701c338f8a1f8a82c780137826.js') }}"
        defer=""></script>
    <script>
        (function() {
            const customFont = document.createElement("link");
            customFont.href =
                "https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap";

            customFont.type = "text/css";
            customFont.rel = "stylesheet";

            document.head.appendChild(customFont);
        })();
    </script>
</body>

</html>
