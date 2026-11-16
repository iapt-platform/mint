<?php

use App\Facades\RedisCache;
use App\Services\OpenSearchService;

/**
 * 调用 OpenSearchService 的 protected 查询构造方法（纯函数，不连 OpenSearch）
 */
function buildSearchQuery(string $method, mixed ...$args): array
{
    return callSearchService($method, ...$args);
}

/**
 * 调用 OpenSearchService 的 protected 方法，返回值不限类型
 */
function callSearchService(string $method, mixed ...$args): mixed
{
    $service = new OpenSearchService;

    return (fn () => $this->{$method}(...$args))->call($service);
}

/**
 * 取出 bool.should 里所有 constant_score 的固定分
 *
 * @return list<float>
 */
function constantScoreBoosts(array $query): array
{
    return array_values(array_map(
        fn (array $clause): float => $clause['constant_score']['boost'],
        array_filter($query['bool']['should'], fn (array $clause): bool => isset($clause['constant_score'])),
    ));
}

it('scores pali fuzzy per word, rewarding word order and exact spelling', function () {
    $query = buildSearchQuery('buildPaliFuzzyQuery', 'evaṃ me sutaṃ');

    // 整句 20 + 相邻两对各 5 + 每词拼写 10、同义 2
    expect(constantScoreBoosts($query))->toEqual([20.0, 5.0, 5.0, 10.0, 2.0, 10.0, 2.0, 10.0, 2.0]);

    $phrase = $query['bool']['should'][0]['constant_score']['filter']['multi_match'];
    expect($phrase['type'])->toBe('phrase')
        ->and($phrase['query'])->toBe('evaṃ me sutaṃ')
        ->and($phrase['fields'])->toBe(['title.text.pali.exact', 'content.text.pali.exact']);

    expect($query['bool'])->not->toHaveKey('minimum_should_match');
});

it('drops punctuation-only words so they cannot block matches', function () {
    $query = buildSearchQuery('buildPaliFuzzyQuery', 'evaṃ – me .');

    $phrase = $query['bool']['should'][0]['constant_score']['filter']['multi_match'];
    expect($phrase['query'])->toBe('evaṃ me')
        ->and(constantScoreBoosts($query))->toEqual([20.0, 10.0, 2.0, 10.0, 2.0]);
});

it('has no order clauses for a single pali word', function () {
    $query = buildSearchQuery('buildPaliFuzzyQuery', 'vitakko');

    expect(constantScoreBoosts($query))->toEqual([10.0, 2.0]);
});

it('ranks definition by each matched single bold word, any word form', function () {
    $query = buildSearchQuery('buildDefinitionQuery', 'vitakko vicāro');
    $should = $query['bool']['should'];

    // 每词：单独黑体任一词形 100 + 原拼写 10、多词黑体任一词形 30 + 原拼写 5；再加整句在多词黑体里 30
    expect(constantScoreBoosts($query))->toEqual([100.0, 10.0, 30.0, 5.0, 100.0, 10.0, 30.0, 5.0, 30.0]);

    expect($should[0]['constant_score']['filter']['match_phrase']['bold_single'])->toBe('vitakko');
    expect($should[1]['constant_score']['filter']['match_phrase']['bold_single'])
        ->toBe(['query' => 'vitakko', 'analyzer' => 'standard']);
    expect($should[2]['constant_score']['filter']['match_phrase']['bold_multi'])->toBe('vitakko');

    // 正文部分整体缩小到 0.1，压不过一个黑体字
    $text = end($should);
    expect(constantScoreBoosts($text))->toEqual([2.0, 1.0, 0.2, 1.0, 0.2]);
});

it('routes cjk definition text to chinese fields', function () {
    $query = buildSearchQuery('buildDefinitionQuery', '寻');
    $text = end($query['bool']['should']);

    expect($text['multi_match']['fields'])->toBe(['title.text.zh^3', 'content.text.zh']);
});

it('keeps pali stopwords in word-order clauses only', function () {
    $query = buildSearchQuery('buildPaliFuzzyQuery', 'evaṃ ca kho');
    $should = $query['bool']['should'];

    // 整句 20 + 两对相邻词各 5 + 只有 evaṃ、kho 两个词单独计分
    expect(constantScoreBoosts($query))->toEqual([20.0, 5.0, 5.0, 10.0, 2.0, 10.0, 2.0]);
    expect($should[0]['constant_score']['filter']['multi_match']['query'])->toBe('evaṃ ca kho');
    expect(end($should)['match']['content.text.pali']['query'])->toBe('evaṃ kho');
});

it('skips adjacent pairs made only of stopwords', function () {
    $query = buildSearchQuery('buildPaliFuzzyQuery', 'na ca kho');

    // 「na ca」那对跳过，只剩「ca kho」
    expect(constantScoreBoosts($query))->toEqual([20.0, 5.0, 10.0, 2.0]);
});

it('treats an all-stopword query as normal words', function () {
    $query = buildSearchQuery('buildPaliFuzzyQuery', 'Eva');

    expect(constantScoreBoosts($query))->toEqual([10.0, 2.0]);
});

it('ignores stopwords for definition bold scoring', function () {
    $query = buildSearchQuery('buildDefinitionQuery', 'vitakko ca');

    // 只有 vitakko 四条 + 整句在多词黑体里 30
    expect(constantScoreBoosts($query))->toEqual([100.0, 10.0, 30.0, 5.0, 30.0]);
});

/**
 * 预置文档总数与各词的文档频率，让 wordRarityWeights 不用连 OpenSearch
 *
 * @param  array<string, int>  $frequencies  原始词 => 文档数
 */
function seedDocumentFrequencies(int $total, array $frequencies): void
{
    $index = config('mint.opensearch.index');
    RedisCache::put("/opensearch/{$index}/doc-count", $total);
    foreach ($frequencies as $word => $df) {
        RedisCache::put(callSearchService('documentFrequencyCacheKey', $index, $word), $df);
    }
}

it('weights rarer words higher and floors very common ones', function () {
    seedDocumentFrequencies(550000, ['rare' => 10, 'mid' => 100, 'common' => 200000]);

    $weights = buildSearchQuery('wordRarityWeights', ['rare', 'mid', 'common']);

    expect($weights['rare'])->toBe(1.0)
        ->and($weights['mid'])->toBeLessThan(1.0)->toBeGreaterThan(0.2)
        ->and($weights['common'])->toBe(0.2);
});

it('does not weight a single word', function () {

    expect(buildSearchQuery('wordRarityWeights', ['vitakko']))->toBe(['vitakko' => 1.0]);
});

it('multiplies per-word clauses by rarity but not word-order clauses', function () {
    $query = buildSearchQuery('buildPaliFuzzyQuery', 'evaṃ me sutaṃ', 1.0, ['evaṃ' => 0.5, 'me' => 1.0, 'sutaṃ' => 0.25]);

    // 整句 20 与相邻词对 5 不乘；拼写 10、同义 2 分别乘 0.5 / 1 / 0.25
    expect(constantScoreBoosts($query))->toEqual([20.0, 5.0, 5.0, 5.0, 1.0, 10.0, 2.0, 2.5, 0.5]);
});
