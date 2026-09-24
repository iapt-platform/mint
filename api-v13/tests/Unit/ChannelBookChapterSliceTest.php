<?php

use App\Http\Controllers\TipitakaReadChapterController;

/**
 * slice() 是纯函数，不碰数据库。段落数上限要造上千段才看得出来，走 HTTP 会真的
 * 去渲染每一段，所以这里直接对着方法本身测。
 *
 * @param  array<int, int>  $paragraphs
 * @param  array<int, int>  $lengths
 * @return array<int, int>
 */
function sliceParagraphs(array $paragraphs, array $lengths, int $limit, string $unit, int $offset): array
{
    $controller = new class extends TipitakaReadChapterController
    {
        /**
         * @param  array<int, int>  $paragraphs
         * @param  array<int, int>  $lengths
         * @return array<int, int>
         */
        public function sliceForTest(array $paragraphs, array $lengths, int $limit, string $unit, int $offset): array
        {
            return $this->slice($paragraphs, $lengths, $limit, $unit, $offset);
        }
    };

    return $controller->sliceForTest($paragraphs, $lengths, $limit, $unit, $offset);
}

it('caps a byte-mode block at 200 paragraphs however short the paragraphs are', function () {
    // 1000 段，每段 1 字节；不设段落上限的话 5000b 会把整章切成一块
    $paragraphs = range(1, 1000);
    $lengths = array_fill_keys($paragraphs, 1);

    expect(sliceParagraphs($paragraphs, $lengths, 5000, 'byte', 0))->toBe(range(1, 200))
        ->and(sliceParagraphs($paragraphs, $lengths, 5000, 'byte', 200))->toBe(range(201, 400))
        ->and(sliceParagraphs($paragraphs, $lengths, 5000, 'byte', 900))->toBe(range(901, 1000));
});

it('caps a paragraph-mode block at 200 paragraphs', function () {
    $paragraphs = range(1, 1000);

    expect(sliceParagraphs($paragraphs, [], 1000, 'para', 0))->toBe(range(1, 200))
        ->and(sliceParagraphs($paragraphs, [], 1000, 'para', 950))->toBe(range(951, 1000));
});

it('still breaks on the byte limit when paragraphs are long enough', function () {
    $paragraphs = range(1, 10);
    $lengths = array_fill_keys($paragraphs, 100);

    expect(sliceParagraphs($paragraphs, $lengths, 250, 'byte', 0))->toBe([1, 2, 3])
        ->and(sliceParagraphs($paragraphs, $lengths, 250, 'byte', 3))->toBe([4, 5, 6]);
});

it('never returns an empty byte-mode block when lengths are missing', function () {
    // pali_texts 里查不到长度时按 0 算，靠段落数上限收口，不会死循环也不会空块
    $paragraphs = range(1, 1000);

    expect(sliceParagraphs($paragraphs, [], 5000, 'byte', 0))->toBe(range(1, 200))
        ->and(sliceParagraphs($paragraphs, [], 5000, 'byte', 800))->toBe(range(801, 1000));
});
