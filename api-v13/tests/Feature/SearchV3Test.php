<?php

use App\Services\OpenSearchService;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request as PsrRequest;
use OpenSearch\Exception\BadRequestHttpException;
use OpenSearch\Exception\NotFoundHttpException;

/*
 * /v3/search 与 /v3/search/{id}（OpenSearch 全文检索）。
 *
 * 这里断言的是形状而非契约（spectator 未安装，见 v3-resource skill 第 3 步）。
 * OpenSearch 一律 mock，不连真实集群；整章文档的 channel 权限见 ChannelContentAccessV3Test。
 */

/** 一条 OpenSearch 原始命中 */
function rawSearchHit(array $source = [], array $highlight = []): array
{
    return [
        '_id' => $source['id'] ?? 'tipitaka_paragraph_pi_26-392',
        '_score' => 12.5,
        '_source' => $source + [
            'id' => 'tipitaka_paragraph_pi_26-392',
            'resource_id' => '3bfe314f-da34-11ec-9f5a-076a934e828a',
            'resource_type' => 'tipitaka',
            'granularity' => 'paragraph',
            'language' => 'pi',
            'related_id' => '26-392',
            'title' => ['text' => ['pali' => '95.']],
            'content' => ['text' => ['pali' => 'Dukkhāti dukkha saṃkiṇṇo']],
            'bold_single' => 'dukkha  saṃkiṇṇo',
            'path' => 'a/b/c',
            'category' => ['añña', 'dīpanī'],
            'updated_at' => '2026-11-16T07:39:25+00:00',
        ],
        'highlight' => $highlight,
    ];
}

/** 让 OpenSearchService::search 返回给定命中，并把收到的参数记进 $captured */
function fakeSearchResult(array $hits, int $total, ?array &$captured = null, array $aggregations = []): void
{
    test()->mock(OpenSearchService::class)
        ->shouldReceive('search')
        ->andReturnUsing(function (array $params) use ($hits, $total, &$captured, $aggregations) {
            $captured = $params;

            return [
                'hits' => ['total' => ['value' => $total, 'relation' => 'eq'], 'hits' => $hits],
                'aggregations' => $aggregations,
            ];
        });
}

it('returns normalized hits with pagination meta', function () {
    fakeSearchResult([rawSearchHit([], [
        'title.text.pali' => ['<mark>95</mark>.'],
        'content.text.pali' => ['<mark>Dukkhāti</mark> dukkha'],
    ])], 45);

    $res = $this->getJson('/api/v3/search?q=dukkha&per_page=20&page=2')->assertSuccessful();

    $hit = $res->json('data.0');
    expect($hit)->toHaveKeys(['id', 'resource_type', 'resource_id', 'granularity', 'language', 'related_id',
        'score', 'title', 'content', 'highlight', 'bold', 'path', 'category', 'tags', 'updated_at'])
        ->and($hit)->not->toHaveKeys(['_source', 'display'])
        ->and($hit['related_id'])->toBe('26-392')
        ->and($hit['highlight'])->toBe('<mark>Dukkhāti</mark> dukkha')   // 正文片段优先于标题
        ->and($hit['bold'])->toBe(['dukkha', 'saṃkiṇṇo']);
    expect($res->json('meta'))->toMatchArray([
        'current_page' => 2, 'per_page' => 20, 'total' => 45, 'total_relation' => 'eq', 'last_page' => 3,
    ])->not->toHaveKey('facets');
});

it('maps filters onto the search service and skips aggregations by default', function () {
    $captured = null;
    fakeSearchResult([], 0, $captured);

    $this->getJson('/api/v3/search?'.http_build_query([
        'q' => 'dukkha', 'search_mode' => 'definition', 'resource_type' => 'tipitaka',
        'granularity' => 'paragraph', 'category' => 'sutta, mūla', 'tags' => 'a,b',
        'related_id' => ['93-5', '93-6'], 'page_refs' => ['V3.81'],
        'channel' => '9d389085-197a-47b7-87bd-d214994c2784',
    ]))->assertSuccessful();

    expect($captured)->toMatchArray([
        'query' => 'dukkha', 'searchMode' => 'definition', 'resourceType' => 'tipitaka',
        'granularity' => 'paragraph', 'category' => ['sutta', 'mūla'], 'tags' => ['a', 'b'],
        'relatedId' => ['93-5', '93-6'], 'pageRefs' => ['V3.81'],
        'channel' => '9d389085-197a-47b7-87bd-d214994c2784', 'aggregations' => false,
        'page' => 1, 'pageSize' => 20,
    ]);
});

