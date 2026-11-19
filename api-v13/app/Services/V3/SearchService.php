<?php

namespace App\Services\V3;

use App\Exceptions\BusinessException;
use App\Http\Requests\V3\IndexSearchRequest;
use App\Services\OpenSearchService;
use Closure;
use OpenSearch\Exception\NotFoundHttpException;
use OpenSearch\Exception\RequestTimeoutHttpException;
use OpenSearch\Exception\ServiceUnavailableHttpException;
use OpenSearch\Exception\TooManyRequestsHttpException;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * v3 全文检索：把 OpenSearch 的原始命中归一化成对外的扁平结构。
 *
 * 查询 DSL 仍由共享的 OpenSearchService 构造（Library 检索页与 AITermService 也在用），
 * 这里只负责入参映射、异常分类和输出形状——不暴露 `_source` / `hits.hits` 这类 OpenSearch 内部结构。
 *
 * 异常分两类：
 * - 搜索服务不可用（连不上、超时、集群过载）→ 503 `search-unavailable`，文案送达客户端，原异常写日志；
 * - 其余（索引映射不对、DSL 写错）是真正的 bug → 原样抛出，兜底处理器回 500 并记日志。
 */
class SearchService
{
    /** 整章正文文档的 id：tipitaka_chapter_{book}-{para}_{channel uuid}，见 IndexTipitaka */
    private const CHAPTER_DOC = '/^tipitaka_chapter_\d+-\d+_([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})$/i';

    public function __construct(
        private readonly OpenSearchService $search,
        private readonly ChannelAbilityService $abilities,
    ) {}

    /**
     * @param  array<string, mixed>  $input  IndexSearchRequest::validated()
     * @return array{
     *     items: list<array<string, mixed>>,
     *     meta: array{current_page: int, per_page: int, total: int, total_relation: string, last_page: int, facets?: array<string, list<array{key: string, count: int}>>}
     * }
     */
    public function search(array $input): array
    {
        $page = (int) ($input['page'] ?? 1);
        $perPage = (int) ($input['per_page'] ?? 20);
        $withFacets = in_array($input['facets'] ?? 'false', ['true', '1'], true);

        $raw = $this->call(fn () => $this->search->search([
            'query' => $input['q'],
            'searchMode' => $input['search_mode'] ?? 'fuzzy',
            'page' => $page,
            'pageSize' => $perPage,
            'resourceType' => $input['resource_type'] ?? null,
            'resourceId' => $input['resource_id'] ?? null,
            'granularity' => $input['granularity'] ?? null,
            'language' => $input['language'] ?? null,
            'category' => self::splitList($input['category'] ?? null) ?: null,
            'tags' => self::splitList($input['tags'] ?? null),
            'pageRefs' => array_values($input['page_refs'] ?? []),
            'relatedId' => array_values($input['related_id'] ?? []) ?: null,
            'author' => $input['author'] ?? null,
            'channel' => $input['channel'] ?? null,
            'aggregations' => $withFacets,
        ]));

        $total = (int) ($raw['hits']['total']['value'] ?? 0);
        $meta = [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            // OpenSearch 默认只精确计数到 10000，gte 表示「至少这么多」
            'total_relation' => (string) ($raw['hits']['total']['relation'] ?? 'eq'),
            'last_page' => max(1, min(
                (int) ceil($total / $perPage),
                intdiv(IndexSearchRequest::MAX_WINDOW, $perPage),
            )),
        ];
        if ($withFacets) {
            $meta['facets'] = self::facets($raw['aggregations'] ?? []);
        }

        return [
            'items' => array_map(self::hit(...), $raw['hits']['hits'] ?? []),
            'meta' => $meta,
        ];
    }

    /**
     * 按文档 id 取一条（带阅读用的 display HTML）。文档不存在 → 404。
     *
     * 整章正文文档按它所属 channel 判权限：索引里收了所有 channel，不在这里挡的话
     * 拼出 id 就能读到私有正文。私有 channel 看不见 → 403。
     *
     * @return array<string, mixed>
     */
    public function find(string $id, ?string $viewerUid): array
    {
        if (preg_match(self::CHAPTER_DOC, $id, $m)) {
            $this->abilities->assertReadable($m[1], $viewerUid);
        }

        try {
            $raw = $this->call(fn () => $this->search->get($id));
        } catch (NotFoundHttpException) {
            abort(404, __('site.not_found'));
        }

        return self::hit($raw);
    }

