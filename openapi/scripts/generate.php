<?php

/**
 * 从 api-v13 的路由与控制器源码生成 OpenAPI 规格。
 *
 * 用法：
 *   php openapi/scripts/generate.php
 *
 * 产物：
 *   openapi/public/assets/protocol/main.yaml          入口，按 path $ref 到下面的文件
 *   openapi/public/assets/protocol/resources/auto/…   每个 path 一个文件（自动生成，勿手改）
 *
 * 手写补充放在 openapi/public/assets/protocol/overrides/<同名路径>.yaml，
 * 生成时会深度合并覆盖自动结果，因此重跑不会丢失人工撰写的描述与示例。
 */

declare(strict_types=1);

use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use Symfony\Component\Yaml\Yaml;

$root = dirname(__DIR__, 2);
$api = $root.'/api-v13';
require $api.'/vendor/autoload.php';

$protocol = dirname(__DIR__).'/public/assets/protocol';
$autoDir = $protocol.'/resources/auto';
$overrideDir = $protocol.'/overrides';

$parser = (new ParserFactory)->createForNewestSupportedVersion();
$finder = new NodeFinder;

/* ---------------------------------------------------------------- 路由 */

exec('cd '.escapeshellarg($api).' && php artisan route:list --json 2>/dev/null', $out, $code);
$routes = json_decode(implode('', $out), true);
if (! is_array($routes)) {
    fwrite(STDERR, "无法读取 php artisan route:list --json\n");
    exit(1);
}

/** 解析控制器方法，结果按 class::method 缓存 */
$methodCache = [];

