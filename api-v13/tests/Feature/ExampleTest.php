<?php

/**
 * 冒烟测试：应用能不能起来、首页能不能渲染。
 *
 * 原来这条断言的是 `route('home')`，那是 Laravel 脚手架的样板——本应用没有叫
 * `home` 的路由（首页是未命名的 `/`，library 那个叫 `library.home`），所以它
 * 从建库起就一直红着。
 *
 * 页面渲染依赖 Vite manifest，CI 里 `npm run build` 排在 `pest` 之前（见
 * .github/workflows/tests.yml），本地没构建过的话这两条会报 ViteException——
 * 那本身就是个有用的信号。
 */
test('the site index renders', function () {
    $this->get('/')->assertOk();
});

test('the library home renders', function () {
    $this->get(route('library.home'))->assertOk();
});
