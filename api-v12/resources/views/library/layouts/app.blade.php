{{-- api-v12/resources/views/library/layouts/app.blade.php --}}
<!doctype html>
<html lang="zh">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>@yield('title', '巴利书库')</title>
    @stack('styles')
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.3.2/dist/js/tabler.min.js"></script>

    <style>
        .book-card {
            transition: transform 0.2s;
        }

        .book-card:hover {
            transform: translateY(-2px);
        }

        .book-cover {
            height: 200px;
            object-fit: cover;
        }

        @media (max-width: 768px) {
            .book-cover {
                height: 150px;
            }
        }

        .nav-overlay {
            position: absolute;
        }

        .hero-section {
            height: 250px;
            width: 100%;
            background-image: url('{{ URL::asset("assets/images/hero-2.jpg") }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.2);
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            max-width: 600px;
            padding: 0 1rem;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .hero-subtitle {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .search-box {
            background: white;
            border-radius: 0.5rem;
            padding: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 0 auto;
        }

        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .stats-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-item {
            text-align: center;
            padding: 2rem 1rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            display: block;
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
            margin-top: 0.5rem;
        }

        /* Navigation Styles */
        .top-nav {
            height: 50px;
            width: 100%;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 0 2rem;
            position: relative;
            z-index: 10;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-item a {
            color: white;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: opacity 0.2s;
            white-space: nowrap;
        }

        .nav-item a:hover {
            opacity: 0.8;
        }

        /* Hamburger Menu */
        .hamburger-btn {
            display: none;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            border-radius: 0.375rem;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem 0.75rem;
            z-index: 1001;
            transition: background 0.2s;
            width: 44px;
            height: 44px;
            align-items: center;
            justify-content: center;
        }

        .hamburger-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* CSS Hamburger Icon */
        .hamburger-icon {
            display: flex;
            flex-direction: column;
            gap: 4px;
            width: 24px;
        }

        .hamburger-icon span {
            display: block;
            height: 2px;
            background: white;
            border-radius: 2px;
            transition: all 0.3s;
        }

        .hamburger-btn.active .hamburger-icon span:nth-child(1) {
            transform: translateY(6px) rotate(45deg);
        }

        .hamburger-btn.active .hamburger-icon span:nth-child(2) {
            opacity: 0;
        }

        .hamburger-btn.active .hamburger-icon span:nth-child(3) {
            transform: translateY(-6px) rotate(-45deg);
        }

        .mobile-menu {
            display: none;
            position: fixed;
            top: 0;
            right: -100%;
            width: 280px;
            height: 100vh;
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(10px);
            transition: right 0.3s ease;
            z-index: 1000;
            padding-top: 60px;
        }

        .mobile-menu.active {
            right: 0;
        }

        .mobile-nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .mobile-nav-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .mobile-nav-item a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 1rem 2rem;
            font-size: 1rem;
            transition: background 0.2s;
        }

        .mobile-nav-item a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .mobile-overlay.active {
            display: block;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .hero-section {
                height: 250px;
            }

            .stat-number {
                font-size: 2rem;
            }

            .top-nav {
                padding: 0 1rem;
            }

            .nav-menu {
                display: none;
            }

            .hamburger-btn {
                display: flex;
            }

            .mobile-menu {
                display: block;
            }
        }

        @media (max-width: 576px) {
            .hero-title {
                font-size: 1.5rem;
            }

            .hero-subtitle {
                font-size: 0.9rem;
            }

            .top-nav {
                padding: 0 0.5rem;
            }
        }
    </style>

    <style>
        :root {
            --sf: #c8860a;
            --sf-light: #f5e6c8;
            --sf-pale: #fdf8f0;
            --ink: #1a1208;
            --ink-soft: #4a3f2f;
            --ink-muted: #8a7a68;
            --bdr: #e8ddd0;
            --card-bg: #fffdf9;
        }

        /* Breadcrumb bar */
        .anthology-breadcrumb-bar {
            background: rgba(255, 255, 255, .55);
            border-bottom: 1px solid var(--bdr);
            padding: .5rem 0;
        }

        .anthology-breadcrumb-bar .bc-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .anthology-breadcrumb-bar .breadcrumb {
            margin: 0;
            font-size: .78rem;
            flex-shrink: 0;
        }

        .anthology-breadcrumb-bar .breadcrumb-item a {
            color: var(--sf);
            text-decoration: none;
        }

        .anthology-breadcrumb-bar .breadcrumb-item.active {
            color: var(--ink-muted);
        }

        .anthology-breadcrumb-bar .breadcrumb-item+.breadcrumb-item::before {
            color: var(--ink-muted);
        }

        /* Top nav inside breadcrumb bar */
        .bc-nav {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            list-style: none;
            margin: 0;
            padding: 0;
            flex-shrink: 0;
        }

        .bc-nav li a {
            font-size: .82rem;
            color: var(--ink-soft);
            text-decoration: none;
            white-space: nowrap;
            transition: color .15s;
        }

        .bc-nav li a:hover {
            color: var(--sf);
        }

        .bc-nav li a.active {
            color: var(--sf);
            font-weight: 600;
        }

        /* Mobile nav: hamburger */
        .bc-hamburger {
            display: none;
            background: none;
            border: 1px solid var(--bdr);
            border-radius: 5px;
            padding: 4px 8px;
            cursor: pointer;
            color: var(--ink-soft);
            line-height: 1;
        }

        .bc-hamburger:hover {
            border-color: var(--sf);
            color: var(--sf);
        }

        /* Mobile drawer */
        .bc-mobile-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .4);
            z-index: 1040;
        }

        .bc-mobile-overlay.open {
            display: block;
        }

        .bc-mobile-drawer {
            position: fixed;
            top: 0;
            right: -100%;
            width: 240px;
            height: 100vh;
            background: var(--card-bg);
            border-left: 1px solid var(--bdr);
            z-index: 1050;
            transition: right .25s ease;
            padding: 1rem 0;
            box-shadow: -4px 0 20px rgba(0, 0, 0, .1);
        }

        .bc-mobile-drawer.open {
            right: 0;
        }

        .bc-mobile-drawer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .5rem 1.25rem .75rem;
            border-bottom: 1px solid var(--bdr);
            margin-bottom: .5rem;
        }

        .bc-mobile-drawer-header span {
            font-size: .85rem;
            font-weight: 600;
            color: var(--ink-soft);
        }

        .bc-mobile-drawer-close {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--ink-muted);
            font-size: 1rem;
            line-height: 1;
            padding: 2px;
        }

        .bc-mobile-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .bc-mobile-nav li a {
            display: block;
            padding: .65rem 1.25rem;
            font-size: .9rem;
            color: var(--ink-soft);
            text-decoration: none;
            border-bottom: 1px solid rgba(232, 221, 208, .5);
            transition: background .15s;
        }

        .bc-mobile-nav li a:hover {
            background: var(--sf-pale);
            color: var(--sf);
        }

        .bc-mobile-nav li a.active {
            color: var(--sf);
            font-weight: 600;
        }

        @media (max-width: 640px) {
            .bc-nav {
                display: none;
            }

            .bc-hamburger {
                display: inline-flex;
                align-items: center;
            }
        }
    </style>


