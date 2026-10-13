<?php

namespace App\Services;

use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Support\InteractsWithTime;
use RedisCluster;

/**
 * 直接操作 Redis 的缓存，取代 Laravel 原生 Cache。
 *
 * 前身是 api-v8 的 App\Tools\RedisClusters。接口与 Laravel 的 Cache 门面对齐
 * （方法名、参数、返回值、null 与 TTL 语义一致），但只实现 api-v13 真正用到的
 * get / put / remember / has / forget / increment 六个；tags、flush 这类在
 * Redis Cluster 上跨 slot 或会清空整个集群的操作故意不提供。
 *
 * 连的是 Redis Cluster，不走 Laravel 的 Redis 门面，用 phpredis 的 RedisCluster 手写连接
 * （https://github.com/phpredis/phpredis/blob/develop/cluster.md）：
 * - 连接：config('mint.redis_cluster.host') : config('mint.redis_cluster.port') 作为种子节点，
 *   其余节点由 phpredis 从集群拓扑自动发现；首次读写时才建连
 * - key：config('mint.redis_cluster.prefix') . '/' . $key（$key 已以 / 开头就不再加），
 *   不再叠加 REDIS_PREFIX / CACHE_PREFIX。所以 `foo` 与 `/foo` 是同一个 key
 * - 值：JSON。读回来对象一律是关联数组，数字是 int / float（不再是数字字符串）；
 *   整数编码后就是纯文本数字，ai-translate 用 int() 直接读 `/task/{id}/pointer` 不受影响。
 *   存不了 JSON 的值（非 UTF-8 字符串、INF/NAN）写入时抛 JsonException
 *
 * 集群与 Laravel Cache 用的单机 Redis 不是同一个库，旧缓存不迁移，切换后冷启动。
 *
 * 业务代码通过门面 App\Facades\RedisCache 调用；测试用 RedisCache::fake()
 * 换成内存实现（见 RedisCacheFake）。
 */
class RedisCache
{
    use InteractsWithTime;

    /**
     * 调用方没给 TTL（或传 null）时的默认过期秒数：30 天。不提供永久保存。
     */
    public static int $defaultTtl = 30 * 24 * 60 * 60;

    /** @var list<string> 种子节点，形如 `host:port` */
    protected array $seeds;

    protected string $prefix;

    protected ?RedisCluster $client = null;

    /**
     * @param  list<string>|null  $seeds
     */
    public function __construct(?array $seeds = null, ?string $prefix = null)
    {
        $this->seeds = $seeds ?? [config('mint.redis_cluster.host').':'.config('mint.redis_cluster.port')];
        $this->prefix = $prefix ?? (string) config('mint.redis_cluster.prefix', '');
    }

    /**
     * 读取；不存在时返回 $default（可以是闭包）。
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->read($this->key($key));

        return $value === null ? value($default) : $this->unserialize($value);
    }

    /**
     * 写入。$ttl 为 null 时用 static::$defaultTtl；算出来的秒数 <= 0 时等同 forget()。
     */
    public function put(string $key, mixed $value, DateTimeInterface|DateInterval|int|null $ttl = null): bool
    {
        $seconds = $this->secondsUntil($ttl ?? static::$defaultTtl);

        if ($seconds <= 0) {
            return $this->forget($key);
        }

        return $this->write($this->key($key), $this->serialize($value), $seconds);
    }

    /**
     * 命中就返回缓存，否则执行 $callback 并写入。
     *
     * 与 Laravel 一致：缓存值为 null 视同未命中，下次会重新计算。
     */
    public function remember(string $key, DateTimeInterface|DateInterval|int|null $ttl, Closure $callback): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $ttl);

        return $value;
    }

    /**
     * 与 Laravel 一致：值为 null 视同不存在。
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function forget(string $key): bool
    {
        return $this->delete($this->key($key));
    }

    /**
     * 原子自增（INCRBY），返回自增后的值。key 不存在时从 0 开始，且不带过期时间
     * （不套用 $defaultTtl：版本号 key 靠它永久递增，过期归零会让旧缓存重新命中）。
     */
    public function increment(string $key, int $value = 1): int
    {
        return $this->incrementBy($this->key($key), $value);
    }

    /**
     * 前缀与 key 之间用 / 隔开：`dev` + `foo` → `dev/foo`；`/task/1` 本身带 /，得 `dev/task/1`。
     */
    protected function key(string $key): string
    {
        return $this->prefix.(str_starts_with($key, '/') ? $key : '/'.$key);
    }

    /**
     * 以下四个是仅有的 Redis 存取原语，RedisCacheFake 只需覆盖它们。
     * 参数 $key 已带前缀。
     */
    protected function read(string $key): ?string
    {
        $value = $this->redis()->get($key);

        return $value === false ? null : $value;
    }

    /**
     * 一律用一条 `SET key value EX seconds`：写值与设过期原子完成。
     * phpredis 的写法是 `set($key, $value, ['EX' => $seconds])`，不是 Laravel 的 `'EX', $seconds`。
     * 不要拆成 SET + EXPIRE（api-v8 的写法）——两条之间出错会留下永不过期的 key。
     */
    protected function write(string $key, string $value, int $seconds): bool
    {
        return (bool) $this->redis()->set($key, $value, ['EX' => $seconds]);
    }

    protected function delete(string $key): bool
    {
        return (bool) $this->redis()->del($key);
    }

    protected function incrementBy(string $key, int $value): int
    {
        return (int) $this->redis()->incrby($key, $value);
    }

    /**
     * 第一个参数 null 表示不读 php.ini 里的具名集群，直接用种子节点。
     */
    protected function redis(): RedisCluster
    {
        return $this->client ??= new RedisCluster(null, $this->seeds);
    }

    /**
     * 不转义中文与斜杠，省空间也方便在 redis-cli 里直接看；1.0 保持为 1.0，读回来仍是 float。
     */
    protected function serialize(mixed $value): string
    {
        return json_encode(
            $value,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR,
        );
    }

    protected function unserialize(string $value): mixed
    {
        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }
}
