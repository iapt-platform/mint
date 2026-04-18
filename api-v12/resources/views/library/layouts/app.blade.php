{{-- api-v12/resources/views/library/layouts/app.blade.php --}}
<!doctype html>
<html lang="zh">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>@yield('title', '巴利书库')</title>
    @stack('styles')

    @vite(['resources/css/main.css', 'resources/js/app.js'])

    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.3.2/dist/js/tabler.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body>
    <div class="page">

        <x-library.header />

        @yield('hero')

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