function parseController(string $class, string $method): array
{
    global $api, $parser, $finder, $methodCache;

    $key = $class.'::'.$method;
    if (isset($methodCache[$key])) {
        return $methodCache[$key];
    }
    $empty = ['summary' => null, 'description' => null, 'params' => [], 'validate' => [], 'resource' => null, 'tags' => ['params' => [], 'deprecated' => false]];

    $file = $api.'/'.str_replace(['App\\', '\\'], ['app/', '/'], $class).'.php';
    if (! is_file($file)) {
        return $methodCache[$key] = $empty;
    }
    $ast = $parser->parse(file_get_contents($file));
    if (! $ast) {
        return $methodCache[$key] = $empty;
    }

    $node = null;
    foreach ($finder->findInstanceOf($ast, Node\Stmt\ClassMethod::class) as $m) {
        if ($m->name->toString() === $method) {
            $node = $m;
            break;
        }
    }
    if (! $node) {
        return $methodCache[$key] = $empty;
    }

    $info = $empty;

    // 文档注释首行作为 summary，其余作为 description；
    // @queryParam / @bodyParam / @urlParam / @deprecated 作为结构化补充
    if ($doc = $node->getDocComment()) {
        $lines = [];
        $tagLines = [];
        foreach (explode("\n", $doc->getText()) as $line) {
            $line = trim(preg_replace('#^\s*(/\*\*|\*/|\*)\s?#u', '', $line));
            if ($line === '') {
                continue;
            }
            if (str_starts_with($line, '@')) {
                $tagLines[] = $line;

                continue;
            }
            // 上一行是带标签的参数说明时，缩进的续行并入该参数的描述
            if ($tagLines && preg_match('/^@(queryParam|bodyParam|urlParam)\b/', end($tagLines))) {
                $tagLines[count($tagLines) - 1] .= ' '.$line;

                continue;
            }
            $lines[] = $line;
        }
        $info['tags'] = parseDocTags($tagLines);
        // Laravel 脚手架留下的英文样板不如按资源名生成的摘要有用
        $stubs = [
            'Display a listing of the resource.',
            'Display the specified resource.',
            'Store a newly created resource in storage.',
            'Update the specified resource in storage.',
            'Remove the specified resource from storage.',
            'Show the form for creating a new resource.',
            'Show the form for editing the specified resource.',
        ];
        $lines = array_values(array_filter($lines, fn ($l) => ! in_array($l, $stubs, true)));
        if ($lines) {
            $info['summary'] = array_shift($lines);
            $info['description'] = $lines ? implode("\n", $lines) : null;
        }
    }

    // $request->input('x', default) / query / has / filled / boolean / integer / string
    $readers = ['input', 'query', 'has', 'filled', 'boolean', 'integer', 'string', 'get', 'post'];
    foreach ($finder->findInstanceOf($node, Node\Expr\MethodCall::class) as $call) {
        if (! $call->name instanceof Node\Identifier) {
            continue;
        }
        $name = $call->name->toString();
        if (! in_array($name, $readers, true) || ! $call->args) {
            continue;
        }
        if (! ($call->var instanceof Node\Expr\Variable && $call->var->name === 'request')) {
            continue;
        }
        $arg = $call->args[0]->value ?? null;
        if (! $arg instanceof Node\Scalar\String_) {
            continue;
        }
        $param = $arg->value;
        $default = null;
        if (isset($call->args[1]) && $call->args[1]->value instanceof Node\Scalar\String_) {
            $default = $call->args[1]->value->value;
        }
        $type = match ($name) {
            'boolean' => 'boolean',
            'integer' => 'integer',
            default => 'string',
        };
        $prev = $info['params'][$param] ?? null;
        $info['params'][$param] = [
            'type' => $prev['type'] ?? $type,
            'default' => $prev['default'] ?? $default,
        ];
    }

    // switch ($request->input('x')) { case 'a': … } → 枚举值
    foreach ($finder->findInstanceOf($node, Node\Stmt\Switch_::class) as $switch) {
        $cond = $switch->cond;
        if (! $cond instanceof Node\Expr\MethodCall || ! $cond->name instanceof Node\Identifier) {
            continue;
        }
        if (! in_array($cond->name->toString(), ['input', 'query', 'get'], true)) {
            continue;
        }
        $arg = $cond->args[0]->value ?? null;
        if (! $arg instanceof Node\Scalar\String_ || ! isset($info['params'][$arg->value])) {
            continue;
        }
        $enum = [];
        foreach ($switch->cases as $case) {
            if ($case->cond instanceof Node\Scalar\String_) {
                $enum[] = $case->cond->value;
            }
        }
        if ($enum) {
            $info['params'][$arg->value]['enum'] = array_values(array_unique($enum));
        }
    }

    // $request->validate([...]) / Validator::make($request->all(), [...])
    foreach ($finder->findInstanceOf($node, Node\Expr\MethodCall::class) as $call) {
        if (! $call->name instanceof Node\Identifier || $call->name->toString() !== 'validate') {
            continue;
        }
        $arg = $call->args[0]->value ?? null;
        if ($arg instanceof Node\Expr\Array_) {
            $info['validate'] += rulesFromArray($arg);
        }
    }
    foreach ($finder->findInstanceOf($node, Node\Expr\StaticCall::class) as $call) {
        if (! $call->name instanceof Node\Identifier || $call->name->toString() !== 'make') {
            continue;
        }
        $arg = $call->args[1]->value ?? null;
        if ($arg instanceof Node\Expr\Array_) {
            $info['validate'] += rulesFromArray($arg);
        }
    }

    // 返回的 Resource 类
    foreach ($finder->findInstanceOf($node, Node\Expr\StaticCall::class) as $call) {
        $cls = $call->class instanceof Node\Name ? $call->class->toString() : null;
        if ($cls && str_ends_with($cls, 'Resource')) {
            $info['resource'] = $cls;
            break;
        }
    }
    if (! $info['resource']) {
        foreach ($finder->findInstanceOf($node, Node\Expr\New_::class) as $new) {
            $cls = $new->class instanceof Node\Name ? $new->class->toString() : null;
            if ($cls && str_ends_with($cls, 'Resource')) {
                $info['resource'] = $cls;
                break;
            }
        }
    }

    return $methodCache[$key] = $info;
}

/**
 * 解析文档注释里的结构化标签。
 *
 * 支持（Scribe 风格，位置固定）：
 *   @queryParam  name type [required] 描述。可在描述里写 Enum: a,b,c / Default: x / Example: y
 *   @bodyParam   name type [required] 描述
 *   @urlParam    name type [required] 描述
 *   @deprecated  弃用说明
 *
 * @return array{params: array<string, array>, deprecated: bool}
 */
