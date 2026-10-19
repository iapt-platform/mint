<?php

/**
 * api-test 本机配置模板。复制成 config.local.php 再填（已 gitignore，不要提交）：
 *
 *   cp config.local.example.php config.local.php
 *
 * 切 server 两种方式（账号 / 种子数据都会跟着走）：
 *   1. 改 `$server` 默认值；
 *   2. `php run.php --server=<alias>` 临时切，alias 见 config.php 的 servers 列表。
 * 每个 server 一套账号 + 种子数据，集中列在 `$servers`，互不干扰。
 * 优先级：命令行参数 > 环境变量（API_TEST_*）> 本文件 > config.php 默认值。
 * 不需要的键删掉或留 null 即可。
 */

$server = 'local';   // local | staging | next | prod | next-cn | prod-cn

$servers = [
    'local' => [
        'username' => 'test',
        'password' => null,
        'token' => null,                       // 填了就直接用，跳过登录
        'fixtures' => [
            // 讨论写用例挂的句子：必须在测试账号自己的 channel 里、由测试账号编辑过
            // 'discussion_sentence' => 'ee5259be-5d1e-4408-a2a6-566a5773eef1',
            // discussion_sentence 所在的 channel（agent 授权的范围）
            // 'discussion_channel' => '1721b7fa-e471-46fd-b860-64b9e79d370e',
            // agent 代写用例：归测试账号所有的 AI 模型 uid；不给则 SKIP
            // 'agent_model' => null,
            // email-certifications「已注册邮箱」用例；不给则 SKIP
            // 'registered_email' => 'someone@example.com',
        ],
    ],

    'staging' => [
        'username' => null,
        'password' => null,
        'token' => null,
        'fixtures' => [
            // 'discussion_sentence' => '',
            // 'discussion_channel' => '',
            // 'agent_model' => null,
            // 'registered_email' => null,
        ],
    ],

    'next' => [
        'username' => null,
        'password' => null,
        'token' => null,
        'fixtures' => [],
    ],

    'prod' => [
        'username' => null,
        'password' => null,
        'token' => null,
        'fixtures' => [],
    ],

    'next-cn' => [
        'username' => null,
        'password' => null,
        'token' => null,
        'fixtures' => [],
    ],

    'prod-cn' => [
        'username' => null,
        'password' => null,
        'token' => null,
        'fixtures' => [],
    ],
];

if (! isset($servers[$server])) {
    throw new RuntimeException(
        'config.local.php: 未知 server "' . $server . '"，可选：' . implode(' / ', array_keys($servers))
    );
}

return [
    'default_server' => $server,
    'servers' => $servers,
];
