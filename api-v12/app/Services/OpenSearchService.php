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

    /** 默认查询排除字段 **/
    private $sourceExcludes = [
        'title.suggest',
        'content.suggest',
    ];

    /** 默认权重配置 **/
    private $weights = [
        'fuzzy' => [
            'bold_single' => 50,
            'bold_multi' => 10,
            'title.pali.text'   => 3,
            'title.zh'          => 3,
            'summary.text'      => 2,
            'content.pali.text' => 1,
            'content.zh'        => 1,
        ],
        'hybrid' => [
            'fuzzy_ratio'    => 0.7,
            'semantic_ratio' => 0.3,
            'bold_single' => 50,
            'bold_multi' => 10,
            'title.pali.text'   => 3,
            'title.zh'          => 3,
            'summary.text'      => 2,
            'content.pali.text' => 1,
            'content.zh'        => 1,
        ],
    ];



    private $indexDefinition = [
        'settings' => [
            'index' => [
                'knn' => true,
            ],
            'analysis' => [
                'analyzer' => [
                    /** */
                    'pali_query_analyzer' => [
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase', 'pali_synonyms'],
                    ],
                    'pali_index_analyzer' => [
                        'type'        => 'custom',
                        'tokenizer' => 'standard',
                        'char_filter' => ['markdown_strip'],
                        'filter' => ['lowercase'],
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
                        'filter' => ['lowercase', 'asciifolding']
                    ],
                    'zh_suggest_analyzer' => [
                        'tokenizer' => 'ik_smart',
                        'char_filter' => ['tsconvert'],
                    ],
                    // 中文简繁统一 (繁 -> 简)
                    'zh_index_analyzer' => [
                        'tokenizer' => 'ik_max_word',
                        'char_filter' => ['tsconvert'],
                    ],
                    'zh_query_analyzer' => [
                        'tokenizer' => 'ik_smart',
                        'char_filter' => ['tsconvert'],
                    ]
                ],
                'filter' => [
                    'pali_synonyms' => [
                        'type' => 'synonym_graph',
                        'synonyms_path' => 'analysis/pali_synonyms.txt',
                        'updateable' => true,
                    ],
                ],
                'char_filter' => [
                    'markdown_strip' => [
                        'type'        => 'pattern_replace',
                        'pattern'     => '\\*\\*|\\*|_|`|~',
                        'replacement' => '',
                    ],
                    "tsconvert" => [
                        "type" => "stconvert",
                        "convert_type" => "t2s"
                    ]
                ],
            ],
        ],
        'mappings' => [
            'properties' => [
                'id' => ['type' => 'keyword'],
                'resource_id' => ['type' => 'keyword'],
                'resource_type' => ['type' => 'keyword'],
                'title' => [
                    'properties' => [
                        'pali' => [
                            'type' => 'text',
                            'fields' => [
                                /**模糊查询 */
                                'text' => [
                                    'type' => 'text',
                                    'analyzer' => 'pali_index_analyzer',
                                    'search_analyzer' => 'pali_query_analyzer',
                                ],
                                /**准确查询 */
                                'exact' => [
                                    'type' => 'text',
                                    'analyzer' => 'markdown_clean',
                                ],
                            ],
                        ],
                        'zh' => [
                            'type' => 'text',
                            'analyzer' => 'zh_index_analyzer',
                            'search_analyzer' => 'zh_query_analyzer',
                        ],

                        'vector' => [
                            'type' => 'knn_vector',
                            'dimension' => 1536,
                            'method' => [
                                'name'       => 'hnsw',
                                'space_type' => 'cosinesimil',
                                'engine'     => 'nmslib',
                            ],
                        ],
                        // 自动建议字段
                        'suggest_pali' => [
                            'type' => 'completion',
                            'analyzer' => 'pali_suggest_analyzer'
                        ],
                        'suggest_zh' => [
                            'type' => 'completion',
                            'analyzer' => 'zh_suggest_analyzer'
                        ],
                    ],
                ],
                /** 简体中文 llm生成 */
                'summary' => [
                    'properties' => [
                        'text' => [
                            'type' => 'text',
                            'analyzer' => 'zh_index_analyzer',
                            'search_analyzer' => 'zh_query_analyzer',
                        ],
                        'vector' => [
                            'type' => 'knn_vector',
                            'dimension' => 1536,
                            'method' => [
                                'name'       => 'hnsw',
                                'space_type' => 'cosinesimil',
                                'engine'     => 'nmslib',
                            ],
                        ],
                    ]
                ],
                'content' => [
                    'properties' => [
                        'pali' => [
                            'type' => 'text',
                            'fields' => [
                                /**模糊查询 */
                                'text' => [
                                    'type' => 'text',
                                    'analyzer' => 'pali_index_analyzer',
                                    'search_analyzer' => 'pali_query_analyzer',
                                ],
                                /**准确查询 */
                                'exact' => [
                                    'type' => 'text',
                                    'analyzer' => 'markdown_clean',
                                ],

                            ],
                        ],
                        'zh' => [
                            'type' => 'text',
                            'analyzer' => 'zh_index_analyzer',
                            'search_analyzer' => 'zh_query_analyzer',
                        ],
                        'tokens' => [
                            'type' => 'nested',
                            'properties' => [
                                'surface' => ['type' => 'keyword'],
                                'lemma' => ['type' => 'keyword'],
                                'compound_parts' => ['type' => 'keyword'],
                                'case' => ['type' => 'keyword'],
                            ],
                        ],
                        'vector' => [
                            'type' => 'knn_vector',
                            'dimension' => 1536,
                            'method' => [
                                'name'       => 'hnsw',
                                'space_type' => 'cosinesimil',
                                'engine'     => 'nmslib',
                            ],
                        ],
                        'suggest_pali' => [
                            'type' => 'completion',
                            'analyzer' => 'pali_suggest_analyzer'
                        ],
                        'suggest_zh' => [
                            'type' => 'completion',
                            'analyzer' => 'zh_suggest_analyzer'
                        ],
                    ],
                ],
                'related_id' => ['type' => 'keyword'],
                'bold_single' => [
                    'type' => 'text',
                    'analyzer' => 'standard',
                    'search_analyzer' => 'pali_query_analyzer',
                ],
                'bold_multi' => [
                    'type' => 'text',
                    'analyzer' => 'standard',
                    'search_analyzer' => 'pali_query_analyzer',
                ],
                'path' => ['type' => 'text', 'analyzer' => 'standard'],
                'page_refs' => [
                    'type' => 'keyword',
                ],
                'tags' => ['type' => 'keyword'],
                'category' => ['type' => 'keyword'],
                'author' => ['type' => 'text'],
                'language' => ['type' => 'keyword'],
                'updated_at' => ['type' => 'date'],
                'granularity' => ['type' => 'keyword'],
                'metadata' => [
                    'properties' => [
                        'APA' => ['type' => 'text', 'index' => false],
                        'MLA' => ['type' => 'text', 'index' => false],
                        'widget' => ['type' => 'text', 'index' => false],
                        'author' => ['type' => 'text'],   //
                        'channel' => ['type' => 'text'], //
                    ],
                ],
            ],
        ],
    ];

    public function __construct()
    {
        $config = config('mint.opensearch.config');
        $hostUrl = "{$config['scheme']}://{$config['host']}:{$config['port']}";

        $this->client = (new GuzzleClientFactory())->create([
            'base_uri' => $hostUrl,
            'auth' => [$config['username'], $config['password']],
            'verify' => $config['ssl_verification'],
        ]);

        $this->openaiApiKey = env('OPENAI_API_KEY');
        $this->http = new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'timeout' => 15,
        ]);
    }

    public function setWeights(string $mode, array $weights)
    {
        if (isset($this->weights[$mode])) {
            $this->weights[$mode] = array_merge($this->weights[$mode], $weights);
        }
    }

    public function testConnection()
    {
        try {
            $info = $this->client->info();
            $message = 'OpenSearch 连接成功: ' . json_encode($info['version']['number']);
            Log::info($message);
            return [true, $message];
        } catch (\Exception $e) {
            $message = 'OpenSearch 连接失败: ' . $e->getMessage();
            Log::error($message);
            return [false, $message];
        }
    }
    public function indexExists()
    {
        $index = config('mint.opensearch.index');
        return $this->client->indices()->exists(['index' => $index]);
    }
    /** 索引管理方法保持不变... **/

    public function createIndex()
    {
        $index = config('mint.opensearch.index');
        $exists = $this->client->indices()->exists(['index' => $index]);
        if ($exists) {
            throw new \Exception("Index [$index] already exists.");
        }
        return $this->client->indices()->create([
            'index' => $index,
            'body' => $this->indexDefinition
        ]);
    }

    public function updateIndex()
    {
        $index = config('mint.opensearch.index');
        $settings = $this->indexDefinition['settings'] ?? [];
        $mappings = $this->indexDefinition['mappings'] ?? [];

        $response = [];
        if (!empty($settings)) {
            $this->client->indices()->close(['index' => $index]);
            $response['settings'] = $this->client->indices()->putSettings([
                'index' => $index,
                'body' => ['settings' => $settings]
            ]);
            $this->client->indices()->open(['index' => $index]);
        }
        if (!empty($mappings)) {
            $response['mappings'] = $this->client->indices()->putMapping([
                'index' => $index,
                'body' => $mappings
            ]);
        }
        return $response;
    }

    public function deleteIndex()
    {
        $index = config('mint.opensearch.index');
        return $this->client->indices()->delete(['index' => $index]);
    }
    /**
     * 获取索引文档数量（支持条件统计）
     *
     * @param  array|null  $query  可选的查询条件（OpenSearch DSL query 部分）
     *                             例如：
     *                             [
     *                                 'term' => ['language' => 'zh']
     *                             ]
     *
     * @return int  文档总数
     *
     * @throws \Exception
     *
     * @example
     * // 获取索引全部文档数量
     * $count = $service->count();
     *
     * // 按条件统计（例如：只统计有 embedding 的文档）
     * $count = $service->count([
     *     'exists' => ['field' => 'content.vector']
     * ]);
     */
    public function count(?array $query = null): int
    {
        $index = config('mint.opensearch.index');

        $params = [
            'index' => $index,
        ];

        // 如果传入 query，则按条件统计
        if (!empty($query)) {
            $params['body'] = [
                'query' => $query
            ];
        }

        $response = $this->client->count($params);

        return (int) ($response['count'] ?? 0);
    }

    public function create(string $id, array $body)
    {
        return $this->client->index([
            'index' => config('mint.opensearch.index'),
            'id' => $id,
            'body' => $body
        ]);
    }

    public function delete($id)
    {
        return $this->client->delete(['index' => config('mint.opensearch.index'), 'id' => $id]);
    }

    /**
     * 执行高级搜索（支持 fuzzy / exact / semantic / hybrid 四种模式）
     *
     * @param  array  $params  搜索参数数组
     *   - query: 搜索关键词
     *   - searchMode: 搜索模式 (fuzzy|exact|semantic|hybrid)
     *   - page: 页码，默认 1
     *   - pageSize: 每页条数，默认 20
     *   - resourceType / language / category / tags / relatedId / pageRefs / author / channel 等过滤条件
     * @return array OpenSearch 返回的搜索结果
     *
     * @throws \Exception
     */
    public function search(array $params)
    {
        // 分页参数
        $page = $params['page'] ?? 1;
        $pageSize = $params['pageSize'] ?? 20;
        $from = ($page - 1) * $pageSize;

        // 搜索模式，默认 fuzzy
        $mode = $params['searchMode'] ?? 'fuzzy';

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
            $filters[] = ['term' => ['category' => $params['category']]];
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
        switch ($mode) {
            case 'exact':
                $query = $this->buildExactQuery($params['query']);
                break;

            case 'semantic':
                $query = $this->buildSemanticQuery($params['query']);
                break;

            case 'hybrid':
                $query = $this->buildHybridQuery($params['query']);
                break;

            case 'fuzzy':
            default:
                $query = $this->buildFuzzyQuery($params['query']);
        }

        if (!empty($params['highlight_pre_tags'])) {
            $highlight_pre_tags = $params['highlight_pre_tags'];
        } else {
            $highlight_pre_tags = ['<mark>'];
        }
        if (!empty($params['highlight_post_tags'])) {
            $highlight_post_tags = $params['highlight_post_tags'];
        } else {
            $highlight_post_tags = ['</mark>'];
        }

        // ---------- 最终 DSL ----------
        $dsl = [
            'from' => $from,
            'size' => $pageSize,
            '_source' => [
                'excludes' => $this->sourceExcludes
            ],
            'query' => !empty($filters)
                ? ['bool' => ['must' => [$query], 'filter' => $filters]]
                : $query,
            'aggs' => [
                'resource_type' => ['terms' => ['field' => 'resource_type']],
                'language' => ['terms' => ['field' => 'language']],
                'category' => ['terms' => ['field' => 'category']],
                'granularity' => ['terms' => ['field' => 'granularity']],
            ],
            'highlight' => [
                'fields' => [
                    'title.pali.text' => new \stdClass(),
                    'title.zh' => new \stdClass(),
                    'summary.text' => new \stdClass(),
                    'content.pali.text' => new \stdClass(),
                    'content.zh' => new \stdClass(),
                ],
                "fragmenter" => "sentence",
                "fragment_size" => 200,
                "number_of_fragments" => 1,
                'pre_tags' => $highlight_pre_tags,
                'post_tags' => $highlight_post_tags,
            ],
        ];

        Log::debug('search', ['dsl' => json_encode($dsl, JSON_UNESCAPED_UNICODE)]);
        // ---------- 执行查询 ----------
        $response = $this->client->search([
            'index' => config('mint.opensearch.index'),
            'body'  => $dsl
        ]);

        return $response;
    }

    /**
     * 构建 exact 查询
     * 精确匹配 title.pali.exact, content.pali.exact, summary
     */
    protected function buildExactQuery(string $query): array
    {
        return [
            'multi_match' => [
                'query'  => $query,
                'fields' => [
                    'title.pali.exact',
                    'content.pali.exact',
                    'summary.text'
                ],
                'type'   => 'best_fields',
            ]
        ];
    }

    /**
     * 构建 semantic 查询
     * 使用 OpenAI embedding，同时查询三个向量字段
     */
    protected function buildSemanticQuery(string $query): array
    {
        $vector = $this->embedText($query);

        // OpenSearch 支持多个 knn 查询，使用 bool should
        return [
            'bool' => [
                'should' => [
                    [
                        'knn' => [
                            'content.vector' => [
                                'vector' => $vector,
                                'k' => 20,
                            ]
                        ]
                    ],
                    [
                        'knn' => [
                            'summary.vector' => [
                                'vector' => $vector,
                                'k' => 10,
                            ]
                        ]
                    ],
                    [
                        'knn' => [
                            'title.vector' => [
                                'vector' => $vector,
                                'k' => 5,
                            ]
                        ]
                    ]
                ],
                'minimum_should_match' => 1
            ]
        ];
    }

    /**
     * 构建 fuzzy 查询
     */
    protected function buildFuzzyQuery(string $query)
    {
        $fields = [];
        foreach ($this->weights['fuzzy'] as $field => $weight) {
            $fields[] = $field . "^" . $weight;
        }

        return [
            'multi_match' => [
                'query'  => $query,
                'fields' => $fields,
                'type'   => 'best_fields'
            ]
        ];
    }

    /**
     * 构建 hybrid 查询 (fuzzy + semantic)
     */
    protected function buildHybridQuery(string $query)
    {
        $fuzzyFields = [];
        foreach ($this->weights['hybrid'] as $field => $weight) {
            if (in_array($field, ['fuzzy_ratio', 'semantic_ratio'])) {
                continue;
            }
            $fuzzyFields[] = $field . "^" . $weight;
        }

        $fuzzyPart = [
            'multi_match' => [
                'query'  => $query,
                'fields' => $fuzzyFields,
                'type'   => 'best_fields'
            ]
        ];

        $vector = $this->embedText($query);

        $fuzzyRatio = $this->weights['hybrid']['fuzzy_ratio'];
        $semanticRatio = $this->weights['hybrid']['semantic_ratio'];

        // 使用 bool should 组合 fuzzy 和 semantic 查询
        return [
            'bool' => [
                'should' => [
                    // Fuzzy 部分，带权重
                    [
                        'constant_score' => [
                            'filter' => $fuzzyPart,
                            'boost' => $fuzzyRatio
                        ]
                    ],
                    // Semantic 部分 - content
                    [
                        'knn' => [
                            'content.vector' => [
                                'vector' => $vector,
                                'k' => 20,
                                'boost' => $semanticRatio * 1.0  // 主要权重
                            ]
                        ]
                    ],
                    // Semantic 部分 - summary
                    [
                        'knn' => [
                            'summary.vector' => [
                                'vector' => $vector,
                                'k' => 10,
                                'boost' => $semanticRatio * 0.8
                            ]
                        ]
                    ],
                    // Semantic 部分 - title
                    [
                        'knn' => [
                            'title.vector' => [
                                'vector' => $vector,
                                'k' => 5,
                                'boost' => $semanticRatio * 1.2  // title 稍微高一点
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * 调用 OpenAI Embedding API
     * 使用 Redis 缓存，避免重复调用
     *
     * @param  string  $text 输入文本
     * @return array 向量 embedding
     * @throws \Exception
     */
    protected function embedText(string $text): array
    {
        if (!$this->openaiApiKey) {
            throw new Exception("请在 .env 设置 OPENAI_API_KEY");
        }

        // 缓存 key，可以用 md5 保证唯一
        $cacheKey = "embedding:" . md5($text);

        // 先查缓存
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

            $json = json_decode((string)$response->getBody(), true);

            if (empty($json['data'][0]['embedding'])) {
                throw new Exception("OpenAI embedding 返回异常: " . json_encode($json));
            }

            return $json['data'][0]['embedding'];
        });
    }

    /**
     * 清理指定文本的 embedding 缓存
     * $service = app(App\Services\OpenSearchService::class);

        // 清理某个文本的缓存
        $service->clearEmbeddingCache("sabbe dhammā anattā");

        // 清理所有 embedding 缓存
        $count = $service->clearAllEmbeddingCache();
        echo "已清理缓存 {$count} 条";
     *
     * @param  string  $text
     * @return bool
     */
    public function clearEmbeddingCache(string $text): bool
    {
        $cacheKey = "embedding:" . md5($text);
        return Cache::forget($cacheKey);
    }

    /**
     * 清理所有 embedding 缓存
     * 注意：这会删除 Redis 里所有 "embedding:*" 的缓存
     *
     * @return int 删除的条数
     */
    public function clearAllEmbeddingCache(): int
    {
        $redis = Cache::getRedis();
        $pattern = "embedding:*";
        $keys = $redis->keys($pattern);
        if (!empty($keys)) {
            $redis->del($keys);
        }
        return count($keys);
    }


    /**
     * 自动建议
     *
     * @param string $query 查询文本
     * @param array|string|null $fields 要查询的字段，可选值：
     *   - null: 查询所有字段 ['title', 'content', 'page_refs']
     *   - 'title': 只查询 title.suggest
     *   - 'content': 只查询 content.pali.suggest
     *   - 'page_refs': 只查询 page_refs.suggest
     *   - ['title', 'content']: 查询多个字段
     * @param string|null $language 语言过滤（可选）
     * @param int $limit 每个字段返回的建议数量
     * @return array
     */
    public function suggest(
        string $query,
        $fields = null,
        ?string $language = null,
        int $limit = 10
    ): array {
        // 字段映射配置
        $fieldMap = [
            'title_pali' => 'title.suggest_pali',
            'title_zh' => 'title.suggest_zh',
            'content_pali' => 'content.suggest_pali',
            'content_zh' => 'content.suggest_zh',
        ];

        // 处理字段参数
        if ($fields === null) {
            // 默认查询所有字段
            $searchFields = array_keys($fieldMap);
        } elseif (is_string($fields)) {
            // 单个字段
            $searchFields = [$fields];
        } else {
            // 数组形式
            $searchFields = $fields;
        }

        // 验证字段有效性
        $searchFields = array_filter($searchFields, function ($field) use ($fieldMap) {
            return isset($fieldMap[$field]);
        });

        if (empty($searchFields)) {
            throw new \InvalidArgumentException('Invalid fields specified for suggestion');
        }

        // 构建 suggest 查询
        $suggests = [];
        foreach ($searchFields as $field) {
            $suggests[$field . '_suggest'] = [
                'prefix' => $query,
                'completion' => [
                    'field' => $fieldMap[$field],
                    'size'  => $limit,
                    'skip_duplicates' => true,
                ]
            ];
        }

        $dsl = ['suggest' => $suggests];

        // 添加语言过滤
        if ($language) {
            $dsl['query'] = ['term' => ['language' => $language]];
        }

        $response = $this->client->search([
            'index' => config('mint.opensearch.index'),
            'body' => $dsl
        ]);

        // 处理返回结果，包含来源信息
        $results = [];
        foreach ($searchFields as $field) {
            $options = $response['suggest'][$field . '_suggest'][0]['options'] ?? [];

            foreach ($options as $opt) {
                $results[] = [
                    'text'      => $opt['text'] ?? '',
                    'source'    => $field,  // 添加来源字段
                    'score'     => $opt['_score'] ?? 0,
                    // 可选：添加文档信息
                    'doc_id'    => $opt['_id'] ?? null,
                    'doc_source' => $opt['_source'] ?? null,
                ];
            }
        }

        // 按分数排序
        usort($results, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $results;
    }
}
