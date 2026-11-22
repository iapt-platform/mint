<?php

use App\Facades\RedisCache;
use App\Services\RedisCache as RedisCacheService;

/*
| 第一部分：语义。TestCase 已把门面换成 RedisCacheFake，跑的是 RedisCache
| 本身的 JSON 序列化 / TTL / null 逻辑；TTL 与 null 语义与 Laravel Cache 逐条对齐。
*/

test('get 不存在时返回默认值，闭包默认值会被求值', function () {
    expect(RedisCache::get('missing'))->toBeNull()
        ->and(RedisCache::get('missing', []))->toBe([])
        ->and(RedisCache::get('missing', fn () => 'lazy'))->toBe('lazy');
});

test('put 后 get 原样取回数组、字符串、布尔', function () {
    RedisCache::put('arr', ['a' => 1, '中' => '文']);
    RedisCache::put('str', 'abc');
    RedisCache::put('bool', false);

    expect(RedisCache::get('arr'))->toBe(['a' => 1, '中' => '文'])
        ->and(RedisCache::get('str'))->toBe('abc')
        ->and(RedisCache::get('bool'))->toBeFalse();
});

test('JSON 往返：数字保持类型，数字字符串仍是字符串', function () {
    RedisCache::put('int', 7);
    RedisCache::put('float', 1.0);
    RedisCache::put('numeric-string', '007');

    expect(RedisCache::get('int'))->toBe(7)
        ->and(RedisCache::get('float'))->toBe(1.0)
        ->and(RedisCache::get('numeric-string'))->toBe('007');
});

test('对象读回来是关联数组', function () {
    RedisCache::put('obj', (object) ['a' => 1, 'nested' => (object) ['b' => 'ā']]);
    RedisCache::put('collection', collect([['id' => 1], ['id' => 2]]));

    expect(RedisCache::get('obj'))->toBe(['a' => 1, 'nested' => ['b' => 'ā']])
        ->and(RedisCache::get('collection'))->toBe([['id' => 1], ['id' => 2]]);
});

test('存不了 JSON 的值写入时抛异常', function () {
    RedisCache::put('bad', "\xB1\x31");
})->throws(JsonException::class);

test('put 带 TTL 到期失效', function () {
    RedisCache::put('int-ttl', 'x', 10);
    RedisCache::put('date-ttl', 'x', now()->addSeconds(10));
    RedisCache::put('interval-ttl', 'x', new DateInterval('PT10S'));

    $this->travel(11)->seconds();

    expect(RedisCache::has('int-ttl'))->toBeFalse()
        ->and(RedisCache::has('date-ttl'))->toBeFalse()
        ->and(RedisCache::has('interval-ttl'))->toBeFalse();
});

test('put / remember 不带 TTL 时按默认 30 天过期', function () {
    RedisCache::put('no-ttl', 'x');
    RedisCache::put('null-ttl', 'x', null);
    RedisCache::remember('remember-null-ttl', null, fn () => 'x');

    $this->travelTo(now()->addDays(30)->subSecond());
    expect(RedisCache::has('no-ttl'))->toBeTrue()
        ->and(RedisCache::has('null-ttl'))->toBeTrue()
        ->and(RedisCache::has('remember-null-ttl'))->toBeTrue();

    $this->travel(1)->seconds();
    expect(RedisCache::has('no-ttl'))->toBeFalse()
        ->and(RedisCache::has('null-ttl'))->toBeFalse()
        ->and(RedisCache::has('remember-null-ttl'))->toBeFalse();
});

test('put 的 TTL <= 0 等同 forget', function () {
    RedisCache::put('k', 'x');
    RedisCache::put('k', 'y', 0);

    expect(RedisCache::has('k'))->toBeFalse();
});

test('remember 命中时不执行回调', function () {
    RedisCache::put('k', 'cached');

    $value = RedisCache::remember('k', 60, fn () => throw new RuntimeException('不该执行'));

    expect($value)->toBe('cached');
});

test('remember 回调返回 null 时不算命中，下次重新计算', function () {
    $calls = 0;
    $callback = function () use (&$calls) {
        $calls++;

        return null;
    };

    RedisCache::remember('k', 60, $callback);
    RedisCache::remember('k', 60, $callback);

    expect($calls)->toBe(2);
});

test('has 对 null 值返回 false', function () {
    RedisCache::put('k', null);

    expect(RedisCache::has('k'))->toBeFalse();
});

test('forget 返回是否真的删掉了', function () {
    RedisCache::put('k', 'x');

    expect(RedisCache::forget('k'))->toBeTrue()
        ->and(RedisCache::forget('k'))->toBeFalse()
        ->and(RedisCache::has('k'))->toBeFalse();
});

test('key 带不带开头的 / 是同一个 key', function () {
    RedisCache::put('a/b', 1);
    RedisCache::put('/c', 2);

    expect(RedisCache::get('/a/b'))->toBe(1)
        ->and(RedisCache::get('c'))->toBe(2);
});

test('increment 从 0 开始累加，返回新值', function () {
    expect(RedisCache::increment('c'))->toBe(1)
        ->and(RedisCache::increment('c', 5))->toBe(6)
        ->and(RedisCache::get('c'))->toBe(6);
});

test('二进制原样往返，长度与区间读取正确', function () {
    $bytes = "\x00\xff\x80PK\x03\x04".random_bytes(1000);
    RedisCache::putBinary('/bin/a', $bytes, 60);

    expect(RedisCache::getBinary('/bin/a'))->toBe($bytes)
        ->and(RedisCache::binaryLength('/bin/a'))->toBe(strlen($bytes))
        ->and(RedisCache::getBinaryRange('/bin/a', 2, 5))->toBe(substr($bytes, 2, 4))
        ->and(RedisCache::getBinaryRange('/bin/a', 1000, 9999))->toBe(substr($bytes, 1000))
        ->and(RedisCache::getBinaryRange('/bin/a', 99999, 100000))->toBe('');
});

