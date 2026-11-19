<?php

return [
    'app' => [
        'icp_code' => env('APP_ICP_CODE', ''),
        'mps_code' => env('APP_MPS_CODE', ''),
        'jwt_secrets_key' => env('JWT_SECRETS_KEY', ''),
        /*
        |--------------------------------------------------------------------------
        | 运维接口令牌（/api/ops/*），通过 Authorization: Bearer <token> 传递
        |--------------------------------------------------------------------------
        */
        'ops_token' => env('APP_OPS_TOKEN', ''),
        /*
        |--------------------------------------------------------------------------
        | AI 对话服务（agent-poc backend）的服务密钥，通过 X-Agent-Service-Key 传递。
        | 提问前扣费（POST /api/v3/agent-charges）与研究过程入库（POST /api/v3/agent-traces）只认它；
        | 为空则这两个接口一律拒绝。
        |--------------------------------------------------------------------------
        */
        'agent_service_key' => env('AGENT_SERVICE_KEY', ''),

    ],
    'languages' => [
        'en' => 'English',
        'zh-Hans' => '简体中文',
        'zh-Hant' => '繁體中文',
        'my' => 'မြန်မာဘာသာ',
        'th' => 'ภาษาไทย',
        'si' => 'සිංහල',
        'vi' => 'Tiếng Việt',
        'lo' => 'ພາສາລາວ',
    ],
    'default_language' => 'en',

    /*
    |--------------------------------------------------------------------------
    | AI 对话积分策略（公开：GET /v3/credit-policy）
    |--------------------------------------------------------------------------
    |
    | 只放当前生效的一版，历史看 git。改规则就改这里，并把 version 改成生效日
    | （UTC，YYYYMMDD）——它会记进每笔积分流水（credit_ledger.policy_version）。
    | 「一天」一律按 UTC 算，每天 0 点（UTC）重置赠送额度。字段说明见 agent-poc 仓库 docs/credits.md。
    |
    */
    'credits' => [
        // 积分功能上线日（UTC）：从这天起的读经 / 编辑时长才换积分；结算任务补发漏掉的日子时也从这天查起
        'earn_since' => '2026-10-23',
        'policy' => [
            'version' => 20261023,
            // 每天赠送的额度（不累积到第二天）
            'daily_free' => ['guest' => 100, 'user' => 500],
            // 每次提问的价格：普通 / 深度思考
            'cost' => ['normal' => 10, 'deep' => 30],
            // 同一 IP 下所有游客每天合计最多消耗多少（防刷），null 为不限
            'guest_ip_daily_cap' => 500,
            // 登录用户读经、编辑时长换积分；daily_cap 为 null 不设上限。积分不过期
            'earn' => [
                'reading' => ['credits_per_minute' => 1, 'daily_cap' => 100],
                'editing' => ['credits_per_minute' => 5, 'daily_cap' => null],
            ],
        ],
    ],
    'library' => [
        'list_min_progress' => env('LIBRARY_LIST_MIN_PROGRESS', 0.5),
    ],
    'snowflake' => [
        'data_center_id' => env('SNOWFLAKE_DATA_CENTER_ID', 1),
        'worker_id' => env('SNOWFLAKE_WORKER_ID', 1),
        /*
        |--------------------------------------------------------------------------
        | snowflake id start date don't modify
        |--------------------------------------------------------------------------
        */
        'start' => '2021-12-22',
    ],

    'server' => [
        'rpc' => [
            'grpc' => env('GRPC_WEB_SERVER', 'http://localhost:9999'),

            'morus' => [
                'host' => env('MORUS_GRPC_HOST', 'localhost'),
                'port' => env('MORUS_GRPC_PORT', 9999),
            ],

            'lily' => [
                'host' => env('LILY_GRPC_HOST', 'localhost'),
                'port' => env('LILY_GRPC_PORT', 9000),
            ],

            'tulip' => [
                'host' => env('TULIP_GRPC_HOST', 'localhost'),
                'port' => env('TULIP_GRPC_PORT', 9990),
            ],
        ],
        'api' => [
            'default' => env('APP_API', 'http://localhost:8000/api'),
            'bamboo' => env('BAMBOO_API_HOST', env('APP_URL') . '/api'),
        ],
        'assets' => env('ASSETS_SERVER', 'localhost:9999'),

        'dashboard_base_path' => env('DASHBOARD_BASE_PATH', 'http://127.0.0.1:4000/pcd-v2026'),
        // dashboard-v6 的站点根。DASHBOARD_BASE_PATH 现在是 v4 的地址，v6 部署在它加 -v2026 的路径下，
        // 与 workspace_base_path 同一约定。v3 发出的邮件链接（找回密码等）都指向 v6，用这一项。
        // TODO v4下线后需要修改
        'dashboard_v6_base_path' => env('DASHBOARD_BASE_PATH', 'http://127.0.0.1:4000/pcd') . '-v2026',
        // TODO v4下线后需要修改
        'workspace_base_path' => env('DASHBOARD_BASE_PATH', 'http://127.0.0.1:4000/pcd') . '-v2026/workspace',

        'cdn_urls' => explode(',', env('CDN_URLS', 'https://www.wikipali.cc/downloads')),

    ],

    'attachments' => [
        'bucket_name' => [
            'temporary' => env('ATTACHMENTS_TEMPORARY_BUCKET_NAME', 'attachments-staging'),
            'permanent' => env('ATTACHMENTS_PERMANENT_BUCKET_NAME', 'attachments-staging'),
        ],
    ],

    'cache' => [
        // 这个值prod,staging无需设置
        'expire' => env('CACHE_EXPIRE', 36000),
    ],

    /**
     * 集群使用独立的配置
     * 而且不能用laravel 原生Redis, Cache 需要手写连接函数
     * https://github.com/phpredis/phpredis/blob/develop/cluster.md
     */
    'redis_cluster' => [
        'host' => env('REDIS_CLUSTER_HOST', '127.0.0.1'),
        'port' => env('REDIS_CLUSTER_PORT', '6379'),
        'prefix' => env('REDIS_CLUSTER_PREFIX', ''),
    ],
    /*
    |--------------------------------------------------------------------------
    | 另外增添的路径
    |--------------------------------------------------------------------------
    |
    |
    */
    'path' => [
        'dependence' => storage_path('depandence'),
        'palitext' => storage_path('resources/pali_html'),
        'palitext_filelist' => storage_path('resources/pali_html/filelist.csv'),
        'palicsv' => storage_path('app/tmp/pali_csv'),
        'pali_title' => storage_path('resources/pali_title'),
        'paliword' => storage_path('resources/pali_word'),
        'paliword_book' => storage_path('resources/pali_word/book'),
        'paliword_index' => storage_path('resources/pali_word/index'),
        'word_statistics' => storage_path('resources/word_statistics/data'),
        'dict_text' => storage_path('resources/dict_text'),
        'corpus' => storage_path('resources/corpus'),
    ],

    'admin' => [
        'root_uuid' => '6e12f8ea-ee4d-4e0f-a6b0-472f2d99a814',
        'robot_uuid' => '6e12f8ea-ee4d-4e0f-a6b0-472f2d99a814',
        'cs_channel' => '1e4b926d-54d7-4932-b8a6-7cdc65abd992',
    ],

    'dependence' => [
        [
            'url' => 'https://www.github.com/iapt-platform/wipali-globle',
            'path' => 'wipali-globle',
        ],
    ],

    'email' => [
        'ScheduleEmailOutputTo' => env('SCHEDULE_EMAIL_OUTPUTTO', 'kosalla1987@126.com'),
        'ScheduleEmailOutputOnFailure' => env('SCHEDULE_EMAIL_OUTPUTONFAILURE', 'kosalla1987@126.com'),
    ],

    'ai' => [
        'proxy' => env('OPENAI_PROXY', 'http://127.0.0.1:4000'),
        'assistant' => 'test161',
        'default' => 'kimi',
        'logo' => [
            'gemini' => 'gemini-color.png',
            'grok' => 'grok.png',
            'Claude' => 'claude-color.png',
            'api.openai.com' => 'openai.png',
            'qwen' => 'qwen-color.png',
            'deepseek' => 'deepseek-color.png',
        ],
    ],
    'mq' => [
        'consumer_loop_limit' => env('MQ_CONSUMER_LOOP_LIMIT', 0),
    ],
    'rabbitmq' => [
        'queues' => [
            'task' => [],
            'discussion' => [],
            'export_pali_chapter' => [],
            'export_article' => [],
            'progress' => [],
            'suggestion' => [],
            'wbw-analyses' => [],
            'ai_translate_v2' => [],
        ],
    ],
    'opensearch' => [
        'index' => 'wikipali_global_20261119',
        // 索引 settings + mappings 定义文件所在目录，文件名为「{index}.json」
        'index_definition_path' => env('OPENSEARCH_INDEX_DEFINITION_PATH', base_path('../opensearch/indices')),
        'config' => [
            'scheme' => env('OPENSEARCH_SCHEME', 'http'),
            'host' => env('OPENSEARCH_HOST', '127.0.0.1'),
            'port' => env('OPENSEARCH_PORT', 9200),
            'username' => env('OPENSEARCH_USERNAME', ''),
            'password' => env('OPENSEARCH_PASSWORD', ''),
            'ssl_verification' => env('OPENSEARCH_SSL_VERIFICATION', false),
            'synonyms_path' => env('OPENSEARCH_SYNONYMS_PATH', '/opt/opensearch/config/analysis'),
        ],

    ],
];
