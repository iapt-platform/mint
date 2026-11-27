<?php

use App\Services\RelatedParagraph\RelatedParagraphChecker;

/**
 * @param  array<int, array{0: int, 1: int, 2?: int}>  $paras  段落号 => [begin, end, sn]
 */
function checkedBook(array $paras, string $layer = 'mula'): array
{
    $rows = [];
    foreach ($paras as $para => $value) {
        $rows[$para] = ['book_name' => 'kn12', 'begin' => $value[0], 'end' => $value[1], 'book_id' => $value[2] ?? 1];
    }

    return (new RelatedParagraphChecker)->summarize([145 => $rows], [1 => $layer, 2 => $layer])['kn12']['books'][145];
}

it('counts a wrap only when the number repeats within the same sn', function () {
    expect(checkedBook([1 => [1, 1], 2 => [81, 81], 3 => [1, 1]])['wraps'])->toBe(['81→1@3'])
        ->and(checkedBook([1 => [162, 162], 2 => [335, 335], 3 => [1, 1]])['wraps'])->toBe([])
        ->and(checkedBook([1 => [1, 1], 2 => [33, 33], 3 => [1, 1, 2]])['wraps'])->toBe([]);
});

it('counts small drops as back references', function () {
    expect(checkedBook([1 => [1, 7], 2 => [7, 7], 3 => [5, 5]]))->toMatchArray(['wraps' => [], 'backs' => 1]);
});

it('lists commentary numbers missing from the mula side', function () {
    $rows = [
        164 => [1 => ['book_name' => 'mn1', 'begin' => 1, 'end' => 2, 'book_id' => 1]],
        130 => [1 => ['book_name' => 'mn1', 'begin' => 2, 'end' => 3, 'book_id' => 2]],
    ];

    $summary = (new RelatedParagraphChecker)->summarize($rows, [1 => 'mula', 2 => 'atthakatha']);

    expect($summary['mn1']['books'][130]['orphans'])->toBe([3])
        ->and($summary['mn1']['mula_max'])->toBe(2);
});
