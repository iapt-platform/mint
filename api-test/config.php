<?php

/**
 * api-test 配置：测试服务器切换 + 鉴权 token。
 *
 * 服务器切换的优先级（高 → 低）：
 *   1. 命令行 `php run.php --server=<alias|完整 base URL>`
 *   2. 环境变量 `API_TEST_BASE`（完整 base URL，覆盖 alias）
 *   3. 本文件的 `default_server`
 *
 * alias 对齐 openapi/public/assets/protocol/main.yaml 里的 servers。
 * 第一个 `/api` 是同源地址，CLI 客户端用不到，故略。
 *
 * token（测试 /v3/me/* 用）优先级：
 *   1. `php run.php --token=...`
 *   2. 环境变量 `API_TEST_TOKEN`
 *   3. 都没有时，用下面的 username/password 调 `POST /v2/sign-in` 现换一个 token
 *
 * username/password（登录换 token 用）优先级：
 *   1. `php run.php --username=... --password=...`
 *   2. 环境变量 `API_TEST_USERNAME` / `API_TEST_PASSWORD`
 *
 * 写操作（POST/DELETE）在**所有服务器上都真实执行**，包括 prod——会产生垃圾数据，
 * 由测试账号定期清理。
 */

return [
    // alias => 完整 base URL（含 /api 前缀）
    'servers' => [
        'local'   => 'http://127.0.0.1:8000/api',
        'staging' => 'https://staging.wikipali.org/api',
        'next'    => 'https://next.wikipali.org/api',
        'prod'    => 'https://www.wikipali.org/api',
        'next-cn' => 'https://next.wikipali.cc/api',
        'prod-cn' => 'https://www.wikipali.cc/api',
    ],
    'default_server' => 'local',

    // bearer token；没有则 /v3/me/* 的“带 token”用例会 FAIL（不跳过）。
    'token' => getenv('API_TEST_TOKEN') ?: null,

    // 登录凭据：sign-in 用 username（也可以是 email）+ password。
    'username' => getenv('API_TEST_USERNAME') ?: null,
    'password' => getenv('API_TEST_PASSWORD') ?: null,

    'timeout' => 20,
];
