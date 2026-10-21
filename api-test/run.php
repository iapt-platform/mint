<?php

/**
 * api-test 入口：装配配置 → 加载用例 → 运行 → 报告。
 *
 * 用法：
 *   php run.php [--server=<alias|URL>] [--token=<bearer>] [--username=<u> --password=<p>] [--filter=<子串>] [--list]
 *
 * 例：
 *   php run.php                          # 默认 local
 *   php run.php --server=staging
 *   php run.php --server=https://foo/api
 *   php run.php --filter=search
 *   php run.php --list                   # 列出可用服务器
 *   php run.php --username=alice --password=secret   # 调 /v3/sessions 换 token
 *
 * 退出码：0 全绿；1 有失败/错误；2 参数或加载错误。
 */

error_reporting(E_ALL & ~E_DEPRECATED);

$opts = [];
foreach (array_slice($argv, 1) as $arg) {
    if (preg_match('/^--([^=]+)(?:=(.*))?$/', $arg, $m)) {
        $opts[$m[1]] = $m[2] ?? true;
    }
}

// --list：只打印服务器清单
if (isset($opts['list'])) {
    $cfg = require __DIR__ . '/config.php';
    echo "可用服务器（--server=alias，默认 {$cfg['default_server']}）：\n";
    foreach ($cfg['servers'] as $alias => $url) {
        printf("  %-8s %s\n", $alias, $url);
    }
    echo "\n也可直接 --server=<完整 base URL>。\n";
    exit(0);
}

// 解析服务器
$config = require __DIR__ . '/config.php';
$servers = $config['servers'];
$serverArg = is_string($opts['server'] ?? null) ? $opts['server'] : null;
$envBase = getenv('API_TEST_BASE') ?: null;

// 除了 base_url，还要记下对应的 alias（$serverKey），好去 config.local.php 的
// servers[alias] 里选账号 / token / 种子数据。完整 URL 会反查 alias。
$serverKey = null;
if ($envBase !== null) {
    $config['base_url'] = rtrim($envBase, '/');
    $serverKey = array_search($config['base_url'], $servers, true) ?: null;
} elseif ($serverArg !== null && isset($servers[$serverArg])) {
    $config['base_url'] = $servers[$serverArg];
    $serverKey = $serverArg;
} elseif ($serverArg !== null && preg_match('#^https?://#', $serverArg)) {
    $config['base_url'] = rtrim($serverArg, '/');
    $serverKey = array_search($config['base_url'], $servers, true) ?: null;
} elseif (isset($servers[$config['default_server']])) {
    $config['base_url'] = $servers[$config['default_server']];
    $serverKey = $config['default_server'];
} else {
    // config.local.php 的 default_server 也可以直接写完整 base URL
    $config['base_url'] = rtrim($config['default_server'], '/');
    $serverKey = array_search($config['base_url'], $servers, true) ?: null;
}

// 按实际 server 选 config.local.php 的 per-server 块（账号 / token / 种子数据跟着 server 走）
$localServers = $config['local_servers'] ?? null;
if (is_array($localServers) && $serverKey !== null && isset($localServers[$serverKey])) {
    $block = $localServers[$serverKey];
    $config['token'] = $block['token'] ?? $config['token'];
    $config['username'] = $block['username'] ?? $config['username'];
    $config['password'] = $block['password'] ?? $config['password'];
    $config['fixtures'] = $block['fixtures'] ?? $config['fixtures'];
}

// 环境变量覆盖（高于 config.local.php，低于命令行）
$config['token'] = getenv('API_TEST_TOKEN') ?: $config['token'];
$config['username'] = getenv('API_TEST_USERNAME') ?: $config['username'];
$config['password'] = getenv('API_TEST_PASSWORD') ?: $config['password'];

// 命令行覆盖（最高优先级）
if (is_string($opts['token'] ?? null) && $opts['token'] !== '') {
    $config['token'] = $opts['token'];
}
if (is_string($opts['username'] ?? null) && $opts['username'] !== '') {
    $config['username'] = $opts['username'];
}
if (is_string($opts['password'] ?? null) && $opts['password'] !== '') {
    $config['password'] = $opts['password'];
}

$GLOBALS['config'] = $config;
$GLOBALS['fixtures'] = array_replace(require __DIR__ . '/fixtures.php', $config['fixtures']);

require __DIR__ . '/src/bootstrap.php';

// 没给 token 但给了账号密码 → 调 /v3/sessions 现换一个
if (empty($config['token']) && $config['username'] && $config['password']) {
    try {
        $config['token'] = login($config['username'], $config['password']);
        $GLOBALS['config']['token'] = $config['token'];
        echo "已用 {$config['username']} 登录（/v3/sessions）换取 token\n\n";
    } catch (Fail $e) {
        echo "登录失败：{$e->getMessage()}（需登录的用例将 FAIL）\n\n";
    }
}

// 加载用例
$caseFiles = glob(__DIR__ . '/cases/*.php') ?: [];
sort($caseFiles);
foreach ($caseFiles as $file) {
    require $file;
}

$tests = $GLOBALS['__tests'];
$filter = is_string($opts['filter'] ?? null) ? $opts['filter'] : null;

$pass = $fail = $skip = $error = 0;
$start = microtime(true);

printf("目标服务器: %s\n\n", $config['base_url']);

foreach ($tests as [$name, $fn]) {
    if ($filter !== null && ! str_contains($name, $filter)) {
        continue;
    }
    try {
        $fn();
        $pass++;
        echo "  ✅ PASS  {$name}\n";
    } catch (Skip $e) {
        $skip++;
        echo "  ⏭️  SKIP  {$name}  ({$e->getMessage()})\n";
    } catch (Fail $e) {
        $fail++;
        echo "  ❌ FAIL  {$name}\n        {$e->getMessage()}\n";
    } catch (Throwable $e) {
        $error++;
        echo "  💥 ERROR {$name}\n        " . get_class($e) . ': ' . $e->getMessage() . "\n";
    }
}

$total = $pass + $fail + $skip + $error;
$elapsed = round(microtime(true) - $start, 2);
printf("\n%d 用例：%d 通过 / %d 失败 / %d 跳过 / %d 错误（%.2fs）\n", $total, $pass, $fail, $skip, $error, $elapsed);

exit($fail === 0 && $error === 0 ? 0 : 1);