function parseDocTags(array $tagLines): array
{
    $result = ['params' => [], 'deprecated' => false];
    foreach ($tagLines as $line) {
        if (str_starts_with($line, '@deprecated')) {
            $result['deprecated'] = true;

            continue;
        }
        if (! preg_match('/^@(queryParam|bodyParam|urlParam)\s+(\S+)\s+(\S+)\s*(.*)$/u', $line, $m)) {
            continue;
        }
        [, $tag, $name, $type, $rest] = $m;
        $rest = trim($rest);
        $required = false;
        if (preg_match('/^required\b\.?\s*/u', $rest)) {
            $required = true;
            $rest = trim(preg_replace('/^required\b\.?\s*/u', '', $rest));
        }
        $entry = [
            'in' => match ($tag) {
                'queryParam' => 'query',
                'urlParam' => 'path',
                default => 'body',
            },
            'type' => normalizeType($type),
            'required' => $required,
        ];
        if (preg_match('/\bEnum:\s*((?:(?!\bDefault:|\bExample:)[^.。\n])+)/u', $rest, $e)) {
            $entry['enum'] = array_values(array_filter(array_map('trim', explode(',', $e[1]))));
            $rest = trim(str_replace($e[0], '', $rest));
        }
        if (preg_match('/\bDefault:\s*((?:(?!\bExample:)[^.。\n])+)/u', $rest, $d)) {
            $entry['default'] = trim($d[1]);
            $rest = trim(str_replace($d[0], '', $rest));
        }
        if (preg_match('/\bExample:\s*([^\n]+)/u', $rest, $x)) {
            $entry['example'] = trim($x[1]);
            $rest = trim(str_replace($x[0], '', $rest));
        }
        // trim() 按字节裁剪会把末尾的中文字符截断成非法 UTF-8，这里用 /u 正则
        $entry['description'] = preg_replace('/^\s+|[\s.。]+$/u', '', $rest);
        $result['params'][$name] = $entry;
    }

    return $result;
}

function normalizeType(string $type): string
{
    return match (strtolower($type)) {
        'int', 'integer' => 'integer',
        'float', 'double', 'number' => 'number',
        'bool', 'boolean' => 'boolean',
        'array', 'string[]', 'int[]' => 'array',
        'object' => 'object',
        default => 'string',
    };
}

/** 验证规则数组 → [字段 => ['required'=>bool,'type'=>string,'enum'=>[]]] */
function rulesFromArray(Node\Expr\Array_ $array): array
{
    $rules = [];
    foreach ($array->items as $item) {
        if (! $item || ! $item->key instanceof Node\Scalar\String_) {
            continue;
        }
        $raw = null;
        if ($item->value instanceof Node\Scalar\String_) {
            $raw = $item->value->value;
        } elseif ($item->value instanceof Node\Expr\Array_) {
            $parts = [];
            foreach ($item->value->items as $r) {
                if ($r && $r->value instanceof Node\Scalar\String_) {
                    $parts[] = $r->value->value;
                }
            }
            $raw = implode('|', $parts);
        }
        if ($raw === null) {
            continue;
        }
        $tokens = explode('|', $raw);
        $rule = ['required' => in_array('required', $tokens, true), 'type' => 'string', 'raw' => $raw];
        foreach ($tokens as $t) {
            if ($t === 'integer' || $t === 'numeric') {
                $rule['type'] = $t === 'integer' ? 'integer' : 'number';
            } elseif ($t === 'boolean') {
                $rule['type'] = 'boolean';
            } elseif ($t === 'array') {
                $rule['type'] = 'array';
            } elseif (str_starts_with($t, 'in:')) {
                $rule['enum'] = explode(',', substr($t, 3));
            }
        }
        $rules[$item->key->value] = $rule;
    }

    return $rules;
}

/** Resource 类的 toArray 键 → schema properties */
function resourceSchema(?string $class): ?array
{
    global $api, $parser, $finder;

    if (! $class) {
        return null;
    }
    $file = $api.'/'.str_replace(['App\\', '\\'], ['app/', '/'], ltrim($class, '\\')).'.php';
    if (! is_file($file)) {
        return null;
    }
    $ast = $parser->parse(file_get_contents($file));
    if (! $ast) {
        return null;
    }
    $props = [];
    foreach ($finder->findInstanceOf($ast, Node\Stmt\ClassMethod::class) as $m) {
        if ($m->name->toString() !== 'toArray') {
            continue;
        }
        foreach ($finder->findInstanceOf($m, Node\Expr\Array_::class) as $array) {
            foreach ($array->items as $item) {
                if ($item && $item->key instanceof Node\Scalar\String_) {
                    $props[$item->key->value] = ['type' => guessType($item->key->value)];
                }
            }
        }
        // $data['x'] = …
        foreach ($finder->findInstanceOf($m, Node\Expr\Assign::class) as $assign) {
            $var = $assign->var;
            if ($var instanceof Node\Expr\ArrayDimFetch && $var->dim instanceof Node\Scalar\String_) {
                $props[$var->dim->value] = ['type' => guessType($var->dim->value)];
            }
        }
    }

    return $props ?: null;
}

