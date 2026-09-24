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
    $empty = ['summary' => null, 'description' => null, 'params' => [], 'validate' => [], 'resource' => null, 'collection' => false, 'tags' => ['params' => [], 'deprecated' => false, 'unauthenticated' => false, 'responses' => [], 'meta' => []]];

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

    // use 语句：短类名 → 全名。控制器里写的是 `ReactionResource::collection(...)`，
    // 只有短名，拼不出文件路径；控制器和 Resource 一旦进了子命名空间
    // （App\Http\Resources\V3\…），按平铺目录找就会静默落空、schema 变成空对象。
    $imports = [];
    foreach ($finder->findInstanceOf($ast, Node\Stmt\Use_::class) as $use) {
        foreach ($use->uses as $single) {
            $imports[$single->getAlias()->toString()] = $single->name->toString();
        }
    }

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

    // 返回的 Resource 类，以及它是 ::collection() 还是单个
    foreach ($finder->findInstanceOf($node, Node\Expr\StaticCall::class) as $call) {
        $cls = $call->class instanceof Node\Name ? $call->class->toString() : null;
        if ($cls && str_ends_with($cls, 'Resource')) {
            $info['resource'] = $imports[$cls] ?? $cls;
            $info['collection'] = $call->name instanceof Node\Identifier
                && $call->name->toString() === 'collection';
            break;
        }
    }
    if (! $info['resource']) {
        foreach ($finder->findInstanceOf($node, Node\Expr\New_::class) as $new) {
            $cls = $new->class instanceof Node\Name ? $new->class->toString() : null;
            if ($cls && str_ends_with($cls, 'Resource')) {
                $info['resource'] = $imports[$cls] ?? $cls;
                break;
            }
        }
    }

    return $methodCache[$key] = $info;
}

/**
 * 把 `Default:` / `Example:` 里写的字面量转成参数声明的那个类型。
 *
 * 文档注释里写什么都是字符串，直接吐出去会得到 `type: integer` 配 `example: '9001'`，
 * redocly 的 no-invalid-parameter-examples 会报警，生成的前端类型也对不上。
 */
function castToType(string $value, string $type): mixed
{
    return match ($type) {
        'integer' => is_numeric($value) ? (int) $value : $value,
        'number' => is_numeric($value) ? (float) $value : $value,
        'boolean' => match (strtolower($value)) {
            'true', '1' => true,
            'false', '0' => false,
            default => $value,
        },
        default => $value,
    };
}

/**
 * 解析文档注释里的结构化标签。
 *
 * 支持（Scribe 风格，位置固定）：
 *   @queryParam  name type [required] 描述。可在描述里写 Enum: a,b,c / Default: x / Example: y
 *   @bodyParam   name type [required] 描述
 *   @urlParam    name type [required] 描述
 *   @deprecated  弃用说明
 *   @unauthenticated       该端点无需登录，不挂 401
 *   @responseStatus 码 描述  额外的状态码。v3 下 2xx 视为另一种成功口径（响应体与 200
 *                          同形，如 201 Created），非 2xx 的响应体是 Problem Details
 *   @meta name type 描述     v3 列表响应的手工分页 meta 字段（如游标式阅读接口）。
 *                           声明后 meta 不再套通用 PaginationMeta，而是按声明生成专用 schema
 *
 * @return array{params: array<string, array>, meta: array<string, array>, deprecated: bool}
 */
