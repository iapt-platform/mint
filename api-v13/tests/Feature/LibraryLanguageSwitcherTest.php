<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * 语言切换器（x-language-switcher）：保留当前地址的路径与查询串，只替换 ?lang=。
 *
 * 以前按路由参数重新生成地址：筛选、页码、搜索词会丢；wiki 的路径参数也叫 {lang}，会被换成界面语言。
 */
it('keeps the current query string when switching the ui language', function () {
    $this->get('/library/tipitaka/channels?type=translation&page=2')
        ->assertOk()
        ->assertSee('/library/tipitaka/channels?type=translation&amp;page=2&amp;lang=zh-Hans', false);
});

it('replaces an existing lang parameter instead of adding a second one', function () {
    $this->get('/library/tipitaka/channels?lang=my&type=translation')
        ->assertOk()
        ->assertSee('/library/tipitaka/channels?lang=zh-Hans&amp;type=translation', false);
});
