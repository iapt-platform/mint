<?php

// api-v8/app/Services/OpenSearchService.php

namespace App\Services;

use App\Facades\RedisCache;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenSearch\GuzzleClientFactory;

class OpenSearchService
{
    private const EMBEDDING_CACHE_VERSION_KEY = 'embedding/version';

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
            'bold_single' => 50,
            'bold_multi' => 10,
            'title.text.pali' => 3,
            'title.text.zh' => 3,
            'summary.text' => 2,
            'content.text.pali' => 1,
            'content.text.zh' => 1,
        ],
        'hybrid' => [
            'fuzzy_ratio' => 0.7,
            'semantic_ratio' => 0.3,
            'bold_single' => 50,
            'bold_multi' => 10,
            'title.text.pali' => 3,
            'title.text.zh' => 3,
            'summary.text' => 2,
            'content.text.pali' => 1,
            'content.text.zh' => 1,
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
     * 分类维度（resource_id、resource_type、language 等）一律 keyword：它们是枚举值，
     * 不该被分词。若声明成 text，'zh-Hans' 会被切成 zh + hans，查 language=zh
     * 就会把 zh-Hans 的文档一并捞进来（实测 401 vs 410），而且不报错。
     *
     * 定义本身在 opensearch/indices/{index}.json，见 indexDefinition()。
     */
    private ?array $indexDefinition = null;

    /**
     * 读取索引定义（settings + mappings）
     *
     * 文件名与索引名一致：{index_definition_path}/{index}.json，
     * 默认目录为仓库根目录的 opensearch/indices/。换索引版本时新增一个同名 JSON。
     *
     * @return array{settings: array, mappings: array}
     *
     * @throws Exception 文件不存在或不是合法 JSON 时抛出
     */
    protected function indexDefinition(): array
    {
        if ($this->indexDefinition !== null) {
            return $this->indexDefinition;
        }

        $path = Str::finish(config('mint.opensearch.index_definition_path'), '/')
            .config('mint.opensearch.index').'.json';
        if (! is_file($path)) {
            throw new Exception("OpenSearch index definition [$path] not found.");
        }

        return $this->indexDefinition = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * 创建 OpenSearchService 实例
     *
     * 从 config('mint.opensearch.config') 读取连接配置，
     * 同时初始化 OpenAI HTTP 客户端用于 embedding 调用。
     */
    public function __construct()
    {
        $config = config('mint.opensearch.config');
        $hostUrl = "{$config['scheme']}://{$config['host']}:{$config['port']}";

        $this->client = (new GuzzleClientFactory)->create([
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

    /**
     * 动态覆盖指定搜索模式的字段权重
     *
     * @param  string  $mode  搜索模式，支持 'fuzzy' | 'hybrid'
     * @param  array  $weights  需要覆盖的权重键值对，例如：['title.text.pali' => 5]
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
     * @return array{0: bool, 1: string} [连接是否成功, 描述信息]
     */
    public function testConnection(): array
    {
        try {
            $info = $this->client->info();
            $message = 'OpenSearch 连接成功: '.json_encode($info['version']['number']);
            Log::info($message);

            return [true, $message];
        } catch (Exception $e) {
            $message = 'OpenSearch 连接失败: '.$e->getMessage();
            Log::error($message);

            return [false, $message];
        }
    }

    /**
     * 检查当前索引是否已存在
     */
    public function indexExists(): bool
    {
        $index = config('mint.opensearch.index');

        return $this->client->indices()->exists(['index' => $index]);
    }

    /**
     * 创建 OpenSearch 索引
     *
     * 使用 indexDefinition() 读到的 settings 和 mappings 创建索引。
     * 若索引已存在则抛出异常，避免覆盖生产数据。
     *
     * @return array OpenSearch 响应
     *
     * @throws Exception 索引已存在时抛出
     */
    public function createIndex(): array
    {
        $index = config('mint.opensearch.index');
        $exists = $this->client->indices()->exists(['index' => $index]);

        if ($exists) {
            throw new Exception("Index [$index] already exists.");
        }

        return $this->client->indices()->create([
            'index' => $index,
            'body' => $this->indexDefinition(),
        ]);
    }

    /**
     * 更新已有索引的 settings 和 mappings
     *
     * 更新 settings 时会临时关闭索引（close → putSettings → open），
     * 更新 mappings 支持热更新（新增字段），不可修改已有字段类型。
     *
     * @return array 包含 'settings' 和/或 'mappings' 的响应数组
     */
    public function updateIndex(): array
    {
        $index = config('mint.opensearch.index');
        $settings = $this->indexDefinition()['settings'] ?? [];
        $mappings = $this->indexDefinition()['mappings'] ?? [];
        $response = [];

        if (! empty($settings)) {
            $this->client->indices()->close(['index' => $index]);
            $response['settings'] = $this->client->indices()->putSettings([
                'index' => $index,
                'body' => ['settings' => $settings],
            ]);
            $this->client->indices()->open(['index' => $index]);
        }

        if (! empty($mappings)) {
            $response['mappings'] = $this->client->indices()->putMapping([
                'index' => $index,
                'body' => $mappings,
            ]);
        }

        return $response;
    }

    /**
     * 切换 pali 同义词文件版本
     *
     * 把 pali_synonyms filter 的 synonyms_path 指向
     * analysis/pali-synonyms-{$version}.txt（文件须已存在于 OpenSearch
     * 各节点的 config 目录中，否则索引无法重新打开）。
     *
     * synonyms_path 属于静态 settings，必须 close → putSettings → open。
     * 无论 putSettings 成功与否都会尝试重新打开索引，避免索引停留在 close 状态。
     *
     * @param  string  $version  版本号，仅允许 [A-Za-z0-9._-]
     * @return array{path: string, settings: array} 新路径与 OpenSearch 响应
     *
     * @throws Exception 版本号非法、索引不存在或 OpenSearch 拒绝时抛出
     *
     * @example
     *   $service->updatePaliSynonymsPath('20260731');
     */
    public function updatePaliSynonymsPath(string $filename): array
    {

        $index = config('mint.opensearch.index');

        if (! $this->client->indices()->exists(['index' => $index])) {
            throw new Exception("Index [$index] does not exist.");
        }

        $path = Str::finish(config('mint.opensearch.config.synonyms_path'), '/').$filename;

        Log::debug('OpenSearchService::updatePaliSynonymsPath', [
            'index' => $index,
            'synonyms_path' => $path,
        ]);

        // 以代码中的 analysis 定义为准，只替换同义词文件路径
        $analysis = $this->indexDefinition()['settings']['analysis'];
        $analysis['filter']['pali_synonyms']['synonyms_path'] = $path;

        $this->client->indices()->close(['index' => $index]);

        try {
            $response = $this->client->indices()->putSettings([
                'index' => $index,
                'body' => ['settings' => ['analysis' => $analysis]],
            ]);
        } finally {
            $this->client->indices()->open(['index' => $index]);
        }

        return ['path' => $path, 'settings' => $response];
    }

    /**
     * 读取当前索引使用的 pali 同义词文件路径
     *
     * @return string|null 形如 "analysis/pali-synonyms-20260731.txt"，未设置时返回 null
     */
    public function getPaliSynonymsPath(): ?string
    {
        return $this->getPaliSynonymsSetting()['synonyms_path'] ?? null;
    }

    /**
     * 读取当前索引 pali_synonyms filter 的完整设置
     *
     * @return array{type?: string, synonyms_path?: string, updateable?: string}|null 未设置时返回 null
     */
    public function getPaliSynonymsSetting(): ?array
    {
        $index = config('mint.opensearch.index');
        $settings = $this->client->indices()->getSettings(['index' => $index]);

        return $settings[$index]['settings']['index']['analysis']['filter']['pali_synonyms'] ?? null;
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
     *                             ['exists' => ['field' => 'content.vector']]
     * @return int 文档总数
     *
     * @throws Exception
     *
     * @example
     *   $service->count();
     *   $service->count(['exists' => ['field' => 'content.vector']]);
     */
    public function count(?array $query = null): int
    {
        $index = config('mint.opensearch.index');
        $params = ['index' => $index];

        if (! empty($query)) {
            $params['body'] = ['query' => $query];
        }

        $response = $this->client->count($params);

        return (int) ($response['count'] ?? 0);
    }

    /**
     * 写入或覆盖单条文档
     *
     * @param  string  $id  文档 ID
     * @param  array  $body  文档内容，字段结构须与 mappings 一致
     * @return array OpenSearch 响应
     */
    public function create(string $id, array $body): array
    {
        return $this->client->index([
            'index' => config('mint.opensearch.index'),
            'id' => $id,
            'body' => $body,
        ]);
    }

    /**
     * 删除单条文档
     *
     * @param  string  $id  文档 ID
     * @return array OpenSearch 响应
     */
    public function delete(string $id): array
    {
        return $this->client->delete([
            'index' => config('mint.opensearch.index'),
            'id' => $id,
        ]);
    }

    /**
     * 执行高级搜索
     *
     * 支持五种搜索模式：
     *   - fuzzy       多字段模糊查询（默认），巴利文走三层排序
     *   - exact       精确匹配，使用 markdown_clean analyzer
     *   - semantic    纯语义向量搜索，需要 OpenAI embedding
     *   - hybrid      fuzzy + semantic 混合，权重由 fuzzy_ratio / semantic_ratio 控制
     *   - definition  定义查询，黑体字（bold_single/bold_multi）高权重，术语定义排前
     *
     * 支持的过滤参数：
     *   resourceType, resourceId, granularity, language, category,
     *   tags, pageRefs, relatedId, author, channel
     *
     * @param  array  $params  {
     *
     * @type string $query              搜索关键词（必填）
     * @type string $searchMode         搜索模式，默认 'fuzzy'
     * @type int $page               页码，默认 1
     * @type int $pageSize           每页条数，默认 20
     * @type string $resourceType       按资源类型过滤
     * @type string $resourceId         按资源 ID 过滤
     * @type string $granularity        按粒度过滤
     * @type string $language           按语言过滤
     * @type string $category           按分类过滤
     * @type array $tags               按标签过滤（terms）
     * @type array $pageRefs           按页码引用过滤（terms）
     * @type string $relatedId          按关联 ID 过滤
     * @type string $author             按作者过滤
     * @type string $channel            按频道过滤
     * @type array $highlight_pre_tags 高亮前置标签，默认 ['<mark>']
     * @type array $highlight_post_tags 高亮后置标签，默认 ['</mark>']
     *             }
     *
     * @return array OpenSearch 原始响应
     *
     * @throws Exception semantic / hybrid 模式下 embedding 调用失败时抛出
     */
    public function search(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['pageSize'] ?? 20;
        $from = ($page - 1) * $pageSize;
        $mode = $params['searchMode'] ?? 'fuzzy';

        // 排除字段
        if (! empty($params['excludes']) && is_array($params['excludes'])) {
            $excludes = array_merge($this->sourceExcludes, $params['excludes']);
        } else {
            $excludes = $this->sourceExcludes;
        }

        // ---------- 过滤条件 ----------
        $filters = [];

        if (! empty($params['resourceType'])) {
            $filters[] = ['term' => ['resource_type' => $params['resourceType']]];
        }

        if (! empty($params['resourceId'])) {
            $filters[] = ['term' => ['resource_id' => $params['resourceId']]];
        }

        if (! empty($params['granularity'])) {
            $filters[] = ['term' => ['granularity' => $params['granularity']]];
        }

        if (! empty($params['language'])) {
            $filters[] = ['term' => ['language' => $params['language']]];
        }

        if (! empty($params['category'])) {
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

        if (! empty($params['tags'])) {
            $filters[] = ['terms' => ['tags' => $params['tags']]];
        }

        if (! empty($params['pageRefs'])) {
            $filters[] = ['terms' => ['page_refs' => $params['pageRefs']]];
        }

        if (! empty($params['relatedId'])) {
            $filters[] = ['term' => ['related_id' => $params['relatedId']]];
        }

        if (! empty($params['author'])) {
            $filters[] = ['match' => ['metadata.author' => $params['author']]];
        }

        if (! empty($params['channel'])) {
            $filters[] = ['term' => ['metadata.channel' => $params['channel']]];
        }

        // ---------- 查询部分 ----------
        $queryText = trim($params['query'] ?? '');

        // fuzzy / definition 的巴利文多词查询按词的罕见度加权；其余模式不需要
        $weights = null;
        if ($queryText !== '' && ! in_array($mode, ['exact', 'semantic', 'hybrid'], true) && ! $this->isCjkQuery($queryText)) {
            $weights = $this->wordRarityWeights($this->scoringWords($this->splitQueryWords($queryText)));
        }

        if ($queryText === '') {
            $query = ['match_all' => new \stdClass];
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

                case 'definition':
                    $query = $this->buildDefinitionQuery($queryText, $weights);
                    break;

                case 'fuzzy':
                default:
                    $query = $this->buildFuzzyQuery($queryText, $weights);
                    break;
            }
        }

        $highlightPreTags = $params['highlight_pre_tags'] ?? ['<mark>'];
        $highlightPostTags = $params['highlight_post_tags'] ?? ['</mark>'];

        // ---------- 最终 DSL ----------
        $dsl = [
            'from' => $from,
            'size' => $pageSize,
            '_source' => ['excludes' => $excludes],
            'query' => ! empty($filters)
                ? [
                    'bool' => [
                        'must' => [$query],
                        'filter' => $filters,
                    ],
                ]
                : $query,
            'aggs' => [
                'resource_type' => [
                    'terms' => ['field' => 'resource_type'],
                ],
                'language' => [
                    'terms' => ['field' => 'language'],
                ],
                'category' => [
                    'terms' => ['field' => 'category'],
                ],
                'granularity' => [
                    'terms' => ['field' => 'granularity'],
                ],
            ],
        ];

        // 只有有搜索词时才开启高亮；字段按语种路由，避免把巴利文喂给中文分词器
        if ($queryText !== '') {
            $highlightFields = $this->isCjkQuery($queryText)
                ? ['title.text.zh', 'summary.text', 'content.text.zh']
                : ['title.text.pali', 'content.text.pali'];

            $dsl['highlight'] = [
                'fields' => array_fill_keys($highlightFields, new \stdClass),
                'fragmenter' => 'sentence',
                'fragment_size' => 200,
                'number_of_fragments' => 1,
                'pre_tags' => $highlightPreTags,
                'post_tags' => $highlightPostTags,
            ];
        }

        Log::debug(
            'OpenSearchService::search',
            ['dsl' => json_encode($dsl, JSON_UNESCAPED_UNICODE)]
        );

        return $this->client->search([
            'index' => config('mint.opensearch.index'),
            'body' => $dsl,
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
     * @return array OpenSearch DSL query 片段
     */
    protected function buildExactQuery(string $query): array
    {
        return [
            'multi_match' => [
                'query' => $query,
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
     * @return array OpenSearch DSL query 片段
     *
     * @throws Exception embedding 调用失败时抛出
     */
    protected function buildSemanticQuery(string $query): array
    {
        $vector = $this->embedText($query);

        return [
            'bool' => [
                'should' => [
                    ['knn' => ['content.vector' => ['vector' => $vector, 'k' => 20]]],
                    ['knn' => ['summary.vector' => ['vector' => $vector, 'k' => 10]]],
                    ['knn' => ['title.vector' => ['vector' => $vector, 'k' => 5]]],
                ],
                'minimum_should_match' => 1,
            ],
        ];
    }

    /**
     * 构建 fuzzy（多字段模糊）查询
     *
     * 按查询语种路由字段，避免巴利文被中文分词器切碎、中文被巴利文 analyzer 误伤：
     *   - 含汉字 → 只搜 bold_* + *.text.zh + summary.text
     *   - 巴利文（拉丁字母）→ 走三层排序（见 buildPaliFuzzyQuery）+ bold_* 提权
     *
     * @param  string  $query  搜索关键词
     * @param  array<string, float>|null  $weights  巴利文各词的罕见度权重，null 表示都按 1
     * @return array OpenSearch DSL query 片段
     */
    protected function buildFuzzyQuery(string $query, ?array $weights = null): array
    {
        if ($this->isCjkQuery($query)) {
            return [
                'multi_match' => [
                    'query' => $query,
                    'fields' => [
                        'bold_single^50',
                        'bold_multi^10',
                        'title.text.zh^3',
                        'summary.text^2',
                        'content.text.zh^1',
                    ],
                    'type' => 'best_fields',
                ],
            ];
        }

        return $this->buildPaliFuzzyQuery($query, 1.0, $weights);
    }

    /**
     * 巴利文 fuzzy 的计分表（全部是固定分，逐项累加）
     *
     * 一个文档的得分 = 命中的每一项相加，于是「部分符合」自然逐级降分：
     *   - phrase   整句按原顺序、拼写完全相同（≥2 个词才有）
     *   - bigram   相邻两词按原顺序、拼写相同，每对一次（≥3 个词才有，2 个词时与 phrase 重合）
     *   - exact    单个原始词拼写相同，每词一次
     *   - synonym  单个原始词经同义词可替代，每词一次（拼写相同的词也会命中这一项）
     *
     * 以 3 个词为例：整句原样 66 > 全部拼写相同但乱序 36 > 两词相邻拼写相同 + 一词同义 31
     * > 两词拼写相同不相邻 24 > 三词全靠同义词 6 > 一词同义 2。
     *
     * @var array{phrase: int, bigram: int, exact: int, synonym: int}
     */
    private const PALI_FUZZY_SCORES = [
        'phrase' => 20,
        'bigram' => 5,
        'exact' => 10,
        'synonym' => 2,
    ];

    /**
     * definition 的计分表（黑体字部分，每个原始词各算一次）
     *
     * 主分不分词形：同义词词典的一行就是一个词的全部变格变位，所以「任一词形」走同义词，
     * 与 v2 search-pali-wbw 把全部词形一视同仁的口径一致；原拼写只给少量加分，排在同档前面。
     *
     *   - single        该词（任一词形）是单独黑体字
     *   - single_exact  再加：拼写也相同
     *   - multi         该词（任一词形）出现在多词黑体短语里
     *   - multi_exact   再加：拼写也相同
     *   - multi_phrase  整句按原顺序、拼写相同出现在多词黑体短语里（≥2 个词才有）
     *
     * 每个词：单独黑体 ≥ 100 > 多词黑体 ≤ 35，于是命中的单独黑体字越多排越前。
     * 正文得分按 DEFINITION_TEXT_SCALE 缩小后叠加，只用来给黑体同分的文档排先后。
     *
     * @var array{single: int, single_exact: int, multi: int, multi_exact: int, multi_phrase: int}
     */
    private const DEFINITION_SCORES = [
        'single' => 100,
        'single_exact' => 10,
        'multi' => 30,
        'multi_exact' => 5,
        'multi_phrase' => 30,
    ];

    /**
     * definition 模式里正文得分的缩放系数，保证正文再怎么命中也压不过一个黑体字
     */
    private const DEFINITION_TEXT_SCALE = 0.1;

    /**
     * 同分时的 BM25 微调权重：BM25 一般 < 30，乘上后 < 0.3，小于计分表的最小档差
     */
    private const TIE_BREAKER_BOOST = 0.01;

    /**
     * 罕见度权重的下限：再常见的词也保留这么多分量，不至于完全不计
     */
    private const RARITY_WEIGHT_FLOOR = 0.2;

    /**
     * 罕见度权重的指数：IDF 取了对数，887 段与 211 段的词只差 0.82 : 1，拉不开；
     * 归一后再取幂放大差距
     */
    private const RARITY_EXPONENT = 4;

    /**
     * 每个词的文档频率在 RedisCache 里的保存期（秒）。语料按月更新，一天足够新
     */
    private const DOC_FREQUENCY_TTL = 86400;

    /**
     * 多词查询里每个词的罕见度权重（类 BM25 的 IDF，按最罕见的词归一到 1）
     *
     * 按词计的固定分让每个词分量相同，常见词（如 bojjhaṅga，到处都是黑体）会把罕见的
     * 目标词（dhammavicayasambojjhaṅga）挤出前排。乘上这个权重后，越罕见的词分越高。
     * 文档频率按 title/content 的 .pali（带同义词，即全部词形）统计，一次 _msearch 取齐，
     * 逐词缓存。取不到时返回全 1（等于不加权），只记日志不报错。
     *
     * @param  list<string>  $words  参与计分的原始词（已去掉停用词）
     * @return array<string, float> 原始词 => 权重，范围 [RARITY_WEIGHT_FLOOR, 1]
     */
    protected function wordRarityWeights(array $words): array
    {
        $uniqueWords = array_values(array_unique($words));
        $flat = array_fill_keys($uniqueWords, 1.0);

        if (count($uniqueWords) < 2) {
            return $flat;
        }

        try {
            $index = config('mint.opensearch.index');
            $total = RedisCache::remember("/opensearch/{$index}/doc-count", self::DOC_FREQUENCY_TTL, fn () => $this->count());
            $frequencies = $this->documentFrequencies($index, $uniqueWords);
        } catch (Exception $e) {
            Log::warning('opensearch word rarity weights failed', ['words' => $uniqueWords, 'error' => $e->getMessage()]);

            return $flat;
        }

        $idf = [];
        foreach ($uniqueWords as $word) {
            $df = $frequencies[$word] ?? 0;
            $idf[$word] = log(1 + ($total - $df + 0.5) / ($df + 0.5));
        }

        $maxIdf = max($idf);
        if ($maxIdf <= 0) {
            return $flat;
        }

        return array_map(
            fn (float $value): float => max(self::RARITY_WEIGHT_FLOOR, ($value / $maxIdf) ** self::RARITY_EXPONENT),
            $idf,
        );
    }

    /**
     * 每个词出现在多少个文档里（含同义词展开的全部词形），逐词缓存，未缓存的一次 _msearch 取齐
     *
     * @param  string  $index  索引名
     * @param  list<string>  $words  原始词
     * @return array<string, int> 原始词 => 文档数
     */
    protected function documentFrequencies(string $index, array $words): array
    {
        $frequencies = [];
        $missing = [];

        foreach ($words as $word) {
            $cached = RedisCache::get($this->documentFrequencyCacheKey($index, $word));
            if ($cached === null) {
                $missing[] = $word;
            } else {
                $frequencies[$word] = (int) $cached;
            }
        }

        if ($missing === []) {
            return $frequencies;
        }

        $body = [];
        foreach ($missing as $word) {
            $body[] = ['index' => $index];
            $body[] = [
                'size' => 0,
                'track_total_hits' => true,
                'query' => ['multi_match' => ['query' => $word, 'fields' => ['title.text.pali', 'content.text.pali']]],
            ];
        }

        $responses = $this->client->msearch(['body' => $body])['responses'] ?? [];

        foreach ($missing as $i => $word) {
            $df = (int) ($responses[$i]['hits']['total']['value'] ?? 0);
            $frequencies[$word] = $df;
            RedisCache::put($this->documentFrequencyCacheKey($index, $word), $df, self::DOC_FREQUENCY_TTL);
        }

        return $frequencies;
    }

    /**
     * @param  string  $index  索引名
     * @param  string  $word  原始词
     * @return string 文档频率的缓存 key
     */
    protected function documentFrequencyCacheKey(string $index, string $word): string
    {
        return "/opensearch/{$index}/word-df/".md5(mb_strtolower($word));
    }

    /**
     * 巴利文停用词（小写；比较前去掉标点）
     *
     * 取自 pg 全文检索的 pali.stop（api-v8/public/app/fts/pali.stop），另加 vā。
     * 停用词只参与词序打分（整句、相邻两词），不单独计分、不单独召回，见 buildPaliFuzzyQuery。
     *
     * @var list<string>
     */
    private const PALI_STOPWORDS = ['na', 'no', 'ca', 'va', 'vā', 'hi', 'pana', 'iva', 'eva'];

    /**
     * 判断原始词是否巴利文停用词
     *
     * @param  string  $word  原始词（可带标点，如「ca,」「‘‘na」）
     * @return bool 是停用词返回 true
     */
    protected function isPaliStopword(string $word): bool
    {
        $normalized = mb_strtolower(preg_replace('/[^\p{L}\p{M}]/u', '', $word) ?? '');

        return in_array($normalized, self::PALI_STOPWORDS, true);
    }

    /**
     * 取出用于单独计分的词：去掉停用词；全是停用词时原样返回，避免查不到东西
     *
     * @param  list<string>  $words  原始词
     * @return list<string>
     */
    protected function scoringWords(array $words): array
    {
        $contentWords = array_values(array_filter(
            $words,
            fn (string $word): bool => ! $this->isPaliStopword($word),
        ));

        return $contentWords === [] ? $words : $contentWords;
    }

    /**
     * 把查询切成原始词
     *
     * 按空白切分，并丢掉不含字母的片段（如「–」「.」）：它们经分析器后没有 token，
     * 留着会让按词计分的子句永远不命中。
     *
     * @param  string  $query  搜索关键词
     * @return list<string>
     */
    protected function splitQueryWords(string $query): array
    {
        $words = preg_split('/\s+/u', trim($query)) ?: [];

        return array_values(array_filter(
            $words,
            fn (string $word): bool => preg_match('/\p{L}/u', $word) === 1,
        ));
    }

    /**
     * 包一层 constant_score：命中即得固定分，不受 BM25 字段长度归一化影响
     *
     * @param  array<string, mixed>  $filter  过滤子句
     * @param  float  $boost  命中时的固定分
     * @return array{constant_score: array{filter: array<string, mixed>, boost: float}}
     */
    protected function constantScore(array $filter, float $boost): array
    {
        return ['constant_score' => ['filter' => $filter, 'boost' => $boost]];
    }

    /**
     * 构建巴利文查询：词序符合、拼写相同得高分，部分符合逐级降分
     *
     * 命中按「原始词」（空格切分）计，不按同义词展开后的 token 计——每个原始词一个
     * constant_score 子句，命中几个同义词都只算一次。计分表见 PALI_FUZZY_SCORES。
     * 拼写相同打在 .exact 子字段（markdown_clean，无同义词），同义词打在 .pali。
     * 最后加一条极小权重的 BM25 子句，只用来给同分文档排先后。
     *
     * 停用词（PALI_STOPWORDS）只出现在整句、相邻两词的子句里，保住词序判断；
     * 不生成单词子句，也不进 BM25 子句，于是只命中「ca」的文档不会被召回。
     * 两个停用词组成的相邻词对同理跳过。查询全是停用词时按普通词处理。
     *
     * 单词子句（拼写、同义词）乘上该词的罕见度权重（见 wordRarityWeights），
     * 常见词分量小、罕见词分量大；词序子句（整句、相邻两词）不乘。
     *
     * @param  string  $query  搜索关键词
     * @param  float  $scale  整体缩放（definition 模式用它把正文得分压到黑体字之下）
     * @param  array<string, float>|null  $weights  原始词 => 罕见度权重，null 表示都按 1
     * @return array OpenSearch DSL query 片段
     */
    protected function buildPaliFuzzyQuery(string $query, float $scale = 1.0, ?array $weights = null): array
    {
        $words = $this->splitQueryWords($query);
        $wordCount = count($words);

        if ($wordCount === 0) {
            return ['match_all' => new \stdClass];
        }

        $scoringWords = $this->scoringWords($words);
        $allStopwords = array_all($words, fn (string $word): bool => $this->isPaliStopword($word));

        $exactFields = ['title.text.pali.exact', 'content.text.pali.exact'];
        $fuzzyFields = ['title.text.pali', 'content.text.pali'];
        $scores = array_map(fn (int $score): float => $score * $scale, self::PALI_FUZZY_SCORES);

        $should = [];

        // 词序：整句原样
        if ($wordCount >= 2) {
            $should[] = $this->constantScore([
                'multi_match' => [
                    'query' => implode(' ', $words),
                    'type' => 'phrase',
                    'fields' => $exactFields,
                ],
            ], $scores['phrase']);
        }

        // 词序：相邻两词原样（部分顺序符合）
        if ($wordCount >= 3) {
            for ($i = 0; $i < $wordCount - 1; $i++) {
                $bothStopwords = $this->isPaliStopword($words[$i]) && $this->isPaliStopword($words[$i + 1]);
                if ($bothStopwords && ! $allStopwords) {
                    continue;
                }

                $should[] = $this->constantScore([
                    'multi_match' => [
                        'query' => $words[$i].' '.$words[$i + 1],
                        'type' => 'phrase',
                        'fields' => $exactFields,
                    ],
                ], $scores['bigram']);
            }
        }

        // 拼写：每个词各算一次（停用词不算），按罕见度加权
        foreach ($scoringWords as $word) {
            $weight = $weights[$word] ?? 1.0;

            $should[] = $this->constantScore([
                'multi_match' => [
                    'query' => $word,
                    'fields' => $exactFields,
                    'operator' => 'and',
                ],
            ], $scores['exact'] * $weight);

            $should[] = $this->constantScore([
                'multi_match' => [
                    'query' => $word,
                    'fields' => $fuzzyFields,
                ],
            ], $scores['synonym'] * $weight);
        }

        // 同分文档按 BM25 排先后
        $should[] = [
            'match' => [
                'content.text.pali' => [
                    'query' => implode(' ', $scoringWords),
                    'boost' => self::TIE_BREAKER_BOOST * $scale,
                ],
            ],
        ];

        return [
            'bool' => [
                'should' => $should,
            ],
        ];
    }

    /**
     * 判断查询是否含中文（CJK 统一表意文字）
     *
     * @param  string  $query  搜索关键词
     * @return bool 含汉字返回 true，否则按巴利文（拉丁字母）处理
     */
    protected function isCjkQuery(string $query): bool
    {
        return (bool) preg_match('/[\x{4E00}-\x{9FFF}\x{3400}-\x{4DBF}\x{F900}-\x{FAFF}]/u', $query);
    }

    /**
     * 构建 definition（定义查询）
     *
     * 义注里的黑体字通常就是被解释的词，所以黑体字命中决定排序，正文只做次级排序：
     *   - 每个原始词单独算：命中的单独黑体字（bold_single）越多分越高
     *   - 单独黑体字 > 多词黑体短语（bold_multi）里的词
     *   - 词形不分高下（走同义词，即该词的全部变格变位），原拼写只在同档内加少量分：
     *     bold_* 建索引用 standard，查询时显式指定 analyzer=standard 即是「拼写相同」，
     *     不指定则走带同义词的 pali_query_analyzer
     *   - 多词黑体也按任一词形算：bold_* 取自 wbw_templates.real（已拆连音），
     *     正文里粘了连音（如 vitakkanti）匹配不到的词，往往靠它召回
     *   - 用 match_phrase 而不是 match：中文在 standard 下按字切分，phrase 要求字连续，
     *     避免一个字重合就命中
     *   - 每个词的黑体分乘上它的罕见度权重（见 wordRarityWeights）：常见词（bojjhaṅga）
     *     不再与罕见的目标词（dhammavicayasambojjhaṅga）同分，挤占前排
     * 计分表见 DEFINITION_SCORES；正文部分复用 fuzzy 的计分，乘 DEFINITION_TEXT_SCALE。
     *
     * @param  string  $query  搜索关键词
     * @param  array<string, float>|null  $weights  原始词 => 罕见度权重，null 表示都按 1
     * @return array OpenSearch DSL query 片段
     */
    protected function buildDefinitionQuery(string $query, ?array $weights = null): array
    {
        $words = $this->splitQueryWords($query);

        if ($words === []) {
            return ['match_all' => new \stdClass];
        }

        $should = [];

        // 停用词不单独算黑体分（整句的 multi_phrase 仍带上它们）
        foreach ($this->scoringWords($words) as $word) {
            $weight = $weights[$word] ?? 1.0;

            foreach (['single' => 'bold_single', 'multi' => 'bold_multi'] as $tier => $field) {
                $should[] = $this->constantScore(
                    ['match_phrase' => [$field => $word]],
                    self::DEFINITION_SCORES[$tier] * $weight,
                );
                $should[] = $this->constantScore(
                    ['match_phrase' => [$field => ['query' => $word, 'analyzer' => 'standard']]],
                    self::DEFINITION_SCORES[$tier.'_exact'] * $weight,
                );
            }
        }

        if (count($words) >= 2) {
            $should[] = $this->constantScore(
                ['match_phrase' => ['bold_multi' => ['query' => implode(' ', $words), 'analyzer' => 'standard']]],
                self::DEFINITION_SCORES['multi_phrase'],
            );
        }

        // 正文：黑体同分时的次级排序，也保留没有黑体的文档（偈颂等）的召回
        if ($this->isCjkQuery($query)) {
            $should[] = [
                'multi_match' => [
                    'query' => $query,
                    'fields' => ['title.text.zh^3', 'content.text.zh'],
                    'boost' => self::DEFINITION_TEXT_SCALE,
                ],
            ];
        } else {
            $should[] = $this->buildPaliFuzzyQuery($query, self::DEFINITION_TEXT_SCALE, $weights);
        }

        return [
            'bool' => [
                'should' => $should,
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
     * @return array OpenSearch DSL query 片段
     *
     * @throws Exception embedding 调用失败时抛出
     */
    protected function buildHybridQuery(string $query): array
    {
        $fuzzyFields = [];
        foreach ($this->weights['hybrid'] as $field => $weight) {
            if (in_array($field, ['fuzzy_ratio', 'semantic_ratio'])) {
                continue;
            }
            $fuzzyFields[] = $field.'^'.$weight;
        }

        $fuzzyPart = [
            'multi_match' => [
                'query' => $query,
                'fields' => $fuzzyFields,
                'type' => 'best_fields',
            ],
        ];

        $vector = $this->embedText($query);
        $fuzzyRatio = $this->weights['hybrid']['fuzzy_ratio'];
        $semanticRatio = $this->weights['hybrid']['semantic_ratio'];

        return [
            'bool' => [
                'should' => [
                    [
                        'constant_score' => [
                            'filter' => $fuzzyPart,
                            'boost' => $fuzzyRatio,
                        ],
                    ],
                    [
                        'knn' => [
                            'content.vector' => [
                                'vector' => $vector,
                                'k' => 20,
                                'boost' => $semanticRatio * 1.0,
                            ],
                        ],
                    ],
                    [
                        'knn' => [
                            'summary.vector' => [
                                'vector' => $vector,
                                'k' => 10,
                                'boost' => $semanticRatio * 0.8,
                            ],
                        ],
                    ],
                    [
                        'knn' => [
                            'title.vector' => [
                                'vector' => $vector,
                                'k' => 5,
                                'boost' => $semanticRatio * 1.2, // title 权重略高
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
     * 缓存 key 格式见 embeddingCacheKey()。
     *
     * @param  string  $text  输入文本
     * @return array 1536 维 float 向量
     *
     * @throws Exception 未设置 OPENAI_API_KEY 或 API 返回异常时抛出
     */
    protected function embedText(string $text): array
    {
        if (! $this->openaiApiKey) {
            throw new Exception('请在 .env 设置 OPENAI_API_KEY');
        }

        return RedisCache::remember($this->embeddingCacheKey($text), now()->addDays(7), function () use ($text) {
            $response = $this->http->post('embeddings', [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->openaiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'text-embedding-3-small',
                    'input' => $text,
                ],
            ]);

            $json = json_decode((string) $response->getBody(), true);

            if (empty($json['data'][0]['embedding'])) {
                throw new Exception('OpenAI embedding 返回异常: '.json_encode($json));
            }

            return $json['data'][0]['embedding'];
        });
    }

    /**
     * embedding 缓存键："embedding/v{版本}/{md5(text)}"。
     *
     * 版本号存在 EMBEDDING_CACHE_VERSION_KEY，clearAllEmbeddingCache() 把它加一，
     * 旧版本的键就再也不会被读到，7 天后自然过期。原来用 Cache::tags()，
     * tag 索引是另外的 key，在 Redis Cluster 上跨 slot。
     */
    private function embeddingCacheKey(string $text): string
    {
        $version = RedisCache::get(self::EMBEDDING_CACHE_VERSION_KEY, 0);

        return "embedding/v{$version}/".md5($text);
    }

    /**
     * 清除指定文本的 embedding 缓存
     *
     * @param  string  $text  原始文本（与调用 embedText 时一致）
     * @return bool 缓存是否成功删除
     *
     * @example
     *   $service->clearEmbeddingCache('sabbe dhammā anattā');
     */
    public function clearEmbeddingCache(string $text): bool
    {
        return RedisCache::forget($this->embeddingCacheKey($text));
    }

    /**
     * 让所有 embedding 缓存失效
     *
     * 不逐条删除，而是把版本号加一；旧键 7 天内自然过期。
     *
     * @example
     *   $service->clearAllEmbeddingCache();
     */
    public function clearAllEmbeddingCache(): void
    {
        RedisCache::increment(self::EMBEDDING_CACHE_VERSION_KEY);
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
     * @param  string  $query  查询前缀文本
     * @param  array|string|null  $fields  要查询的字段标识符，null 表示全部字段
     * @param  string|null  $language  可选的语言过滤（term query）
     * @param  int  $limit  每个字段返回的建议数量，默认 10
     * @return array 建议结果列表，每项包含：
     *               text, source（字段标识符）, score, doc_id, doc_source
     *
     * @throws \InvalidArgumentException $fields 中含无效字段标识符时抛出
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
            'title_pali' => 'title.suggest.pali',
            'title_zh' => 'title.suggest.zh',
            'content_pali' => 'content.suggest.pali',
            'content_zh' => 'content.suggest.zh',
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
            fn ($field) => isset($fieldMap[$field])
        ));

        if (empty($searchFields)) {
            throw new \InvalidArgumentException('Invalid fields specified for suggestion');
        }

        // 构建 suggest DSL
        $suggests = [];
        foreach ($searchFields as $field) {
            $suggests[$field.'_suggest'] = [
                'prefix' => $query,
                'completion' => [
                    'field' => $fieldMap[$field],
                    'size' => $limit,
                    'skip_duplicates' => true,
                ],
            ];
        }

        $dsl = ['suggest' => $suggests];

        if ($language) {
            $dsl['query'] = ['term' => ['language' => $language]];
        }

        $response = $this->client->search([
            'index' => config('mint.opensearch.index'),
            'body' => $dsl,
        ]);

        // 整理结果，附加来源字段
        $results = [];
        foreach ($searchFields as $field) {
            $options = $response['suggest'][$field.'_suggest'][0]['options'] ?? [];

            foreach ($options as $opt) {
                $results[] = [
                    'text' => $opt['text'] ?? '',
                    'source' => $field,
                    'score' => $opt['_score'] ?? 0,
                    'doc_id' => $opt['_id'] ?? null,
                    'doc_source' => $opt['_source'] ?? null,
                ];
            }
        }

        // 按分数降序排序
        usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $results;
    }

    /**
     * 按文档 ID 获取单条完整文档（包含 content.display）
     *
     * @param  string  $id  文档 ID，例如 "term_{guid}"
     * @return array OpenSearch 原始响应
     */
    public function get(string $id): array
    {
        return $this->client->get([
            'index' => config('mint.opensearch.index'),
            'id' => $id,
        ]);
    }

    /**
     * 校验 pali_synonyms 同义词词典是否对指定词生效
     *
     * 通过 OpenSearch _analyze API，用当前索引的 pali_query_analyzer
     * 对输入文本做实时分析，返回展开后的全部 token（含原词与同义词）。
     * 可用于快速确认 analysis/pali_synonyms.txt 中的某一行是否已生效
     * （例如 dhamma,dharma,法 => dhamma 是否真的展开出 dharma、法）。
     *
     * @param  string  $text  待检测的巴利词，例如 "dhamma"
     * @return array<int, string> 分析器输出的 token 文本数组（已去重，保留原始顺序）
     *
     * @throws Exception 索引不存在或 OpenSearch 调用失败时抛出
     *
     * @example
     *   $service->pali_query_health_check('dhamma');
     *   // => ['dhamma', 'dharma', '法']
     */
    public function pali_query_health_check(string $text): array
    {
        $index = config('mint.opensearch.index');

        if (! $this->client->indices()->exists(['index' => $index])) {
            throw new Exception("Index [$index] does not exist.");
        }

        $response = $this->client->indices()->analyze([
            'index' => $index,
            'body' => [
                'analyzer' => 'pali_query_analyzer',
                'text' => $text,
            ],
        ]);

        $tokens = array_map(
            fn ($token) => $token['token'] ?? '',
            $response['tokens'] ?? []
        );

        return array_values(array_unique(array_filter($tokens, fn ($t) => $t !== '')));
    }
}