it('returns facets only when asked', function () {
    $captured = null;
    fakeSearchResult([], 0, $captured, [
        'language' => ['buckets' => [['key' => 'pi', 'doc_count' => 7]]],
    ]);

    $this->getJson('/api/v3/search?q=dukkha&facets=true')
        ->assertSuccessful()
        ->assertJsonPath('meta.facets.language.0', ['key' => 'pi', 'count' => 7]);
    expect($captured['aggregations'])->toBeTrue();
});

it('accepts the same parameters in a POST body', function () {
    $captured = null;
    fakeSearchResult([rawSearchHit()], 1, $captured);

    $this->postJson('/api/v3/search', ['q' => 'dukkha', 'facets' => true, 'related_id' => ['26-392']])
        ->assertSuccessful()
        ->assertJsonPath('data.0.related_id', '26-392');
    expect($captured['relatedId'])->toBe(['26-392'])
        ->and($captured['aggregations'])->toBeTrue();
});

it('rejects invalid parameters', function (array $query, string $field) {
    $this->getJson('/api/v3/search?'.http_build_query($query))
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field)
        ->assertHeader('Content-Type', 'application/problem+json');
})->with([
    'missing q' => [[], 'q'],
    'unknown mode' => [['q' => 'x', 'search_mode' => 'bogus'], 'search_mode'],
    'unknown resource type' => [['q' => 'x', 'resource_type' => 'article'], 'resource_type'],
    'per_page too large' => [['q' => 'x', 'per_page' => 101], 'per_page'],
    'too deep' => [['q' => 'x', 'per_page' => 100, 'page' => 101], 'page'],
    'channel not uuid' => [['q' => 'x', 'channel' => 'abc'], 'channel'],
]);

it('answers 503 with a readable detail when the search cluster is unreachable', function () {
    test()->mock(OpenSearchService::class)
        ->shouldReceive('search')
        ->andThrow(new ConnectException('Connection refused', new PsrRequest('POST', 'http://opensearch')));

    $res = $this->getJson('/api/v3/search?q=dukkha', ['Accept-Language' => 'zh-Hans'])
        ->assertServiceUnavailable()
        ->assertJsonPath('type', 'urn:problem:search-unavailable');
    expect($res->json('detail'))->not->toBe('site.search_unavailable')
        ->and($res->getContent())->not->toContain('Connection refused');
});

it('does not leak OpenSearch errors that are real bugs', function () {
    test()->mock(OpenSearchService::class)
        ->shouldReceive('search')
        ->andThrow(new BadRequestHttpException('illegal_argument_exception: Text fields are not optimised'));
    config(['app.debug' => false]);

    $res = $this->getJson('/api/v3/search?q=dukkha')->assertInternalServerError();
    expect($res->getContent())->not->toContain('illegal_argument_exception');
});

it('shows one document with its display html', function () {
    test()->mock(OpenSearchService::class)
        ->shouldReceive('get')
        ->with('term_x')
        ->andReturn(rawSearchHit([
            'id' => 'term_x', 'resource_type' => 'term', 'related_id' => null,
            'bold_single' => ['苦', 'dukkha'],
            'content' => ['text' => ['zh' => '苦'], 'display' => '<p>苦</p>'],
        ]));

    $this->getJson('/api/v3/search/term_x')
        ->assertSuccessful()
        ->assertJsonPath('data.id', 'term_x')
        ->assertJsonPath('data.display', '<p>苦</p>')
        ->assertJsonPath('data.bold', ['苦', 'dukkha'])
        ->assertJsonPath('data.related_id', null);
});

it('answers 404 only when the document does not exist', function () {
    test()->mock(OpenSearchService::class)
        ->shouldReceive('get')
        ->andThrow(new NotFoundHttpException('not found'));

    $this->getJson('/api/v3/search/term_missing')->assertNotFound();
});

it('answers 503 instead of 404 when the cluster is down while showing', function () {
    test()->mock(OpenSearchService::class)
        ->shouldReceive('get')
        ->andThrow(new ConnectException('Connection refused', new PsrRequest('GET', 'http://opensearch')));

    $this->getJson('/api/v3/search/term_x')->assertServiceUnavailable();
});

it('only registers GET and POST on the collection', function () {
    $this->putJson('/api/v3/search/term_x', [])->assertMethodNotAllowed();
});
