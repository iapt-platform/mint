<!doctype html>
<html lang="zh">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>@yield('title', '巴利书库')</title>
    @stack('styles')
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/icons-sprite.svg" rel="stylesheet" />
    <script
        src="https://cdn.jsdelivr.net/npm/@tabler/core@1.3.2/dist/js/tabler.min.js">
    </script>

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
        }

        @media (max-width: 576px) {
            .hero-title {
                font-size: 1.5rem;
            }

            .hero-subtitle {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-overlay">
                <div style="height:30px;width:100%;display: flex;justify-content: center;">
                    <div style="color:white;flex:1;">
                    </div>
                    <div style="color:white;">
                        <x-language-switcher />
                    </div>
                </div>
            </div>

            <div class="hero-content">

                <h1 class="hero-title">巴利书库</h1>
                <p class="hero-subtitle">探索wikipali，开启智慧之门</p>

                <div class="search-box">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" placeholder="搜索图书、作者或主题...">
                        <button class="btn btn-primary btn-lg" type="button">
                            <i class="ti ti-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <div class="page-wrapper">
            @yield('content')
        </div>
    </div>

    <!-- Tabler JS and Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta21/dist/js/tabler.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
