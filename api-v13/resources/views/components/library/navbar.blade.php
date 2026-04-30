{{-- resources/views/components/library/navbar.blade.php
     替换原 components/library/header.blade.php。
     文件名从 header 改为 navbar，与规范目录对齐。
     原文件内容不变，仅做变量名规范化（--wp-* token 替代硬编码色值已在 CSS 层处理）。
--}}
<div class="anthology-breadcrumb-bar">
    <div class="container-xl">
        <div class="bc-inner">

            <ol class="breadcrumb mb-0">
                @yield('breadcrumb')
            </ol>

            <ul class="bc-nav">
                <li><a href="{{ route('library.home') }}"
                       class="{{ request()->routeIs('library.home') ? 'active' : '' }}">首页</a></li>
                <li><a href="{{ route('library.tipitaka.index') }}"
                       class="{{ request()->routeIs('library.tipitaka.*') ? 'active' : '' }}">三藏</a></li>
                <li><a href="{{ route('library.wiki.home') }}"
                       class="{{ request()->routeIs('library.wiki.*') ? 'active' : '' }}">百科</a></li>
                <li><a href="{{ route('library.anthology.index') }}"
                       class="{{ request()->routeIs('library.anthology.*') ? 'active' : '' }}">文集</a></li>
                <li><a href="{{ route('library.download') }}"
                       class="{{ request()->routeIs('library.download') ? 'active' : '' }}">下载</a></li>
                <li>
                    <x-language-switcher />
                </li>
            </ul>

            <button class="bc-hamburger" id="bcHamburger" aria-label="打开导航">
                <i class="ti ti-menu-2"></i>
            </button>

        </div>
    </div>
</div>

<div class="bc-mobile-overlay" id="bcOverlay"></div>

<div class="bc-mobile-drawer" id="bcDrawer">
    <div class="bc-mobile-drawer-header">
        <span>导航</span>
        <button class="bc-mobile-drawer-close" id="bcDrawerClose" aria-label="关闭">
            <i class="ti ti-x"></i>
        </button>
    </div>
    <ul class="bc-mobile-nav">
        <li><a href="{{ route('library.home') }}"
               class="{{ request()->routeIs('library.home') ? 'active' : '' }}">首页</a></li>
        <li><a href="{{ route('library.tipitaka.index') }}"
               class="{{ request()->routeIs('library.tipitaka.*') ? 'active' : '' }}">三藏</a></li>
        <li><a href="{{ route('library.wiki.home') }}"
               class="{{ request()->routeIs('library.wiki.*') ? 'active' : '' }}">百科</a></li>
        <li><a href="{{ route('library.anthology.index') }}"
               class="{{ request()->routeIs('library.anthology.*') ? 'active' : '' }}">文集</a></li>
        <li><a href="{{ route('library.download') }}"
               class="{{ request()->routeIs('library.download') ? 'active' : '' }}">下载</a></li>
        <li style="padding: 1rem 0.25rem;">
            <x-language-switcher />
        </li>
    </ul>
</div>
