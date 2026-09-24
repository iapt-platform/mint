<?php

namespace App\Services\V3;

use App\Models\PaliText;
use App\Models\Sentence;
use App\Services\PaliContentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

/**
 * 三藏译文的阅读与下载：按 channel 取已翻译的段落，游标式分块。
 *
 * 服务 `GET /v3/tipitaka-reading/{channel}`。控制器只负责把 validated() 传进来、
 * 把结果包成 Resource；这里是全部业务逻辑——过滤收窄、章节展开、keyset 游标、
 * 字节切块、进度统计、输出裁剪。
 *
 * 渲染本身交给 {@see PaliContentService}（v2/v3 共享的纯能力），这里不重复实现。
 */
class TipitakaReadingService
{
    /**
     * 字节模式下单块的字节上限。请求超过这个数按这个数算，不报错。
     */
    public const MAX_BYTES_PER_PAGE = 5000;

    /**
     * 单块最多返回的段落数，两种单位都受它约束。
     *
     * 每段要单独渲染一次，块的成本跟段落数成正比而不是跟字节数成正比。段落模式
     * 直接把 page_size 压到这个数；字节模式下偈颂体的书平均每段只有 20~30 字节，
     * 光靠字节上限仍可能切出几百段，所以累加时两个上限谁先到就在哪断块。
     */
    public const MAX_PARAGRAPHS_PER_PAGE = 200;

    public function __construct(private readonly PaliContentService $paliService) {}

    /**
     * 取一块译文。
     *
     * @param  array<string, mixed>  $filters  已经过 FormRequest 校验的查询参数
     * @return array{items: array<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    public function page(string $channel, array $filters): array
    {
        $unit = $filters['unit'] ?? 'para';
        $pageSize = min(
            max(1, (int) ($filters['page_size'] ?? 10)),
            $unit === 'byte' ? self::MAX_BYTES_PER_PAGE : self::MAX_PARAGRAPHS_PER_PAGE
        );

        $scope = $this->scopedQuery($channel, $filters);

        // 多取一行用来判断后面还有没有，不必再查一次
        $want = $unit === 'byte'
            ? self::MAX_PARAGRAPHS_PER_PAGE
            : min($pageSize, self::MAX_PARAGRAPHS_PER_PAGE);

        $candidates = (clone $scope)
            ->when($filters['after'] ?? null, fn (Builder $q, string $after) => $this->applyCursor($q, $after))
            ->distinct()
            ->select(['book_id', 'paragraph'])
            ->orderBy('book_id')
            ->orderBy('paragraph')
            ->limit($want + 1)
            ->get()
            ->map(fn ($row) => ['book' => (int) $row->book_id, 'para' => (int) $row->paragraph])
            ->all();

        $hasMoreBeyondWant = count($candidates) > $want;
        $candidates = array_slice($candidates, 0, $want);

        $slice = $unit === 'byte'
            ? $this->sliceByBytes($candidates, $this->paragraphLengths($candidates), $pageSize)
            : $candidates;
        $hasMore = $hasMoreBeyondWant || count($slice) < count($candidates);

        $format = $filters['format'] ?? 'html';
        $include = $filters['include'] ?? 'display';

        $items = [];
        foreach ($slice as $coord) {
            $paragraph = $this->paliService->readParagraph($coord['book'], $coord['para'], $channel, $format);
            if (empty($paragraph['display'])) {
                continue;
            }
            // 结果可以跨书，所以每条都要带 book——只给 para 在跨书时不唯一
            $items[] = ['book' => $coord['book']] + $this->filterInclude($paragraph, $include);
        }

        $last = $slice === [] ? null : $slice[count($slice) - 1];

        return [
            'items' => $items,
            'meta' => [
                'page_size' => $pageSize,
                'page_size_unit' => $unit,
                // 本块覆盖的最后一段。渲染为空的段落会被剔出 data 但仍算在块内，
                // 所以游标按 slice 推进而不是按 data 推进，否则会卡在那一段上打转
                'next_cursor' => $hasMore && $last ? "{$last['book']}-{$last['para']}" : null,
            ] + $this->progressMeta($scope, $filters, $last),
        ];
    }

    /**
     * 按 filter 收窄的**基础查询**：只有 where，不带 select / distinct / order。
     *
     * 分页取数和 count 两边对它的要求不一样（一个要去重的两列，一个要聚合），
     * 混在一起会拼出 `select distinct book_id, paragraph, count(...)` 这种非法 SQL。
     * 所以基础查询保持干净，各自 clone 之后再加自己那套。
     *
     * @param  array<string, mixed>  $filters
     * @return Builder<Sentence>
     */
    private function scopedQuery(string $channel, array $filters): Builder
    {
        $query = Sentence::query()
            ->where('channel_uid', $channel)
            ->where('strlen', '>', 0);

        if (! isset($filters['book'])) {
            return $query;
        }
        $book = (int) $filters['book'];
        $query->where('book_id', $book);

        if (isset($filters['chapter'])) {
            [$from, $to] = $this->chapterRange($book, (int) $filters['chapter']);

            return $query->whereBetween('paragraph', [$from, $to]);
        }
        if (isset($filters['para'])) {
            $from = (int) $filters['para'];
            $to = (int) ($filters['to'] ?? $from);
            if ($to < $from) {
                throw ValidationException::withMessages(['to' => __('site.invalid_parameter')]);
            }

            return $query->whereBetween('paragraph', [$from, $to]);
        }

        return $query;
    }