function guessType(string $field): string
{
    if (str_ends_with($field, '_at') || $field === 'created' || $field === 'updated') {
        return 'string';
    }
    if (preg_match('/^(is_|has_|can_)/', $field)) {
        return 'boolean';
    }
    if (preg_match('/(_id|_count|_number|^count$|^id$|^status$|^type$|^level$|^progress$)$/', $field)) {
        return 'integer';
    }

    return 'string';
}

/* ------------------------------------------------------- 生成 operation */

$listParams = Yaml::parseFile($protocol.'/resources/list_query.yaml');

function buildOperation(array $route, string $httpMethod): array
{
    global $listParams;

    [$class, $method] = array_pad(explode('@', $route['action']), 2, '__invoke');
    $info = parseController($class, $method);
    $short = preg_replace('/^App\\\\Http\\\\Controllers\\\\/', '', $class);
    $tag = tagOf($route['uri']);

    $op = [
        'summary' => $info['summary'] ?: defaultSummary($method, $route['uri']),
        'tags' => [$tag],
        'operationId' => strtolower($httpMethod).'_'.preg_replace('/[^a-zA-Z0-9]+/', '_', $route['uri']),
        'description' => trim(($info['description'] ?? '')."\n\n实现：`{$short}@{$method}`"),
    ];

    if (! empty($info['tags']['deprecated'])) {
        $op['deprecated'] = true;
    }
    $documented = $info['tags']['params'] ?? [];

    $params = [];
    // 路径参数
    if (preg_match_all('/\{(\w+)\??\}/', $route['uri'], $m)) {
        foreach ($m[1] as $name) {
            $doc = $documented[$name] ?? null;
            $param = [
                'name' => $name,
                'in' => 'path',
                'required' => true,
                'schema' => ['type' => $doc['type'] ?? 'string'],
            ];
            if (! empty($doc['description'])) {
                $param['description'] = $doc['description'];
            }
            if (! empty($doc['enum'])) {
                $param['schema']['enum'] = $doc['enum'];
            }
            if (isset($doc['example'])) {
                $param['example'] = $doc['example'];
            }
            $params[] = $param;
        }
    }
    // 查询参数：文档注释里写明的优先，其余由控制器源码推断
    $isRead = in_array($httpMethod, ['GET', 'DELETE'], true);
    $names = array_keys($info['params']);
    foreach ($documented as $name => $doc) {
        if ($doc['in'] === 'query' && ! in_array($name, $names, true)) {
            $names[] = $name;
        }
    }
    foreach ($names as $name) {
        $p = $info['params'][$name] ?? ['type' => 'string', 'default' => null];
        $doc = $documented[$name] ?? null;
        if (preg_match('/\{'.preg_quote($name, '/').'\??\}/', $route['uri'])) {
            continue;
        }
        // 文档注释把它标成 body 参数时，不再当查询参数输出
        if ($doc && $doc['in'] !== 'query') {
            continue;
        }
        if (! $isRead && ! $doc) {
            continue;
        }
        if (! $isRead) {
            continue;
        }
        $schema = ['type' => $doc['type'] ?? $p['type']];
        $enum = $doc['enum'] ?? $p['enum'] ?? null;
        if ($enum) {
            $schema['enum'] = $enum;
        }
        $default = $doc['default'] ?? $p['default'] ?? null;
        if ($default !== null) {
            $schema['default'] = $default;
        }
        $param = ['name' => $name, 'in' => 'query', 'schema' => $schema];
        if (! empty($doc['description'])) {
            $param['description'] = $doc['description'];
        }
        if (! empty($doc['required'])) {
            $param['required'] = true;
        }
        if (isset($doc['example'])) {
            $param['example'] = $doc['example'];
        }
        $params[] = $param;
    }
    if ($method === 'index' && $httpMethod === 'GET') {
        $known = array_column($params, 'name');
        foreach ($listParams as $lp) {
            if (! in_array($lp['name'], $known, true)) {
                $params[] = $lp;
            }
        }
    }
    if ($params) {
        $op['parameters'] = $params;
    }

    // 请求体
    if (! $isRead) {
        $props = [];
        $required = [];
        foreach ($info['validate'] as $field => $rule) {
            $schema = ['type' => $rule['type'], 'description' => '规则：'.$rule['raw']];
            if (! empty($rule['enum'])) {
                $schema['enum'] = $rule['enum'];
            }
            $props[$field] = $schema;
            if ($rule['required']) {
                $required[] = $field;
            }
        }
        foreach ($info['params'] as $name => $p) {
            if (isset($props[$name])) {
                continue;
            }
            $schema = ['type' => $p['type']];
            if (! empty($p['enum'])) {
                $schema['enum'] = $p['enum'];
            }
            if ($p['default'] !== null) {
                $schema['default'] = $p['default'];
            }
            $props[$name] = $schema;
        }
        // 文档注释里写明的字段最权威，覆盖推断结果
        foreach ($documented as $name => $doc) {
            if ($doc['in'] === 'path') {
                continue;
            }
            $schema = ['type' => $doc['type']];
            if (! empty($doc['description'])) {
                $schema['description'] = $doc['description'];
            }
            if (! empty($doc['enum'])) {
                $schema['enum'] = $doc['enum'];
            }
            if (isset($doc['default'])) {
                $schema['default'] = $doc['default'];
            }
            if (isset($doc['example'])) {
                $schema['example'] = $doc['example'];
            }
            $props[$name] = $schema;
            if (! empty($doc['required']) && ! in_array($name, $required, true)) {
                $required[] = $name;
            }
        }
        if ($props) {
            $body = ['type' => 'object', 'properties' => $props];
            if ($required) {
                $body['required'] = $required;
            }
            $op['requestBody'] = [
                'required' => (bool) $required,
                'content' => ['application/json' => ['schema' => $body]],
            ];
        }
    }

    // 响应
    $data = ['type' => 'object'];
    if ($props = resourceSchema($info['resource'])) {
        $data = ['type' => 'object', 'properties' => $props];
    }
    if ($method === 'index') {
        $data = [
            'type' => 'object',
            'properties' => [
                'rows' => ['type' => 'array', 'items' => $data],
                'count' => ['type' => 'integer'],
            ],
        ];
    }
    $op['responses'] = [
        '200' => [
            'description' => '成功。业务失败时 HTTP 仍可能为 200，以 ok 字段为准。',
            'content' => ['application/json' => ['schema' => [
                'type' => 'object',
                'properties' => [
                    'ok' => ['type' => 'boolean'],
                    'message' => ['type' => 'string'],
                    'data' => $data,
                ],
            ]]],
        ],
        '401' => ['$ref' => '../../main.yaml#/components/responses/Unauthorized'],
        '422' => ['$ref' => '../../main.yaml#/components/responses/ValidationError'],
    ];

    return $op;
}

