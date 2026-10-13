<?php

namespace App\Services;

/**
 * RedisCache 的内存实现，供测试使用（相当于 Laravel Cache 的 array store）。
 *
 * 只替换四个存取原语，序列化、TTL 换算、null 语义仍走 RedisCache 本身，
 * 所以测试覆盖到的是真实逻辑。通过 App\Facades\RedisCache::fake() 启用。
 */
class RedisCacheFake extends RedisCache
{
    /** @var array<string, array{value: string, expiresAt: int|null}> */
    protected array $items = [];

    protected function read(string $key): ?string
    {
        $item = $this->items[$key] ?? null;

        if ($item === null) {
            return null;
        }

        if ($item['expiresAt'] !== null && $item['expiresAt'] <= $this->currentTime()) {
            unset($this->items[$key]);

            return null;
        }

        return $item['value'];
    }

    protected function write(string $key, string $value, int $seconds): bool
    {
        $this->items[$key] = [
            'value' => $value,
            'expiresAt' => $this->currentTime() + $seconds,
        ];

        return true;
    }

    protected function delete(string $key): bool
    {
        $existed = $this->read($key) !== null;
        unset($this->items[$key]);

        return $existed;
    }

    protected function incrementBy(string $key, int $value): int
    {
        $current = $this->read($key);
        $next = (int) ($current ?? 0) + $value;

        $this->items[$key] = [
            'value' => (string) $next,
            'expiresAt' => $current === null ? null : $this->items[$key]['expiresAt'],
        ];

        return $next;
    }
}
