<?php

// api-v8/app/Services/OpenSearchService.php

namespace App\Services;

use OpenSearch\GuzzleClientFactory;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Exception;

class OpenSearchService
{
    protected $client;
    protected $http;
    protected $openaiApiKey;

    /**
     * 默认查询排除字段
     *
     * @var array
     */
    private $sourceExcludes = [
        'title.suggest.pali',
        'title.suggest.zh',
        'content.suggest.pali',
        'content.suggest.zh',
        'content.display',   // 新增，列表页不返回 HTML
    ];

    /**
     * 默认权重配置
     *
     * fuzzy / hybrid 两种模式各自的字段权重。
     * hybrid 额外包含 fuzzy_ratio / semantic_ratio 用于控制两路得分的混合比例。
     *
     * 字段名已按新映射结构调整：
     *   title.text.pali  →  原 title.pali.text
     *   title.text.zh    →  原 title.zh
     *   content.text.pali → 原 content.pali.text
     *   content.text.zh  → 原 content.zh
     *
     * @var array
     */
    private $weights = [
        'fuzzy' => [
            'bold_single'        => 50,
            'bold_multi'         => 10,
            'title.text.pali'    => 3,
            'title.text.zh'      => 3,
            'summary.text'       => 2,
            'content.text.pali'  => 1,
            'content.text.zh'    => 1,
        ],
        'hybrid' => [
            'fuzzy_ratio'        => 0.7,
            'semantic_ratio'     => 0.3,
            'bold_single'        => 50,
            'bold_multi'         => 10,
            'title.text.pali'    => 3,
            'title.text.zh'      => 3,
            'summary.text'       => 2,
            'content.text.pali'  => 1,
            'content.text.zh'    => 1,
        ],
    ];