function defaultSummary(string $method, string $uri): string
{
    $res = preg_replace('#^api/(v2|v3|ops)/#', '', $uri);

    return match ($method) {
        'index' => "列出 {$res}",
        'show' => "获取单个 {$res}",
        'store' => "新建 {$res}",
        'update' => "更新 {$res}",
        'destroy' => "删除 {$res}",
        default => "{$method} {$res}",
    };
}

function tagOf(string $uri): string
{
    $path = preg_replace('#^api/(v2|v3|ops)/#', '', $uri);
    $first = explode('/', $path)[0];

    return $first === '' ? 'misc' : $first;
}

/* ----------------------------------------------------------- 遍历路由 */

$paths = [];
foreach ($routes as $route) {
    $uri = $route['uri'];
    if (! str_starts_with($uri, 'api/') || ! str_contains((string) $route['action'], '@')) {
        continue;
    }
    if (str_contains($route['action'], 'Laravel\\') || str_contains($route['uri'], 'sanctum')) {
        continue;
    }
    foreach (explode('|', $route['method']) as $httpMethod) {
        if (in_array($httpMethod, ['HEAD', 'OPTIONS'], true)) {
            continue;
        }
        $openapiPath = '/'.preg_replace('#^api/#', '', $uri);
        $openapiPath = preg_replace('/\{(\w+)\?\}/', '{$1}', $openapiPath);
        $paths[$openapiPath][strtolower($httpMethod)] = buildOperation($route, $httpMethod);
    }
}
ksort($paths);

