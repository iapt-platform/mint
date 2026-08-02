<?php

use App\Console\Commands\ExportPaliSynonyms;

/**
 * 调用 ExportPaliSynonyms 的私有方法 filterSynonymTerms
 *
 * @param  array<int, string>  $terms
 * @return array<int, string>
 */
function filterSynonymTerms(array $terms): array
{
    $method = new ReflectionMethod(ExportPaliSynonyms::class, 'filterSynonymTerms');

    return $method->invoke(new ExportPaliSynonyms, $terms);
}

test('保留含字母或数字的 term', function () {
    expect(filterSynonymTerms(['buddhavacana', 'buddhavacanaṃ', '心一境性', 'သမာဓိ', 'samādhi2']))
        ->toBe(['buddhavacana', 'buddhavacanaṃ', '心一境性', 'သမာဓိ', 'samādhi2']);
});

test('丢弃会被 analyzer 完全消除的纯符号 term', function (string $term) {
    expect(filterSynonymTerms(['khārika', $term]))->toBe(['khārika']);
})->with([
    '圈码数字' => '②',
    '破折号' => '——',
    '半个括号' => '?)',
    '注释符号' => '(#=‹)',
    '空字符串' => '',
    '纯空白' => "  \t ",
]);

test('丢弃带词典源码标记的脏词条', function (string $term) {
    expect(filterSynonymTerms([$term, 'ceteti']))->toBe(['ceteti']);
})->with([
    '# 开头会让整行变成注释' => '#=cetayati)',
    '嵌套源码标记' => '(#=)(‹paññāpeti)',
    '词源标记' => '(‹akiñcana无任何)',
    '括号包裹的变格标注' => '(ku的离格)',
    '方括号包裹的词根' => '[ava-hīḷanā＜hīḍ]',
    '半角尖括号' => '<upadheyya>',
]);

test('保留词中带括号的正常释义', function (string $term) {
    expect(filterSynonymTerms(['arahant', $term]))->toBe(['arahant', $term]);
})->with([
    '中文补注' => '阿拉汉[果]',
    '巴利变格补注' => 'bhesajja[ṃ]',
    '中文夹注' => '互不分离的(俱生相应)法',
]);

test('去重并保持原顺序', function () {
    expect(filterSynonymTerms(['eka', 'eko', 'eka', 'ekaṃ', 'eko']))
        ->toBe(['eka', 'eko', 'ekaṃ']);
});

test('term 首尾空白被去掉', function () {
    expect(filterSynonymTerms([' sati ', "jetavana\t"]))->toBe(['sati', 'jetavana']);
});

test('保留含空格的多词 term', function () {
    expect(filterSynonymTerms(['jetavana', 'jetavana monastery']))
        ->toBe(['jetavana', 'jetavana monastery']);
});
