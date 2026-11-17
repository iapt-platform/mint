<?php

namespace App\Services\Markup\Resolve;

use App\Models\PaliText;

/**
 * quote 模板：按 `book-para` 取段落的目录标题与路径，一次查完。
 */
final class QuoteLoader extends Loader
{
    /** @var array<string, array{0: int, 1: int}> */
    private array $pending = [];

    /** @var array<string, array{toc: string, path: mixed}|null> */
    private array $results = [];

    public function request(string $paraId): void
    {
        $parts = explode('-', $paraId);
        if (count($parts) < 2 || ! ctype_digit($parts[0]) || ! ctype_digit($parts[1])) {
            $this->results[$paraId] = null;

            return;
        }
        if (! array_key_exists($paraId, $this->results)) {
            $this->pending[$paraId] = [(int) $parts[0], (int) $parts[1]];
        }
    }

    /**
     * @return array{toc: string, path: mixed}|null
     */
    public function result(string $paraId): ?array
    {
        return $this->results[$paraId] ?? null;
    }

    public function hasPending(): bool
    {
        return $this->pending !== [];
    }

    public function load(): void
    {
        $batch = $this->pending;
        $this->pending = [];
        $rows = PaliText::query()
            ->where(function ($query) use ($batch) {
                foreach ($batch as [$book, $para]) {
                    $query->orWhere(fn ($where) => $where->where('book', $book)->where('paragraph', $para));
                }
            })
            ->get(['book', 'paragraph', 'toc', 'path'])
            ->keyBy(fn (PaliText $row) => "{$row->book}-{$row->paragraph}");
        foreach ($batch as $paraId => [$book, $para]) {
            $row = $rows->get("{$book}-{$para}");
            $this->results[$paraId] = $row === null ? null : [
                'toc' => (string) $row->toc,
                'path' => json_decode((string) $row->path, true),
            ];
        }
    }
}