function parseDocTags(array $tagLines): array
{
    $result = ['params' => [], 'deprecated' => false, 'unauthenticated' => false, 'responses' => [], 'meta' => []];
    foreach ($tagLines as $line) {
        if (str_starts_with($line, '@deprecated')) {
            $result['deprecated'] = true;

            continue;
        }
        if (str_starts_with($line, '@unauthenticated')) {
            $result['unauthenticated'] = true;

            continue;
        }
        // @responseStatus 503 服务停机维护中 / @responseStatus 201 首次创建
        if (preg_match('/^@responseStatus\s+(\d{3})\s*(.*)$/u', $line, $r)) {
            $result['responses'][$r[1]] = trim($r[2]) ?: '';

            continue;
        }
        // @meta name type 描述 —— 手工分页的 meta 字段
        if (preg_match('/^@meta\s+(\S+)\s+(\S+)\s*(.*)$/u', $line, $m)) {
            [, $name, $type, $rest] = $m;
            $result['meta'][$name] = [
                'type' => normalizeType($type),
                'description' => preg_replace('/^\s+|[\s.。]+$/u', '', $rest),
            ];

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
            $entry['default'] = castToType(trim($d[1]), $entry['type']);
            $rest = trim(str_replace($d[0], '', $rest));
        }
        if (preg_match('/\bExample:\s*([^\n]+)/u', $rest, $x)) {
            $example = trim($x[1]);
            // 形如 ["a","b"] 或 {...} 的示例按 JSON 解析，否则数组参数的示例
            // 会变成字符串，与 schema 的 type 对不上
            if (preg_match('/^[\[{]/', $example)) {
                $decoded = json_decode($example, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $example = $decoded;
                }
            }
            $entry['example'] = is_string($example) ? castToType($example, $entry['type']) : $example;
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
    $class = ltrim($class, '\\');
    $file = $api.'/'.str_replace(['App\\', '\\'], ['app/', '/'], $class).'.php';
    // 控制器里通常写的是短类名（new ChannelResource(...)），按全名拼不出路径，
    // 回落到 app/Http/Resources 下同名文件
    if (! is_file($file) && ! str_contains($class, '\\')) {
        $file = $api.'/app/Http/Resources/'.$class.'.php';
    }
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
        // 声明优先：toArray 上写了 @return array{...} 就用它，类型是真声明而非按字段名猜
        if ($doc = $m->getDocComment()) {
            if ($shape = parseArrayShape($doc->getText())) {
                return $shape;
            }
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

/**
 * 解析 PHPDoc 里的 `@return array{uid: string, studio: array{...}|false, progress?: float}`。
 *
 * 支持嵌套 array{}、联合类型（取第一个具体类型，含 null/false 则标 nullable）、
 * `key?:` 表示该键只在部分口径下出现。解析不出形状时返回 null，调用方回落到按字段名猜。
 *
 * @return array<string, array>|null
 */
function parseArrayShape(string $docText): ?array
{
    if (! preg_match('/@return\s+array\s*\{/s', $docText, $m, PREG_OFFSET_CAPTURE)) {
        return null;
    }
    // 去掉每行开头的 * 再找花括号，避免注释前缀干扰
    $body = preg_replace('#^\s*\*\s?#m', '', $docText);
    if (! preg_match('/@return\s+array\s*\{(.*)/s', $body, $mm)) {
        return null;
    }
    $inner = extractBraced($mm[1]);

    return $inner === null ? null : shapeToProperties($inner);
}

/** 从 `{` 之后的文本里取出配平的内容 */
function extractBraced(string $text): ?string
{
    $depth = 1;
    $out = '';
    $len = strlen($text);
    for ($i = 0; $i < $len; $i++) {
        $ch = $text[$i];
        if ($ch === '{') {
            $depth++;
        } elseif ($ch === '}') {
            $depth--;
            if ($depth === 0) {
                return $out;
            }
        }
        $out .= $ch;
    }

    return null;
}

/** 按顶层逗号切分，忽略嵌套花括号与尖括号里的逗号 */
function splitTopLevel(string $text): array
{
    $parts = [];
    $buf = '';
    $depth = 0;
    $len = strlen($text);
    for ($i = 0; $i < $len; $i++) {
        $ch = $text[$i];
        if ($ch === '{' || $ch === '<') {
            $depth++;
        } elseif ($ch === '}' || $ch === '>') {
            $depth--;
        }
        if ($ch === ',' && $depth === 0) {
            $parts[] = $buf;
            $buf = '';

            continue;
        }
        $buf .= $ch;
    }
    if (trim($buf) !== '') {
        $parts[] = $buf;
    }

    return $parts;
}

/** array shape 的内容 → OpenAPI properties */
function shapeToProperties(string $inner): array
{
    $props = [];
    foreach (splitTopLevel($inner) as $part) {
        $part = trim($part);
        if ($part === '' || ! str_contains($part, ':')) {
            continue;
        }
        [$key, $type] = explode(':', $part, 2);
        $key = trim($key);
        $optional = str_ends_with($key, '?');
        $key = rtrim($key, '?');
        if ($key === '') {
            continue;
        }
        $schema = phpTypeToSchema(trim($type));
        if ($optional) {
            $schema['description'] = trim(($schema['description'] ?? '').' 仅在部分查询口径下返回。');
        }
        $props[$key] = $schema;
    }

    return $props;
}

/** PHPDoc 类型 → OpenAPI schema */
function phpTypeToSchema(string $type): array
{
    $type = trim($type);
    $nullable = false;
    if (str_starts_with($type, '?')) {
        $nullable = true;
        $type = substr($type, 1);
    }
    // 联合类型：null / false 视为可空，取第一个具体类型
    $members = splitTopLevel(str_replace('|', ',', preg_replace('/\|/', '|', $type)));
    if (count($members) > 1) {
        $concrete = [];
        foreach ($members as $mem) {
            $mem = trim($mem);
            if (in_array(strtolower($mem), ['null', 'false', 'true'], true)) {
                $nullable = true;

                continue;
            }
            $concrete[] = $mem;
        }
        $type = $concrete ? $concrete[0] : 'mixed';
    }
    $type = trim($type);

    // 嵌套 array{...}
    if (preg_match('/^array\s*\{(.*)$/s', $type, $m)) {
        $innerText = extractBraced($m[1]);
        $schema = ['type' => 'object'];
        if ($innerText !== null && ($p = shapeToProperties($innerText))) {
            $schema['properties'] = $p;
        }
        if ($nullable) {
            $schema['nullable'] = true;
        }

        return $schema;
    }
    // 列表：array<T> / T[] / list<T>
    if (preg_match('/^(?:array|list)\s*<(.+)>$/s', $type, $m)) {
        $args = splitTopLevel($m[1]);
        $itemType = trim(end($args));
        $schema = ['type' => 'array', 'items' => phpTypeToSchema($itemType)];
        if ($nullable) {
            $schema['nullable'] = true;
        }

        return $schema;
    }
    if (str_ends_with($type, '[]')) {
        $schema = ['type' => 'array', 'items' => phpTypeToSchema(substr($type, 0, -2))];
        if ($nullable) {
            $schema['nullable'] = true;
        }

        return $schema;
    }

    $schema = match (strtolower($type)) {
        'int', 'integer' => ['type' => 'integer'],
        'float', 'double' => ['type' => 'number'],
        'bool', 'boolean' => ['type' => 'boolean'],
        'array' => ['type' => 'array', 'items' => ['type' => 'string']],
        'object', 'mixed', '' => ['type' => 'object'],
        default => ['type' => 'string'],
    };
    if ($nullable) {
        $schema['nullable'] = true;
    }

    return $schema;
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

/**
 * v3 列表响应的 meta schema。
 *
 * 默认是 Eloquent 分页的 PaginationMeta；控制器 docblock 里写了 `@meta` 标签时
 * （手工分页，如游标式阅读接口），按声明生成专用 schema，字段名与类型都不再猜测。
 *
 * @param  array  $info  parseController 的返回
 * @return array  meta 的 OpenAPI schema
 */
function metaSchema(array $info): array
{
    $meta = $info['tags']['meta'] ?? [];
    if (empty($meta)) {
        return ['$ref' => '../../main.yaml#/components/schemas/PaginationMeta'];
    }

    $properties = [];
    foreach ($meta as $name => $field) {
        $property = ['type' => $field['type']];
        if (! empty($field['description'])) {
            $property['description'] = $field['description'];
        }
        $properties[$name] = $property;
    }

    return ['type' => 'object', 'properties' => $properties];
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
    // v2 列表的通用参数（search/order/dir/limit/offset）只属于 v2；
    // v3 的分页是 page/per_page，且参数一律要在文档注释里写明，不做兜底注入
    if ($method === 'index' && $httpMethod === 'GET' && ! isV3($route['uri'])) {
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

    // 响应。v2 与 v3 的信封不同：v2 是 {ok, message, data} 且业务失败也回 200，
    // v3 用真实状态码、Laravel 默认的 {data} 包装、错误体是 RFC 9457 Problem Details
    $data = ['type' => 'object'];
    if ($props = resourceSchema($info['resource'])) {
        $data = ['type' => 'object', 'properties' => $props];
    }

    if (isV3($route['uri'])) {
        // v3 用 Laravel Resource 的原生形状：列表 {data: [...], meta: {...}}，单个 {data: {...}}
        //
        // 判断依据是控制器返回的是不是 `::collection()`，不能只看方法名叫不叫 index：
        // 单动作控制器的方法名是 __invoke，照样可以返回集合。
        $isList = $method === 'index' || ! empty($info['collection']);
        $success = $isList
            ? [
                'type' => 'object',
                'properties' => [
                    'data' => ['type' => 'array', 'items' => $data],
                    'meta' => metaSchema($info),
                ],
            ]
            : ['type' => 'object', 'properties' => ['data' => $data]];

        $responses = [
            '200' => [
                'description' => '成功',
                'content' => ['application/json' => ['schema' => $success]],
            ],
        ];
        // 无鉴权的端点用 @unauthenticated 标注，就不再挂 401
        if (empty($info['tags']['unauthenticated'])) {
            $responses['401'] = ['$ref' => '../../main.yaml#/components/responses/ProblemUnauthorized'];
        }
        // 有入参才可能 422
        if (! empty($op['parameters']) || ! empty($op['requestBody'])) {
            $responses['422'] = ['$ref' => '../../main.yaml#/components/responses/ProblemValidation'];
        }
        // @responseStatus 声明的额外状态码。2xx 是另一种成功口径（如 201 Created），
        // 响应体与 200 同形；其余是错误，响应体是 Problem Details。
        foreach ($info['tags']['responses'] ?? [] as $code => $desc) {
            $isSuccess = $code >= 200 && $code < 300;
            $responses[(string) $code] = $isSuccess
                ? [
                    'description' => $desc !== '' ? $desc : '成功',
                    'content' => ['application/json' => ['schema' => $success]],
                ]
                : [
                    'description' => $desc !== '' ? $desc : 'Problem Details',
                    'content' => ['application/problem+json' => [
                        'schema' => ['$ref' => '../../main.yaml#/components/schemas/ProblemDetails'],
                    ]],
                ];
        }
        ksort($responses);
        $op['responses'] = $responses;

        return $op;
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

/** v3 路由用新契约：真实状态码 + {data} 包装 + Problem Details */
function isV3(string $uri): bool
{
    return str_starts_with($uri, 'api/v3/');
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
    $action = (string) $route['action'];
    // 原来用「action 里有没有 @」当作「是不是控制器」，把**单动作控制器**
    // （invokable，action 就是类名、没有 @method）整条漏掉了，而且不报错。
    // buildOperation() 下游本来就有 __invoke 的兜底，这里放行即可。
    if (! str_starts_with($uri, 'api/') || $action === '' || $action === 'Closure') {
        continue;
    }
    if (! str_contains($action, '@') && ! str_contains($action, 'Controllers\\')) {
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
        // 列表值整体替换；含 $ref / oneOf / anyOf / allOf 的 schema 也整体替换，
        // 否则自动结果里的 `type: object` 会与 oneOf 并存，变成不合法 schema
        $replace = ! is_array($v)
            || array_is_list($v)
            || array_intersect(['$ref', 'oneOf', 'anyOf', 'allOf'], array_keys($v)) !== [];
        $base[$k] = (is_array($v) && isset($base[$k]) && is_array($base[$k]) && ! $replace)
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
    // 第一项是 Swagger UI 的默认选择，放同源代理，避免 Try it out 误打生产
    'servers' => [
        ['url' => '/api', 'description' => '同源（预览页里 Try it out 走 vite 代理，避免跨域）'],
        ['url' => 'http://127.0.0.1:8000/api', 'description' => '本地开发（直连，浏览器里会被 CORS 挡住）'],
        ['url' => 'https://staging.wikipali.org/api', 'description' => '测试'],
        ['url' => 'https://www.wikipali.org/api', 'description' => '生产'],
        ['url' => 'https://www.wikipali.cc/api', 'description' => '生产（中国）'],
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
            'ProblemUnauthorized' => [
                'description' => '未登录或无权限（v3）',
                'content' => ['application/problem+json' => [
                    'schema' => ['$ref' => '#/components/schemas/ProblemDetails'],
                ]],
            ],
            'ProblemValidation' => [
                'description' => '参数校验失败（v3）',
                'content' => ['application/problem+json' => [
                    'schema' => ['$ref' => '#/components/schemas/ProblemDetails'],
                ]],
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
            'ProblemDetails' => [
                'type' => 'object',
                'description' => "RFC 9457 Problem Details，v3 端点的错误响应体。\n".
                    "由 bootstrap/app.php 的异常处理器统一渲染，控制器不自己拼。\n".
                    'media type 是 application/problem+json。',
                'properties' => [
                    'type' => ['type' => 'string', 'description' => '问题类型标识，形如 urn:problem:not-found', 'example' => 'urn:problem:not-found'],
                    'title' => ['type' => 'string', 'description' => '稳定的英文摘要，同类问题恒定，供机器识别'],
                    'status' => ['type' => 'integer'],
                    'detail' => ['type' => 'string', 'description' => '面向人的文案，已本地化；5xx 在生产环境会省略以免泄露内部信息'],
                    'instance' => ['type' => 'string', 'description' => '出问题的请求 URI'],
                    'errors' => [
                        'type' => 'object',
                        'description' => '字段级校验错误，仅 422 时出现',
                        'additionalProperties' => ['type' => 'array', 'items' => ['type' => 'string']],
                    ],
                ],
                'required' => ['type', 'title', 'status'],
            ],
            'PaginationMeta' => [
                'type' => 'object',
                'description' => "v3 列表接口的分页信息，由 Laravel paginator 生成。\n".
                    "刻意不含 links / path：那些是 APP_URL 拼出的绝对地址，反代下会拼错，前端也用不到。\n".
                    '手工分页的接口会附加自己的字段（如 has_more / first_para / page_size）。',
                'properties' => [
                    'current_page' => ['type' => 'integer'],
                    'per_page' => ['type' => 'integer'],
                    'total' => ['type' => 'integer'],
                    'last_page' => ['type' => 'integer'],
                    'from' => ['type' => 'integer', 'nullable' => true],
                    'to' => ['type' => 'integer', 'nullable' => true],
                ],
                // 手工分页的接口未必有 per_page（例如按字节切页的阅读接口用 page_size）
                'required' => ['current_page', 'total'],
            ],
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
