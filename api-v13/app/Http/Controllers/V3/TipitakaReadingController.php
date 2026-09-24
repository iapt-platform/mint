<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\TipitakaReadingRequest;
use App\Http\Resources\V3\ReadParagraphResource;
use App\Services\V3\TipitakaReadingService;

/**
 * 三藏译文的阅读与下载：按 channel 取已翻译的段落，游标式分块。
 *
 * 路径开头是「领域-功能」而不是资源坐标：`channel + book + para` 在 wikipali 里是
 * 通用坐标，sentences、wbw、批注、进度全都用它，放在路径开头会让每个新能力来抢
 * 同一个前缀，也看不出这条 API 到底干什么。所以路径是 `/v3/tipitaka-reading/{channel}`，
 * 坐标降成 filter。
 *
 * 曾经叫 `/v3/tipitaka-read-para` 与 `/v3/tipitaka-read-chapter`，中间短暂叫过
 * `/v3/channels/{channel}/books/{book}/…`，见 CLAUDE.md 的「v2 → v3 对照表」。
 *
 * **这不是 v2 `chapter-content` / `paragraph-content` 的替代品**：那两个支持多
 * channel 与 edit 模式、返回 sentenceIds 给编辑器；这里只做单 channel 的只读渲染。
 */
class TipitakaReadingController extends Controller
{
    public function __construct(private readonly TipitakaReadingService $reading) {}

    /**
     * 读取译文
     *
     * 取这个 channel 已经翻译的段落，按 (book, para) 升序，用游标分块。
     * 不带任何查询串就是从头取整个 channel——下载场景要的就是这个；
     * 阅读场景用 `book` + `chapter` 或 `book` + `para`/`to` 把范围收窄。
     *
     * 续传把 `meta.next_cursor` 原样传回 `after` 即可，它为 null 表示取完了。
     * **不要自己拼游标**：它的内部形式（`{book}-{para}`）是实现细节，会变。
     *
     * `total` / `remaining` 只在带了 `book` 过滤时才给：不带过滤时结果集是整个
     * channel（实测最大的有 52 万段、跨 217 本书），每翻一页都去 count 一次不划算。
     * 无过滤时用 `next_cursor` 是否为 null 判断结束，不要指望百分比。
     *
     * @unauthenticated
     *
     * @urlParam channel string required 译文 channel 的 uuid
     *
     * @queryParam book integer 典籍 id。与 chapter / para / to 一起用时必填。Example: 9002
     * @queryParam chapter integer 章节起始段落号，服务端按 chapter_len 展开成段落区间。
     *             与 para / to 互斥
     * @queryParam para integer 起始段落号
     * @queryParam to integer 结束段落号（含）。不传则等于 para，即只取一段
     * @queryParam after string 游标，取 meta.next_cursor 原样回传。Example: 9002-15
     * @queryParam page_size integer 每块大小，含义由 unit 决定。上限 unit=para 时 200、
     *             unit=byte 时 5000，超出不报错按上限算，meta.page_size 回实际生效的值。
     *             Default: 10
     * @queryParam unit string 每块大小的单位：para 按段落数，byte 按段落长度累加。
     *             两种单位下每块都至少一段、至多 200 段。Enum: para,byte Default: para
     * @queryParam format string 内容格式。Enum: html,markdown,react,text Default: html
     * @queryParam include string 输出口径：display 整段合并，sentences 逐句，all 两者都给。
     *             不叫 view 是因为 v2 的 `view=` 是必填互斥的业务路径开关，名字撞上会误导。
     *             Enum: display,sentences,all Default: display
     *
     * @responseStatus 404 指定的 chapter 在 pali_texts 里不存在
     *
     * @meta page_size integer 每块大小（按 unit 计）
     * @meta page_size_unit string 每块大小的单位：para 或 byte
     * @meta next_cursor string 下一块的游标，原样传回 after；为 null 表示取完
     * @meta total integer 过滤范围内已翻译的段落总数。仅在带了 book 过滤时出现
     * @meta remaining integer 本块之后过滤范围内还剩多少段。仅在带了 book 过滤时出现
     */
    public function __invoke(TipitakaReadingRequest $request, string $channel)
    {
        $page = $this->reading->page($channel, $request->validated());

        return ReadParagraphResource::collection($page['items'])
            ->additional(['meta' => $page['meta']]);
    }
}
