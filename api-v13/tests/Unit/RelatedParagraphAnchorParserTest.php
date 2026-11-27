<?php

use App\Services\RelatedParagraph\AnchorParser;

/*
 * 锚点写法全表（documents/related-paragraphs-rebuild.md §3.1）每一行一条用例。
 */

it('parses every para anchor form found in wbw_templates', function (string $word, int $begin, int $end, ?string $suffix, bool $warns) {
    $parsed = AnchorParser::parse($word);

    expect($parsed['kind'])->toBe(AnchorParser::KIND_PARA)
        ->and($parsed['begin'])->toBe($begin)
        ->and($parsed['end'])->toBe($end)
        ->and($parsed['suffix'])->toBe($suffix)
        ->and($parsed['warning'] !== null)->toBe($warns);
})->with([
    '单号' => ['para5', 5, 5, null, false],
    '单号带后缀' => ['para5_mn1', 5, 5, 'mn1', false],
    '单号 0' => ['para0_an10', 0, 0, 'an10', false],
    '前导 0' => ['para01_mn1', 1, 1, 'mn1', true],
    '完整区间' => ['para23-25', 23, 25, null, false],
    '完整区间带后缀' => ['para156-166_an10', 156, 166, 'an10', false],
    '跨位数区间' => ['para308-1151_an5', 308, 1151, 'an5', false],
    '缩写区间' => ['para292-3', 292, 293, null, false],
    '缩写区间带后缀' => ['para380-1_mn1', 380, 381, 'mn1', false],
    '缩写区间多位' => ['para1008-9_sn5', 1008, 1009, 'sn5', false],
    '链式' => ['para18-19-20', 18, 20, null, false],
    '链式缩写' => ['para292-3-6', 292, 296, null, false],
    '倒序笔误' => ['para504-407_dn1', 504, 507, 'dn1', true],
    '倒序笔误 2' => ['para706-608', 706, 708, null, true],
    '截断' => ['para179-', 179, 179, null, true],
]);

it('carries when an abbreviated end would not exceed the start', function () {
    expect(AnchorParser::completePrefix(298, '3'))->toBe(303);
});

it('parses chapter, preface, book and page markers', function () {
    expect(AnchorParser::parse('dn1_3'))->toMatchArray(['kind' => AnchorParser::KIND_CHAPTER, 'name' => 'dn1', 'preface' => false])
        ->and(AnchorParser::parse('an5_1_1'))->toMatchArray(['kind' => AnchorParser::KIND_CHAPTER, 'name' => 'an5', 'preface' => false])
        ->and(AnchorParser::parse('mn1_0'))->toMatchArray(['kind' => AnchorParser::KIND_CHAPTER, 'name' => 'mn1', 'preface' => true])
        ->and(AnchorParser::parse('kn14b_17'))->toMatchArray(['kind' => AnchorParser::KIND_CHAPTER, 'name' => 'kn14'])
        ->and(AnchorParser::parse('kn11'))->toMatchArray(['kind' => AnchorParser::KIND_BOOK, 'name' => 'kn11'])
        ->and(AnchorParser::parse('P1.0023'))->toMatchArray(['kind' => AnchorParser::KIND_PAGE])
        ->and(AnchorParser::parse('M0.0001'))->toMatchArray(['kind' => AnchorParser::KIND_PAGE]);
});

it('rejects forms outside the table instead of skipping them', function (string $word) {
    AnchorParser::parse($word);
})->throws(InvalidArgumentException::class)->with(['para', 'para5,7', 'paraX', 'foo']);
