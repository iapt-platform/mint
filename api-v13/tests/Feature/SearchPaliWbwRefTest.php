<?php

/**
 * search-pali-wbw 的资源里要带上页码引用 ref。
 *
 * 每个 (book, paragraph) 在 page_numbers 里可能因为 wid 不同而有多行，
 * 输出时每个 type 只保留 wid 最小的那一行，并且暴露 type / page / title 三个字段；
 * title 是书缩写：type 为 M 时取 abbr_my、为 P 时取 abbr_pts。
 * 另外恒有一条 type='wp' 的条目，page 为段落号、title 为 abbr_wp。
 */
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * 造一行能被检索命中的 wbw 词元。
 */
function wbwRow(int $paragraph = 1): array
{
    return [
        'book' => 1,
        'paragraph' => $paragraph,
        'wid' => 1,
        'word' => 'dhammo',
        'real' => 'dhammo',
        'type' => '',
        'gramma' => '',
        'part' => '',
        'style' => '',
        'pcd_book_id' => 1,
        'weight' => 1,
    ];
}

/**
 * 造一段 pali_text，让资源能生成标题 / 链接 / 高亮。
 */
function paliTextRow(int $paragraph = 1): array
{
    return [
        'book' => 1,
        'paragraph' => $paragraph,
        'level' => 1,
        'class' => '',
        'toc' => 'Dhammapada',
        'text' => '',
        'html' => '<p>dhammo</p>',
    ];
}

/**
 * 造一行 page_numbers。
 */
function pageNumberRow(string $type, int $wid, int $page, int $paragraph = 1): array
{
    return [
        'type' => $type,
        'volume' => 1,
        'page' => $page,
        'book' => 1,
        'paragraph' => $paragraph,
        'wid' => $wid,
        'pcd_book_id' => 1,
    ];
}

it('adds ref with the smallest wid page for each type', function () {
    DB::table('wbw_templates')->insert([wbwRow()]);
    DB::table('pali_texts')->insert([paliTextRow()]);
    DB::table('page_numbers')->insert([
        pageNumberRow('a', 5, 111),
        pageNumberRow('a', 2, 222),
        pageNumberRow('a', 9, 333),
        pageNumberRow('b', 4, 444),
        pageNumberRow('b', 1, 555),
    ]);

    $response = $this->getJson('/api/v2/search-pali-wbw?key=dhammo')
        ->assertOk();

    $ref = $response->json('data.rows.0.ref');
    $byType = collect($ref)->keyBy('type');

    expect($byType->get('a'))->toBe(['type' => 'a', 'page' => 222, 'title' => null])
        ->and($byType->get('b'))->toBe(['type' => 'b', 'page' => 555, 'title' => null])
        ->and($byType->get('wp'))->toBe(['type' => 'wp', 'page' => 1, 'title' => null]);
});

it('adds the series abbreviation as title for M and P types', function () {
    DB::table('wbw_templates')->insert([wbwRow(10)]);
    DB::table('pali_texts')->insert([paliTextRow(10)]);
    DB::table('page_numbers')->insert([
        pageNumberRow('M', 1, 111, 10),
        pageNumberRow('P', 2, 222, 10),
        pageNumberRow('V', 3, 333, 10),
    ]);

    $response = $this->getJson('/api/v2/search-pali-wbw?key=dhammo')
        ->assertOk();

    $byType = collect($response->json('data.rows.0.ref'))->keyBy('type');

    expect($byType->get('M'))->toBe(['type' => 'M', 'page' => 111, 'title' => 'namakkāra'])
        ->and($byType->get('P'))->toBe(['type' => 'P', 'page' => 222, 'title' => 'Nam'])
        ->and($byType->get('V'))->toBe(['type' => 'V', 'page' => 333, 'title' => null])
        ->and($byType->get('wp'))->toBe(['type' => 'wp', 'page' => 10, 'title' => 'namakkāra.']);
});

it('adds the wp entry even when there are no page_numbers rows', function () {
    DB::table('wbw_templates')->insert([wbwRow(10)]);
    DB::table('pali_texts')->insert([paliTextRow(10)]);

    $response = $this->getJson('/api/v2/search-pali-wbw?key=dhammo')
        ->assertOk();

    expect($response->json('data.rows.0.ref'))->toBe([
        ['type' => 'wp', 'page' => 10, 'title' => 'namakkāra.'],
    ]);
});