/* --------------------------------------------------------- 合并 override */

function deepMerge(array $base, array $over): array
{
    foreach ($over as $k => $v) {
        $base[$k] = (is_array($v) && isset($base[$k]) && is_array($base[$k]) && ! array_is_list($v))
            ? deepMerge($base[$k], $v)
            : $v;
    }

    return $base;
}

function pathSlug(string $path): string
{
    return trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $path), '-') ?: 'root';
}

$overridden = 0;
foreach ($paths as $path => $ops) {
    $file = $overrideDir.'/'.pathSlug($path).'.yaml';
    if (is_file($file)) {
        $paths[$path] = deepMerge($ops, Yaml::parseFile($file) ?: []);
        $overridden++;
    }
}

/* ------------------------------------------------------------- 写文件 */

foreach (glob($autoDir.'/*.yaml') ?: [] as $stale) {
    unlink($stale);
}
@mkdir($autoDir, 0o755, true);
@mkdir($overrideDir, 0o755, true);

$refs = [];
foreach ($paths as $path => $ops) {
    $name = pathSlug($path).'.yaml';
    file_put_contents(
        $autoDir.'/'.$name,
        "# 由 openapi/scripts/generate.php 生成，请勿手改；补充写到 overrides/{$name}\n".
        Yaml::dump($ops, 12, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK)
    );
    $refs[$path] = ['$ref' => "./resources/auto/{$name}"];
}

$main = [
    'openapi' => '3.0.3',
    'info' => [
        'title' => 'MINT API',
        'description' => "International Academy Of Pali Tipitaka(国际巴利三藏学院)\n\n".
            "由 `php openapi/scripts/generate.php` 依据 api-v13 的路由与控制器源码生成。\n".
            "响应统一封装为 `{ok, message, data}`；`ok=false` 时 `message` 为错误信息。",
        'version' => date('Y.n.j'),
    ],
    'servers' => [
        ['url' => 'https://www.wikipali.org/api', 'description' => '生产'],
        ['url' => 'https://www.wikipali.cc/api', 'description' => '生产（中国）'],
        ['url' => 'https://staging.wikipali.org/api', 'description' => '测试'],
        ['url' => 'http://127.0.0.1:8000/api', 'description' => '本地开发'],
    ],
    'components' => [
        'securitySchemes' => [
            'bearerAuth' => ['type' => 'http', 'scheme' => 'bearer', 'description' => '用户 token 或 access token'],
        ],
        'responses' => [
            'Unauthorized' => [
                'description' => '未登录或无权限',
                'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Envelope']]],
            ],
            'ValidationError' => [
                'description' => '参数校验失败',
                'content' => ['application/json' => ['schema' => [
                    'type' => 'object',
                    'properties' => [
                        'message' => ['type' => 'string'],
                        'errors' => ['type' => 'object', 'additionalProperties' => ['type' => 'array', 'items' => ['type' => 'string']]],
                    ],
                ]]],
            ],
        ],
        'schemas' => [
            'Envelope' => [
                'type' => 'object',
                'properties' => [
                    'ok' => ['type' => 'boolean'],
                    'message' => ['type' => 'string'],
                    'data' => ['description' => '业务数据，形状随接口而定'],
                ],
            ],
        ],
    ],
    'security' => [['bearerAuth' => []]],
    'paths' => $refs,
];

$mainYaml = Yaml::dump($main, 8, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
// 空数组被 dump 成 {  }，而 security requirement 的值必须是数组
$mainYaml = str_replace('bearerAuth: {  }', 'bearerAuth: []', $mainYaml);
file_put_contents(
    $protocol.'/main.yaml',
    "# 由 openapi/scripts/generate.php 生成，请勿手改\n".$mainYaml
);

printf("生成 %d 个 path，%d 个 operation，%d 个 path 应用了 overrides\n",
    count($paths),
    array_sum(array_map('count', $paths)),
    $overridden
);
