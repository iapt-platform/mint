<?php

/**
 * 从打包后的全量 OpenAPI spec 里切出仅 /v3/* 的部分，供 openapi-generator
 * 生成精简的 PHP 客户端（目前只测 /api/v3 路由）。
 *
 * 用法：
 *   php tools/filter-v3.php spec/openapi.json spec/openapi-v3.json
 *
 * 逻辑：保留 /v3/* 路径，并递归收集它们引用的 components（schemas / responses /
 * parameters / requestBodies / examples / headers / securitySchemes / links /
 * callbacks），其余组件与路径一律丢弃。
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "只允许 CLI 运行\n");
    exit(2);
}

[$script, $in, $out] = array_pad($argv, 3, null);
if (!$in || !$out || !is_file($in)) {
    fwrite(STDERR, "用法: php {$script} <in.json> <out.json>\n");
    exit(2);
}

$spec = json_decode(file_get_contents($in), true);
if (!is_array($spec) || !isset($spec['paths'])) {
    fwrite(STDERR, "无法解析 {$in}\n");
    exit(2);
}

/** 递归收集节点里所有指向 #/components/ 的 $ref */
function collectComponentRefs(mixed $node, array &$refs): void
{
    if (!is_array($node)) {
        return;
    }
    if (isset($node['$ref']) && is_string($node['$ref']) && str_starts_with($node['$ref'], '#/components/')) {
        $refs[$node['$ref']] = true;
        // $ref 节点本身不再展开（其内容在 components 里，会被下一轮收集）
        return;
    }
    foreach ($node as $value) {
        collectComponentRefs($value, $refs);
    }
}

/** 按 "#/components/{category}/{name}" 取节点 */
function resolveComponent(array $spec, string $ref): mixed
{
    $parts = explode('/', ltrim($ref, '#/'));
    // ['components', category, name]
    return $spec[$parts[0]][$parts[1]][$parts[2]] ?? null;
}

// 1) 选出 v3 路径
$v3Paths = [];
foreach ($spec['paths'] as $path => $item) {
    if (str_starts_with((string) $path, '/v3/')) {
        $v3Paths[$path] = $item;
    }
}
if ($v3Paths === []) {
    fwrite(STDERR, "spec 里没有 /v3/* 路径\n");
    exit(2);
}

// 2) 修正鉴权语义：源 spec 用全局 security 把全部路径标成 bearerAuth，
//    但 v3 实际只有 /v3/me/* 挂 auth.v3。这里逐 operation 写准，并清掉全局 security。
foreach ($v3Paths as $path => &$item) {
    foreach ($item as $method => &$op) {
        if (!is_array($op) || strtolower($method) === 'parameters') {
            continue;
        }
        $op['security'] = str_starts_with((string) $path, '/v3/me/')
            ? [['bearerAuth' => []]]
            : [];
    }
    unset($op);
}
unset($item);

// 3) 递归收集引用的组件（含 security scheme）
$refs = ['#/components/securitySchemes/bearerAuth' => true];
collectComponentRefs($v3Paths, $refs);
do {
    $before = count($refs);
    foreach (array_keys($refs) as $ref) {
        $node = resolveComponent($spec, $ref);
        if (is_array($node)) {
            collectComponentRefs($node, $refs);
        }
    }
} while (count($refs) !== $before);

// 4) 只保留被引用的组件（保留原始分类顺序）
$categories = ['schemas', 'responses', 'parameters', 'examples', 'requestBodies', 'headers', 'securitySchemes', 'links', 'callbacks'];
$components = $spec['components'] ?? [];
$keptComponents = [];
foreach ($categories as $cat) {
    if (!isset($components[$cat])) {
        continue;
    }
    foreach ($components[$cat] as $name => $node) {
        $ref = "#/components/{$cat}/{$name}";
        if (isset($refs[$ref])) {
            $keptComponents[$cat][$name] = $node;
        }
    }
}

$result = [];
foreach (['openapi', 'info', 'servers', 'tags', 'externalDocs'] as $key) {
    if (isset($spec[$key])) {
        $result[$key] = $spec[$key];
    }
}
$result['components'] = $keptComponents;
$result['paths'] = $v3Paths;

// 5) 修掉源 spec 的数组缺 items 问题：generate.php 给 query/body 参数只写了
//    `type: array` 没写 `items`（严格校验会拒绝）。v3 里的这类参数都是字符串数组
//    （page_refs / related_id），补成 string items。
normalizeArraySchemas($result);

// 6) 修掉「spec 与实现不一致」的 3 个端点（搜索/建议/升级）：实现仍返回
//    {success, data:对象} 或 {data:对象}，而 spec 写成了 {data:数组, meta}。
//    按现实修正 200 响应 schema，否则生成的客户端反序列化会抛
//    "Invalid array 'object[]'"。根因在控制器还没迁到 v3 Resource 信封，
//    正确的长期修法在 api-v13 侧，这里只是让测试客户端能用。
applyDriftPatches($result);

file_put_contents($out, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
fwrite(STDOUT, 'v3 paths: ' . count($v3Paths) . ', components refs: ' . count($refs) . " -> {$out}\n");

/** 递归给缺 items 的 `type: array` schema 补 string items */
function normalizeArraySchemas(mixed &$node): void
{
    if (! is_array($node)) {
        return;
    }
    if (($node['type'] ?? null) === 'array' && ! isset($node['items'])) {
        $node['items'] = ['type' => 'string'];
    }
    foreach ($node as &$value) {
        normalizeArraySchemas($value);
    }
    unset($value);
}

/** 修正 search / search-suggest / upgrade 的 200 响应 schema，使其与实现一致 */
function applyDriftPatches(array &$spec): void
{
    $object = ['type' => 'object'];
    $dataObject = ['type' => 'object', 'properties' => ['data' => $object]];
    $successData = ['type' => 'object', 'properties' => ['success' => ['type' => 'boolean'], 'data' => $object]];

    foreach (['get', 'post'] as $method) {
        if (isset($spec['paths']['/v3/search'][$method]['responses']['200']['content']['application/json']['schema'])) {
            $spec['paths']['/v3/search'][$method]['responses']['200']['content']['application/json']['schema'] = $successData;
        }
    }
    if (isset($spec['paths']['/v3/search-suggest']['get']['responses']['200']['content']['application/json']['schema'])) {
        $spec['paths']['/v3/search-suggest']['get']['responses']['200']['content']['application/json']['schema'] = $successData;
    }
    if (isset($spec['paths']['/v3/upgrade']['get']['responses']['200']['content']['application/json']['schema'])) {
        $spec['paths']['/v3/upgrade']['get']['responses']['200']['content']['application/json']['schema'] = $dataObject;
    }
}
