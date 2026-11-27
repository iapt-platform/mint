<?php

use App\Services\RelatedParagraph\ParagraphMapBuilder;

/*
 * 重建规则（documents/related-paragraphs-rebuild.md §3.1）：继承、sn 边界、书名优先级、
 * 序言、补丁覆盖、有号没名字清零。
 */

/**
 * @return array<int, array{0: string, 1: int, 2: int}> 段落号 => [book_name, begin, end]
 */
function relatedMap(array $paragraphs, array $markers, array $titles = [1 => 100], array $snNames = [], array $patch = []): array
{
    $built = (new ParagraphMapBuilder)->build($paragraphs, $markers, $titles, $snNames, $patch);

    return array_map(fn (array $row) => [$row['book_name'], $row['begin'], $row['end']], $built['rows']);
}

it('inherits the last anchor until the next one', function () {
    expect(relatedMap([1, 2, 3, 4], [1 => ['dn1'], 2 => ['para1'], 4 => ['para2-3']]))->toBe([
        1 => ['dn1', 0, 0],
        2 => ['dn1', 1, 1],
        3 => ['dn1', 1, 1],
        4 => ['dn1', 2, 3],
    ]);
});

it('lets the suffixed twin decide the name', function () {
    expect(relatedMap([1], [1 => ['para4', 'para4_dn1']], snNames: [100 => 'abhi1'])[1])->toBe(['dn1', 4, 4]);
});

it('stops inheriting at a book_titles boundary', function () {
    $map = relatedMap([1, 2, 3], [1 => ['vin6', 'para75']], titles: [1 => 100, 3 => 101]);

    expect($map[2])->toBe(['vin6', 75, 75])
        ->and($map[3])->toBe(['vin6', 0, 0]);
});

it('stops inheriting when the book name changes', function () {
    $map = relatedMap([1, 2, 3], [1 => ['kn10', 'para196'], 2 => ['kn11']]);

    expect($map[2])->toBe(['kn11', 0, 0])
        ->and($map[3])->toBe(['kn11', 0, 0]);
});

it('prefers the sn name table over in-file markers for bare anchors', function () {
    expect(relatedMap([1], [1 => ['para7']], snNames: [100 => 'abhi3'])[1])->toBe(['abhi3', 7, 7]);
});

it('keeps preface paragraphs under X_A even when the anchor carries the X suffix', function () {
    $map = relatedMap([1, 2, 3, 4], [1 => ['mn1_0'], 2 => ['para1', 'para1_mn1'], 3 => ['mn1_1'], 4 => ['para1_mn1']]);

    expect($map[2])->toBe(['mn1_A', 1, 1])
        ->and($map[3])->toBe(['mn1', 0, 0])
        ->and($map[4])->toBe(['mn1', 1, 1]);
});

it('clears numbers that end up without a name', function () {
    $built = (new ParagraphMapBuilder)->build([1, 2], [1 => ['para5']], [1 => 100], []);

    expect($built['rows'][1])->toMatchArray(['book_name' => '', 'begin' => 0, 'end' => 0, 'cleared' => true])
        ->and($built['rows'][2]['cleared'])->toBeTrue();
});

it('overrides and adds paragraphs from the patch table', function () {
    $patch = [
        2 => ['book_name' => null, 'begin' => 82, 'end' => 83],
        3 => ['book_name' => 'kn12', 'begin' => 9, 'end' => 9],
    ];

    expect(relatedMap([1, 2, 3], [1 => ['kn12', 'para81']], titles: [1 => 100, 3 => 101], patch: $patch))->toBe([
        1 => ['kn12', 81, 81],
        2 => ['kn12', 82, 83],
        3 => ['kn12', 9, 9],
    ]);
});

it('gives a patched name to a paragraph that would otherwise be cleared', function () {
    expect(relatedMap([1], [1 => ['para5']], patch: [1 => ['book_name' => 'vin3', 'begin' => 5, 'end' => 5]])[1])
        ->toBe(['vin3', 5, 5]);
});

it('assigns book_id from book_titles and 0 before the first title', function () {
    $built = (new ParagraphMapBuilder)->build([1, 2, 5], [], [2 => 7, 5 => 8], []);

    expect(array_column($built['rows'], 'book_id'))->toBe([0, 7, 8]);
});
