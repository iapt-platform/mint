<?php

/**
 * 测试引导：加载生成的 Guzzle 客户端 + 注册/断言 helper。
 * 纯 PHP，无测试框架，不依赖 Laravel。
 */

require_once __DIR__ . '/../client/vendor/autoload.php';

use GuzzleHttp\Client;
use \WikipaliApi\ApiException;
use \WikipaliApi\Configuration;

final class Fail extends RuntimeException
{
}

final class Skip extends RuntimeException
{
}

$GLOBALS['__tests'] = [];

function test(string $name, callable $fn): void
{
    $GLOBALS['__tests'][] = [$name, $fn];
}

function skip(string $reason): void
{
    throw new Skip($reason);
}

function fail(string $message): void
{
    throw new Fail($message);
}

function config(): array
{
    return $GLOBALS['config'];
}

function fixtures(): array
{
    return $GLOBALS['fixtures'];
}

/** 造一个已指向目标服务器的 Configuration；传 $token 时带 bearer。 */
function makeConfig(?string $token = null): Configuration
{
    $c = new Configuration();
    $c->setHost(config()['base_url']);
    if ($token !== null) {
        $c->setAccessToken($token);
    }

    return $c;
}

/** 实例化生成的 Api 类。$class 传完整 FQCN，如 \WikipaliApi\Api\HeartbeatApi::class */
function api(string $class, ?string $token = null): object
{
    // http_errors 保持默认 true：生成的客户端靠 RequestException→ApiException 这条链
    // 把非 2xx 的 body 塞进 ApiException（尤其 204-only 的 DELETE，它没有显式状态检查，
    // 关掉 http_errors 会让错误 body 丢失）。
    $http = new Client(['timeout' => config()['timeout']]);

    return new $class($http, makeConfig($token));
}

/**
 * 统一执行一次请求，返回 ['status'=>int, 'body'=>?array, 'headers'=>array]。
 * $fn 必须返回 WithHttpInfo 的三元组 [$content, $status, $headers]。
 * 非 2xx 时生成的客户端抛 ApiException，这里捕获并解出状态码与 body。
 */
function request(callable $fn): array
{
    try {
        [$content, $status, $headers] = $fn();

        return ['status' => $status, 'body' => normalize($content), 'headers' => $headers];
    } catch (ApiException $e) {
        $status = $e->getCode();
        // 网络层错误（DNS/连接/超时）时，生成客户端把异常包成 ApiException 但 code 不是
        // 合法 HTTP 状态（可能是 0，真实 curl 错误在 message 里）；转成可读的 Fail。
        if ($status < 100 || $status > 599) {
            throw new Fail(sprintf('网络错误（服务器不可达）：%s', $e->getMessage()));
        }
        $body = json_decode((string) $e->getResponseBody(), true);

        return ['status' => $status, 'body' => is_array($body) ? $body : null, 'headers' => $e->getResponseHeaders()];
    }
}

/**
 * 原始 HTTP 请求：绕过生成客户端的必填参数校验，用于「缺参/非法组合」这类负向用例。
 * $path 以 / 开头（如 /v3/reactions），base URL 已含 /api 前缀。
 */
function raw(string $method, string $path, ?array $query = null, ?array $json = null, ?string $token = null): array
{
    $http = new Client(['timeout' => config()['timeout'], 'http_errors' => false]);
    $opts = ['headers' => []];
    if ($query !== null) {
        $opts['query'] = $query;
    }
    if ($json !== null) {
        $opts['json'] = $json;
    }
    if ($token !== null) {
        $opts['headers']['Authorization'] = 'Bearer ' . $token;
    }
    try {
        $resp = $http->request($method, config()['base_url'] . $path, $opts);
    } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        // 网络层错误（DNS 解析失败、连接拒绝、超时等）→ 转成 Fail，别让 Guzzle 异常
        // 一路冒成 fatal error。消息带上目标，方便定位是哪个服务器连不上。
        throw new Fail(sprintf('网络错误（%s %s%s）：%s', strtoupper($method), config()['base_url'], $path, $e->getMessage()));
    }
    $body = json_decode((string) $resp->getBody(), true);

    return ['status' => $resp->getStatusCode(), 'body' => is_array($body) ? $body : null, 'headers' => $resp->getHeaders()];
}

function normalize(mixed $content): mixed
{
    if ($content === null || is_array($content)) {
        return $content;
    }
    if (is_object($content)) {
        return json_decode((string) json_encode($content), true);
    }

    return $content;
}

/**
 * 调 v3 登录接口换 bearer token。
 * `POST /v3/sessions {login, password}`（login 可以是用户名或邮箱），成功 201
 * `{data:{token, user}}`；账号或密码不对 422，尝试过频 429（同账号 + IP 每分钟 5 次）。
 */
function login(string $username, string $password): string
{
    $res = raw('POST', '/v3/sessions', null, ['login' => $username, 'password' => $password]);
    $token = $res['body']['data']['token'] ?? null;
    if ($res['status'] === 201 && is_string($token) && $token !== '') {
        return $token;
    }
    fail(sprintf('登录失败（HTTP %d）：%s', $res['status'], $res['body']['detail'] ?? $res['body']['title'] ?? '未知错误'));
}

/** 取当前 token；没有就 FAIL（需登录用例不跳过，缺 token 就是失败）。 */
function required_token(): string
{
    $token = config()['token'];
    if (! $token) {
        fail('缺少 token：请用 --token= 或 --username/--password（自动调 /v3/sessions）');
    }

    return $token;
}

/* ---- 断言 ---- */

function assert_status(int $expected, array $res, string $what): void
{
    if ($res['status'] !== $expected) {
        fail(sprintf(
            '%s：期望 HTTP %d，实际 %d，body=%s',
            $what,
            $expected,
            $res['status'],
            substr((string) json_encode($res['body'], JSON_UNESCAPED_UNICODE), 0, 400)
        ));
    }
}

function assert_has_key(string $key, mixed $value, string $what): void
{
    if (! is_array($value) || ! array_key_exists($key, $value)) {
        fail(sprintf('%s：响应缺少字段 `%s`，body=%s', $what, $key, substr((string) json_encode($value, JSON_UNESCAPED_UNICODE), 0, 400)));
    }
}

function assert_eq(mixed $expected, mixed $actual, string $what): void
{
    if ($expected !== $actual) {
        fail(sprintf('%s：期望 %s，实际 %s', $what, var_export($expected, true), var_export($actual, true)));
    }
}

/** 断言失败响应是 RFC 9457 Problem Details，且 status 与 HTTP 码一致。 */
function assert_problem(array $res, string $what): void
{
    $b = $res['body'];
    if (! is_array($b) || ! isset($b['type'], $b['title'], $b['status'])) {
        fail(sprintf('%s：不是 Problem Details（缺 type/title/status），body=%s', $what, substr((string) json_encode($b, JSON_UNESCAPED_UNICODE), 0, 400)));
    }
    assert_eq($res['status'], (int) $b['status'], $what . '：Problem.status 与 HTTP 状态码不一致');
}

/** 断言 422 Problem Details 的 `errors` 里有指定字段（FormRequest / ValidationException 的字段级错误）。 */
function assert_field_error(string $field, array $res, string $what): void
{
    assert_status(422, $res, $what);
    assert_problem($res, $what);
    if (! isset($res['body']['errors'][$field])) {
        fail(sprintf('%s：errors 里缺少字段 `%s`，body=%s', $what, $field, substr((string) json_encode($res['body'], JSON_UNESCAPED_UNICODE), 0, 400)));
    }
}
