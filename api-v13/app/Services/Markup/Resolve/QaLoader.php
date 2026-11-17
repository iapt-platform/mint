<?php

namespace App\Services\Markup\Resolve;

use App\Models\Discussion;

/**
 * qa 模板：按 id 取问答话题的标题与挂载对象，一次查完。
 */
final class QaLoader extends Loader
{
    /** @var array<string, true> */
    private array $pending = [];

    /** @var array<string, Discussion|null> */
    private array $results = [];

    public function request(string $id): void
    {
        if ($id !== '' && ! array_key_exists($id, $this->results)) {
            $this->pending[$id] = true;
        }
    }

    public function result(string $id): ?Discussion
    {
        return $this->results[$id] ?? null;
    }

    public function hasPending(): bool
    {
        return $this->pending !== [];
    }

    public function load(): void
    {
        $ids = array_keys($this->pending);
        $this->pending = [];
        $found = Discussion::whereIn('id', $ids)->get(['id', 'title', 'res_id', 'res_type'])
            ->keyBy(fn (Discussion $row) => (string) $row->id);
        foreach ($ids as $id) {
            $this->results[$id] = $found->get($id);
        }
    }
}