</head>

<body>
    <div class="page">

        <x-library.header />

        @yield('hero')

        <div class="page-wrapper">
            @yield('content')
        </div>

    </div>

    <!-- Tabler JS and Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta21/dist/js/tabler.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Hamburger Menu Toggle

        (function() {
            const btn = document.getElementById('bcHamburger');
            const overlay = document.getElementById('bcOverlay');
            const drawer = document.getElementById('bcDrawer');
            const close = document.getElementById('bcDrawerClose');

            if (!btn) return;

            function open() {
                drawer.classList.add('open');
                overlay.classList.add('open');
            }

            function shut() {
                drawer.classList.remove('open');
                overlay.classList.remove('open');
            }

            btn.addEventListener('click', open);
            overlay.addEventListener('click', shut);
            close.addEventListener('click', shut);

            drawer.querySelectorAll('a').forEach(a => {
                a.addEventListener('click', shut);
            });
        })();
        const mobileOverlay = document.getElementById('mobileOverlay');

        function toggleMenu() {
            mobileMenu.classList.toggle('active');
            mobileOverlay.classList.toggle('active');
            hamburgerBtn.classList.toggle('active');
        }

        hamburgerBtn.addEventListener('click', toggleMenu);
        mobileOverlay.addEventListener('click', toggleMenu);

        // Close menu when clicking on a link
        document.querySelectorAll('.mobile-nav-item a').forEach(link => {
            link.addEventListener('click', () => {
                toggleMenu();
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