    /**
     * OpenSearch 索引定义（settings + mappings）
     *
     * 字段结构说明：
     *
     * title
     *   ├── text
     *   │     ├── pali   (text)  模糊查询 + exact subfield 精确查询
     *   │     └── zh     (text)  中文分词查询
     *   ├── vector (knn_vector, dim=1536)
     *   └── suggest
     *         ├── pali   (completion)
     *         └── zh     (completion)
     *
     * content（结构与 title 一致，额外包含 tokens nested 字段）
     *   ├── text
     *   │     ├── pali   (text)
     *   │     └── zh     (text)
     *   ├── tokens (nested)
     *   ├── vector (knn_vector, dim=1536)
     *   └── suggest
     *         ├── pali   (completion)
     *         └── zh     (completion)
     *
     * summary（中文摘要，结构保持不变）
     *   ├── text   (text)
     *   └── vector (knn_vector, dim=1536)
     *
     * @var array
     */
    private $indexDefinition = [
        'settings' => [
            'index' => [
                'knn' => true,
            ],
            'analysis' => [
                'analyzer' => [
                    'pali_query_analyzer' => [
                        'tokenizer' => 'standard',
                        'filter'    => ['lowercase', 'pali_synonyms'],
                    ],
                    'pali_index_analyzer' => [
                        'type'        => 'custom',
                        'tokenizer'   => 'standard',
                        'char_filter' => ['markdown_strip'],
                        'filter'      => ['lowercase'],
                    ],
                    'markdown_clean' => [
                        'type'        => 'custom',
                        'tokenizer'   => 'standard',
                        'char_filter' => ['markdown_strip'],
                        'filter'      => ['lowercase'],
                    ],
                    // Suggest 专用（忽略大小写 + 变音）
                    'pali_suggest_analyzer' => [
                        'tokenizer' => 'standard',
                        'filter'    => ['lowercase', 'asciifolding'],
                    ],
                    'zh_suggest_analyzer' => [
                        'tokenizer'   => 'ik_max_word',
                        'char_filter' => ['tsconvert'],
                    ],
                    // 中文简繁统一 (繁 -> 简)
                    'zh_index_analyzer' => [
                        'tokenizer'   => 'ik_max_word',
                        'char_filter' => ['tsconvert'],
                    ],
                    'zh_query_analyzer' => [
                        'tokenizer'   => 'ik_smart',
                        'char_filter' => ['tsconvert'],
                    ],
                ],
                'filter' => [
                    'pali_synonyms' => [
                        'type'           => 'synonym_graph',
                        'synonyms_path'  => 'analysis/pali_synonyms.txt',
                        'updateable'     => true,
                    ],
                ],
                'char_filter' => [
                    'markdown_strip' => [
                        'type'        => 'pattern_replace',
                        'pattern'     => '\\*\\*|\\*|_|`|~',
                        'replacement' => '',
                    ],
                    'tsconvert' => [
                        'type'         => 'stconvert',
                        'convert_type' => 't2s',
                    ],
                ],
            ],
        ],
        'mappings' => [
            'properties' => [
                'id'            => ['type' => 'keyword'],
                'resource_id'   => ['type' => 'keyword'],
                'resource_type' => ['type' => 'keyword'],

                // ----------------------------------------------------------------
                // title
                //   text.pali  → 模糊查询（+ exact subfield 精确查询）
                //   text.zh    → 中文查询
                //   vector     → 语义向量
                //   suggest.pali / suggest.zh → 自动建议
                // ----------------------------------------------------------------
                'title' => [
                    'properties' => [
                        'text' => [
                            'properties' => [
                                'pali' => [
                                    'type'            => 'text',
                                    'analyzer'        => 'pali_index_analyzer',
                                    'search_analyzer' => 'pali_query_analyzer',
                                    'fields' => [
                                        'exact' => [
                                            'type'     => 'text',
                                            'analyzer' => 'markdown_clean',
                                        ],
                                    ],
                                ],
                                'zh' => [
                                    'type'            => 'text',
                                    'analyzer'        => 'zh_index_analyzer',
                                    'search_analyzer' => 'zh_query_analyzer',
                                ],
                            ],
                        ],
                        'vector' => [
                            'type'      => 'knn_vector',
                            'dimension' => 1536,
                            'method'    => [
                                'name'       => 'hnsw',
                                'space_type' => 'cosinesimil',
                                'engine'     => 'faiss',
                            ],
                        ],
                        'suggest' => [
                            'properties' => [
                                'pali' => [
                                    'type'     => 'completion',
                                    'analyzer' => 'pali_suggest_analyzer',
                                ],
                                'zh' => [
                                    'type'     => 'completion',
                                    'analyzer' => 'zh_suggest_analyzer',
                                ],
                            ],
                        ],
                    ],
                ],

                // ----------------------------------------------------------------
                // summary（LLM 生成的简体中文摘要，结构保持不变）
                //   text   → 中文查询
                //   vector → 语义向量
                // ----------------------------------------------------------------
                'summary' => [
                    'properties' => [
                        'text' => [
                            'type'            => 'text',
                            'analyzer'        => 'zh_index_analyzer',
                            'search_analyzer' => 'zh_query_analyzer',
                        ],
                        'vector' => [
                            'type'      => 'knn_vector',
                            'dimension' => 1536,
                            'method'    => [
                                'name'       => 'hnsw',
                                'space_type' => 'cosinesimil',
                                'engine'     => 'faiss',
                            ],
                        ],
                    ],
                ],

                // ----------------------------------------------------------------
                // content（结构与 title 对称，额外包含 tokens nested 字段）
                //   text.pali  → 模糊查询（+ exact subfield 精确查询）
                //   text.zh    → 中文查询
                //   tokens     → 词法分析结果（nested）
                //   vector     → 语义向量
                //   suggest.pali / suggest.zh → 自动建议
                // ----------------------------------------------------------------
                'content' => [
                    'properties' => [
                        'text' => [
                            'properties' => [
                                'pali' => [
                                    'type'            => 'text',
                                    'analyzer'        => 'pali_index_analyzer',
                                    'search_analyzer' => 'pali_query_analyzer',
                                    'fields' => [
                                        'exact' => [
                                            'type'     => 'text',
                                            'analyzer' => 'markdown_clean',
                                        ],
                                    ],
                                ],
                                'zh' => [
                                    'type'            => 'text',
                                    'analyzer'        => 'zh_index_analyzer',
                                    'search_analyzer' => 'zh_query_analyzer',
                                ],
                            ],
                        ],
                        'tokens' => [
                            'type'       => 'nested',
                            'properties' => [
                                'surface'        => ['type' => 'keyword'],
                                'lemma'          => ['type' => 'keyword'],
                                'compound_parts' => ['type' => 'keyword'],
                                'case'           => ['type' => 'keyword'],
                            ],
                        ],
                        'vector' => [
                            'type'      => 'knn_vector',
                            'dimension' => 1536,
                            'method'    => [
                                'name'       => 'hnsw',
                                'space_type' => 'cosinesimil',
                                'engine'     => 'faiss',
                            ],
                        ],
                        'suggest' => [
                            'properties' => [
                                'pali' => [
                                    'type'     => 'completion',
                                    'analyzer' => 'pali_suggest_analyzer',
                                ],
                                'zh' => [
                                    'type'     => 'completion',
                                    'analyzer' => 'zh_suggest_analyzer',
                                ],
                            ],
                        ],
                        // 前端展示用，原始 HTML，不参与索引
                        'display' => [
                            'type'         => 'text',
                            'index'        => false,
                        ],
                    ],
                ],

                'related_id'  => ['type' => 'keyword'],
                'bold_single' => [
                    'type'            => 'text',
                    'analyzer'        => 'standard',
                    'search_analyzer' => 'pali_query_analyzer',
                ],
                'bold_multi' => [
                    'type'            => 'text',
                    'analyzer'        => 'standard',
                    'search_analyzer' => 'pali_query_analyzer',
                ],
                'path'      => ['type' => 'text', 'analyzer' => 'standard'],
                'page_refs' => ['type' => 'keyword'],
                'tags'      => ['type' => 'keyword'],
                'category'  => ['type' => 'keyword'],
                'author'    => ['type' => 'text'],
                'language'  => ['type' => 'keyword'],
                'updated_at'  => ['type' => 'date'],
                'granularity' => ['type' => 'keyword'],
                'metadata' => [
                    'properties' => [
                        'APA'     => ['type' => 'text', 'index' => false],
                        'MLA'     => ['type' => 'text', 'index' => false],
                        'widget'  => ['type' => 'text', 'index' => false],
                        'author'  => ['type' => 'text'],
                        'channel' => ['type' => 'text'],
                    ],
                ],
            ],
        ],
    ];

