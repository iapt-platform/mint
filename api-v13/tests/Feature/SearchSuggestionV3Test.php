<?php

use App\Services\OpenSearchService;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request as PsrRequest;

/*
 * /v3/search-suggestions（搜索框前缀补全）。
 *
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。OpenSearch 一律 mock。
 */

/** OpenSearchService::suggest 的一条原始结果 */
function rawSuggestion(string $text, string $field, float $score, string $docId = 'doc'): array
{
    return [
        'text' => $text, 'source' => $field, 'score' => $score, 'doc_id' => $docId,
        'doc_source' => ['resource_type' => 'tipitaka', 'language' => 'pi', 'granularity' => 'paragraph', 'category' => ['sutta']],
    ];
}

/** 让 suggest 返回给定结果，收到的参数记进 $captured */
function fakeSuggestions(array $rows, ?array &$captured = null): void
{
    test()->mock(OpenSearchService::class)
        ->shouldReceive('suggest')
        ->andReturnUsing(function (...$args) use ($rows, &$captured) {
            $captured = $args;

            return $rows;
        });
}

it('merges fields, drops duplicate words and caps the total', function () {
    $captured = null;
    fakeSuggestions([
        rawSuggestion('dukkhasutta', 'title_pali', 3.0, 'a'),
        rawSuggestion('Dukkhasutta', 'content_pali', 2.5, 'b'),   // 同一个词，分数低的丢掉
        rawSuggestion('dukkha', 'content_pali', 2.0, 'c'),
        rawSuggestion('dukkhatā', 'content_pali', 1.0, 'd'),
    ], $captured);

    $res = $this->getJson('/api/v3/search-suggestions?q=dukkha&limit=2')->assertSuccessful();

    expect($res->json('data'))->toHaveCount(2)
        ->and($res->json('data.0'))->toBe([
            'text' => 'dukkhasutta', 'source' => 'title_pali', 'score' => 3, 'doc_id' => 'a',
            'resource_type' => 'tipitaka', 'language' => 'pi', 'granularity' => 'paragraph', 'category' => ['sutta'],
        ])
        ->and($res->json('data.1.text'))->toBe('dukkha')
        ->and($res->json('meta.total'))->toBe(2);
    // 默认查全部四个字段，每个字段取满 limit 再统一截断
    expect($captured)->toBe(['dukkha', ['title_pali', 'title_zh', 'content_pali', 'content_zh'], null, 2]);
});

it('passes only the requested fields', function () {
    $captured = null;
    fakeSuggestions([], $captured);

    $this->getJson('/api/v3/search-suggestions?q=nibb&fields=title_pali, content_pali')
        ->assertSuccessful()
        ->assertJsonPath('data', [])
        ->assertJsonPath('meta.total', 0);
    expect($captured[1])->toBe(['title_pali', 'content_pali'])
        ->and($captured[3])->toBe(10);
});

it('rejects invalid parameters', function (array $query, string $field) {
    $this->getJson('/api/v3/search-suggestions?'.http_build_query($query))
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field)
        ->assertHeader('Content-Type', 'application/problem+json');
})->with([
    'missing q' => [[], 'q'],
    'empty q' => [['q' => ''], 'q'],
    'unknown field' => [['q' => 'd', 'fields' => 'title'], 'fields'],
    'only commas' => [['q' => 'd', 'fields' => ',,'], 'fields'],
    'limit too large' => [['q' => 'd', 'limit' => 51], 'limit'],
]);

it('answers 503 when the search cluster is unreachable', function () {
    test()->mock(OpenSearchService::class)
        ->shouldReceive('suggest')
        ->andThrow(new ConnectException('Connection refused', new PsrRequest('POST', 'http://opensearch')));

    $res = $this->getJson('/api/v3/search-suggestions?q=dukkha')
        ->assertServiceUnavailable()
        ->assertJsonPath('type', 'urn:problem:search-unavailable');
    expect($res->getContent())->not->toContain('Connection refused');
});

it('no longer serves the old search-suggest path', function () {
    $this->getJson('/api/v3/search-suggest?q=dukkha')->assertNotFound();
});
