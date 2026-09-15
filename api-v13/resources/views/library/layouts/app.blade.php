{{-- api-v12/resources/views/library/layouts/app.blade.php --}}
<!doctype html>
<html lang="zh">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>@yield('title', __('library.site_name'))</title>
    @stack('styles')

    @vite(['resources/js/app.js'])

</head>

<body>
    <div class="page">

        <div class="hero-wrapper">
            <x-library.header />
            @yield('hero')
        </div>

        <div class="page-wrapper">
            @yield('content')
        </div>

    </div>

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
