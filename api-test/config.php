<?php

/**
 * api-test 配置：测试服务器切换 + 鉴权 token。
 *
 * 服务器切换的优先级（高 → 低）：
 *   1. 命令行 `php run.php --server=<alias|完整 base URL>`
 *   2. 环境变量 `API_TEST_BASE`（完整 base URL，覆盖 alias）
 *   3. config.local.php 的 `default_server`
 *
 * alias 对齐 openapi/public/assets/protocol/main.yaml 里的 servers。
 * 第一个 `/api` 是同源地址，CLI 客户端用不到，故略。
 *
 * 本机的账号、默认服务器、种子数据覆盖写在 config.local.php（模板
 * config.local.example.php，已 gitignore）。config.local.php 两种写法都认：
 *   - 新分组：顶层 `servers` 按 alias 分组，run.php 按实际 server 选对应那套（推荐）
 *   - 旧扁平：顶层直接写 username / token / fixtures（一套，切环境要手动改）
 *
 * token（需登录的用例用）优先级（在 run.php 里按序套用）：
 *   1. `php run.php --token=...`
 *   2. 环境变量 `API_TEST_TOKEN`
 *   3. 实际 server 对应的 `servers[alias].token`
 *   4. 都没有时，用 username/password 调 `POST /v3/sessions` 现换一个 token
 *
 * username/password 同理：--username/--password > API_TEST_USERNAME/PASSWORD > servers[alias]
 *
 * 写操作（POST/DELETE）在**所有服务器上都真实执行**，包括 prod——会产生垃圾数据，
 * 由测试账号定期清理。
 */

$local = is_file(__DIR__ . '/config.local.php') ? require __DIR__ . '/config.local.php' : [];

return [
    // alias => 完整 base URL（含 /api 前缀）
    'servers' => [
        'local'   => 'http://127.0.0.1:8000/api',
        'staging' => 'https://staging.wikipali.cc/api',
        'next'    => 'https://next.wikipali.org/api',
        'prod'    => 'https://www.wikipali.org/api',
        'next-cn' => 'https://next.wikipali.cc/api',
        'prod-cn' => 'https://www.wikipali.cc/api',
    ],
    'default_server' => $local['default_server'] ?? 'local',

    // 旧扁平结构兜底（新分组的 config.local.php 顶层没有这些键，留空即可）
    'token' => $local['token'] ?? null,
    'username' => $local['username'] ?? null,
    'password' => $local['password'] ?? null,
    'fixtures' => $local['fixtures'] ?? [],

    // 新分组结构：per-server 配置块（username/password/token/fixtures），
    // run.php 解析出实际 server 后从这里选对应那套。
    'local_servers' => $local['servers'] ?? null,

    'timeout' => 20,
];
