// resources/js/modules/navbar.js
// 顶部导航 mobile 抽屉开关逻辑。
// 来源：library/layouts/app.blade.php 内联 script（去除了无效的 mobileMenu 引用）。

export function initNavbar() {
    const btn     = document.getElementById('bcHamburger');
    const overlay = document.getElementById('bcOverlay');
    const drawer  = document.getElementById('bcDrawer');
    const close   = document.getElementById('bcDrawerClose');

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
}