test('二进制 key 不存在时读到 null，TTL <= 0 等同 forget', function () {
    RedisCache::putBinary('/bin/b', 'x', 60);
    RedisCache::putBinary('/bin/b', 'y', 0);

    expect(RedisCache::getBinary('/bin/b'))->toBeNull()
        ->and(RedisCache::binaryLength('/bin/b'))->toBeNull()
        ->and(RedisCache::getBinaryRange('/bin/b', 0, 10))->toBe('');
});

test('非 UTF-8 字节用 put 存不了，必须走 putBinary', function () {
    expect(fn () => RedisCache::put('/bin/c', "\xff\xfe"))->toThrow(JsonException::class);
});

/*
| 第二部分：契约。连真实 Redis Cluster（mint.redis_cluster），证明 key 名带前缀、
| 值是 JSON、整数是纯文本（ai-translate 用 int() 读 /task/{id}/pointer）、TTL 与写值一条命令落地，
| 以及 phpredis 对不存在的 key 返回 false 时被归一成 null。集群不可用时跳过。
*/

function redisCacheTestPrefix(): string
{
    return 'pest-redis-cache';
}

function rawRedisCluster(): RedisCluster
{
    return new RedisCluster(null, [config('mint.redis_cluster.host').':'.config('mint.redis_cluster.port')]);
}

function realRedisAvailable(): bool
{
    try {
        rawRedisCluster()->get('pest-ping');

        return true;
    } catch (Throwable) {
        return false;
    }
}

afterEach(function () {
    if (! realRedisAvailable()) {
        return;
    }

    $redis = rawRedisCluster();
    foreach (['pointer', 'ttl', 'missing', 'default-ttl'] as $key) {
        $redis->del(redisCacheTestPrefix().'/'.$key);
    }
});

test('数字以纯文本落在集群里，key 带前缀', function () {
    (new RedisCacheService(prefix: redisCacheTestPrefix()))->put('pointer', 7);

    expect(rawRedisCluster()->get(redisCacheTestPrefix().'/pointer'))->toBe('7');
})->skip(fn () => ! realRedisAvailable(), '没有可用的 Redis Cluster');

test('带 TTL 写入时 key 带过期时间，读回原值', function () {
    $cache = new RedisCacheService(prefix: redisCacheTestPrefix());
    $cache->put('ttl', ['a' => 1, 'b' => 'ā'], 60);

    expect(rawRedisCluster()->get(redisCacheTestPrefix().'/ttl'))->toBe('{"a":1,"b":"ā"}')
        ->and($cache->get('ttl'))->toBe(['a' => 1, 'b' => 'ā'])
        ->and(rawRedisCluster()->ttl(redisCacheTestPrefix().'/ttl'))->toBeGreaterThan(0)->toBeLessThanOrEqual(60);
})->skip(fn () => ! realRedisAvailable(), '没有可用的 Redis Cluster');

test('不带 TTL 写入时集群里的 key 带默认 30 天过期', function () {
    (new RedisCacheService(prefix: redisCacheTestPrefix()))->put('default-ttl', 'x');

    expect(rawRedisCluster()->ttl(redisCacheTestPrefix().'/default-ttl'))
        ->toBeGreaterThan(RedisCacheService::$defaultTtl - 10)
        ->toBeLessThanOrEqual(RedisCacheService::$defaultTtl);
})->skip(fn () => ! realRedisAvailable(), '没有可用的 Redis Cluster');

test('前缀与 key 之间用 / 隔开，key 自带 / 时不重复', function () {
    $cache = new RedisCacheService(prefix: redisCacheTestPrefix());
    $cache->put('pointer', 1, 60);
    $cache->put('/ttl', 2, 60);

    expect(rawRedisCluster()->get(redisCacheTestPrefix().'/pointer'))->toBe('1')
        ->and(rawRedisCluster()->get(redisCacheTestPrefix().'/ttl'))->toBe('2')
        ->and(rawRedisCluster()->exists(redisCacheTestPrefix().'//ttl'))->toBe(0);
})->skip(fn () => ! realRedisAvailable(), '没有可用的 Redis Cluster');

test('不存在的 key 读到 null / 默认值，forget 返回 false', function () {
    $cache = new RedisCacheService(prefix: redisCacheTestPrefix());

    expect($cache->get('missing'))->toBeNull()
        ->and($cache->get('missing', 'd'))->toBe('d')
        ->and($cache->forget('missing'))->toBeFalse();
})->skip(fn () => ! realRedisAvailable(), '没有可用的 Redis Cluster');

test('二进制在集群里原样落地、带 TTL，可按区间读', function () {
    $cache = new RedisCacheService(prefix: redisCacheTestPrefix());
    $bytes = random_bytes(3 * 1024 * 1024);
    $cache->putBinary('bin', $bytes, 60);

    $chunks = '';
    for ($offset = 0; $offset < strlen($bytes); $offset += 1048576) {
        $chunks .= $cache->getBinaryRange('bin', $offset, $offset + 1048575);
    }

    expect(rawRedisCluster()->get(redisCacheTestPrefix().'/bin'))->toBe($bytes)
        ->and(rawRedisCluster()->ttl(redisCacheTestPrefix().'/bin'))->toBeGreaterThan(0)->toBeLessThanOrEqual(60)
        ->and($cache->binaryLength('bin'))->toBe(strlen($bytes))
        ->and($chunks)->toBe($bytes)
        ->and($cache->binaryLength('missing-bin'))->toBeNull();
})->skip(fn () => ! realRedisAvailable(), '没有可用的 Redis Cluster');
