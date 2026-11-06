<div class="anthology-breadcrumb-bar">
    <div class="container-xl">
        <div class="bc-inner">

            {{-- 站点 logo，回 wikipali 首页。浅色导航栏用深色字版；压在 hero 上时（导航透明）换白字版，见 navbar.css --}}
            <a href="{{ url('/') }}" class="bc-logo" aria-label="WikiPāli">
                <img src="{{ asset('assets/images/wikipali_logo_dark.svg') }}" alt="WikiPāli" class="bc-logo__img bc-logo__img--dark">
                <img src="{{ asset('assets/images/wikipali_logo.svg') }}" alt="" aria-hidden="true" class="bc-logo__img bc-logo__img--light">
            </a>

            <ol class="breadcrumb mb-0">
                @yield('breadcrumb')
            </ol>

            <ul class="bc-nav">
                <li>
                    <a href="{{ route('library.home') }}"
                        class="{{ request()->routeIs('library.home') ? 'active' : '' }}">
                        {{ __('site.nav.home') }}
                    </a>
                </li>

                <li>
                    <a href="{{ route('library.tipitaka.index') }}"
                        class="{{ request()->routeIs('library.tipitaka.*') ? 'active' : '' }}">
                        {{ __('site.nav.tipitaka') }}
                    </a>
                </li>

                <li>
                    <a href="{{ route('library.wiki.home') }}"
                        class="{{ request()->routeIs('library.wiki.*') ? 'active' : '' }}">
                        {{ __('site.nav.wiki') }}
                    </a>
                </li>

                <li>
                    <a href="{{ route('library.anthology.index') }}"
                        class="{{ request()->routeIs('library.anthology.*') ? 'active' : '' }}">
                        {{ __('site.nav.anthology') }}
                    </a>
                </li>

                <li>
                    <a href="{{ route('library.course') }}"
                        class="{{ request()->routeIs('library.course', 'library.course.history') ? 'active' : '' }}">
                        {{ __('site.nav.course') }}
                    </a>
                </li>

                <li>
                    <a href="{{ route('library.download') }}"
                        class="{{ request()->routeIs('library.download') ? 'active' : '' }}">
                        {{ __('site.nav.download') }}
                    </a>
                </li>

                <li>
                    <x-language-switcher />
                </li>
            </ul>

            <button class="bc-hamburger" id="bcHamburger"
                aria-label="{{ __('site.nav.open_menu') }}">
                <i class="ti ti-menu-2"></i>
            </button>

        </div>
    </div>
</div>

<div class="bc-mobile-overlay" id="bcOverlay"></div>

<div class="bc-mobile-drawer" id="bcDrawer">
    <div class="bc-mobile-drawer-header">
        <span>{{ __('site.nav.menu') }}</span>

        <button class="bc-mobile-drawer-close" id="bcDrawerClose"
            aria-label="{{ __('site.nav.close_menu') }}">
            <i class="ti ti-x"></i>
        </button>
    </div>

    <ul class="bc-mobile-nav">
        <li><a href="{{ route('library.home') }}"
                class="{{ request()->routeIs('library.home') ? 'active' : '' }}">
                {{ __('site.nav.home') }}
            </a></li>

        <li><a href="{{ route('library.tipitaka.index') }}"
                class="{{ request()->routeIs('library.tipitaka.*') ? 'active' : '' }}">
                {{ __('site.nav.tipitaka') }}
            </a></li>

        <li><a href="{{ route('library.wiki.home') }}"
                class="{{ request()->routeIs('library.wiki.*') ? 'active' : '' }}">
                {{ __('site.nav.wiki') }}
            </a></li>

        <li><a href="{{ route('library.anthology.index') }}"
                class="{{ request()->routeIs('library.anthology.*') ? 'active' : '' }}">
                {{ __('site.nav.anthology') }}
            </a></li>

        <li><a href="{{ route('library.course') }}"
                class="{{ request()->routeIs('library.course', 'library.course.history') ? 'active' : '' }}">
                {{ __('site.nav.course') }}
            </a></li>

        <li><a href="{{ route('library.download') }}"
                class="{{ request()->routeIs('library.download') ? 'active' : '' }}">
                {{ __('site.nav.download') }}
            </a></li>

        <li style="padding: 1rem 0.25rem;">
            <x-language-switcher />
        </li>
    </ul>
</div>
