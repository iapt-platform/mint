{{-- api-v12/resources/views/components/library/header.blade.php --}}
<div class="anthology-breadcrumb-bar">
    <div class="container-xl">
        <div class="bc-inner">

            <ol class="breadcrumb mb-0">
                @yield('breadcrumb')
            </ol>

            <ul class="bc-nav">
                <li><a href="{{ route('library.home') }}">首页</a></li>
                <li><a href="{{ route('library.wiki.home') }}">百科</a></li>
                <li><a href="{{ route('library.anthology.index') }}">文集</a></li>
                <li><a href="{{ route('library.download') }}">下载</a></li>

                @auth
                <li><a href="#">我的账户</a></li>
                @else
                <li><a href="#">注册/登录</a></li>
                @endauth

                <li>
                    <x-language-switcher />
                </li>
            </ul>

            <button class="bc-hamburger" id="bcHamburger">
                <i class="ti ti-menu-2"></i>
            </button>

        </div>
    </div>
</div>


<div class="bc-mobile-overlay" id="bcOverlay"></div>

<div class="bc-mobile-drawer" id="bcDrawer">
    <div class="bc-mobile-drawer-header">
        <span>导航</span>

        <button class="bc-mobile-drawer-close" id="bcDrawerClose">
            <i class="ti ti-x"></i>
        </button>
    </div>

    <ul class="bc-mobile-nav">
        <li><a href="{{ route('library.home') }}">首页</a></li>
        <li><a href="{{ route('library.wiki.home') }}">百科</a></li>
        <li><a href="{{ route('library.anthology.index') }}">文集</a></li>
        <li><a href="{{ route('library.download') }}">下载</a></li>

        @auth
        <li><a href="#">我的账户</a></li>
        @else
        <li><a href="#">注册/登录</a></li>
        @endauth

        <li style="padding:1rem 1.25rem;">
            <x-language-switcher />
        </li>
    </ul>
</div>
