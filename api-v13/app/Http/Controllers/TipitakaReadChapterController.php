<?php

namespace App\Http\Controllers;

use App\Http\Resources\V3Resource;
use App\Models\PaliText;
use App\Models\Sentence;
use App\Services\PaliContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TipitakaReadChapterController extends Controller
{
    /**
     * 字节模式下单块的字节上限。请求超过这个数按这个数算，不报错。
     */
    protected const MAX_BYTES_PER_PAGE = 5000;

    /**
     * 单块最多返回的段落数，两种单位都受它约束。
     *
     * 每段要单独渲染一次，块的成本跟段落数成正比而不是跟字节数成正比。段落模式
     * 直接把 pagesize 压到这个数；字节模式下偈颂体的书平均每段只有 20~30 字节，
     * 光靠字节上限仍可能切出几百段，所以累加时两个上限谁先到就在哪断块。
     */
    protected const MAX_PARAGRAPHS_PER_PAGE = 200;

    /**
     * 按章节读取译文
     *
     * `para` 定位章节（它是章节的起始段落号，章节长度取那一行的 `chapter_len`），
     * `from` 是本次取数的起点游标，从它开始取 `pagesize` 大小的一块。章节不存在、
     * 或该 channel 在这个章节里没有任何译文，返回 404。
     *
     * 取数的段落集合来自 sentences 表里该 channel 实际有译文的段落，不是章节的
     * 全部段落——多数译文是残缺的，按 pali_texts 的整段区间切块会切出空块。
     * 所以 `pagesize` 数的永远是有内容的段落。
     *
     * 游标式分块是为了前端的下载进度条与断点续传，meta 里给三个数：
     * `total_para` 是分母（该 channel 在本章节有多少段），`remaining_para` 是
     * `last_para` 之后本章节内还剩多少段，进度即
     * `(total_para - remaining_para) / total_para`；续传时把上次的
     * `last_para + 1` 当作下次的 `from` 传回来，`remaining_para` 为 0 即取完。
     *
     * `first_para` / `last_para` 是本块实际覆盖的段落闭区间，取自切块结果而不是
     * `data`：渲染为空的段落会被剔出 `data` 但仍算在块内，所以 `data` 可能比这个
     * 区间短，而续传必须按 `last_para` 推进，否则会卡在那一段上原地打转。
     * `first_para` 是游标顺延后的落点，可能大于请求的 `from`（即 `current_para`）。
     *
     * @unauthenticated
     *
     * @queryParam book integer required 典籍 id
     * @queryParam para integer required 章节的起始段落号，决定本次取数的段落区间
     * @queryParam from integer 起点游标，从这一段开始取；不传则等于 para，即从章节
     *             开头取起。可以是章节区间内的任意段落号，落在没有译文的段落上就
     *             顺延到它之后的第一段。超出章节区间、或它之后已经没有译文，
     *             返回 422。
     * @queryParam channel string required 译文 channel 的 uuid
     * @queryParam format string 内容格式。Enum: html,markdown,react,text Default: html
     * @queryParam view string 输出口径：display 整段合并，sentences 逐句，all 两者都给。
     *             Enum: display,sentences,all Default: display
     * @queryParam pagesize integer 每块大小，含义由 unit 决定。上限 unit=para 时 200、
     *             unit=byte 时 5000，超出不报错按上限算，meta.page_size 回实际
     *             生效的值。Default: 10
     * @queryParam unit string 每块大小的单位：para 按段落数，byte 按段落长度累加。
     *             两种单位下每块都至少一段、至多 200 段。Enum: para,byte Default: para
     */
    public function index(Request $request, PaliContentService $paliService)
    {
        $data = $request->validate([
            'book' => 'required|integer',
            'para' => 'required|integer',
            'from' => 'integer|min:1',
            'channel' => 'required|uuid',
            'format' => 'string|in:html,markdown,react,text',
            'view' => 'string|in:display,sentences,all',
            'pagesize' => 'integer|min:1',
            'unit' => 'string|in:para,byte',
        ]);

        return $this->chapter(
            (int) $data['book'],
            (int) $data['para'],
            $data['channel'],
            $data,
            $paliService
        );
    }

    /**
     * 按 id 读取章节
     *
     * 与 index 等价，只是把 book 与 para 合成一个路径参数；游标 `from` 仍走
     * 查询参数。格式不对或 channel 不是 uuid 返回 422。
     *
     * @unauthenticated
     *
     * @urlParam tipitaka_read_chapter string required 章节 id，格式 {book}-{para}。Example: 9002-1
     *
     * @queryParam from integer 起点游标，从这一段开始取；不传则从章节开头取起。
     *             超出章节区间返回 422。
     * @queryParam channel string required 译文 channel 的 uuid
     * @queryParam format string 内容格式。Enum: html,markdown,react,text Default: html
     * @queryParam view string 输出口径：display 整段合并，sentences 逐句，all 两者都给。
     *             Enum: display,sentences,all Default: display
     * @queryParam pagesize integer 每块大小，含义由 unit 决定。上限 unit=para 时 200、
     *             unit=byte 时 5000，超出不报错按上限算，meta.page_size 回实际
     *             生效的值。Default: 10
     * @queryParam unit string 每块大小的单位：para 按段落数，byte 按段落长度累加。
     *             两种单位下每块都至少一段、至多 200 段。Enum: para,byte Default: para
     */
    public function show(Request $request, string $id, PaliContentService $paliService)
    {
        $arrId = explode('-', $id);
        if (count($arrId) !== 2 || ! is_numeric($arrId[0]) || ! is_numeric($arrId[1])) {
            throw ValidationException::withMessages(['id' => __('site.invalid_parameter')]);
        }
        $channel = $request->input('channel');
        if (! Str::isUuid($channel)) {
            throw ValidationException::withMessages(['channel' => __('site.invalid_parameter')]);
        }

        $data = $request->validate([
            'from' => 'integer|min:1',
            'format' => 'string|in:html,markdown,react,text',
            'view' => 'string|in:display,sentences,all',
            'pagesize' => 'integer|min:1',
            'unit' => 'string|in:para,byte',
        ]);

        return $this->chapter(
            (int) $arrId[0],
            (int) $arrId[1],
            $channel,
            $data,
            $paliService
        );
    }

    /**
     * @param  array{from?: int|string, format?: string, view?: string, pagesize?: int|string, unit?: string}  $param
     */
    protected function chapter(
        int $book,
        int $para,
        string $channel,
        array $param,
        PaliContentService $paliService
    ) {
        $format = $param['format'] ?? 'html';
        $view = $param['view'] ?? 'display';
        $unit = $param['unit'] ?? 'para';
        // 超上限不报错，按上限算；meta.page_size 回的是实际生效的值
        $pageSize = min(
            max(1, (int) ($param['pagesize'] ?? 10)),
            $unit === 'byte' ? self::MAX_BYTES_PER_PAGE : self::MAX_PARAGRAPHS_PER_PAGE
        );
        // 游标默认落在章节开头，即第一次调用不必传 para
        $cursor = max(1, (int) ($param['from'] ?? $para));

        $head = PaliText::where('book', $book)->where('paragraph', $para)->first();
        if (! $head) {
            abort(404, __('site.not_found'));
        }
        $to = $para + max(1, (int) $head->chapter_len) - 1;

        // 游标必须落在本章节的段落区间里，越界是调用方算错了，直接报错而不是
        // 悄悄钳到边界——断点续传时静默纠偏会把 bug 藏起来
        if ($cursor < $para || $cursor > $to) {
            throw ValidationException::withMessages(['from' => __('site.invalid_parameter')]);
        }

        // 只取段落号，不取句子内容——最大的章节有一万五千段，整章的句子拉回来没有意义。
        // 整章都要查，因为 total_para 是进度条的分母，不能只算游标之后的部分。
        /** @var array<int, int> $translated 升序段落号 */
        $translated = Sentence::where('book_id', $book)
            ->whereBetween('paragraph', [$para, $to])
            ->where('channel_uid', $channel)
            ->where('strlen', '>', 0)
            ->distinct()
            ->orderBy('paragraph')
            ->pluck('paragraph')
            ->map(fn ($paragraph) => (int) $paragraph)
            ->all();

        $total = count($translated);
        if ($total === 0) {
            abort(404, __('site.not_found'));
        }

        $offset = $this->cursorOffset($translated, $cursor);
        if ($offset === null) {
            throw ValidationException::withMessages(['from' => __('site.invalid_parameter')]);
        }

        // 只有按字节切块才需要原文长度，按段落切块完全不必碰 pali_texts
        $lengths = $unit === 'byte' ? $this->paragraphLengths($book, $translated) : [];
        $slice = $this->slice($translated, $lengths, $pageSize, $unit, $offset);

        $items = [];
        foreach ($slice as $paragraphNumber) {
            $paragraph = $paliService->readParagraph($book, $paragraphNumber, $channel, $format);
            if (empty($paragraph['display'])) {
                continue;
            }
            $items[] = $this->filterView($paragraph, $view);
        }

        return V3Resource::collection($items)->additional(['meta' => [
            // 请求的游标 from 原样回显；实际取到的第一段看 first_para（游标
            // 落在没有译文的段落上时两者不同）
            'current_para' => $cursor,
            // 该 channel 在本章节内有译文的段落总数，进度条的分母
            'total_para' => $total,
            // 每块大小带单位（段落数或字节数），不是 Laravel 的 per_page，
            // 所以单列 page_size + page_size_unit 两个键
            'page_size' => $pageSize,
            'page_size_unit' => $unit,
            'book' => $book,
            'chapter' => $para,
            // 本块实际覆盖的段落区间，两端都是闭区间、都是真实存在的段落号。
            // 取自切出来的这一块，不是取自 data：段落渲染出来是空的会被剔出
            // data，但仍算在本块之内，last_para 照样要跨过它——否则续传会卡在
            // 那一段上原地打转。所以 data 可能比 first_para..last_para 短。
            // first_para 是游标顺延后的落点，可能大于请求的 current_para；
            // last_para + 1 就是下一块要传的 from。
            'first_para' => $slice[0],
            'last_para' => $slice[count($slice) - 1],
            // last_para 之后本章节内还剩多少段，0 即取完；续传把 last_para + 1 当 from 传回
            'remaining_para' => $total - $offset - count($slice),
        ]]);
    }

    /**
     * 游标 $cursor 落在升序段落号里的下标：它本身有译文就是它，否则顺延到之后的
     * 第一段。$cursor 已在章节区间内（调用方先校验过），但可能落在最后一段译文
     * 之后，那时返回 null。
     *
     * @param  array<int, int>  $paragraphs  升序段落号
     */
    protected function cursorOffset(array $paragraphs, int $cursor): ?int
    {
        foreach ($paragraphs as $offset => $paragraph) {
            if ($paragraph >= $cursor) {
                return $offset;
            }
        }

        return null;
    }

    /**
     * 取这些段落的原文字节数。按首尾段落号一次查回整段区间，避免几千个值的
     * IN 列表；区间里没有译文的段落多查回来几行，比拼 IN 便宜得多。
     *
     * @param  array<int, int>  $paragraphs  升序段落号，至少一个
     * @return array<int, int> 段落号 => 原文字节数
     */
    protected function paragraphLengths(int $book, array $paragraphs): array
    {
        return PaliText::where('book', $book)
            ->whereBetween('paragraph', [$paragraphs[0], $paragraphs[count($paragraphs) - 1]])
            ->pluck('lenght', 'paragraph')
            ->all();
    }

    /**
     * 从 $offset 起取一块段落号。按段落数时直接切片，按字节时累加原文长度，
     * 字节数或段落数任一到顶即断块，每块至少一段。
     *
     * @param  array<int, int>  $paragraphs  升序段落号
     * @param  array<int, int>  $lengths  段落号 => 原文字节数，只在 byte 模式用得上
     * @param  string  $unit  para 按段落数，byte 按字节数
     * @param  int  $offset  起点下标，调用方保证在范围内
     * @return array<int, int> 非空
     */
    protected function slice(array $paragraphs, array $lengths, int $limit, string $unit, int $offset): array
    {
        if ($unit !== 'byte') {
            return array_slice($paragraphs, $offset, max(1, min($limit, self::MAX_PARAGRAPHS_PER_PAGE)));
        }

        $bytes = 0;
        $take = 0;
        $count = count($paragraphs);
        while ($offset + $take < $count) {
            $bytes += (int) ($lengths[$paragraphs[$offset + $take]] ?? 0);
            $take++;
            if ($bytes >= $limit || $take >= self::MAX_PARAGRAPHS_PER_PAGE) {
                break;
            }
        }

        return array_slice($paragraphs, $offset, $take);
    }

    /**
     * 按 view 裁剪输出。display 只要段落 html，sentences 只要句子列表，all 两者都要。
     *
     * @param  array{para: int, display: string, sentences: array}  $paragraph
     */
    protected function filterView(array $paragraph, string $view): array
    {
        return match ($view) {
            'sentences' => ['para' => $paragraph['para'], 'sentences' => $paragraph['sentences']],
            'all' => $paragraph,
            default => ['para' => $paragraph['para'], 'display' => $paragraph['display']],
        };
    }
}
