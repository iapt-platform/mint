@extends('blog.layouts.app')

@section('title', $user["nickName"])

@section('content')
<section class="article-list">
    @foreach ($posts as $post)
    <article class="">
        <header class="article-header">
            <div class="article-details">
                <header class="article-category">
                    <a href="https://demo.stack.jimmycai.com/categories/themes/">
                        Themes
                    </a>

                    <a href="https://demo.stack.jimmycai.com/categories/syntax/">
                        Syntax
                    </a>
                </header>

                <div class="article-title-wrapper">
                    <h2 class="article-title">
                        <a
                            href="{{ route('library.book.read', $post['uid']) }}">{{ $post->title }}</a>
                    </h2>

                    <h3 class="article-subtitle">
                        {{ $post->summary }}
                    </h3>
                </div>

                <footer class="article-time">
                    <div>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-calendar-time"
                            width="56"
                            height="56"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <path
                                d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4"></path>
                            <circle cx="18" cy="18" r="4"></circle>
                            <path d="M15 3v4"></path>
                            <path d="M7 3v4"></path>
                            <path d="M3 11h16"></path>
                            <path d="M18 16.496v1.504l1 1"></path>
                        </svg>
                        <time class="article-time--published">{{ $post->formatted_updated_at }}</time>
                    </div>

                    <div>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-clock"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <circle cx="12" cy="12" r="9"></circle>
                            <polyline points="12 7 12 12 15 15"></polyline>
                        </svg>

                        <time class="article-time--reading"> 3 minute read </time>
                    </div>
                </footer>
            </div>
        </header>
    </article>
    @endforeach
    <article class="has-image">
        <header class="article-header">
            <div class="article-image">
                <a href="https://demo.stack.jimmycai.com/p/hello-world/">
                    <img
                        src="./Hugo Theme Stack Starter_files/cover_hu13459586684579990428.jpg"
                        srcset="
                      /p/hello-world/cover_hu13459586684579990428.jpg  800w,
                      /p/hello-world/cover_hu3425483315149503896.jpg  1600w
                    "
                        width="800"
                        height="534"
                        loading="lazy"
                        alt="Featured image of post Hello World" />
                </a>
            </div>

            <div class="article-details">
                <header class="article-category">
                    <a
                        href="https://demo.stack.jimmycai.com/categories/example-category/"
                        style="background-color: #2a9d8f; color: #fff">
                        Example Category
                    </a>
                </header>

                <div class="article-title-wrapper">
                    <h2 class="article-title">
                        <a href="https://demo.stack.jimmycai.com/p/hello-world/">Hello World</a>
                    </h2>

                    <h3 class="article-subtitle">Welcome to Hugo Theme Stack</h3>
                </div>

                <footer class="article-time">
                    <div>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-calendar-time"
                            width="56"
                            height="56"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <path
                                d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4"></path>
                            <circle cx="18" cy="18" r="4"></circle>
                            <path d="M15 3v4"></path>
                            <path d="M7 3v4"></path>
                            <path d="M3 11h16"></path>
                            <path d="M18 16.496v1.504l1 1"></path>
                        </svg>
                        <time class="article-time--published">Mar 06, 2022</time>
                    </div>

                    <div>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-clock"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <circle cx="12" cy="12" r="9"></circle>
                            <polyline points="12 7 12 12 15 15"></polyline>
                        </svg>

                        <time class="article-time--reading"> 1 minute read </time>
                    </div>
                </footer>
            </div>
        </header>
    </article>

    <article class="">
        <header class="article-header">
            <div class="article-details">
                <header class="article-category">
                    <a href="https://demo.stack.jimmycai.com/categories/themes/">
                        Themes
                    </a>

                    <a href="https://demo.stack.jimmycai.com/categories/syntax/">
                        Syntax
                    </a>
                </header>

                <div class="article-title-wrapper">
                    <h2 class="article-title">
                        <a
                            href="https://demo.stack.jimmycai.com/p/markdown-syntax-guide/">Markdown Syntax Guide</a>
                    </h2>

                    <h3 class="article-subtitle">
                        Sample article showcasing basic Markdown syntax and
                        formatting for HTML elements.
                    </h3>
                </div>

                <footer class="article-time">
                    <div>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-calendar-time"
                            width="56"
                            height="56"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <path
                                d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4"></path>
                            <circle cx="18" cy="18" r="4"></circle>
                            <path d="M15 3v4"></path>
                            <path d="M7 3v4"></path>
                            <path d="M3 11h16"></path>
                            <path d="M18 16.496v1.504l1 1"></path>
                        </svg>
                        <time class="article-time--published">Sep 07, 2023</time>
                    </div>

                    <div>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-clock"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <circle cx="12" cy="12" r="9"></circle>
                            <polyline points="12 7 12 12 15 15"></polyline>
                        </svg>

                        <time class="article-time--reading"> 3 minute read </time>
                    </div>
                </footer>
            </div>
        </header>
    </article>

    <article class="has-image">
        <header class="article-header">
            <div class="article-image">
                <a href="https://demo.stack.jimmycai.com/p/image-gallery/">
                    <img
                        src="./Hugo Theme Stack Starter_files/2_hu3578945376017100738.jpg"
                        srcset="
                      /p/image-gallery/2_hu3578945376017100738.jpg  800w,
                      /p/image-gallery/2_hu15750790370579438.jpg   1600w
                    "
                        width="800"
                        height="1200"
                        loading="lazy"
                        alt="Featured image of post Image gallery" />
                </a>
            </div>

            <div class="article-details">
                <div class="article-title-wrapper">
                    <h2 class="article-title">
                        <a href="https://demo.stack.jimmycai.com/p/image-gallery/">Image gallery</a>
                    </h2>

                    <h3 class="article-subtitle">
                        Create beautiful interactive image gallery using Markdown
                    </h3>
                </div>

                <footer class="article-time">
                    <div>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-calendar-time"
                            width="56"
                            height="56"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <path
                                d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4"></path>
                            <circle cx="18" cy="18" r="4"></circle>
                            <path d="M15 3v4"></path>
                            <path d="M7 3v4"></path>
                            <path d="M3 11h16"></path>
                            <path d="M18 16.496v1.504l1 1"></path>
                        </svg>
                        <time class="article-time--published">Aug 26, 2023</time>
                    </div>

                    <div>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-clock"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <circle cx="12" cy="12" r="9"></circle>
                            <polyline points="12 7 12 12 15 15"></polyline>
                        </svg>

                        <time class="article-time--reading"> 1 minute read </time>
                    </div>
                </footer>
            </div>
        </header>
    </article>

    <article class="has-image">
        <header class="article-header">
            <div class="article-image">
                <a href="https://demo.stack.jimmycai.com/p/shortcodes/">
                    <img
                        src="./Hugo Theme Stack Starter_files/cover_hu5876910065799140332.jpg"
                        srcset="
                      /p/shortcodes/cover_hu5876910065799140332.jpg   800w,
                      /p/shortcodes/cover_hu14584859319700861491.jpg 1600w
                    "
                        width="800"
                        height="533"
                        loading="lazy"
                        alt="Featured image of post Shortcodes" />
                </a>
            </div>

            <div class="article-details">
                <div class="article-title-wrapper">
                    <h2 class="article-title">
                        <a href="https://demo.stack.jimmycai.com/p/shortcodes/">Shortcodes</a>
                    </h2>

                    <h3 class="article-subtitle">
                        Useful shortcodes that can be used in Markdown
                    </h3>
                </div>

                <footer class="article-time">
                    <div>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-calendar-time"
                            width="56"
                            height="56"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <path
                                d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4"></path>
                            <circle cx="18" cy="18" r="4"></circle>
                            <path d="M15 3v4"></path>
                            <path d="M7 3v4"></path>
                            <path d="M3 11h16"></path>
                            <path d="M18 16.496v1.504l1 1"></path>
                        </svg>
                        <time class="article-time--published">Aug 25, 2023</time>
                    </div>

                    <div>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-clock"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <circle cx="12" cy="12" r="9"></circle>
                            <polyline points="12 7 12 12 15 15"></polyline>
                        </svg>

                        <time class="article-time--reading"> 1 minute read </time>
                    </div>
                </footer>
            </div>
        </header>
    </article>

    <article class="">
        <header class="article-header">
            <div class="article-details">
                <div class="article-title-wrapper">
                    <h2 class="article-title">
                        <a
                            href="https://demo.stack.jimmycai.com/p/math-typesetting/">Math Typesetting</a>
                    </h2>

                    <h3 class="article-subtitle">Math typesetting using KaTeX</h3>
                </div>

                <footer class="article-time">
                    <div>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-calendar-time"
                            width="56"
                            height="56"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <path
                                d="M11.795 21h-6.795a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v4"></path>
                            <circle cx="18" cy="18" r="4"></circle>
                            <path d="M15 3v4"></path>
                            <path d="M7 3v4"></path>
                            <path d="M3 11h16"></path>
                            <path d="M18 16.496v1.504l1 1"></path>
                        </svg>
                        <time class="article-time--published">Aug 24, 2023</time>
                    </div>

                    <div>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="icon icon-tabler icon-tabler-clock"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z"></path>
                            <circle cx="12" cy="12" r="9"></circle>
                            <polyline points="12 7 12 12 15 15"></polyline>
                        </svg>

                        <time class="article-time--reading"> 1 minute read </time>
                    </div>
                </footer>
            </div>
        </header>
    </article>
</section>
@endsection
