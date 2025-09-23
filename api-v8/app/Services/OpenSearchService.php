<?php

namespace App\Services;

use OpenSearch\GuzzleClientFactory;
use Illuminate\Support\Facades\Log;

class OpenSearchService
{
    protected $client;
    private $indexDefinition = [
        'settings' => [
            // 必须启用 knn，否则 knn_vector 无法使用
            'index' => [
                'knn' => true,
            ],
            'analysis' => [
                'analyzer' => [
                    // Pali + 中文 搜索时的分析器：中文用 IK，Pali 用小写化 + 同义词
                    'pali_query_analyzer' => [
                        'tokenizer' => 'ik_max_word',   // IK 分词器，中文有效
                        'filter' => [
                            'lowercase',                // 小写化，保证 Pali 一致性
                            'pali_synonyms',            // Pali 同义词扩展
                        ],
                    ],
                    // Pali 索引时的分析器：只做小写化
                    'pali_index_analyzer' => [
                        'tokenizer' => 'standard',
                        'filter' => ['lowercase'],
                    ],
                ],
                'filter' => [
                    // Pali 同义词过滤器，基于文件定义
                    'pali_synonyms' => [
                        'type' => 'synonym_graph',
                        'synonyms_path' => 'analysis/pali_synonyms.txt',
                    ],
                ],
            ],
        ],
        'mappings' => [
            'properties' => [
                'id' => ['type' => 'keyword'],             // OpenSearch 唯一 ID
                'resource_id' => ['type' => 'keyword'],    // 数据库 UUID
                'resource_type' => ['type' => 'keyword'],  // 资源类型

                'title' => [
                    'properties' => [
                        'display' => ['type' => 'text', 'analyzer' => 'ik_max_word'],
                        'text' => [
                            'type' => 'text',
                            'analyzer' => 'ik_smart',           // 中文分词
                            'search_analyzer' => 'ik_smart',
                        ]
                    ],
                ],

                'summary' => [
                    'type' => 'text',
                    'analyzer' => 'ik_smart',
                    'search_analyzer' => 'ik_smart',
                ],

                'content' => [
                    'properties' => [
                        'display' => ['type' => 'text', 'analyzer' => 'ik_max_word'],
                        'text' => [
                            'type' => 'text',
                            'analyzer' => 'pali_index_analyzer',      // 索引时 Pali 小写化
                            'search_analyzer' => 'pali_query_analyzer', // 查询时 中文 IK + Pali 同义词
                        ],
                        'exact' => ['type' => 'text', 'analyzer' => 'standard'],
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
                    ],
                ],

                'related_id' => ['type' => 'keyword'],
                'bold_single' => ['type' => 'text', 'analyzer' => 'standard'],
                'bold_multi' => ['type' => 'text', 'analyzer' => 'standard'],
                'path' => ['type' => 'text', 'analyzer' => 'standard'],
                'page_refs' => ['type' => 'keyword'],
                'tags' => ['type' => 'keyword'],
                'category' => ['type' => 'keyword'],
                'author' => [
                    'type' => 'text',
                ],
                'language' => ['type' => 'keyword'],
                'updated_at' => ['type' => 'date'],
                'granularity' => ['type' => 'keyword'],
                'metadata' => [
                    'properties' => [
                        'APA' => ['type' => 'text', 'analyzer' => 'ik_max_word'],
                        'MLA' => ['type' => 'text', 'analyzer' => 'ik_max_word'],
                    ],
                ],
            ],
        ],
    ];




    public function __construct()
    {
        $config = config('mint.opensearch.config');

        // 构建主机 URL
        $hostUrl = "{$config['scheme']}://{$config['host']}:{$config['port']}";

        // 使用 GuzzleClientFactory 创建客户端
        $this->client = (new GuzzleClientFactory())->create([
            'base_uri' => $hostUrl, // 如 http://127.0.0.1:9200 或 https://your-host:9200
            'auth' => [$config['username'], $config['password']], // 基本认证
            'verify' => $config['ssl_verification'], // SSL 验证
        ]);
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

    /**
     * 创建 OpenSearch 索引
     * @param string $index 索引名称（如 wikipali_resources）
     * @param array $settings 索引设置和映射
     * @return array 响应结果
     * [
     *       'acknowledged' => true, // 表示请求是否被服务器接受并处理
     *       'shards_acknowledged' => true, // 表示分片是否成功初始化
     *       'index' => '索引名称' // 创建的索引名称
     *   ]
     */
    public function createIndex()
    {
        $index = config('mint.opensearch.index');

        // 检查索引是否存在
        try {
            $exists = $this->client->indices()->exists(['index' => $index]);
            if ($exists) {
                throw new \Exception("Index [$index] already exists. Use updateIndex() to modify settings or mappings.");
            }
        } catch (\Exception $e) {
            // 如果存在，直接抛出异常或处理
            throw $e;
        }

        // 创建索引
        return $this->client->indices()->create([
            'index' => $index,
            'body' => $this->indexDefinition
        ]);
    }

    /**
     * 更新 OpenSearch 索引的设置或映射
     * @param string $index 索引名称
     * @param array $settings 更新的设置或映射
     * @return array 响应结果
     */
    public function updateIndex()
    {
        $index = config('mint.opensearch.index');

        // 分离设置和映射
        $settings = isset($this->indexDefinition['settings']) ? $this->indexDefinition['settings'] : [];
        $mappings = isset($this->indexDefinition['mappings']) ? $this->indexDefinition['mappings'] : [];

        $response = [];

        // 更新设置（需要先关闭索引）
        if (!empty($settings)) {
            try {
                $this->client->indices()->close(['index' => $index]);
                $response['settings'] = $this->client->indices()->putSettings([
                    'index' => $index,
                    'body' => ['settings' => $settings]
                ]);
                $this->client->indices()->open(['index' => $index]);
            } catch (\Exception $e) {
                throw new \Exception("Failed to update settings for index [$index]: " . $e->getMessage());
            }
        }

        // 更新映射
        if (!empty($mappings)) {
            try {
                $response['mappings'] = $this->client->indices()->putMapping([
                    'index' => $index,
                    'body' => $mappings
                ]);
            } catch (\Exception $e) {
                throw new \Exception("Failed to update mappings for index [$index]: " . $e->getMessage());
            }
        }

        return $response;
    }

    /**
     * 删除 OpenSearch 索引
     * @param string $index 索引名称
     * @return array 响应结果
     */
    public function deleteIndex()
    {
        $index = config('mint.opensearch.index');
        return $this->client->indices()->delete(['index' => $index]);
    }
    /**
     * 索引单个文档
     * @param string $index 索引名称（如 wikipali_resources）
     * @param string $id 文档 ID
     * @param array $body 文档内容
     * @return array 响应结果
     */
    public function create(string $id, array $body)
    {
        return $this->client->index([
            'index' => config('mint.opensearch.index'),
            'id' => $id,
            'body' => $body
        ]);
    }

    public function search($dsl)
    {
        return $this->client->search(['index' => config('mint.opensearch.index'), 'body' => $dsl]);
    }

    public function delete($id)
    {
        return $this->client->delete(['index' => config('mint.opensearch.index'), 'id' => $id]);
    }
}