    /**
     * 章节起始段落号 → 段落闭区间，长度取 pali_texts 那一行的 chapter_len。
     *
     * @return array{int, int}
     */
    private function chapterRange(int $book, int $chapter): array
    {
        $head = PaliText::where('book', $book)->where('paragraph', $chapter)->first();
        if (! $head) {
            abort(404, __('site.not_found'));
        }

        return [$chapter, $chapter + max(1, (int) $head->chapter_len) - 1];
    }

    /**
     * 游标 `{book}-{para}`：取排在它之后的行。
     *
     * 用行值比较 `(book_id, paragraph) > (?, ?)` 而不是 `book > ? or (book = ? and para > ?)`，
     * 一是短，二是 Postgres 能直接走 (book_id, paragraph, …) 那条复合索引。
     *
     * @param  Builder<Sentence>  $query
     * @return Builder<Sentence>
     */
    private function applyCursor(Builder $query, string $after): Builder
    {
        [$book, $para] = array_map('intval', explode('-', $after, 2));

        return $query->whereRaw('(book_id, paragraph) > (?, ?)', [$book, $para]);
    }

    /**
     * 按原文字节数累加切块，到上限或到 200 段为止，每块至少一段。
     *
     * 纯函数：长度由调用方查好传进来，这里不碰数据库。段落数上限要造上千段才看得
     * 出来，走 HTTP 会真的去渲染每一段，所以单元测试直接对着它测。
     *
     * @param  array<int, array{book: int, para: int}>  $candidates
     * @param  array<int, array<int, int>>  $lengths  book => (para => 字节数)
     * @return array<int, array{book: int, para: int}>
     */
    public function sliceByBytes(array $candidates, array $lengths, int $limit): array
    {
        $bytes = 0;
        $take = 0;
        foreach ($candidates as $coord) {
            $bytes += (int) ($lengths[$coord['book']][$coord['para']] ?? 0);
            $take++;
            if ($bytes >= $limit || $take >= self::MAX_PARAGRAPHS_PER_PAGE) {
                break;
            }
        }

        return array_slice($candidates, 0, $take);
    }

    /**
     * 取这些段落的原文字节数。按书分组、每本用首尾段落号一次查回整段区间，
     * 避免几百个值的 IN 列表；区间里多查回来几行比拼 IN 便宜。
     *
     * @param  array<int, array{book: int, para: int}>  $candidates
     * @return array<int, array<int, int>> book => (para => 字节数)
     */
    private function paragraphLengths(array $candidates): array
    {
        $byBook = [];
        foreach ($candidates as $coord) {
            $byBook[$coord['book']][] = $coord['para'];
        }

        $lengths = [];
        foreach ($byBook as $book => $paras) {
            $lengths[$book] = PaliText::where('book', $book)
                ->whereBetween('paragraph', [min($paras), max($paras)])
                ->pluck('lenght', 'paragraph')
                ->all();
        }

        return $lengths;
    }

    /**
     * 进度用的 total / remaining，**只在带了 book 过滤时给**。
     *
     * 不带过滤时结果集是整个 channel（实测最大的 52 万段），每页 count 一次要
     * 八百毫秒、两百多 MB，不值得。那种场景用 next_cursor 判断结束。
     *
     * @param  Builder<Sentence>  $scope
     * @param  array<string, mixed>  $filters
     * @param  array{book: int, para: int}|null  $last
     * @return array<string, int>
     */
    private function progressMeta(Builder $scope, array $filters, ?array $last): array
    {
        if (! isset($filters['book'])) {
            return [];
        }

        $countIn = fn (Builder $q): int => (int) (
            $q->selectRaw('count(distinct (book_id, paragraph)) as aggregate')->first()?->aggregate ?? 0
        );

        $total = $countIn(clone $scope);
        $remaining = $last === null
            ? 0
            : $countIn($this->applyCursor(clone $scope, "{$last['book']}-{$last['para']}"));

        return ['total' => $total, 'remaining' => $remaining];
    }

    /**
     * 按 include 裁剪输出。display 只要段落 html，sentences 只要句子列表，all 两者都要。
     *
     * @param  array{para: int, display: string, sentences: array}  $paragraph
     * @return array<string, mixed>
     */
    private function filterInclude(array $paragraph, string $include): array
    {
        return match ($include) {
            'sentences' => ['para' => $paragraph['para'], 'sentences' => $paragraph['sentences']],
            'all' => $paragraph,
            default => ['para' => $paragraph['para'], 'display' => $paragraph['display']],
        };
    }
}