    /**
     * 创建 OpenSearchService 实例
     *
     * 从 config('mint.opensearch.config') 读取连接配置，
     * 同时初始化 OpenAI HTTP 客户端用于 embedding 调用。
     */
    public function __construct()
    {
        $config  = config('mint.opensearch.config');
        $hostUrl = "{$config['scheme']}://{$config['host']}:{$config['port']}";

        $this->client = (new GuzzleClientFactory())->create([
            'base_uri' => $hostUrl,
            'auth'     => [$config['username'], $config['password']],
            'verify'   => $config['ssl_verification'],
        ]);

        $this->openaiApiKey = env('OPENAI_API_KEY');
        $this->http = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'timeout'  => 15,
        ]);
    }

    /**
     * 动态覆盖指定搜索模式的字段权重
     *
     * @param  string  $mode     搜索模式，支持 'fuzzy' | 'hybrid'
     * @param  array   $weights  需要覆盖的权重键值对，例如：['title.text.pali' => 5]
     * @return void
     */
    public function setWeights(string $mode, array $weights): void
    {
        if (isset($this->weights[$mode])) {
            $this->weights[$mode] = array_merge($this->weights[$mode], $weights);
        }
    }

    /**
     * 测试与 OpenSearch 集群的连接状态
     *
     * @return array{0: bool, 1: string}  [连接是否成功, 描述信息]
     */
    public function testConnection(): array
    {
        try {
            $info    = $this->client->info();
            $message = 'OpenSearch 连接成功: ' . json_encode($info['version']['number']);
            Log::info($message);
            return [true, $message];
        } catch (\Exception $e) {
            $message = 'OpenSearch 连接失败: ' . $e->getMessage();
            Log::error($message);
            return [false, $message];
        }
    }

    /**
     * 检查当前索引是否已存在
     *
     * @return bool
     */
    public function indexExists(): bool
    {
        $index = config('mint.opensearch.index');
        return $this->client->indices()->exists(['index' => $index]);
    }

    /**
     * 创建 OpenSearch 索引
     *
     * 使用 $indexDefinition 中定义的 settings 和 mappings 创建索引。
     * 若索引已存在则抛出异常，避免覆盖生产数据。
     *
     * @return array OpenSearch 响应
     *
     * @throws \Exception 索引已存在时抛出
     */
    public function createIndex(): array
    {
        $index  = config('mint.opensearch.index');
        $exists = $this->client->indices()->exists(['index' => $index]);

        if ($exists) {
            throw new \Exception("Index [$index] already exists.");
        }

        return $this->client->indices()->create([
            'index' => $index,
            'body'  => $this->indexDefinition,
        ]);
    }

    /**
     * 更新已有索引的 settings 和 mappings
     *
     * 更新 settings 时会临时关闭索引（close → putSettings → open），
     * 更新 mappings 支持热更新（新增字段），不可修改已有字段类型。
     *
     * @return array  包含 'settings' 和/或 'mappings' 的响应数组
     */
    public function updateIndex(): array
    {
        $index    = config('mint.opensearch.index');
        $settings = $this->indexDefinition['settings'] ?? [];
        $mappings = $this->indexDefinition['mappings'] ?? [];
        $response = [];

        if (!empty($settings)) {
            $this->client->indices()->close(['index' => $index]);
            $response['settings'] = $this->client->indices()->putSettings([
                'index' => $index,
                'body'  => ['settings' => $settings],
            ]);
            $this->client->indices()->open(['index' => $index]);
        }

        if (!empty($mappings)) {
            $response['mappings'] = $this->client->indices()->putMapping([
                'index' => $index,
                'body'  => $mappings,
            ]);
        }

        return $response;
    }

    /**
     * 删除当前索引
     *
     * @return array OpenSearch 响应
     */
    public function deleteIndex(): array
    {
        $index = config('mint.opensearch.index');
        return $this->client->indices()->delete(['index' => $index]);
    }

    /**
     * 统计索引文档数量（支持可选条件过滤）
     *
     * @param  array|null  $query  OpenSearch DSL query 子句，为 null 时统计全部文档。
     *                             示例：['term' => ['language' => 'zh']]
     *                                   ['exists' => ['field' => 'content.vector']]
     * @return int  文档总数
     *
     * @throws \Exception
     *
     * @example
     *   $service->count();
     *   $service->count(['exists' => ['field' => 'content.vector']]);
     */
    public function count(?array $query = null): int
    {
        $index  = config('mint.opensearch.index');
        $params = ['index' => $index];

        if (!empty($query)) {
            $params['body'] = ['query' => $query];
        }

        $response = $this->client->count($params);

        return (int) ($response['count'] ?? 0);
    }

    /**
     * 写入或覆盖单条文档
     *
     * @param  string  $id    文档 ID
     * @param  array   $body  文档内容，字段结构须与 mappings 一致
     * @return array   OpenSearch 响应
     */
    public function create(string $id, array $body): array
    {
        return $this->client->index([
            'index' => config('mint.opensearch.index'),
            'id'    => $id,
            'body'  => $body,
        ]);
    }

    /**
     * 删除单条文档
     *
     * @param  string  $id  文档 ID
     * @return array   OpenSearch 响应
     */
    public function delete(string $id): array
    {
        return $this->client->delete([
            'index' => config('mint.opensearch.index'),
            'id'    => $id,
        ]);
    }

    /**
     * 执行高级搜索
     *
     * 支持四种搜索模式：
     *   - fuzzy    多字段模糊查询（默认），基于 BM25
     *   - exact    精确匹配，使用 markdown_clean analyzer
     *   - semantic 纯语义向量搜索，需要 OpenAI embedding
     *   - hybrid   fuzzy + semantic 混合，权重由 fuzzy_ratio / semantic_ratio 控制
     *
     * 支持的过滤参数：
     *   resourceType, resourceId, granularity, language, category,
     *   tags, pageRefs, relatedId, author, channel
     *
     * @param  array  $params  {
     *   @type string      $query              搜索关键词（必填）
     *   @type string      $searchMode         搜索模式，默认 'fuzzy'
     *   @type int         $page               页码，默认 1
     *   @type int         $pageSize           每页条数，默认 20
     *   @type string      $resourceType       按资源类型过滤
     *   @type string      $resourceId         按资源 ID 过滤
     *   @type string      $granularity        按粒度过滤
     *   @type string      $language           按语言过滤
     *   @type string      $category           按分类过滤
     *   @type array       $tags               按标签过滤（terms）
     *   @type array       $pageRefs           按页码引用过滤（terms）
     *   @type string      $relatedId          按关联 ID 过滤
     *   @type string      $author             按作者过滤
     *   @type string      $channel            按频道过滤
     *   @type array       $highlight_pre_tags 高亮前置标签，默认 ['<mark>']
     *   @type array       $highlight_post_tags 高亮后置标签，默认 ['</mark>']
     * }
     * @return array  OpenSearch 原始响应
     *
     * @throws \Exception  semantic / hybrid 模式下 embedding 调用失败时抛出
     */
    public function search(array $params): array
    {
        $page     = $params['page'] ?? 1;
        $pageSize = $params['pageSize'] ?? 20;
        $from     = ($page - 1) * $pageSize;
        $mode     = $params['searchMode'] ?? 'fuzzy';

        // 排除字段
        if (!empty($params['excludes']) && is_array($params['excludes'])) {
            $excludes = array_merge($this->sourceExcludes, $params['excludes']);
        } else {
            $excludes = $this->sourceExcludes;
        }

        // ---------- 过滤条件 ----------
        $filters = [];

        if (!empty($params['resourceType'])) {
            $filters[] = ['term' => ['resource_type' => $params['resourceType']]];
        }

        if (!empty($params['resourceId'])) {
            $filters[] = ['term' => ['resource_id' => $params['resourceId']]];
        }

        if (!empty($params['granularity'])) {
            $filters[] = ['term' => ['granularity' => $params['granularity']]];
        }

        if (!empty($params['language'])) {
            $filters[] = ['term' => ['language' => $params['language']]];
        }

        if (!empty($params['category'])) {
            if (is_array($params['category'])) {
                $categories = $params['category'];
            } else {
                $categories = [$params['category']];
            }

            // 必须匹配全部：为每个 category 创建一个 term 条件
            foreach ($categories as $category) {
                $filters[] = ['term' => ['category' => $category]];
            }
        }

        if (!empty($params['tags'])) {
            $filters[] = ['terms' => ['tags' => $params['tags']]];
        }

        if (!empty($params['pageRefs'])) {
            $filters[] = ['terms' => ['page_refs' => $params['pageRefs']]];
        }

        if (!empty($params['relatedId'])) {
            $filters[] = ['term' => ['related_id' => $params['relatedId']]];
        }

        if (!empty($params['author'])) {
            $filters[] = ['match' => ['metadata.author' => $params['author']]];
        }

        if (!empty($params['channel'])) {
            $filters[] = ['term' => ['metadata.channel' => $params['channel']]];
        }

        // ---------- 查询部分 ----------
        $queryText = trim($params['query'] ?? '');

        if ($queryText === '') {
            $query = ['match_all' => new \stdClass()];
        } else {
            switch ($mode) {
                case 'exact':
                    $query = $this->buildExactQuery($queryText);
                    break;

                case 'semantic':
                    $query = $this->buildSemanticQuery($queryText);
                    break;

                case 'hybrid':
                    $query = $this->buildHybridQuery($queryText);
                    break;

                case 'fuzzy':
                default:
                    $query = $this->buildFuzzyQuery($queryText);
                    break;
            }
        }

        $highlightPreTags  = $params['highlight_pre_tags'] ?? ['<mark>'];
        $highlightPostTags = $params['highlight_post_tags'] ?? ['</mark>'];

        // ---------- 最终 DSL ----------
        $dsl = [
            'from'    => $from,
            'size'    => $pageSize,
            '_source' => ['excludes' => $excludes],
            'query'   => !empty($filters)
                ? [
                    'bool' => [
                        'must'   => [$query],
                        'filter' => $filters,
                    ]
                ]
                : $query,
            'aggs' => [
                'resource_type' => [
                    'terms' => ['field' => 'resource_type']
                ],
                'language' => [
                    'terms' => ['field' => 'language']
                ],
                'category' => [
                    'terms' => ['field' => 'category']
                ],
                'granularity' => [
                    'terms' => ['field' => 'granularity']
                ],
            ],
        ];

        // 只有有搜索词时才开启高亮
        if ($queryText !== '') {
            $dsl['highlight'] = [
                'fields' => [
                    'title.text.pali'   => new \stdClass(),
                    'title.text.zh'     => new \stdClass(),
                    'summary.text'      => new \stdClass(),
                    'content.text.pali' => new \stdClass(),
                    'content.text.zh'   => new \stdClass(),
                ],
                'fragmenter'          => 'sentence',
                'fragment_size'       => 200,
                'number_of_fragments' => 1,
                'pre_tags'            => $highlightPreTags,
                'post_tags'           => $highlightPostTags,
            ];
        }

        Log::debug(
            'OpenSearchService::search',
            ['dsl' => json_encode($dsl, JSON_UNESCAPED_UNICODE)]
        );

        return $this->client->search([
            'index' => config('mint.opensearch.index'),
            'body'  => $dsl,
        ]);
    }

    /**
     * 构建 exact（精确匹配）查询
     *
     * 使用 markdown_clean analyzer 的 exact subfield 进行匹配，
     * 适合巴利文词形精确检索场景。
     *
     * 查询字段：title.text.pali.exact, content.text.pali.exact, summary.text
     *
     * @param  string  $query  搜索关键词
     * @return array   OpenSearch DSL query 片段
     */
    protected function buildExactQuery(string $query): array
    {
        return [
            'multi_match' => [
                'query'  => $query,
                'fields' => [
                    'title.text.pali.exact',
                    'content.text.pali.exact',
                    'summary.text',
                ],
                'type' => 'best_fields',
            ],
        ];
    }

    /**
     * 构建 semantic（纯语义向量）查询
     *
     * 将查询文本通过 OpenAI embedding API 转为向量，
     * 同时对 content.vector、summary.vector、title.vector 三个 knn 字段检索，
     * 使用 bool should 合并结果。
     *
     * @param  string  $query  搜索关键词
     * @return array   OpenSearch DSL query 片段
     *
     * @throws \Exception  embedding 调用失败时抛出
     */
    protected function buildSemanticQuery(string $query): array
    {
        $vector = $this->embedText($query);

        return [
            'bool' => [
                'should' => [
                    ['knn' => ['content.vector' => ['vector' => $vector, 'k' => 20]]],
                    ['knn' => ['summary.vector' => ['vector' => $vector, 'k' => 10]]],
                    ['knn' => ['title.vector'   => ['vector' => $vector, 'k' => 5]]],
                ],
                'minimum_should_match' => 1,
            ],
        ];
    }

    /**
     * 构建 fuzzy（多字段模糊）查询
     *
     * 基于 BM25 的 multi_match best_fields 查询，
     * 字段权重取自 $weights['fuzzy']。
     *
     * @param  string  $query  搜索关键词
     * @return array   OpenSearch DSL query 片段
     */
    protected function buildFuzzyQuery(string $query): array
    {
        $fields = [];
        foreach ($this->weights['fuzzy'] as $field => $weight) {
            $fields[] = $field . '^' . $weight;
        }

        return [
            'multi_match' => [
                'query'  => $query,
                'fields' => $fields,
                'type'   => 'best_fields',
            ],
        ];
    }

    /**
     * 构建 hybrid（模糊 + 语义混合）查询
     *
     * 使用 bool should 将 fuzzy（constant_score 包裹）与三路 knn 向量查询合并，
     * 权重比例由 $weights['hybrid']['fuzzy_ratio'] 和 'semantic_ratio' 控制。
     * title.vector 的语义权重略高（×1.2），以提升标题匹配的排名。
     *
     * @param  string  $query  搜索关键词
     * @return array   OpenSearch DSL query 片段
     *
     * @throws \Exception  embedding 调用失败时抛出
     */
    protected function buildHybridQuery(string $query): array
    {
        $fuzzyFields = [];
        foreach ($this->weights['hybrid'] as $field => $weight) {
            if (in_array($field, ['fuzzy_ratio', 'semantic_ratio'])) {
                continue;
            }
            $fuzzyFields[] = $field . '^' . $weight;
        }

        $fuzzyPart = [
            'multi_match' => [
                'query'  => $query,
                'fields' => $fuzzyFields,
                'type'   => 'best_fields',
            ],
        ];

        $vector        = $this->embedText($query);
        $fuzzyRatio    = $this->weights['hybrid']['fuzzy_ratio'];
        $semanticRatio = $this->weights['hybrid']['semantic_ratio'];

        return [
            'bool' => [
                'should' => [
                    [
                        'constant_score' => [
                            'filter' => $fuzzyPart,
                            'boost'  => $fuzzyRatio,
                        ],
                    ],
                    [
                        'knn' => [
                            'content.vector' => [
                                'vector' => $vector,
                                'k'      => 20,
                                'boost'  => $semanticRatio * 1.0,
                            ],
                        ],
                    ],
                    [
                        'knn' => [
                            'summary.vector' => [
                                'vector' => $vector,
                                'k'      => 10,
                                'boost'  => $semanticRatio * 0.8,
                            ],
                        ],
                    ],
                    [
                        'knn' => [
                            'title.vector' => [
                                'vector' => $vector,
                                'k'      => 5,
                                'boost'  => $semanticRatio * 1.2, // title 权重略高
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * 调用 OpenAI Embedding API 将文本转为向量
     *
     * 使用 Redis 缓存（TTL 7 天），相同文本不会重复请求 API，
     * 缓存 key 格式为 "embedding:{md5(text)}"。
     *
     * @param  string  $text  输入文本
     * @return array   1536 维 float 向量
     *
     * @throws \Exception  未设置 OPENAI_API_KEY 或 API 返回异常时抛出
     */
    protected function embedText(string $text): array
    {
        if (!$this->openaiApiKey) {
            throw new Exception('请在 .env 设置 OPENAI_API_KEY');
        }

        $cacheKey = 'embedding:' . md5($text);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($text) {
            $response = $this->http->post('embeddings', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openaiApiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => 'text-embedding-3-small',
                    'input' => $text,
                ],
            ]);

            $json = json_decode((string) $response->getBody(), true);

            if (empty($json['data'][0]['embedding'])) {
                throw new Exception('OpenAI embedding 返回异常: ' . json_encode($json));
            }

            return $json['data'][0]['embedding'];
        });
    }

    /**
     * 清除指定文本的 embedding 缓存
     *
     * @param  string  $text  原始文本（与调用 embedText 时一致）
     * @return bool    缓存是否成功删除
     *
     * @example
     *   $service->clearEmbeddingCache('sabbe dhammā anattā');
     */
    public function clearEmbeddingCache(string $text): bool
    {
        $cacheKey = 'embedding:' . md5($text);
        return Cache::forget($cacheKey);
    }

    /**
     * 清除 Redis 中所有 embedding 缓存
     *
     * 匹配 "embedding:*" 模式的全部键，生产环境请谨慎调用。
     *
     * @return int  已删除的缓存条数
     *
     * @example
     *   $count = $service->clearAllEmbeddingCache();
     *   echo "已清理缓存 {$count} 条";
     */
    public function clearAllEmbeddingCache(): int
    {
        $redis   = Cache::getRedis();
        $keys    = $redis->keys('embedding:*');

        if (!empty($keys)) {
            $redis->del($keys);
        }

        return count($keys);
    }

    /**
     * 自动建议（Completion Suggest）
     *
     * 基于 completion 字段实现前缀补全，支持同时查询多个语言字段。
     * 结果按 _score 降序排序，跨字段去重。
     *
     * 可用字段标识符（$fields 参数）：
     *   - 'title_pali'    → title.suggest.pali
     *   - 'title_zh'      → title.suggest.zh
     *   - 'content_pali'  → content.suggest.pali
     *   - 'content_zh'    → content.suggest.zh
     *
     * @param  string            $query     查询前缀文本
     * @param  array|string|null $fields    要查询的字段标识符，null 表示全部字段
     * @param  string|null       $language  可选的语言过滤（term query）
     * @param  int               $limit     每个字段返回的建议数量，默认 10
     * @return array  建议结果列表，每项包含：
     *                text, source（字段标识符）, score, doc_id, doc_source
     *
     * @throws \InvalidArgumentException  $fields 中含无效字段标识符时抛出
     *
     * @example
     *   // 查询所有字段
     *   $service->suggest('nibb');
     *
     *   // 只查询巴利文标题建议
     *   $service->suggest('nibb', 'title_pali');
     *
     *   // 查询多个字段，限制语言
     *   $service->suggest('涅', ['title_zh', 'content_zh'], 'zh', 5);
     */
    public function suggest(
        string $query,
        $fields = null,
        ?string $language = null,
        int $limit = 10
    ): array {
        // 字段标识符 → OpenSearch completion 字段路径
        $fieldMap = [
            'title_pali'   => 'title.suggest.pali',
            'title_zh'     => 'title.suggest.zh',
            'content_pali' => 'content.suggest.pali',
            'content_zh'   => 'content.suggest.zh',
        ];

        // 处理字段参数
        if ($fields === null) {
            $searchFields = array_keys($fieldMap);
        } elseif (is_string($fields)) {
            $searchFields = [$fields];
        } else {
            $searchFields = $fields;
        }

        // 过滤无效字段
        $searchFields = array_values(array_filter(
            $searchFields,
            fn($field) => isset($fieldMap[$field])
        ));

        if (empty($searchFields)) {
            throw new \InvalidArgumentException('Invalid fields specified for suggestion');
        }

        // 构建 suggest DSL
        $suggests = [];
        foreach ($searchFields as $field) {
            $suggests[$field . '_suggest'] = [
                'prefix'     => $query,
                'completion' => [
                    'field'            => $fieldMap[$field],
                    'size'             => $limit,
                    'skip_duplicates'  => true,
                ],
            ];
        }

        $dsl = ['suggest' => $suggests];

        if ($language) {
            $dsl['query'] = ['term' => ['language' => $language]];
        }

        $response = $this->client->search([
            'index' => config('mint.opensearch.index'),
            'body'  => $dsl,
        ]);

        // 整理结果，附加来源字段
        $results = [];
        foreach ($searchFields as $field) {
            $options = $response['suggest'][$field . '_suggest'][0]['options'] ?? [];

            foreach ($options as $opt) {
                $results[] = [
                    'text'       => $opt['text']    ?? '',
                    'source'     => $field,
                    'score'      => $opt['_score']  ?? 0,
                    'doc_id'     => $opt['_id']     ?? null,
                    'doc_source' => $opt['_source'] ?? null,
                ];
            }
        }

        // 按分数降序排序
        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        return $results;
    }

    /**
     * 按文档 ID 获取单条完整文档（包含 content.display）
     *
     * @param  string  $id  文档 ID，例如 "term_{guid}"
     * @return array   OpenSearch 原始响应
     */
    public function get(string $id): array
    {
        return $this->client->get([
            'index' => config('mint.opensearch.index'),
            'id'    => $id,
        ]);
    }
}
