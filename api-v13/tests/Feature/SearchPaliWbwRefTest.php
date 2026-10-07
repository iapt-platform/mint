<?php

/**
 * search-pali-wbw 的资源里要带上页码引用 ref。
 *
 * 每个 (book, paragraph) 在 page_numbers 里可能因为 wid 不同而有多行，
 * 输出时每个 type 只保留 wid 最小的那一行，并且暴露 type / page / title 三个字段；
 * type 把单字母代号映射成缩写（M→My、P→PTS、V→VRI、T→Thai、O→Other），
 * title 是书缩写：type 为 M 时取 abbr_my、为 P 时取 abbr_pts。
 * 另外恒有一条 type='WP' 的条目，page 为段落号、title 为 abbr_wp。
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
    DB::table('wbw_templates')->insert([wbwRow(10)]);
    DB::table('pali_texts')->insert([paliTextRow(10)]);
    DB::table('page_numbers')->insert([
        pageNumberRow('M', 5, 111, 10),
        pageNumberRow('M', 2, 222, 10),
        pageNumberRow('M', 9, 333, 10),
        pageNumberRow('P', 4, 444, 10),
        pageNumberRow('P', 1, 555, 10),
    ]);

    $response = $this->getJson('/api/v2/search-pali-wbw?key=dhammo')
        ->assertOk();

    $byType = collect($response->json('data.rows.0.ref'))->keyBy('type');

    expect($byType->get('My'))->toBe(['type' => 'My', 'page' => 222, 'title' => 'namakkāra'])
        ->and($byType->get('PTS'))->toBe(['type' => 'PTS', 'page' => 555, 'title' => 'Nam'])
        ->and($byType->get('WP'))->toBe(['type' => 'WP', 'page' => 10, 'title' => 'namakkāra.']);
});

it('maps all page number types to their abbreviations', function () {
    DB::table('wbw_templates')->insert([wbwRow(10)]);
    DB::table('pali_texts')->insert([paliTextRow(10)]);
    DB::table('page_numbers')->insert([
        pageNumberRow('P', 1, 101, 10),
        pageNumberRow('M', 2, 102, 10),
        pageNumberRow('V', 3, 103, 10),
        pageNumberRow('T', 4, 104, 10),
        pageNumberRow('O', 5, 105, 10),
    ]);

    $response = $this->getJson('/api/v2/search-pali-wbw?key=dhammo')
        ->assertOk();

    $byType = collect($response->json('data.rows.0.ref'))->keyBy('type');

    expect($byType->get('PTS'))->toBe(['type' => 'PTS', 'page' => 101, 'title' => 'Nam'])
        ->and($byType->get('My'))->toBe(['type' => 'My', 'page' => 102, 'title' => 'namakkāra'])
        ->and($byType->get('VRI'))->toBe(['type' => 'VRI', 'page' => 103, 'title' => null])
        ->and($byType->get('Thai'))->toBe(['type' => 'Thai', 'page' => 104, 'title' => null])
        ->and($byType->get('Other'))->toBe(['type' => 'Other', 'page' => 105, 'title' => null]);
});

it('adds the WP entry even when there are no page_numbers rows', function () {
    DB::table('wbw_templates')->insert([wbwRow(10)]);
    DB::table('pali_texts')->insert([paliTextRow(10)]);

    $response = $this->getJson('/api/v2/search-pali-wbw?key=dhammo')
        ->assertOk();

    expect($response->json('data.rows.0.ref'))->toBe([
        ['type' => 'WP', 'page' => 10, 'title' => 'namakkāra.'],
    ]);
});
