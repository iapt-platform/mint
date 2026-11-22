<?php

namespace App\Services;

use App\Facades\RedisCache;
use App\Models\Sentence;

/**
 * 阅读的内容索引（纯能力，v3 阅读与三藏下载共用）：某 channel 哪些书、哪些段有译文（句子 strlen > 0）。
 *
 * - **段号索引** `/read-index/{channel}/{book}`：这本书里有译文的段，存成连续区间；
 * - **书号索引** `/read-index/{channel}/books`：有译文的书。整 channel 重建要几百毫秒，只在
 *   「新出现一本书」或「可能少了一本书」时删。
 *
 * 只随句子失效（{@see PaliContentService::paragraphChanged()}，Sentence 的模型事件调用）：
 * 只有「有译文的段」可能变了才删，改一句译文的内容不动索引。批注不影响索引。
 * **绕过模型事件的批量写入（`Sentence::where()->update()/delete()`、`DB::table('sentences')`）要自己调
 * paragraphChanged()**，否则会读到旧索引直到过期。
 */
class ReadingIndexService
{
    /** @var array<string, list<int>> */
    private array $paragraphs = [];

    /**
     * 有译文的书号，升序。
     *
     * @return list<int>
     */
    public function books(string $channel): array
    {
        return RedisCache::remember(
            self::booksKey($channel),
            (int) config('mint.cache.read_index_ttl'),
            fn () => Sentence::where('channel_uid', $channel)
                ->where('strlen', '>', 0)
                ->distinct()
                ->orderBy('book_id')
                ->pluck('book_id')
                ->map(fn ($book) => (int) $book)
                ->all()
        );
    }

    /**
     * 这本书里有译文的段落号，升序。
     *
     * @return list<int>
     */
    public function paragraphs(string $channel, int $book): array
    {
        $key = self::paragraphsKey($channel, $book);
        if (isset($this->paragraphs[$key])) {
            return $this->paragraphs[$key];
        }
        // 存成连续区间 [[起, 止], …]：整本译完的书只有一个区间，展开成段号列表要八十多 KB
        $ranges = RedisCache::remember(
            $key,
            (int) config('mint.cache.read_index_ttl'),
            fn () => self::toRanges(Sentence::where('channel_uid', $channel)
                ->where('book_id', $book)
                ->where('strlen', '>', 0)
                ->distinct()
                ->orderBy('paragraph')
                ->pluck('paragraph')
                ->map(fn ($para) => (int) $para)
                ->all())
        );
        $paras = [];
        foreach ($ranges as [$from, $to]) {
            array_push($paras, ...range($from, $to));
        }

        return $this->paragraphs[$key] = $paras;
    }

    /**
     * 这本书「有译文的段」可能变了：删段号索引；书号索引按两个参数决定删不删。
     *
     * $mayAddBook（这本书现在有了译文）时，只有它不在已缓存的书号里才删；
     * $mayRemoveBook（删了句子、strlen 变成 0、句子搬去了别的书或 channel）时一律删，下次重建。
     */
    public function forget(string $channel, int $book, bool $mayAddBook = false, bool $mayRemoveBook = false): void
    {
        $key = self::paragraphsKey($channel, $book);
        RedisCache::forget($key);
        unset($this->paragraphs[$key]);

        if ($mayRemoveBook) {
            RedisCache::forget(self::booksKey($channel));
        } elseif ($mayAddBook) {
            $books = RedisCache::get(self::booksKey($channel));
            if (is_array($books) && ! in_array($book, $books, true)) {
                RedisCache::forget(self::booksKey($channel));
            }
        }
    }

    /**
     * 升序段号 → 连续区间。
     *
     * @param  list<int>  $paras
     * @return list<array{int, int}>
     */
    public static function toRanges(array $paras): array
    {
        $ranges = [];
        foreach ($paras as $para) {
            $last = count($ranges) - 1;
            if ($last >= 0 && $ranges[$last][1] === $para - 1) {
                $ranges[$last][1] = $para;
            } else {
                $ranges[] = [$para, $para];
            }
        }

        return $ranges;
    }

    private static function booksKey(string $channel): string
    {
        return "/read-index/{$channel}/books";
    }

    private static function paragraphsKey(string $channel, int $book): string
    {
        return "/read-index/{$channel}/{$book}";
    }
}