    /**
     * 调 OpenSearch，把「服务不可用」类异常转成 503。
     *
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    private function call(Closure $callback): mixed
    {
        try {
            return $callback();
        } catch (ClientExceptionInterface|ServiceUnavailableHttpException|RequestTimeoutHttpException|TooManyRequestsHttpException $e) {
            report($e);

            throw new BusinessException(__('site.search_unavailable'), 503, 'search-unavailable');
        }
    }

    /**
     * 一条 OpenSearch 命中（search 的 hit 或 get 的文档）→ 对外结构。
     *
     * 一条文档通常只有一种语言有值，title / content 把 text.* 下的非空值拼起来即可。
     * 高亮优先取正文片段，没有再取标题。
     *
     * @param  array<string, mixed>  $hit
     * @return array<string, mixed>
     */
    private static function hit(array $hit): array
    {
        $source = $hit['_source'] ?? [];
        $highlight = $hit['highlight'] ?? [];
        $fragment = null;
        foreach (['content.text.pali', 'content.text.zh', 'summary.text', 'title.text.pali', 'title.text.zh'] as $field) {
            if (! empty($highlight[$field][0])) {
                $fragment = (string) $highlight[$field][0];
                break;
            }
        }

        $bold = $source['bold_single'] ?? [];
        $bold = is_array($bold)
            ? array_values(array_filter(array_map('strval', $bold), fn (string $v) => trim($v) !== ''))
            : preg_split('/\s+/u', trim((string) $bold), -1, PREG_SPLIT_NO_EMPTY);

        $item = [
            'id' => (string) ($source['id'] ?? $hit['_id'] ?? ''),
            'resource_type' => (string) ($source['resource_type'] ?? ''),
            'resource_id' => isset($source['resource_id']) ? (string) $source['resource_id'] : null,
            'granularity' => isset($source['granularity']) ? (string) $source['granularity'] : null,
            'language' => isset($source['language']) ? (string) $source['language'] : null,
            'related_id' => isset($source['related_id']) ? (string) $source['related_id'] : null,
            'score' => isset($hit['_score']) ? (float) $hit['_score'] : null,
            'title' => self::joinText($source['title']['text'] ?? []),
            'content' => self::joinText($source['content']['text'] ?? []),
            'highlight' => $fragment,
            'bold' => $bold,
            'path' => isset($source['path']) && $source['path'] !== '' ? (string) $source['path'] : null,
            'category' => array_values((array) ($source['category'] ?? [])),
            'tags' => array_values((array) ($source['tags'] ?? [])),
            'updated_at' => isset($source['updated_at']) ? (string) $source['updated_at'] : null,
        ];

        if (isset($source['content']['display'])) {
            $item['display'] = (string) $source['content']['display'];
        }

        return $item;
    }

    /**
     * @param  mixed  $text  title.text / content.text（按语言分的对象）
     */
    private static function joinText(mixed $text): string
    {
        return implode('', array_filter(
            is_array($text) ? array_values($text) : [],
            fn ($v) => is_string($v) && $v !== '',
        ));
    }

    /**
     * @param  array<string, mixed>  $aggregations
     * @return array<string, list<array{key: string, count: int}>>
     */
    private static function facets(array $aggregations): array
    {
        $facets = [];
        foreach ($aggregations as $name => $agg) {
            $facets[$name] = array_map(fn (array $bucket) => [
                'key' => (string) $bucket['key'],
                'count' => (int) $bucket['doc_count'],
            ], $agg['buckets'] ?? []);
        }

        return $facets;
    }

    /**
     * 逗号分隔的列表（category / tags），去空白、去空项。
     *
     * @return list<string>
     */
    private static function splitList(?string $value): array
    {
        if ($value === null) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value)), fn (string $v) => $v !== ''));
    }
}
