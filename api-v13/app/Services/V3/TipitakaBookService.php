<?php

namespace App\Services\V3;

use App\Models\BookTitle;
use App\Models\PaliText;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * 按 `pcd_book_id`（`book_titles.sn`）批量取书的基本信息：书名所在段、书名、tag、层级。
 *
 * 检索结果按书汇总时用（逐词检索的 /aggregate、页码检索），一次两条查询，不逐本查。
 */
class TipitakaBookService
{
    /** 书的 tag → layer（同 TipitakaTitleService），一本书有多个时按这个顺序取第一个 */
    private const LAYER_TAGS = ['mūla' => 'mula', 'aṭṭhakathā' => 'atthakatha', 'ṭīkā' => 'tika', 'añña' => 'anna'];

    /**
     * 结果以 book_id 为键；book_titles 里没有的书不在其中。
     *
     * @param  iterable<int>  $bookIds  pcd_book_id
     * @return Collection<int, array{book_id: int, book: int, para: int, title: string, layer: string|null, tags: list<string>}>
     */
    public function find(iterable $bookIds): Collection
    {
        $ids = collect($bookIds)->map(fn ($id) => (int) $id)->unique()->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        $titles = BookTitle::whereIn('sn', $ids)->get(['sn', 'book', 'paragraph']);
        if ($titles->isEmpty()) {
            return collect();
        }

        $heads = PaliText::query()
            ->where(function (Builder $q) use ($titles) {
                foreach ($titles as $title) {
                    $q->orWhere(fn (Builder $q) => $q->where('book', $title->book)->where('paragraph', $title->paragraph));
                }
            })
            ->with('tagMaps.tags')
            ->get(['uid', 'book', 'paragraph', 'toc'])
            ->keyBy(fn (PaliText $t) => "{$t->book}-{$t->paragraph}");

        return $titles->mapWithKeys(function (BookTitle $title) use ($heads) {
            $head = $heads->get("{$title->book}-{$title->paragraph}");
            $tags = $head ? $head->tagMaps->map(fn ($map) => $map->tags?->name)->filter()->map('strval')->values()->all() : [];
            $layer = null;
            foreach (self::LAYER_TAGS as $tag => $value) {
                if (in_array($tag, $tags, true)) {
                    $layer = $value;
                    break;
                }
            }

            return [(int) $title->sn => [
                'book_id' => (int) $title->sn,
                'book' => (int) $title->book,
                'para' => (int) $title->paragraph,
                'title' => (string) ($head?->toc ?? ''),
                'layer' => $layer,
                'tags' => $tags,
            ]];
        });
    }
}
