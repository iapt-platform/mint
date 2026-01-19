<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Reading - {{ $book['title'] }}</title>
    <!-- Tabler CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.3.2/dist/css/tabler.min.css" />
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <script
        src="https://cdn.jsdelivr.net/npm/@tabler/core@1.3.2/dist/js/tabler.min.js">
    </script>
    <style>
        /* Custom styles for responsive layout */
        body {
            font-family: 'Inter', sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }

        .main-container {
            display: flex;
            gap: 20px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .toc-sidebar {
            width: 250px;
            flex-shrink: 0;
            display: none;
        }

        .content-area {
            flex-grow: 1;
            max-width: 100%;
        }

        .right-sidebar {
            width: 300px;
            flex-shrink: 0;
            display: none;
        }

        .related-books {
            margin-top: 30px;
        }

        .card-img-container {
            height: 150px;
            overflow: hidden;
        }

        .card-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Mobile: Show only content, TOC in drawer */
        @media (max-width: 767px) {
            .content-area {
                width: 100%;
            }

            .main-container {
                padding: 0;

            }

            .card {
                border: none;
            }
        }

        /* Tablet: Show TOC and content */
        @media (min-width: 768px) {
            .toc-sidebar {
                display: block;
            }

            .content-area {
                max-width: calc(100% - 270px);
            }
        }

        /* Desktop: Show TOC, content, and right sidebar */
        @media (min-width: 992px) {
            .right-sidebar {
                display: block;
            }

            .content-area {
                max-width: calc(100% - 570px);
            }
        }

        /* Dark mode styles */
        .dark-mode {
            background-color: #1a1a1a;
            color: #ffffff;
        }

        .dark-mode .card {
            background-color: #2a2a2a;
            border-color: #3a3a3a;
            color: #ffffff;
        }

        .dark-mode .navbar {
            background-color: #2a2a2a;
        }

        .dark-mode .offcanvas {
            background-color: #2a2a2a;
            color: #ffffff;
        }

        .dark-mode .offcanvas .nav-link {
            color: #ffffff;
        }

        .dark-mode .toc-sidebar,
        .dark-mode .right-sidebar {
            background-color: #2a2a2a;
        }

        .toc-sidebar ul {
            list-style: none;
            padding: 0;
        }

        .toc-sidebar ul li {
            padding: 10px 0;
        }

        .toc-sidebar ul li a {
            color: #206bc4;
            text-decoration: none;
        }

        .toc-sidebar ul li a:hover {
            text-decoration: underline;
        }

        .dark-mode .toc-sidebar ul li a {
            color: #4dabf7;
        }

        /* Multi-level TOC styles */
        .toc-sidebar ul,
        .offcanvas-body ul {
            list-style: none;
            padding: 0;
        }

        .toc-sidebar ul li,
        .offcanvas-body ul li {
            padding: 5px 0;
        }

        .toc-sidebar ul li a,
        .offcanvas-body ul li a {
            color: #206bc4;
            text-decoration: none;
        }

        .toc-sidebar ul li a:hover,
        .offcanvas-body ul li a:hover {
            text-decoration: underline;
        }

        .dark-mode .toc-sidebar ul li a,
        .dark-mode .offcanvas-body ul li a {
            color: #4dabf7;
        }

        /* Indentation for TOC levels */
        .toc-level-1 {
            padding-left: 0 !important;
        }

        .toc-level-2 {
            padding-left: 10px !important;
        }

        .toc-level-3 {
            padding-left: 20px !important;
        }

        .toc-level-4 {
            padding-left: 30px !important;
        }

        /* Disabled TOC item styles */
        .toc-disabled {
            color: #6c757d;
            cursor: not-allowed;
            pointer-events: none;
        }

        .dark-mode .toc-disabled {
            color: #adb5bd;
        }
    </style>
</head>

<body class="{{ session('theme', 'light') }}-mode">
    <!-- Navbar -->
    <header class="navbar navbar-expand-md navbar-light d-print-none">
        <div class="container-xl">
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#tocDrawer" aria-controls="tocDrawer">
                <span class="navbar-toggler-icon"></span>
            </button>
            <h1 class="navbar-brand">{{ $book['title'] }}</h1>
            <div class="navbar-nav flex-row order-md-last">
                <!-- Theme Toggle -->
                <div class="nav-item">
                    <a href="#" class="nav-link" id="themeToggle">
                        <i class="fas fa-moon"></i>
                    </a>
                </div>
                <!-- User Settings -->
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                        <span class="avatar avatar-sm" style="background-image: url({{ auth()->user()->avatar ?? 'https://via.placeholder.com/40' }})">use</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="#">Profile</a>
                        <a class="dropdown-item" href="#">Settings</a>
                        <a class="dropdown-item" href="{{ route('logout') }}">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- TOC Drawer for Mobile -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="tocDrawer" aria-labelledby="tocDrawerLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="tocDrawerLabel">Table of Contents</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            @if(isset($book['toc']) && is_array($book['toc']) && count($book['toc']) > 0)
            <ul>
                @foreach ($book['toc'] as $index => $item)
                <li class="toc-level-{{ $item['level'] }} {{ $item['disabled'] ? 'toc-disabled' : '' }}">
                    @if (!$item['disabled'])
                    <a href="{{ route('library.book.read', $item['id']) }}">{{ $item['title'] }}</a>
                    @else
                    <span>{{ $item['title'] }}</span>
                    @endif
                </li>
                @endforeach
            </ul>
            @else
            <div class="alert alert-warning">
                此书没有目录
            </div>
            @endif
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-container">
        <!-- Table of Contents Sidebar (Tablet+) -->
        <div class="toc-sidebar card">
            <div class="card-body">
                <h5>Table of Contents</h5>
                @if(isset($book['toc']) && is_array($book['toc']) && count($book['toc']) > 0)
                <ul>
                    @foreach ($book['toc'] as $index => $item)
                    <li class="toc-level-{{ $item['level'] }} {{ $item['disabled'] ? 'toc-disabled' : '' }}">
                        @if (!$item['disabled'])
                        <a href="{{ route('library.book.read', $item['id']) }}">
                            {{ $item['title'] }}
                        </a>
                        @else
                        <span>{{ $item['title'] }}</span>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="alert alert-warning">
                    此书没有目录
                </div>
                @endif
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-area card">

            <div class="card-body">
                <!-- text area -->
                <div>
                    <h2>{{ $book['title'] }}</h2>
                    <p><strong>Author:</strong> <span>{{ $book['author'] }}@
                            <a href="{{ route('blog.index', ['user' => $book['publisher']->username]) }}">
                                {{ $book['publisher']->nickname }}
                            </a>
                        </span></p>

                    <div class="content">
                        @if(isset($book['content']))
                        @foreach ($book['content'] as $index => $paragraph)
                        <div id="para-{{ $paragraph['id'] }}">

                            @foreach ($paragraph['text'] as $rows)
                            <div style="display:flex;">

                                @foreach ($rows as $col)
                                <div style="flex:1;">
                                    @if($paragraph['level']<8)
                                        <h{{ $paragraph['level'] }}>{{ $col }}</h{{ $paragraph['level'] }}>
                                        @else
                                        <p>{{ $col }}</p>
                                        @endif
                                </div>
                                @endforeach

                            </div>
                            @endforeach
                        </div>
                        @endforeach
                        @else
                        <div>没有内容</div>
                        @endif
                    </div>
                </div>
                <!-- Nav buttons -->
                <div class="mt-6 pt-6">
                    <ul class="pagination">
                        @if(!empty($book["pagination"]["prev"]))
                        <li class="page-item page-prev">
                            <a class="page-link" href='{{ route("library.book.read",$book["pagination"]["prev"]["id"]) }}'>
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/chevron-left -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                            <path d="M15 6l-6 6l6 6"></path>
                                        </svg>
                                    </div>
                                    <div class="col">
                                        <div class="page-item-subtitle">previous</div>
                                        <div class="page-item-title">{{ $book["pagination"]["prev"]["title"] }}</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                        @endif
                        @if(!empty($book["pagination"]["next"]))
                        <li class="page-item page-next">
                            <a class="page-link" href='{{ route("library.book.read",$book["pagination"]["next"]["id"]) }}'>
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="page-item-subtitle">next</div>
                                        <div class="page-item-title">{{ $book["pagination"]["next"]["title"] }}</div>
                                    </div>
                                    <div class="col-auto">
                                        <!-- Download SVG icon from http://tabler.io/icons/icon/chevron-right -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                            <path d="M9 6l6 6l-6 6"></path>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
                <!-- Related Books -->
                <div class="related-books">
                    <h3>Related Books</h3>
                    <div class="row row-cards">
                        @if(isset($relatedBooks))
                        @foreach ($relatedBooks as $relatedBook)
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-img-container">
                                    <img src="{{ $relatedBook['image'] }}" alt="{{ $relatedBook['title'] }}">
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">{{ $relatedBook['title'] }}</h5>
                                    <p class="card-text">{{ $relatedBook['description'] }}</p>
                                    <a href="{{ $relatedBook['link'] }}" class="btn btn-primary">Read Now</a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @else
                        <div>没有相关章节</div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        <!-- Right Sidebar (Desktop) -->
        <div class="right-sidebar card">
            <div class="card-body">
                <h5>Download</h5>
                <ul class="list-unstyled">
                    @if(isset($book['downloads']))
                    @foreach ($book['downloads'] as $download)
                    <li><a href="{{ $download['url'] }}" class="btn btn-outline-primary mb-2 w-100"><i class="fas fa-download me-2"></i>{{ $download['format'] }}</a></li>
                    @endforeach
                    @else
                    <div>没有下载链接</div>
                    @endif
                </ul>
                <h5>Category</h5>
                @foreach ($book['categories'] as $category)
                <span class="badge bg-blue text-blue-fg">{{ $category['name'] }}</span>
                @endforeach
                <h5>Tags</h5>
                <div>
                    @foreach ($book['tags'] as $tag)
                    <span class="badge me-1">{{ $tag['name'] }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Tabler JS and Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta21/dist/js/tabler.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        themeToggle.addEventListener('click', (e) => {
            e.preventDefault();
            if (body.classList.contains('light-mode')) {
                body.classList.remove('light-mode');
                body.classList.add('dark-mode');
                fetch('{{ route("theme.toggle") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        theme: 'dark'
                    })
                });
                themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                body.classList.remove('dark-mode');
                body.classList.add('light-mode');
                fetch('{{ route("theme.toggle") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        theme: 'light'
                    })
                });
                themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            }
        });

        // Smooth scroll for TOC links
        document.querySelectorAll('.toc-sidebar a, .offcanvas-body a').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
                // Close drawer on mobile after clicking
                if (window.innerWidth < 768) {
                    const drawer = document.querySelector('#tocDrawer');
                    const bsDrawer = bootstrap.Offcanvas.getInstance(drawer);
                    bsDrawer.hide();
                }
            });
        });
    </script>
</body>

</html>
