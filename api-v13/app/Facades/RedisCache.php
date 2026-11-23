<?php

namespace App\Facades;

use App\Services\RedisCacheFake;
use Illuminate\Support\Facades\Facade;

/**
 * 业务代码用的缓存门面，取代 Illuminate\Support\Facades\Cache。
 *
 * @method static mixed get(string $key, mixed $default = null)
 * @method static bool put(string $key, mixed $value, \DateTimeInterface|\DateInterval|int|null $ttl = null)
 * @method static mixed remember(string $key, \DateTimeInterface|\DateInterval|int|null $ttl, \Closure $callback)
 * @method static bool has(string $key)
 * @method static bool forget(string $key)
 * @method static int increment(string $key, int $value = 1)
 *
 * @see \App\Services\RedisCache
 */
class RedisCache extends Facade
{
    /**
     * 换成内存实现，测试用。
     */
    public static function fake(): RedisCacheFake
    {
        static::swap($fake = new RedisCacheFake);

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return \App\Services\RedisCache::class;
    }
}
