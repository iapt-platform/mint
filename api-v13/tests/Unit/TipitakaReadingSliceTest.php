<?php

use App\Services\V3\TipitakaReadingService;

/**
 * sliceByBytes() 是纯函数，不碰数据库。段落数上限要造上千段才看得出来，走 HTTP
 * 会真的去渲染每一段，所以这里直接对着方法本身测。
 *
 * 逻辑在 Service 里，所以不必再为了拿到它去造控制器的匿名子类——
 * 这正是「业务逻辑一律进 Service」买到的好处之一。
 *
 * @param  array<int, int>  $paras  段落号，统一当作 book 9000
 * @param  array<int, int>  $lengths  段落号 => 字节数
 * @return array<int, int> 切出来的段落号
 */
function sliceParagraphs(array $paras, array $lengths, int $limit): array
{
    $candidates = array_map(fn (int $para) => ['book' => 9000, 'para' => $para], $paras);
    $slice = app(TipitakaReadingService::class)->sliceByBytes($candidates, [9000 => $lengths], $limit);

    return array_column($slice, 'para');
}

it('caps a byte-mode block at 200 paragraphs however short the paragraphs are', function () {
    // 1000 段，每段 1 字节；不设段落上限的话 5000b 会把整章切成一块
    $paras = range(1, 1000);
    $lengths = array_fill_keys($paras, 1);

    expect(sliceParagraphs($paras, $lengths, 5000))->toBe(range(1, 200))
        ->and(sliceParagraphs(range(201, 1000), $lengths, 5000))->toBe(range(201, 400))
        ->and(sliceParagraphs(range(901, 1000), $lengths, 5000))->toBe(range(901, 1000));
});

it('still breaks on the byte limit when paragraphs are long enough', function () {
    $paras = range(1, 10);
    $lengths = array_fill_keys($paras, 100);

    expect(sliceParagraphs($paras, $lengths, 250))->toBe([1, 2, 3])
        ->and(sliceParagraphs(range(4, 10), $lengths, 250))->toBe([4, 5, 6]);
});

it('never returns an empty byte-mode block when lengths are missing', function () {
    // pali_texts 里查不到长度时按 0 算，靠段落数上限收口，不会死循环也不会空块
    expect(sliceParagraphs(range(1, 1000), [], 5000))->toBe(range(1, 200))
        ->and(sliceParagraphs(range(801, 1000), [], 5000))->toBe(range(801, 1000));
});

it('returns an empty block for no candidates', function () {
    expect(sliceParagraphs([], [], 5000))->toBe([]);
});
