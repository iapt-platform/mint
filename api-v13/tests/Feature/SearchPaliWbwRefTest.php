<?php

/**
 * search-pali-wbw 的资源里要带上 page_numbers 的页码引用。
 *
 * 每个 (book, paragraph) 在 page_numbers 里可能因为 wid 不同而有多行，
 * 输出时每个 type 只保留 wid 最小的那一行，并且只暴露 type / page 两个字段。
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
function pageNumberRow(string $type, int $wid, int $page): array
{
    return [
        'type' => $type,
        'volume' => 1,
        'page' => $page,
        'book' => 1,
        'paragraph' => 1,
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
    expect($ref)->toHaveCount(2);

    $byType = collect($ref)->keyBy('type');

    expect($byType->get('a'))->toBe(['type' => 'a', 'page' => 222])
        ->and($byType->get('b'))->toBe(['type' => 'b', 'page' => 555]);
});

it('omits ref when there are no page_numbers rows', function () {
    DB::table('wbw_templates')->insert([wbwRow()]);
    DB::table('pali_texts')->insert([paliTextRow()]);

    $response = $this->getJson('/api/v2/search-pali-wbw?key=dhammo')
        ->assertOk();

    expect($response->json('data.rows.0'))->not->toHaveKey('ref');
});
